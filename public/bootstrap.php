<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_DIR', realpath(dirname(dirname(dirname(__FILE__) . '..'))));

define('CONFIG_DIR', 
	BASE_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR);
define('SNOW_DIR', 
	BASE_DIR.DIRECTORY_SEPARATOR.'snow'.DIRECTORY_SEPARATOR);
define('VIEW_DIR', 
	BASE_DIR.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR);
define('INCLUDE_DIR', 
	BASE_DIR.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);

include(SNOW_DIR.'lib/util.php');
disable_magic_quotes();

include(SNOW_DIR.'lib/webapp.php');

