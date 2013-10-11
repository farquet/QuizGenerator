<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/latex.lib.php");
require_once("../lib/Question.class.php");
require_once("../lib/QuestionManager.class.php");
require_once("../lib/CourseManager.class.php");

?>
<html>
<head>
<title>Database test</title>
</head>
<body>
<?php
$tex = readTex("survey06.tex");
$questions = extractQuestions($tex);

$qm = new QuestionManager();
$cm = new CourseManager();
$cat_id = 1;

if($cm->category_name($cat_id))
{
	foreach ($questions as $question)
	{
		$question->set_cat_id($cat_id);
		$insert = $qm->insert_question($question);
		if($insert)
		{
			print "Question successfully added to the database with id ".$insert."!<br />\n";
		}
		else
		{
			print "Question hasn't been inserted in the database. Question already exists.<br />\n";
		}
		print $question;
		print "<hr />";
	}
}
else
	print "Questions haven't been inserted in the database. Category doesn't exist.<br />\n";

unset($qm); // closing database connection
unset($cm); // closing database connection
?>
</body>
</html>