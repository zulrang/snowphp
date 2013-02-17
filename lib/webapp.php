<?php

include('dispatcher.php');
include('view.php');

class WebApp {
	public function __construct() {
		$this->dispatcher = new Dispatcher();
		$this->view = new View(new Template(), 'base.php');
	}

	public function run() {
		$this->dispatcher->run($this);
	}
}
