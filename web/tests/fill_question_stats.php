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
<title>Add random statistics to questions</title>
</head>
<body>
<?php

$qm = new QuestionManager();

$questions = $qm->get_questions(true);

define("MIN_NEW_STATS", 30);
define("MAX_NEW_STATS", 80);

$percent = 0;

foreach ($questions as $question)
{
	$number_of_stats = rand(MIN_NEW_STATS, MAX_NEW_STATS); // picking a number betwee min and max
	
	$some_stats = array();
	
	for($i = 0; $i < 4; $i++)
		$some_stats[] = rand(0, $number_of_stats);
	
	$success_rate = max($some_stats); // to make the mean closer to the max
	
	$question->set_stat_correct($success_rate+$question->get_stat_correct());
	$question->set_stat_total($number_of_stats+$question->get_stat_total());
	$qm->update_question($question, true);
	
	$percent += $success_rate * 100 / $number_of_stats;
}
$percent = round($percent / count($questions), 2);

print "Random statistics filled for ".count($questions)." questions.<br /> Mean percentage of correctness is ".$percent;

unset($qm); // closing database connection
?>
</body>
</html>