<?php

//Extensions
require 'ext.php';
require 'timing.php';

//Pre-loaded grammar
require '../grammar.php';

$DEBUG = from_request('debug');
$ERROR = null;

//Plain text content-type? 
if (from_request('text') || $DEBUG){
	header('content-type: text/plain');
	if ($DEBUG){
		startTiming();
	}
}

//Query from request
$q = from_request('q');
$result = parse($q);

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
		//Reset blanking flag
		$blank = false;
		
		if ($DEBUG) { echo "\"$v\" \n"; }
		
		//Check whether this bracket is mismatched
		$close = strpos($v, ']');
		if ($close === false){
			return setError("Mismatched bracket from char $open");
		}
		
		//Get the bracketed expression, like [sport] or [!sport]
		//$target will be replaced by $resolution
		$target = substr($v, $open, $close + 1 - $open);
		//$expression will be stripped of commands, to leave a grammar key.
		$expression = $target;

		//A bool for whether the $target needs to be transformed with expression.
		$retarget = null;
		
		//Handle hidden early calls
		if ($expression[1] == '!'){
			//Hide this tag but replace other instances (for early calls)
			$v = str_replace($expression, '', $v);
			$expression = str_replace('!', '', $expression);
			
			//Update target after the '!' is removed because
			// we want to replace all [sport] not [!sport]
			$target = $expression;
		}
		elseif ($expression[1] == '?'){
			//Flip a coin. If tails, render this expression blank (later)
			if (mt_rand(0,1) === 0){
				$blank = true;
			}
			else{
				$expression = str_replace('?', '', $expression);
			}
		}
		
		if ($blank) {
			$resolution = '';
		}
		else{
			//$resolution is a plain string from grammar, like 'football'
			$resolution = resolveBracket($expression);
		}
		
		//Clean up double spaces - could make this a while loop
		$v = str_replace('  ',' ',$v);
		
		//THIS IS IT FOLKS
		//Replace all instances of [target] with its resolution
		$v = str_replace($target, $resolution, $v);
		if ($DEBUG) {
			echo "\tReplacing all \"$target\" with \"$resolution\" \n";
			echo "\t --> \"$v\" \n";
		}
		
		//Break out if there has been an error in this stage
		if ($ERROR) { return $ERROR; }
		
		//Get next open for while loop
		$open = strpos($v, '[');
	}
	
	// We've now replaced all [expression]s
	
	// Replace all the a/an words which have been marked with Chr(254)-Chr(255)
	// TODO: Let's do this a better way sometime in the future
	
	$_A = ' a ';
	$_An = ' an ';
	
	$v = replaceArticles($v, $_A, $_An, $_A, $_An);
	$v = replaceArticles($v, $_An, $_A, $_A, $_An);
	
	return $v;
}

function replaceArticles($v, $a, $b, $_A, $_An)
{
	//TODO Use regex to match start/end of string instead of flanking spaces
	
	$finalProcessedPos = 0;
	
	$openA = strpos($v, $a);
	while ($openA !== false && $finalProcessedPos < $openA)
	{
		//^start $end
		$pos = strpos($v, $a);
		$finalProcessedPos = $pos;
				
		//Take a sample of the next 4 letters after the ' a '
		// trim spaces, and check the 0th one
		$nextLetter = trim(substr($v, $pos + 3 , 4))[0];
		$resolution = isVowel($nextLetter) ? $_An : $_A;
		$v = str_replace($a, $resolution, $v);
		
		//echo "\n4 nl $nextLetter res $resolution v $v";
		
		//Check for more
		$openA = strpos($v, $a);
	}
	
	//echo "\n6 return $v";
	return $v;
	
}

// Takes a bracketed expression like '[sport]' and resolves it into
// a grammar entry (like 'football') or an error.
// $bw bracketed word
function resolveBracket($bw){

	global $grammar;
	
	//Remove square brackets (first and last char)
	$bw = substr($bw, 1, strlen($bw) - 2);

	//Throw an error if it still contains square brackets
	if (strpos($bw, '[') !== false || strpos($bw, ']') !== false){
		$errorZone = substr($bw, strpos($bw, '['), 6) . '...';
		return setError("Nested brackets at \"$errorZone\"; make an early call - see docs");
	}
	
	//Handle a label inside the tag like [sport#1] (remove the label)
	$labelPosition = strpos($bw, '#');
	if ($labelPosition !== false){
		$bw = substr($bw, 0, $labelPosition);
	}
	
	//If the brackets contain terms separated by slash, we treat those as literals
	// and randomise between them.
	if (strpos($bw, '/') !== false){
		$terms = explode('/', $bw);
		return randomFrom($terms);
	}
	
	//Pick from conventional grammar
	//If its type exists in grammar, pick a random member
	if ($grammar[$bw]){
		return randomFrom($grammar[$bw]);
	}
	elseif ($bw == 'a' || $bw == 'an'){
		//We used to mark these up specially but now let's just remove brackets
		// because they'll get picked up that way
		return "þ{$bw}ÿ";
	}
	else{
		return setError("Unknown: '$bw'");
	}
}

?>
<?php 

	if ($DEBUG){
		$totalTime = round(stopTiming(),4);
		echo "-------------\nTime: {$totalTime}s\n-------------\n";
	}
	
	echo $result;
?>