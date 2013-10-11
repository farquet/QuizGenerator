<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "../";
require_once($root."lib/CourseManager.class.php");
require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/UserStatsManager.class.php");
require_once($root."lib/config.inc.php");

?>
<html>
<head>
<title>Course list</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
</head>
<body>
<?php

// what to display to the user depending on what happened working with the database
$info = "";

$cm = new CourseManager();

// creating a new course
if(isset($_POST['course_name']))
{
	$course = $_POST['course_name'];
	
	if(!$cm->create_course($course))
	{
		$course_id = $cm->course_id($course);
		
		if ($course_id)
			$info .= "`".$course."` course already exists.<br />".PHP_EOL;
		else
			$info .= "This course name is empty or longer than ".MAX_LENGTH_COURSES." chars."."<br />".PHP_EOL;
	}
}

// resetting question statistics
if (isset($_GET['rst']) && $_GET['rst'] == "y") // resetting statistics for this course
{
	$qm = new QuestionManager();
	$qm->reset_stats();
	/*$courses_ids = $cm->get_courses_ids();
	foreach ($courses_ids as $course_id)
	{
		$categories_ids = $cm->get_categories_ids_from_course($course_id);
		foreach ($categories_ids as $cat_id)
		{
			$questions = $qm->get_questions_by_cat($cat_id);
			
			foreach ($questions as $q)
			{
				$q->reset_stats();
				$qm->update_question($q);
			}
		}
	}*/
	$info .= "All question statistics have been reset !<br />";
	unset($qm);
}

// resetting users quizzes stats
$user_stats = new UserStatsManager();
// automatically if older than x months
$stats_del_count = $user_stats->delete_older_than(MAX_QUIZ_SAVED_DURATION);
if ($stats_del_count > 0)
	$info .= "Automatic deletion of users quizzes stats older than ".MAX_QUIZ_SAVED_DURATION." months : ".$stats_del_count." deletions.<br />".PHP_EOL;
// or on admin request
if (isset($_GET['rst_u']) && $_GET['rst_u'] == 'y')
{
	$user_stats->reset_all();
	$info .= "All users statistics have been deleted.<br />".PHP_EOL;
}
unset($user_stats); // closing database connection

include('top.php'); // menu

// print a warning if magic quotes are enabled
if (get_magic_quotes_gpc())
{
?>
<br />
<div class="f-left"><br /><img src="../images/warning.png" width="30px"></div>
<div class="f-left" style="margin-left:10px;">The option <em>magic_quotes_gpc</em> is set to <em>On</em>!<br/>
Please set it to <em>Off</em> in php.ini, in order to have this project work properly.<br />
Magic quotes is now a deprecated option.<br />
<a href="http://en.wikipedia.org/wiki/Magic_quotes">Have a look here to have more information.</a></div>
<div class="f-clear"><br />
<?php
}

// iterating over all courses id and finding corresponding name
$courses_ids = $cm->get_courses_ids();

print '<ul class="list">'.PHP_EOL;
foreach ($courses_ids as $id)
{
	$count = count($cm->get_categories_ids_from_course($id));
	print '<li><a href="course.php?c='.$id.'">'.$cm->course_name($id);
	print ' <span class="small">('.$count.')</span></a>';
	print '&nbsp;&nbsp;&nbsp;&nbsp;<a href="rename.php?co='.$id.'"><img src="'.$root.'images/modify.png" width="15px" alt="modify" /></a>';
	print '&nbsp;&nbsp;&nbsp;&nbsp;<a href="delete.php?co='.$id.'">';
	print '<img src="'.$root.'images/delete.png" width="15px" alt="delete" /></a></li>'.PHP_EOL;
}
print '<li id="new" style="font-style:italic"><a href="#" onClick="show_form();">+ new course</a></li>'.PHP_EOL;
print '</ul>'.PHP_EOL;

unset($cm); // closing database connection
?>
<div class="info"><?=$info?></div>
<br />
<form name="stats" method="post" action="index.php?rst=y" onSubmit="return confirm('Do you really want to reset all questions statistics for all courses ?');">
    <input type="submit" name="Reset questions statistics" id="Reset questions statistics" value="Reset questions statistics" style="margin-left:20px">
  </form>
  
  <form name="stats" method="post" action="index.php?rst_u=y" onSubmit="return confirm('Do you really want to reset all users statistics for all courses ?');">
    <input type="submit" name="Reset users quizzes statistics" id="Reset users quizzes statistics" value="Reset users quizzes statistics" style="margin-left:20px"><span style="margin-left:20px" class="small">Note that they are stored for a maximum of <?=MAX_QUIZ_SAVED_DURATION?> months.</span>
  </form>
<script>
function show_form() {
	var form = '<form action="#" method="post" name="courses" id="courses">\
	<input name="course_name" type="text" size="30" value="">\
	<input name="button" type="submit" value="Create">\
	</form>';

	document.getElementById('new').innerHTML = form;
}
</script>
</body>
</html>