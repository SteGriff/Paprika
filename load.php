<?php

require 'ext.php';

//Render any errors/debug in plain text.
header('content-type: text/plain');

$GRAMMAR_FILE = 'index.grammar';

//Final output, $result:
$result = '';

//Check the grammar manifest exists
if (!file_exists($GRAMMAR_FILE)){
	echo "Can't find the grammar manifest; should be '$GRAMMAR_FILE'.\n";
	die;
}

//Open the grammar manifest
$man = fopen($GRAMMAR_FILE , 'r');

$category = '';
$catGrammar = array();
$grammar = array();

while (!feof($man)){

	$thisFile = trim(fgets($man));
	
	if ($thisFile == ''){
		continue;
	}
	
	if (!file_exists($thisFile)){
		echo "Can't find a linked grammar file, '$thisFile'.\n";
		die;
	}
	
	echo "Loading $thisFile...\n";
	$handle = fopen($thisFile , 'r');
	loadGrammar($handle);
	echo "\n...Done\n";
	
}

// === Save grammar to PHP array file ===

// Start buffering output
//ob_start();

$grammarString = '<?php $grammar = ';
$grammarString .= var_export($grammar, true);
$grammarString .= '?>';

// Dump to buffer
//echo '<?php $grammar=';
//var_dump($grammar);
//echo '';

// Get buffer into string and write to file
//$grammarString = ob_get_contents();

$outFile = fopen('grammar.php','w');
fwrite($outFile, $grammarString);
fclose($outFile);
 
// Clean buffer and stop
//ob_end_clean();

// === Debug info for the admin ===
echo "\n\n=====FINAL GRAMMAR=====\n\n";
echo $grammarString;
echo "\n\n=====END=====\n\n";

function loadGrammar($h){

	global $category;
	global $catGrammar;
	global $grammar;

	//Read through grammar
	while (!feof($h)){
		$line = fgets($h);
		
		if (ignoreLine($line)){
			continue;
		}

		if (lineIsCategory($line)){
			//echo $line;
			//Write the last category to grammar if it exists
			if ($category > ''){
				$category = trim($category);
				$grammar[$category] = $catGrammar;
			}
			$category = substr($line, 1);
			$catGrammar = [];
		}
		else{
			//It's a member
			$member = trim($line);
			array_push($catGrammar, $member);
		}
	}

	//Commit the final category
	$category = trim($category);
	$grammar[$category] = $catGrammar;

	//Debug?
	if (from_request('debug')){
		var_dump($grammar);
	}
	
}

function ignoreLine($l){
	return (
		$l[0] == '#'
		|| trim($l) <= ""
	);
}

function lineIsCategory($l){
	return $l[0] == '*';
}

?>