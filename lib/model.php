<?php

include_once('db.php');

define('MODEL_DIR', 
	BASE_DIR.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR);

class Model {

	// database handle
	public $db;
	protected static $_models = array();

	public function use_db($db) {
		$this->db = $db;
	}

	public function init() {
		if(!empty($this->db_name)) {
			$this->db = db($this->db_name);
		}
	}

	public function db() {
		if(empty($this->db)) {
			$this->init();
		}
		return $this->db;
	}

	public function __construct() {

		$full_name = get_class($this);
		$this->name = preg_replace('/Model$/', '', $full_name);
		$this->lc_name = strtolower($this->name);

	}

	public static function _get_model($name) {

		if(!isset(self::$_models[$name])) {
			// init model
			$model_filename = MODEL_DIR.$name.'.php';
			if(is_file($model_filename)) {
				
				include($model_filename);
				// instantiate the model
				$model_name = camel_case($name).'Model';
				$model = new $model_name();

				// update cache
				self::$_models[$name] = $model;

			} else {
				throw new Exception("Invalid model name");
			}
		}

		return self::$_models[$name];
	}
}
