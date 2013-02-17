<?php

class Request {

	public function is_post() {
		return ($_SERVER['REQUEST_METHOD'] == 'POST');
	}

	public function param($name, $default=null) {

		if ($this->is_post()) {
			if(isset($_POST[$name])) {
				return $_POST[$name];
			}
		} else {
			if(isset($_GET[$name])) {
				return $_GET[$name];
			}
		}

		return $default;

	}

	public function referrer() {
		return $_SERVER['HTTP_REFERER'];
	}

	public function to_string() {
		return "Request: $this->controller > $this->action (".
			implode(", ", $this->params) . ")";
	}

	public function css($file) {
		return $this->webpath . 'css/'. $file;
	}

	public function img($file) {
		return $this->webpath . 'img/'. $file;
	}

	public function url($uri) {
		if(preg_match('/^\//', $uri)) {
			// absolute as opposed to relative to controller
			$uri = substr($uri, 1);			
		} else {
			$uri = ( 
				$this->controller == 'default' ? 
				'' : 
				$this->controller . '/'
			) . $uri;
		}

		return $this->webpath . $uri;
	}

	public function __construct($uri) {
		$this->get = $_GET;
		$this->post = $_POST;
		$this->request_uri = $uri;
		$this->parse_uri($uri);

		//$_SESSION['CURRENT_URL'] = $_SESSION['LAST_URL'];

	}

	public function parse_uri($uri) {

		// remove the script name from the URI
		$parts = explode('/', $_SERVER["SCRIPT_NAME"]);
		$script = array_pop($parts);

		$path = str_replace($script, '', $_SERVER["SCRIPT_NAME"]);
		$this->webpath = $path;
		$uri = str_replace($path, '', $uri);

		// remove any trailing slashes
		$uri = preg_replace('/\/$/', '', $uri);
		$uri = preg_replace('/\/\?/', '?', $uri);

		list($uri, $this->query) = explode('?', $uri.'?');

		// create request parts
		$parts = explode('/', $uri, 3);

		$this->controller = 'default';
		$this->action = 'index';
		$params = '';

		if(count($parts) > 0 && isset($parts[0]) && $parts[0] != '') {
			$this->controller = $parts[0];
		}

		if(count($parts) > 1) {
			$this->action = $parts[1];
		}

		if(count($parts) > 2) {
			$params = $parts[2];
		}

		// create query part
		$params = explode( '/', $params);
		//$query = '';
		if($params[count($params)-1] == '') {
			$params = array();
		}

		//if(count($params) > 0) {
		//	$parts = explode('?', $params[count($params)-1]);
		//	$params[count($params)-1] = $parts[0];
		//	if(count($parts) > 1) {
		//		$query = $parts[1];
		//	}
		//}

		$this->params = $params;
		//$this->query = $query;

	}

}
