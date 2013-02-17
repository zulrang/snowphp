<?php
include_once('json.php');

if (!function_exists('to_currency')) {
	function to_currency($number) {
		$number = sprintf('%.2f', $number);
		while(true) {
			$replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
			if ($replaced != $number) {
				$number = $replaced;
			} else {
				break;
			}
		}
		$number = preg_replace('/^-(.*)$/', '($1)', $number);
		return '$'.$number;
	}
}

if (!function_exists('disable_magic_quotes')) {
	function disable_magic_quotes() {
		if (get_magic_quotes_gpc()) {
			$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			while (list($key, $val) = each($process)) {
				foreach ($val as $k => $v) {
					unset($process[$key][$k]);
					if (is_array($v)) {
						$process[$key][stripslashes($k)] = $v;
						$process[] = &$process[$key][stripslashes($k)];
					} else {
						$process[$key][stripslashes($k)] = stripslashes($v);
					}
				}
			}
			unset($process);
		}
	}
}

if (!function_exists('str_repeat_ext')) {
	function str_repeat_ext($input, $multiplier, $separator='')
	{
		return $multiplier==0 ? '' : str_repeat($input.$separator, $multiplier-1).$input;
	}
}

if (!function_exists('json_config')) {
	function json_config($filename) {
		if(is_readable($filename)) {
			return JSON::Decode(file_get_contents($filename), true);
		} else {
			throw new InvalidArgumentException("$filename does not exist.");
		}
	}
}

if (!function_exists('title_case')) {
	function title_case($str) {
		return ucwords(strtolower($str));
	}
}

// converts test_class_model to TestClassModel
if (!function_exists('camel_case')) {
	function camel_case($str) {
		$str = preg_replace('/_/', ' ', $str);
		$str = ucwords(strtolower($str));
		return preg_replace('/ /', '', $str);
	}
}

if (!function_exists('create_token')) {
	function create_token() {
		return sha1(uniqid(mt_rand(), true));
	}
}

if (!function_exists('neat_trim')) {
	function neat_trim($str, $n, $delim=' ...') { 
	   $len = strlen($str); 
	   if ($len > $n) { 
		   preg_match('/(.{' . $n . '}.*?)\b/', $str, $matches); 
		   return rtrim($matches[1]) . $delim; 
	   } 
	   else { 
		   return $str; 
	   } 
	} 
}
?>