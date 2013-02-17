<?

// the Template class handles processing php templates

define("TEMPLATE_DIR", BASE_DIR . '/view/');

class Template {
	
	public function __construct() {
	}

	public function get_filename($template) {
		$path_parts = pathinfo($template);
		$filename = TEMPLATE_DIR;
		if($path_parts['dirname'] != '.') {
			$filename = $filename . $path_parts['dirname'] . '/';
		}
		$filename = $filename . $path_parts['filename'] . '.php';

		return $filename;
	}
	
	public function process($template, $vars, $suppress_comments=true) {
		
		$filename = $this->get_filename($template);

		if (is_file($filename)) {

			$_template_output = '';

			if(!$suppress_comments) { $_template_output .= "\n<!-- start $template -->\n"; };
			extract($vars);
			unset($vars);
			ob_start();
			require $filename;
			$_template_output .= ob_get_clean();
			if(!$suppress_comments) { $_template_output .= "\n<!-- end $template -->\n"; };

			return $_template_output;
		} else {
			throw new Exception("File doesn't exist: " . $filename);
		}
		
		return false;
	}
}

?>