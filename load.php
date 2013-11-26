<?php

require 'ext.php';
require 'timing.php';

//Render any errors/debug in plain text.
header('content-type: text/plain');

startTiming();

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
	
	loadGrammar($thisFile);
	
}

// === Save grammar to PHP array file ===

$grammarString = '<?php $grammar = ' . var_export($grammar, true) . ' ?>';

$outFile = fopen('grammar.php','w');
fwrite($outFile, $grammarString);
fclose($outFile);

// === Debug info for the admin ===
echo "\n\n=====FINAL GRAMMAR=====\n\n";
echo $grammarString;
echo "\n\n==========END==========\n";

$totalTime = stopTiming();
echo "    $totalTime secs\n=======================\n";

function loadGrammar($thisFile){

	global $category;
	global $catGrammar;
	global $grammar;

	echo "Loading $thisFile...\n";
	$h = fopen($thisFile , 'r');
	
	//Read through grammar
	while (!feof($h)){
		$line = fgets($h);
		
		if (ignoreLine($line)){
			continue;
		}

		if (lineIsCategory($line)){
			// We've come across a new category
			// So write the last category to grammar (if we've done one)
			if ($category > ''){
				commitCategory();
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
	commitCategory();

	//Debug?
	if (from_request('debug')){
		var_dump($grammar);
	}
	
	$category='';
	$catGrammar=[];
	echo "...Done\n\n";
}

function commitCategory(){
	global $category;
	global $catGrammar;
	global $grammar;
	
	$category = trim($category);
	echo " - Set $category\n";
	$grammar[$category] = $catGrammar;
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