<?php

//Extensions
require 'ext.php';
//Pre-loaded grammar
require 'grammar.php';

$DEBUG = from_request('debug');
$ERROR = null;

//Plain text content-type? 
if (from_request('text') || $DEBUG){
	header('content-type: text/plain');
}
	
//Query from request
$q = from_request('q');
$result = parse($q);

function setError($text){
	global $ERROR;
	$ERROR = "\n<p>*$text</p>\n";
	return $ERROR;
}

function randomFrom($range){
	return $range[array_rand($range)];
}

function parse($v){
	global $DEBUG;
	global $ERROR;

	//While string contains open square
	$open = strpos($v, '[');
	
	// If we do a loop with no useful operation, nops increases
	// If nops is 2, we exit with error.
	$lastV = '';
	$nops = 0;
	
	while ($open !== false){
	
		//Infinite loop check
		if ($v == $lastV){
			$nops++;
			if ($nops == 2){
				return setError("Stuck in a loop. Please email me the formula you entered.");
			}
		}
		
		$lastV = $v;
		
		//OK Go
		//Reset command flag
		$flag = null;
		
		if ($DEBUG) { echo "(v: '$v')\n"; }
		
		//Check whether this bracket is mismatched
		$close = strpos($v, ']');
		if ($close === false){
			return setError("Mismatched bracket from char $open");
		}
		
		//Get the bracketed expression, like [sport] or [U sport]
		//$target will be replaced by $resolution
		$target = substr($v, $open, $close + 1 - $open);
		//$expression will be stripped of commands, to leave a grammar key.
		$expression = $target;

		//A bool for whether the $target needs to be transformed with expression.
		$retarget = null;
		
		//Pre-treatment the formula or mark for posttreatment using $flag
		if ($expression[2] == ' '){
			//This means the expression starts with a symbol then a space, like [U animal]
			switch ($expression[1]){
				case 'U':
					//Uppercase-ify
					$flag = $expression[1];
					break;
				case 'L':
					//Lowercase-ify
					$flag = $expression[1];
					break;
				case 'H':
					//Hide this tag but replace other instances (for early calls)
					$v = str_replace($expression, '', $v);
					//Update target after the 'H ' is removed
					// because we want to replace [sport] not [H sport]
					$retarget = true;
					break;
				case '?':
					//Flip a coin. If tails, render this expression blank (later)
					if (mt_rand(0,1) === 0){
						$flag = $expression[1];
					}
					break;
			}
			//Now remove the command and space so that the expression can be recognised in grammar
			$expression = $expression[0] . substr($expression, 3);
			if ($retarget) {$target = $expression;}
		}
		
		//$resolution is a plain string from grammar, like 'football'
		$resolution = resolveBracket($expression);
		if ($DEBUG) { echo "(Resolution: '$resolution')\n";}
				
		//Handle any processing flags we set earlier
		switch ($flag){
			case 'U':
				$resolution = strtoupper($resolution);
				break;
			case 'L':
				$resolution = strtolower($resolution);
				break;
			case '?':
				$resolution = '';
				break;
		}
		
		//Clean up double spaces - could make this a while loop
		$v = str_replace('  ',' ',$v);
		
		//THIS IS IT FOLKS
		//Replace all instances of [target] with its resolution
		$v = str_replace($target, $resolution, $v);
		if ($DEBUG) { echo "(New v: '$v')\n";}
		
		//Break out if there has been an error in this stage
		if ($ERROR) { return $ERROR; }
		
		//Get next open for while loop
		$open = strpos($v, '[');
	}
	
	// We've now replaced all [expression]s
	
	// Replace all the a/an words which have been marked with Chr(254)-Chr(255)
	// TODO: Let's do this a better way sometime in the future
	$open = strpos($v, 'þ');
	while ($open !== false){
		$close = strpos($v, 'ÿ');
		if ($close === false){
			return setError("Bad a/an matching. Developer error.");
		}
		$expression = substr($v, $open, $close + 1 - $open);
		$nextChar = trim(substr($v, $close + 1))[0];
		$resolution = isVowel($nextChar) ? 'an' : 'a';
		$v = str_replace($expression, $resolution, $v);
		$open = strpos($v, 'þ');
	}
	
	return $v;
}

// Takes a bracketed expression like '[sport]' and resolves it into
// a grammar entry (like 'football') or an error.
// $bw bracketed word
function resolveBracket($bw){

	global $grammar;
	
	//Remove square brackets (first and last char)
	$bw = substr($bw, 1, strlen($bw) - 2);

	if (strpos($bw, '[') !== false || strpos($bw, ']') !== false){
		return setError("Nested brackets; make an early call - see docs");
	}
	
	//Handle a label inside the tag like [sport#1]
	$labelPosition = strpos($bw, '#');
	if ($labelPosition !== false){
		$bw = substr($bw, 0, $labelPosition);
	}
	
	//If its type exists in grammar, pick a random member
	if ($grammar[$bw]){
		return randomFrom($grammar[$bw]);
	}
	elseif ($bw == 'a' || $bw == 'an'){
		//Deal with these on a seperate iteration
		return "þ{$bw}ÿ";
	}
	else{
		return setError("Unknown: '$bw'");
	}
}

function isVowel($c){
	//Can find character c in vowel array?
	return strpos('aeiou', $c) !== false;
}

?>
<?php 
	echo $result;
?>