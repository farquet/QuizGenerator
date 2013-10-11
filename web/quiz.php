<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "./";

require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/CourseManager.class.php");
require_once($root."lib/config.inc.php");
require_once($root."lib/id_encoder.lib.php");

session_start();

$qm = new QuestionManager();
$cm = new CourseManager();

$cat_ids = array();
$questions = array();

$mode = "normal";

if (isset($_POST['difficulty']))
	$mode = $_POST['difficulty'];

if (isset($_GET['q'])) // if we receive an encoded quiz in URL
{
	$ids_string = basis_decode($_GET['q'], ID_ENCODER_BASIS);
	
	if ($ids_string)
	{
		$questions_ids = string_to_array($ids_string);
		
		if ($questions_ids)
		{
			foreach($questions_ids as $id)
			{
				$q = $qm->get_question_by_id($id);
				if ($q)
					$questions[] = $q;
			}
		}
	}
}
else if (isset($_POST['cat']))
{
	$cat_ids = $_POST['cat'];
	
	foreach ($cat_ids as $cat_id)
		$questions = array_merge($questions, $qm->get_questions_by_cat($cat_id, true)); // get questions from this category
	
	// removing questions that don't pass the audit test
	for ($i = 0; $i < count($questions); $i++)
		if ($questions[$i]->audit_errors() > 0)
		{
			unset($questions[$i]);
		}
	$questions = array_values($questions); // normalizing keys if we have deleted questions
	
	if ($mode == "normal")
		shuffle($questions); // shuffling questions from all cattegories selected
}

// selection of questions with probabilities if we need an easy or an hard quiz and we have lots of questions
if (count($questions) > QUIZ_SIZE && $mode != "normal")
{
	$questions_temp = $questions;
	$questions = array();
	$constant = 5;
	
	// if percent statistics are relatively close (+- 20% between min and max), the differences in the weighted sum will be close,
	// so we chose to remove the min_percent get better randomness. We add a small probability (5) for the min to avoid being 0.
	
	$min_percent = $questions_temp[0]->get_stat_percent();
	$max_percent = $questions_temp[0]->get_stat_percent();
	for ($l=1; $l < count($questions_temp); $l++)
	{
		if ($questions_temp[$l]->get_stat_percent() < $min_percent)
			$min_percent = $questions_temp[$l]->get_stat_percent();
		
		if ($questions_temp[$l]->get_stat_percent() > $max_percent)
			$max_percent = $questions_temp[$l]->get_stat_percent();
	}
	
	// summing all probabilities (building a cumulative function)
	$weight_sum = array();
	if ($mode == "easy")
		$weight_sum[0] = ($questions_temp[0]->get_stat_percent() - $min_percent) + $constant; // +$constant to avoid probability 0
	else // if hard mode, we want high percentage to have small probability
		$weight_sum[0] = ($max_percent - $questions_temp[0]->get_stat_percent()) + $constant;
	
	for ($i=1; $i < count($questions_temp); $i++)
	{
		if ($mode == "easy")
			$weight_sum[$i] = $weight_sum[$i-1] + ($questions_temp[$i]->get_stat_percent() - $min_percent) + $constant;
		else
			$weight_sum[$i] = $weight_sum[$i-1] + ($max_percent - $questions_temp[$i]->get_stat_percent()) + $constant;
	}
	
	$pick = 0;
	while($pick < QUIZ_SIZE) // until we have as much questions as we need
	{
		$rand = mt_rand(0,end($weight_sum)); // picking randomly a number between 0 and the sum of all weights
		for ($k=0; $rand > $weight_sum[$k]; $k++) ; // we find the index of the step in the cumulative function
		
		// removing question if we already selected it
		$found = false;
		foreach ($questions as $qu)
			if ($qu->get_id() == $questions_temp[$k]->get_id())
				$found = true;
		
		if (!$found) // the question has not been already picked so we add it to our questions
		{
			$questions[] = $questions_temp[$k];
			$pick++;
		}
	}
}

$course_id = "";
if (isset($_GET['co']))
	$course_id = $_GET['co'];
	
if(strlen($course_id) <= 0) // if the course is not set, menu cannot be displayed correctly, so we find it and refresh with good url
{
	$course_id = $cm->course_from_category($questions[0]->get_cat_id());
	$location = "Location: quiz.php?co=".$course_id;
	
	if (isset($_GET['q']))
		$location .= "&q=".$_GET['q'];
		
	header($location);
}

if (count($questions) <= 0) // if we have no question to display, we just redirect
	header("Location: choices.php?co=".$course_id);
?>
<html>
<head>
<title>Take a quiz</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<script type="text/javascript" src="<?=$root?>MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php include_once("top.php"); ?>
<h3>Random quiz</h3>
<p></p>
<form method="POST" action="quiz_correction.php?co=<?=$course_id?>">
<?php
$ids_displayed = array();
$stats = 0;
foreach ($questions as $key => $question)
{
	// if the question has bugs, we skip it
	if ($question->audit_errors() <= 0)
	{
		print '<div class="question_box">'.$question->html_form_field($key+1, true);
		if (SHOW_STATS)
			print '<br /><span class="small" style="margin-left:35px">Stat : '.$question->get_stat_percent()."%</span>";
		print '</div>';
		$stats += $question->get_stat_percent();
		$ids_displayed[] = $question->get_id();
	}
	
	// if we have displayed the number of questions we want, we stop displaying new ones
	if (count($ids_displayed) >= QUIZ_SIZE)
		break;
}

unset($qm); // closing database connection
unset($cm);

if (SHOW_STATS)
	print '<br /><span class="small" style="margin-left:35px">Average Stats : <b>'.($stats/QUIZ_SIZE).'%</b></span><br />';
?>
<br />
<input type="hidden" name="ids" value="<?=join("-", $ids_displayed)?>">
<input type="hidden" name="difficulty" value="<?=$mode?>">
<input type="submit" value="Correct it">
</form>
<div class="menu" style="text-align:right;font-size:smaller;">Quiz reference : <?=basis_encode(array_to_string($ids_displayed), ID_ENCODER_BASIS)?></div>
<br />
</body>
</html>