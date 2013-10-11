<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "./";

require_once($root."lib/CourseManager.class.php");
require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/config.inc.php");

session_start();

$cm = new CourseManager();
$qm = new QuestionManager();

$course_id = "";
$course_name = "";
$cat_ids = array();

if (isset($_GET['co']))
{
	$course_id = $_GET['co'];
	$course_name = $cm->course_name($course_id);
	
	if ($course_name) // we ensure that if we receive an id, this is a safe one and that exists in the database
	{
		$cat_ids = $cm->get_categories_ids_from_course($course_id);
	}
	else {
		$course_id = "";
		$course_name = "";
	}
}
?>
<html>
<head>
<title>Choose course and category</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<script type="text/javascript" src="<?=$root?>MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php include_once("top.php"); ?>
<h3>Select your quiz</h3>
<p></p>
<?php
if (strlen($course_id) <= 0)
{

$co_ids = $cm->get_courses_ids();

if (count($co_ids) <= 0)
{
	print "Come back soon, there is no quiz available right now.";
}
else
{

if (!isset($_SESSION['sciper']))
{
	print '<p style="margin-left:30px"><a href="login.php" style="color:red">';
	print 'Log in with your Tequila account to keep track of your quizzes !</a></p>';
}
?>
	<p>Select your course.</p>
	<form method="GET" action="#">
	<?php
	foreach ($co_ids as $key => $id)
	{
		$course_name = $cm->course_name($id);
		
		if ($course_name)
		{
			print '<input type="radio" name="co" id="'.$id.'" value="'.$id.'"';
			if ($key == 0) print ' checked';
			print ' />'.PHP_EOL;
			print '<label for="'.$id.'"> '.$course_name.'</label><br />'.PHP_EOL;
		}
	}
	?>
	<br />
	<input type="submit" value="Choose">
	</form>
	<?php
}
}
elseif (count($cat_ids) <= 0)
{
	print "No chapter available in this course. Come back later !";
}
else // if there is a course_id received
{
?>
<p>Choose the chapters you want to include in the quiz.</p>
<form method="post" action="quiz.php?co=<?=$course_id?>">
<?php
foreach ($cat_ids as $cat_id)
{
	$cat_name = $cm->category_name($cat_id);
	$questions = $qm->get_questions_by_cat($cat_id);
	
	if ($cat_name && count($questions) > 0)
	{
		print '<input type="checkbox" name="cat[]" id="'.$cat_id.'" value="'.$cat_id.'"/>'.PHP_EOL;
		print '<label for="'.$cat_id.'"> '.$cat_name.'</label><br />'.PHP_EOL;
	}
}
?>
<br />
<div class="small" style="margin-left:20px;" id="difficulty">
<a onClick="show_difficulty()">+ Choose difficulty</a>
</div>
<br />
<input type="submit" value="Start quiz">
</form>
<script>
function show_difficulty() {
	var form = '<div class="difficulty"><input type="radio" name="difficulty" id="easy" value="easy" />\
	<label for="easy">Easy Mode</label><br />\
	<input type="radio" name="difficulty" id="normal" value="normal" checked/>\
	<label for="normal">Normal Mode</label><br />\
	<input type="radio" name="difficulty" id="hard" value="hard" />\
	<label for="hard">Hard Mode</label><br />\
	<div class="popup"><ul><li>Easy Mode : most succeeded questions</li>\
	<li>Normal Mode : completely random questions</li>\
	<li>Hard Mode : most failed questions</li></ul></div></div>';

	document.getElementById('difficulty').innerHTML = form;
}
</script>
<?php
}

$target = "quiz.php";

if (strlen($course_id) > 0)
	$target .= "?co=".$course_id;
?>
<div class="menu">
<hr />
<form method="get" action="<?=$target?>">
  <p>Enter quiz reference :
  <input name="q" type="text" id="q" size="35">
  <input type="submit" name="Take quiz" id="Take quiz" value="Take quiz">
  </p>
</form>
</div>
</body>
</html>