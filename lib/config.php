<?

include_once('util.php');

$_CONFIG = false;

function config($k) {
	global $_CONFIG;	
	if(!$_CONFIG) {
		load_config();
	}
	return $_CONFIG[$k];	
}

function load_config() {
	global $_CONFIG;
	$_CONFIG = json_config(CONFIG_DIR . 'app.json');
}

?>