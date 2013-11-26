<!DOCTYPE HTML>
<html>
<head>
<title>Navigator - Paprika Grammar</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
body{
	font-family: "Open Sans", "Helvetica", Arial, sans-serif;
}
h2 small{
	font-weight: 100;
	font-size: small;
}
</style>
</head>
<body>

<h1>Paprika Grammar Navigator</h1>

<?php

require 'grammar.php';
require 'timing.php';

header('content-type: text/plain');

startTiming();

foreach ($grammar as $category => $definition){
	echo "<h2 id='$category'>$category <small><a href='#top'>[back to top]</a></small></h2>\n";
	echo "<ul>\n";
	foreach ($definition as $d){
		$pattern = '/\[([a-zA-Z0-9 _]+)\]/i';
		$d = preg_replace($pattern, '[<a href="#$1">$1</a>]', $d);
		echo "<li>$d</li>\n";
	}
	echo "</ul>\n";
}

$totalTime = round(stopTiming(),4);
echo "\n<hr>\n<p><strong>End of grammars.</strong> $totalTime seconds to render.</p>\n";

?>
	
</body>
</html>
