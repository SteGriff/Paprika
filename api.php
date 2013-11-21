<?php

//Extensions
require 'ext.php';
//Pre-loaded grammar
require 'grammar.php';

$DEBUG = from_request('debug');

//Plain text content-type? 
if (from_request('text') || $DEBUG){
	header('content-type: text/plain');
}
	
//Query from request
$q = from_request('q');
$result = parse($q);

function randomFrom($range){
	return $range[array_rand($range)];
}

function parse($v){
	global $DEBUG;
	
	//While string contains open square
	$open = strpos($v, '[');
	while ($open !== false){
		if ($DEBUG) { echo "(v: '$v')\n"; }
		$close = strpos($v, ']');
		if ($close === false){
			return "\n*Mismatched bracket from char $open\n";
		}
		
		//The bracketed expression, like [sport]
		$expression = substr($v, $open, $close + 1 - $open);

		//Handle hidden early calls
		if ($expression[1] == "!"){
			$v = str_replace($expression, '', $v);
			$expression = str_replace('!', '', $expression);
		}
		
		$resolution = resolveBracket($expression);
		if ($DEBUG) { echo "(Resolution: '$resolution')\n";}
				
		$v = str_replace($expression, $resolution, $v);
		if ($DEBUG) { echo "(New v: '$v')\n";}
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
	
	//Remove square brackets (first and last char)
	$v = substr($v, 1, strlen($v) - 2);

	if (strpos($v, '[') !== false || strpos($v, ']') !== false){
		return "\n*Nested brackets; make an early call - see docs\n";
	}
	
	//Handle a label inside the tag like [sport#1]
	$labelPosition = strpos($v, '#');
	if ($labelPosition !== false){
		$v = substr($v, 0, $labelPosition);
	}
	
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