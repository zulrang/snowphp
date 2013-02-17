<?php

include_once('model.php');
include_once('html_helper.php');

class Controller {

	private $_models;

	public function __construct($request, $view) {
		$this->request = $request;
		$this->view = $view;
		$this->view->stash('request', $this->request);
		$this->view->stash('h', new HtmlHelper($this->request));

		$full_name = get_class($this);
		$name = preg_replace('/Controller$/', '', $full_name);
		$this->name = ($name == '') ? 'Default' : $name;
		$this->lc_name = strtolower($this->name);

		// init model cache
		$this->_models = array();
	}

	public function index() {
		echo $this->request->to_string();
	}

	public function model($name) {
		
		return Model::_get_model($name);
		
	}

}
