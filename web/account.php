<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "./";

require_once($root."lib/CourseManager.class.php");
require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/UserStatsManager.class.php");
require_once($root."lib/config.inc.php");
require_once($root."lib/grade.lib.php");
require_once($root."lib/id_encoder.lib.php");

session_start();

// redirecting if user is not logged in
if (!isset($_SESSION['sciper']))
	header("Location: choices.php");

$sciper = $_SESSION['sciper'];

$cm = new CourseManager();
$qm = new QuestionManager();

// Retrieving stats in database
$user_stats = new UserStatsManager();
$quizzes = $user_stats->get_stats($sciper);

$quiz_count = count($quizzes);
?>
<html>
<head>
<title>My account : <?=$_SESSION['sciper']?></title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<script type="text/javascript" src="<?=$root?>MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php include_once("top.php"); ?>
<h3 style="margin-left:20px">My Account : <?=$_SESSION['sciper']?></h3>
<?php
if ($quiz_count < 1)
	print '<p>You haven\'t done any quiz yet ! Shame on you, <a href="choices.php">start here</a> !</p>';
else
{
?>
<p style="margin-left:20px">You have <?=$quiz_count?> quiz<?php if ($quiz_count > 1) print 'zes'; ?> saved.</p>
<table cellpadding="5px" cellspacing="0px" style="margin-left:20px">
<tr><th></th><th>Grade</th><th>Score</th><th>Difficulty</th><th>Course</th><th>Quiz Reference</th><th>Date</th>
</tr>
<?php

$five_average = 0;
$total_average = 0;
$corrupted_count = 0;

for ($i=0; $i < $quiz_count; $i++)
	{
		$quiz = $quizzes[$i];
		
		$difficulty = $quiz['difficulty'];
		$q_ids = unserialize($quiz['questions_ids']);
		$user_choices = unserialize($quiz['user_choices']);
		$time_db = explode(' ', $quiz['time']);
		$course_id = $quiz['course_id'];
		
		$date = join('.', array_reverse(explode('-', $time_db[0]))); // converting date to dd.mm.yyyy format
		$hour_array = explode(':', $time_db[1]);
		$hour = $hour_array[0].':'.$hour_array[1];
		
		$correct_count = 0;
		$left_blank = 0;
		$corrupted = false; // will be true if there is a question with no corresponding id
		$questions = array();
		
		foreach ($q_ids as $q_id)
		{
			$q = $qm->get_question_by_id($q_id);
			if($q) // if the question exists
				$questions[] = $q;
		}
		
		// corrupted if a question id didn't exist in the database
		if (count($questions) != count($q_ids))
		{
			$corrupted = true;
			$corrupted_count++;
		}
		
		// calculating score for this quiz
		for($k=0; $k < count($questions); $k++)
		{
			$question = $questions[$k];
			if($user_choices[$k] == 0)
				$left_blank++;
			elseif($user_choices[$k] == $question->get_solution())
				$correct_count++;
		}
		$score = $correct_count.'/'.count($questions);
		
		if ($corrupted)
			$score = "-";
		
		// computing lasec grade
		$grade = lasec_grade($correct_count, $left_blank, count($questions));
		
		if ($corrupted)
			$grade = "-";
		
		// computing averages
		if (!$corrupted)
		{
			$total_average += $grade;
			if ($i - $corrupted_count < 5)
				$five_average += $grade;
		}
		
		$quiz_ref = '<span style="color:red">(a question has been removed)</span>';
		
		// computing quiz reference and encoding user choices
		if(!$corrupted)
			$quiz_ref = basis_encode(array_to_string($q_ids), ID_ENCODER_BASIS);
			
		$choices_encoded = basis_encode(array_to_string($user_choices), ID_ENCODER_BASIS);
		
		print '<tr style="text-align:center" class="account_rows">';
		
		//print '<td width="20px">'.($i+1).'.</td>'.PHP_EOL;
		print '<td width="120px" class="small">';
		if (!$corrupted)
		{
			print '<ul><li><a href="quiz_correction.php?co='.$course_id.'&q='.$quiz_ref.'&uc='.$choices_encoded.'">';
			print 'View correction</a></li>';
			print '<li><a href="quiz.php?co='.$course_id.'&q='.$quiz_ref.'">Do it again !</a></li>';
		}
		else
			print '<ul><li>-</li><li>-</li></ul>'.PHP_EOL;
		print '</td>'.PHP_EOL;
		
		print '<td width="50px"><span style="font-size:1.4em;font-style:bold;">'.$grade.'</span></td>'.PHP_EOL;
		print '<td width="50px">'.$score.'</td>'.PHP_EOL;
		
		print '<td width="80px">'.$difficulty.'</td>';
		print '<td width="200px" class="small">'.$cm->course_name($course_id).'</td>';
		print '<td width="200px" class="small">'.$quiz_ref.'</td>'.PHP_EOL;
		print '<td width="120px">'.$hour.', '.$date.'</td>'.PHP_EOL;
		
		print'</tr>'.PHP_EOL;
	}

$five_average = round($five_average / 5, 2);
if ($quiz_count != $corrupted_count) // if all quizzes aren't corrupted
	$total_average = round($total_average / ($quiz_count-$corrupted_count), 2);
else
	$total_average = "-";
?>
</table>
<div style="margin-left:20px;"><br />
<?php if($quiz_count > 5) { ?>The average of your five last quizzes is <b><?=$five_average?></b>.<br />
<?php } ?>
The average of your all your saved quizzes is <b><?=$total_average?></b>.</div>
<?php
if ($quiz_count >= MAX_SAVED_STATS)
{
	print '<div style="margin-left:20px"><br />The maximum number of saved quizzes has been reached ('.MAX_SAVED_STATS.').<br />'.PHP_EOL;
	print 'Your new quizzes will replace the older ones.<div/>';
}
}
unset($cm); // closing database connection
unset($qm);
?>
<br />
</body>
</html>