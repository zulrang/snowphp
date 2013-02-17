<? 

// basic url handling functions

function host() {
	if(isset($_ENV['HTTP_HOST'])) {
		return $_ENV['HTTP_HOST'];
	} elseif(isset($_ENV['SERVER_NAME'])) {
		return $_ENV['SERVER_NAME'];
	} else {
		return 'nasicweb.nasic.wpafb.af.mil';
	}  
}

function protocol() {
	return 'https';
}

function url($location) {
	global $_WEBDIR;
	$url = protocol() . '://' . host() . $_WEBDIR . '/';
	if(isset($location)) { 
		$url .= $location;
	}
	return $url;
}

?>