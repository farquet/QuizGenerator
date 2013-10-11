<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/Question.class.php");
require_once("../lib/QuestionManager.class.php");

?>
<html>
<head>
<title>Database test</title>
<script type="text/javascript" src="../MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php

$manager = new QuestionManager();

//print $manager->get_question_by_id(50);
//print "<hr />";

foreach ($manager->get_questions_by_cat(1) as $question)
	print $question->html_form_field();

unset($manager); // closing database connection

?>
</body>
</html>