<?php

// the view object handles all communication with the 
// web browser and user

include_once('template.php');
include_once('url.php');
include_once('html_controls.php');

class View {
	
	public $template;
	private $flash_message;
	private $flash_type;
	private $base;
	private $sub_base;
	private $_stash;
	
	public function __construct($template, $base=false) {
		$this->template = $template;
		session_start();
		if(isset($_SESSION['_VIEW_FLASH_MESSAGE'])) {
			$this->flash_message = $_SESSION['_VIEW_FLASH_MESSAGE'];
			unset($_SESSION['_VIEW_FLASH_MESSAGE']);
		}
		if(isset($_SESSION['_VIEW_FLASH_TYPE'])) {
			$this->flash_type = $_SESSION['_VIEW_FLASH_TYPE'];
			unset($_SESSION['_VIEW_FLASH_TYPE']);
		}

		if($base) {
			$this->base = $base;
		} else {
			$this->base = false;
		}

		$this->sub_base = false;

		$this->_stash = array();
	}
	
	public function has_flash() {
		if(isset($this->flash_message) && $this->flash_message) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_flash_type() {
		return $this->flash_type;
	}
	
	public function get_flash() {
		return $this->flash_message;
	}
	
	public function clear_flash() { 
		unset($_SESSION['_VIEW_FLASH_TYPE']);
		unset($_SESSION['_VIEW_FLASH_MESSAGE']);
	}
	
	public function save_flash() {
		$_SESSION['_VIEW_FLASH_TYPE'] = $this->flash_type;
		$_SESSION['_VIEW_FLASH_MESSAGE'] = $this->flash_message;
	}	
	
	public function flash($message, $type='notice') {
		$this->flash_message = $message;
		$this->flash_type = $type;
		$this->save_flash();
	}

	public function stash($name, $value) {
		$this->_stash[$name] = $value;
	}

	public function set_sub_base($sub_base) {
		$this->sub_base = $sub_base;
	}
	
	public function display($template, $vars=array()) {
		global $user;
		$vars['view'] =& $this;
		$vars['user'] =& $user;
		if($this->has_flash()) {
			$vars['flash'] = array(
				'message' => $this->flash_message,
				'type' => $this->flash_type
			);
			$this->clear_flash();
		}

		foreach($this->_stash as $k => $v) {
			$vars[$k] = $v;
		}

		if($this->base) {

			if($this->sub_base) {
				$vars['sub_content'] = $this->template->process($template, $vars, true);
				$vars['content'] = $this->template->process($this->sub_base, $vars, true);
			} else {
				$vars['content'] = $this->template->process($template, $vars, true);				
			}
			echo $this->template->process($this->base, $vars, true);
		} else {
			echo $this->template->process($template, $vars, true);
		}
	}

	public function lookup($name, $set, $selected='') {
		$id = $name;

		if(preg_match('/\]$/', $id)) {
			$id	= preg_replace('/\[/', '_', $id);
			$id	= preg_replace('/\]/', '', $id);
		}

		$output = "<select name='$name' id='$id'>\n";
		foreach($set as $k => $v) {
			$sel = ($k === $selected) ? " selected='selected'" : '';
			$output .= "<option value='$k'$sel>$v</option>\n";
		}
		$output .= "</select>";
		return $output;
	}

	public function datebox($params) {
		
		if(!isset($params['id'])) $params['id'] = $params['name'];

		if(preg_match('/\]$/', $params['id'])) {
			$params['id']	= preg_replace('/\[/', '_', $params['id']);
			$params['id']	= preg_replace('/\]/', '', $params['id']);
		}

		if(!isset($params['value'])) $params['value'] = '';

		return "
		<div class='date_field_container'>
		<input type='hidden' name='${params['name']}' id='${params['id']}' value='${params['value']}'/>
		<span class='date_field' id='${params['id']}_display'>${params['value']}&nbsp;</span><span id='${params['id']}_trigger' class='calicon'>&nbsp;</span>
		<script>
			Calendar.setup(
			{
				displayArea : '${params['id']}_display',
				inputField  : '${params['id']}',         // ID of the input field
				ifFormat    : '%d-%b-%Y',    // the date format
				daFormat    : '%d-%b-%Y',    // the date format
				button      : '${params['id']}_trigger',       // ID of the button
				weekNumbers : false
			}
			);
		</script>&nbsp;<!-- fixes empty div float problem -->
		</div>
		";
	}


	public function forward($location) {
		$url = $location;
		if(preg_match('/:\/\//', $location)) {
			$url = url($location);
		}
		header('Location: '. $url);
		echo " ";
		exit;
	}
	
	public function send_json($var) {
		header('Content-type: application/json');
		header('Cache-control: no-cache');
		header('Pragma: no-cache');
		echo json_encode($var);
	}
}

?>