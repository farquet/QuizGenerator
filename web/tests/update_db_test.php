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

$id = 93;
$question = $manager->get_question_by_id($id);
print $question."<hr />".PHP_EOL;

$question->set_title('Tick the \emph{co\textbf{r}r\textbf{e}ct} assertion.');

if ($manager->update_question($question))
	print "Question successfully updated in database.<hr />".PHP_EOL;
else
	print "Question couldn't be updated (bad category id).<hr />".PHP_EOL;

print $manager->get_question_by_id($id);

unset($manager); // closing database connection

?>
</body>
</html>