<?php

include('request.php');
include('controller.php');

if(!defined('BASE_DIR')) {
	throw new Exception('BASE_DIR undefined!');
	exit;
}

define('CONTROLLER_DIR', 
	BASE_DIR.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR);

class Dispatcher {

	public function run($webapp) {

		$request = new Request($_SERVER["REQUEST_URI"]);
		$this->dispatch($request, $webapp->view);

	}

	public function dispatch($request, $view) {


		$controller_file = CONTROLLER_DIR.$request->controller.'.php';

		if(!file_exists($controller_file)) {
			
			array_unshift($request->params, $request->action);
			$request->action = $request->controller;			

			$request->controller = 'default';
			$controller_file = CONTROLLER_DIR.$request->controller.'.php';

		}

		if(!file_exists($controller_file)) {
			// no default controller file
			echo "No default controller found at '/controller/default.php'";
			exit;
		}

		// include model
		include($controller_file);

		// instantiate the controller
		$controller_name = camel_case($request->controller.'_Controller');
		$c = new $controller_name($request, $view);

		// call action function, returning into data
		if(!method_exists($c, $request->action)) {
			array_unshift($request->params, $request->action);
			$request->action = 'index';
		}

		// use switch instead of call_user_func_array because it's faster
		switch(count($request->params)) { 
      case 0: $c->{$request->action}(); break; 
      case 1: $c->{$request->action}($request->params[0]); break; 
      case 2: $c->{$request->action}($request->params[0], $request->params[1]); break; 
      case 3: $c->{$request->action}($request->params[0], $request->params[1], $request->params[2]); break; 
      case 4: $c->{$request->action}($request->params[0], $request->params[1], $request->params[2], $request->params[3]); break; 
      case 5: $c->{$request->action}($request->params[0], $request->params[1], $request->params[2], $request->params[3], $request->params[4]); break; 
      default: call_user_func_array(array($c, $request->action), $p);  break; 
    } 

	}

}
