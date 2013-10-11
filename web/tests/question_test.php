<html>
<head>
<title>Test of the Question class</title>
</head>
<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/Question.class.php");

// Test a question and display errors found
function test_question(Question $test)
{
	// audit errors on the question
	$error_count = $test->audit_errors();
	echo $error_count ." error(s) found.<br />";
	
	if ($error_count > 0)
	{
		print "<ul>";
		foreach ($test->get_errors() as $error)
			print "<li style=\"color:red\">".$error."</li>"; // displaying each error found
		print "</ul>";
	}
}

$question = new Question(); //empty question
print '<div style="margin-left:50px;">'.$question.'</div><br />';
test_question($question);
print "<hr />";

$question = new Question("this is a title", array("first", "", "third"), 10); // bad answer and solution
print '<div style="margin-left:50px;">'.$question.'</div><br />';
test_question($question);
print "<hr />";

$question = new Question("this is a title", array("first", "second", "third"), 1); // correct question
print '<div style="margin-left:50px;">'.$question.'</div><br />';
test_question($question);
print "<hr />";

$long_answer = "";
for($i = 0; $i < 350; $i++)
	$long_answer .= "hello ";
$question = new Question("this is a title", array("one", "two", $long_answer), 3); //too long answer
print '<div style="margin-left:50px;">'.$question.'</div><br />';
test_question($question);
?>