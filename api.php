<?php

//Extensions
require 'ext.php';
//Pre-loaded grammar
require 'grammar.php';

//Plain text content-type? 
if (from_request('text')){
	header('content-type: text/plain');
}
if (from_request('debug')){
	var_dump($grammar);
}
	
//Query from request
$q = from_request('q');
$result = parse($q);

function randomFrom($range){
	return $range[array_rand($range)];
}

function parse($v){
	//While string contains open square
	$open = strpos($v, '[');
	while ($open !== false){
		//echo "(v: '$v')\n";
		$close = strpos($v, ']');
		if ($close === false){
			return "\n*Mismatched bracket from char $open\n";
		}
		$expression = substr($v, $open, $close + 1 - $open);
		//echo "--$expression--\n";
		
		$resolution = resolveBracket($expression);
		//echo "(Resolution: '$resolution')\n";
		$v = str_replace($expression, $resolution, $v);
		//echo "(New v: '$v')\n";
		//Get next open for while loop
		$open = strpos($v, '[');
	}
	
	$open = strpos($v, 'þ');
	while ($open !== false){
		$close = strpos($v, 'ÿ');
		if ($close === false){
			return "\n*Bad a/an matching. Developer error.";
		}
		$expression = substr($v, $open, $close + 1 - $open);
		$nextChar = trim(substr($v, $close + 1))[0];
		//die( "\n~~$nextChar~~");
		$resolution = isVowel($nextChar) ? 'an' : 'a';
		$v = str_replace($expression, $resolution, $v);
		$open = strpos($v, 'þ');
	}
	
	return $v;
}

function resolveBracket($v){

	global $grammar;
	
	//Remove square brackets
	$v = str_replace('[', '', $v);
	$v = str_replace(']', '', $v);
	
	//If its type exists in grammar, pick a random member
	if ($grammar[$v]){
		return randomFrom($grammar[$v]);
	}
	elseif ($v == 'a' || $v == 'an'){
		//Deal with these on a seperate iteration
		return "þ{$v}ÿ";
	}
	else{
		return "\n*Unknown: '$v'\n";
	}
}

function isVowel($c){
	//Can find character c in vowel array?
	return strpos('aeiouy', $c) !== false;
}

?>
<?php 
	echo $result;
?>