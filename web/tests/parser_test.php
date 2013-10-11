<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/latex.lib.php");
require_once("../lib/Question.class.php");

?>
<html>
<head>
<title>Bachelor Project</title>
<script type="text/javascript" src="../MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php
$tex = readTex("latex-samples/survey06.tex");

$questions = extractQuestions($tex);

print "<form>";
foreach ($questions as $question)
{
	print $question->html_form_field(true);
	print "<hr />";
}
print "</form>";
?>
</body>
</html>