<?php

function from_request($k){
	if (isset($_POST[$k])){
		return $_POST[$k];
	}
	elseif (isset($_GET[$k])){
		return $_GET[$k];
	}
	return null;
}

function http_fatal_400($msg){
	header("HTTP/1.1 400 Bad Request [$msg]");
	die();
}
function http_fatal_500($msg){
	header("HTTP/1.1 500 Server fail [$msg]");
	die();
}
function http_201($type, $id){
	header("HTTP/1.1 201 Created [$type $id]");
	echo $id;
	die();
}
function json($o){
	header("content-type: application/json");
	die(json_encode($o));
}

?>