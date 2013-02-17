<?php

include('bootstrap.php');

function request($request) {

	if(isset($request['model']) && isset($request['action'])) {
		$model = $request['model'];
		$action = $request['action'];
		$params = $request;
		unset($params['model']);
		unset($params['action']);
	}

	$model_file = MODEL_DIR.$model.'.php';

	if(file_exists($model_file)) {

		// include model
		include($model_file);

		// instantiate the model
		$model_name = camel_case($model.'Model');
		$model_obj = new $model_name;

		// call action function, returning into data
		if(method_exists($model_obj, $action)) {
			$data = $model_obj->{$action}($params);
		} else {
			$data = array('error'=>"Action $action for model $model does not exist");
		}

	} else {
		$data = array('error'=>"Model $model does not exist");
	}

	return $data;

}

$data = request($_GET);

header('Content-type: application/json');

$json = json_encode($data);

echo isset($_GET['callback'])
	? "{$_GET['callback']}($json)"
	: $json;
