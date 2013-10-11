<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/


require_once("../lib/latex.lib.php");

?>
<html>
<head>
<title>Latex detection test</title>
</head>
<body>
<?php

$test = 'This is a \emph{test} string \test where I left some stuff that looks like \LaTeX commands. ';
$test .= 'This script should find latex commands and protected chars, such as \# and put them in red.';

$result = $test;
foreach(latex_detection($test) as $match)
{
	print $match."<br />";
	// warning: a tex command shouldn t be contained in another one
	$result = str_replace($match, "\n<span style='color:red;'>".$match."</span>", $result);
}

print "<br />".$result."<br />";

?>
</body>
</html>