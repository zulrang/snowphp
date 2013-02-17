<?php

class HtmlHelper {

	public function __construct($request) {
		$this->request = $request;
	}

	public function css($file) {
		return $this->request->webpath . 'css/'. $file;
	}

	public function js($file) {
		return $this->request->webpath . 'js/'. $file;
	}

	public function img($file) {
		return $this->request->webpath . 'img/'. $file;
	}

	public function url($location) {
		return $this->request->url($location);
	}

	public function incl($path) {
		include("../view/include/".$path);
	}

	public function rowalt() {
		static $n;
		$n = $n % 2 + 1;
		return $n;
	}

	public function link_button($caption, $location, $icon) {
		
		$url = $this->url($location);

		return "<a class='button' href='$url'><span>".
					 "<img src='/fam/$icon.png'/>$caption</span></a>";

	}
}