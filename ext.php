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

function json($o){
	header("content-type: application/json");
	die(json_encode($o));
}

function isVowel($c){
	//Can find character c in vowel array?
	return strpos('aeiou', $c) !== false;
}

function setError($text){
	global $ERROR;
	$ERROR = "\n<p>*$text</p>\n";
	return $ERROR;
}

function randomFrom($array){
	return $array[array_rand($array)];
}

?>