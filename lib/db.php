<?php
// db.php
// version: 1.3
// updated: 2012 Sep 11
// by: ft4rwco
// 
// 2012 Sep 11 - added instance name to PDO object
// 2012 Aug 28 - added getval
//             - added oracle query builder (alpha)
//             - built check for large varchar into bindArrayValue
// 2012 Aug 16 - Changed configuration to INI
// 2012 Mar 08 - added bindArrayValue to MyPDOStatement
//             - Removed error reporting
// 2012 Aug 16 - Changed configuration to INI

define('DB_VERSION', 1.3);

require_once('util.php');
require_once('db_pager.php');

if(!defined('CONFIG_DIR')) {
	define('CONFIG_DIR', dirname(__FILE__).'/../config/');
}

if(!defined('BASE_DIR')) {
	define("BASE_DIR", realpath(dirname(dirname(__FILE__) . '../')));
}

class MyPDO extends PDO
{
    protected $qcache; // prepared query cache
	protected $query;
	
	public static $DB_CONFIG_FILENAME = 'database.json';
	public static $DATABASES;
	public static $DATABASE_CONFIG;
	public $lastID;
	public $driver;
	public $instance_name;
	
	public static $keywords_lc = array(
		' select ',
		' update ',
		' delete ',
		' from ',
		' where ',
		' join ',
		' in ',
		' group by ',
		' desc ',
		' asc ',
		' view ',
		' table ',
		' drop ',
		' or ',
		' and ',
		' replace '
	);
	
	public static $keywords_uc = array(
		' SELECT ',
		' UPDATE ',
		' DELETE ',
		' FROM ',
		' WHERE ',
		' DESC ',
		' IN ',
		' GROUP BY ',
		' JOIN ',
		' ASC ',
		' VIEW ',
		' TABLE ',
		' DROP ',
		' OR ',
		' AND ',
		' REPLACE '
	);

	public function log($str) {
		
		fputs($this->log_fh, "[".date('r')."]: ".$str."\n");
	}
	
	public function __construct($dsn, $username=null, $password=null, $driver_options=null) {
		$this->log_fh = fopen(BASE_DIR.'/logs/db.log','a');
		$this->log('==================== NEW REQUEST ======================');
		parent::__construct($dsn, $username, $password, $driver_options);		
	}
	
	public function __destruct() {
		fclose($this->log_fh);
		//parent::__destruct();
	}
	
	public function prepare($statement, $driver_options = array() ) {
		$this->query = $statement;
		return parent::prepare($statement, $driver_options);
	}
	
	public function last_query() {
		return $this->query;
	}

  // Usage: $dbobject->getrow($sql, $args...)
  // returns the first row
   public function getrow($sql, $args=array()) {
		$rs = $this->pquery($sql, $args);
		if(is_object($rs)) {
			return $rs->fetch();
		} else {
			return false;
		}
	}

	// Usage: $dbobject->getval($sql, $args...)
	// returns the first column of the first row
	public function getval($sql, $args=array()) {
		$rs = $this->pquery($sql, $args);
		if(is_object($rs)) {
			return $rs->fetchColumn();
		} else {
			return false;
		}
	}

  // Usage: $dbobject->pquery($sql, $args...)
  // returns a PDOStatement object
   public function pquery($sql, $args=array()) {

        if(isset($this->qcache[$sql]) && is_object($this->qcache[$sql]))
        {
            $query = $this->qcache[$sql];
        } else {
            $query = $this->prepare($sql);
            $this->qcache[$sql] = $query;
        }

        if($query->execute($args)) {
       		return $query;
		}// else { // turn on exceptions
		//	$error = $query->errorInfo();
		//	//die(print_r($error, true));
		//	throw new PDOException('SQL ERROR: ' . $error[2] . "DATA: " . print_r($args, true));
		//}
  }
	
	public function pquery_by_field($sql, $field, $args=array()) {
		$q = $this->pquery($sql, $args);
		$table = array();
		while($row = $q->fetch()) {
			$table[$row[$field]] = $row;
		}
		return $table;
	}
	
	public function pquery_by_id($sql, $args=array()) {
		return $this->pquery_by_field($sql, 'id', $args);
	}
	
	public function log_query($sql, $args) {
		$sql = preg_replace('/\s+/', ' ', $sql);
		/*$sql = str_replace(MyPDO::$keywords_lc, MyPDO::$keywords_uc, $sql);
		$num = count($args);
		if($num > 0) {
			for($i=0;$i<$num;$i++) {
				if(strval(floatval($args[$i])) !== $args[$i]) {
					$args[$i] = "'".$args[$i]."'";
				}
			}
			$search = array_fill(0, $num, '/\?/');
			$sql = preg_replace($search, $args, $sql);
		}*/
		$this->log($sql);
		$this->log(implode(', ', $args));
	}
	
	public function pquery_array($sql, $args=array()) {
		$q = $this->pquery($sql, $args);
		$table = array();
		while($row = $q->fetch()) {
			$table[] = $row;
		}
		return $table;
	}

	public function table_lookup($table_name, $name_field) {
		$q = $this->query("select id, $name_field from $table_name order by $name_field");
		$table = array();
		while($row = $q->fetch()) {
			$table[$row['id']] = $row[$name_field];
		}
		return $table;
	}

	public function get_pager($sql, $args=array()) {

		$pager = new DBPager($this, $sql, $args);
		return $pager;
		
	}

/*
	public function pquery_lookup($sql, $args=array()) {
		$q = $this->pquery($sql, $args);
		$table = array();
		while($row = $q->fetch()) {
			$table[$row['id']] = $row['name'];
		}
		return $table;
	}
	*/
}

class MyPDOStatement extends PDOStatement {
	
	protected $pdo;
	
	protected function __construct($pdo) {
		$this->pdo = $pdo;
	}

		/**
	 * @param array $array : associative array containing the values ​​to bind
	 * @param array $typeArray : associative array with the desired value for its corresponding key in $array
	 * @param string $table : table to check for large varchars against
	 * */
	function bindArrayValue($array, $typeArray = false, $table=null)
	{
		$numBound = 0;
		$boundParams = array();

    foreach($array as $key => $value)
    {
      if($typeArray and count($typeArray) > 0) {
        $this->bindValue(":$key",$value,$typeArray[$key]);
      	$numBound++;
      }
      else
      {
        if(is_int($value))
          $param = PDO::PARAM_INT;
        elseif(is_bool($value))
          $param = PDO::PARAM_BOOL;
        elseif(is_null($value))
          $param = PDO::PARAM_NULL;
        elseif(is_string($value))

        	// check for large varchar field > 1333
        	if(!empty($table) && $this->pdo->driver == 'oci') {
        		$data_length = $this->pdo->getval(
        			"select data_length 
        			 from user_tab_columns 
        			 where table_name = ? and 
        			       column_name = ?",
        			array(strtoupper($table), strtoupper($key))
        		);

        		if($data_length >= 1333) {
        			$boundParams[] = $value;
        			$this->bindParam(":$key",$boundParams[count($boundParams)-1],PDO::PARAM_STR, $data_length);
        			$param = false;
        			$numBound++;

        		} else {
        			$param = PDO::PARAM_STR;
        		}

        	} else {
          	$param = PDO::PARAM_STR;
          }
        else
          $param = FALSE;
            
        if($param) {
          $this->bindValue(":$key",$value,$param);
        	$numBound++;
        }
      }
    }

    return $numBound;
	}
	
	public function fetchFirst() {
		$row = $this->fetch( PDO::FETCH_NUM );
		return $row[0];
	}
	
	public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null) {
		
		$row = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
		
		if(is_array($row)) {
			foreach($row as $key => $value) {
				if(strval(intval($value)) === $value) {
					$row[$key] = intval($value);
				} elseif(strval(floatval($value)) === $value || (strval(floatval($value)) === "0".$value && !("0" === "0".$value))) {
					$row[$key] = floatval($value);
				}
			}
		}
		
		return $row;
	}
	
	public function execute($args = null) {
		if(is_array($args)) {
			$this->pdo->log_query($this->queryString, $args);
			return parent::execute($args);
		} else {
			$args = func_get_args();
			if(count($args) > 0) {
				$this->pdo->log_query($this->queryString, $args);
				return eval('return parent::execute($args);');
			} else {
				$this->pdo->log_query($this->queryString, array());
				return parent::execute();
			}
		}
	}
	
	public function last_query() {
		return $this->pdo->last_query();
	}	
}

MyPDO::$DB_CONFIG_FILENAME = CONFIG_DIR . 'database.ini';



function get_db_config($name) {
	if(!MyPDO::$DATABASE_CONFIG) {
		MyPDO::$DATABASE_CONFIG = parse_ini_file(MyPDO::$DB_CONFIG_FILENAME, true);
	}
	return MyPDO::$DATABASE_CONFIG[$name];
}

function db_connect($name) {
	$config = get_db_config($name);
	
//	$db =& NewADOConnection('oci8po');
//	$db->NLS_DATE_FORMAT = 'DD-Mon-YY';
//	$db->PConnect($config['instance'], $config['username'], $config['password']);
//	$db->SetFetchMode(ADODB_FETCH_ASSOC);

	if(preg_match('/sqlite/', $config['dsn'])) {

		$parts = explode(':', $config['dsn']);
		$parts[1] = BASE_DIR . DIRECTORY_SEPARATOR .  $parts[1];
		$config['dsn'] = implode(':', $parts);

		$db = new MyPDO($config['dsn']);
		$db->driver = 'sqlite';
		
	} else {
		$db = new MyPDO($config['dsn'], $config['username'], $config['password']);
		$db->driver = 'oci';
		preg_match('/dbname=(.*?);?/', $config['dsn'], $matches);
		$db->instance_name = $matches[1];

	}

	foreach($config as $k => $v) {
		if(preg_match('/^ATTR/', $k)) {
			$db->setAttribute( constant("PDO::{$k}"), constant("PDO::{$v}"));	
		}
	}

	$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('MyPDOStatement', array($db)));
	
	if(preg_match('/oci/', $config['dsn'])) {
		$db->exec("ALTER SESSION SET NLS_DATE_FORMAT = 'DD-Mon-YY'");
	}
	
	return $db;
}

$_DATABASES = array();

function db($name='default') {
	global $_DATABASES;
	if(!isset($_DATABASES[$name]) || is_null($_DATABASES[$name])) {
		$_DATABASES[$name] =& db_connect($name);
	}
	return $_DATABASES[$name];
}

function create_insert_sql($table_name, $fields) {
	$sql = "insert into " . $table_name;
	if(is_array($fields) && sizeof($fields) > 0) {
		$sql .= " (" . implode(", ", $fields) . ")";		
		$sql .= " values (" . str_repeat_ext('?', sizeof($fields), ',') . ")";
	} else {
		$sql .= " values (" . str_repeat_ext('?', $fields, ',') . ")";
	}
	return $sql;
}

function create_update_sql($table_name, $fields) {
	$sql = "update " . $table_name . " set";
	if(is_array($fields) && sizeof($fields) > 0) {
		$sql .= " " . implode(" = ?, ", $fields) . " = ? ";		
	}
	return $sql;
}

function create_update_bind_sql($table_name, $fields) {
	$sql = "update " . $table_name . " set ";
	$sets = array();
	foreach($fields as $field) {
		$sets[] = "$field = :$field";
	}
	if(is_array($fields) && sizeof($fields) > 0) {
		$sql .= implode(", ", $sets);		
	}
	return $sql;
}

function makeBindVars($item) {
	return ":$item";
}


?>