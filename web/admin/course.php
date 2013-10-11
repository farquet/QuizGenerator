<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "../";
require_once($root."lib/CourseManager.class.php");
require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/config.inc.php");

// id and name of the course we are modifying
$course_id = "";
$course_name = "";

// what to display to the user depending on what happened working with the database
$info = "";

$cm = new CourseManager();
$qm = new QuestionManager();
// Verifying if a specific course id has been set in the URL
if (isset($_GET['c']))
{
	$course_id = $_GET['c'];
	if (is_numeric($course_id))
		$course_name = $cm->course_name($course_id);
}
if (!$course_name)
{
	// redirection to the course list if we have no course specified in URL
	header("Location: index.php");
	unset($cm); // closing database properly
	exit();
}
if (isset($_GET['rst']) && $_GET['rst'] == "y") // resetting statistics for this course
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
	$info .= "All question statistics have been reset !<br />";
}
?>
<html>
<head>
<title><?=$course_name?></title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
</head>
<body>
<?php
include('top.php'); // menu
?>
<h3><?=$course_name?></h3>
<?php

// creating new category
if(isset($_POST['category_name']))
{
	$category = $_POST['category_name'];
	
	if(!$cm->create_category($category, $course_id))
	{
		$category_id = $cm->category_id($category, $course_id);
		
		if ($category_id)
			$info .= "`".$category."` category already exists.<br />".PHP_EOL;
		else
			$info .= "This category name is empty or longer than ".MAX_LENGTH_COURSES." chars."."<br />".PHP_EOL;
	}
}

// displaying all categories from this course
$categories_ids = $cm->get_categories_ids_from_course($course_id);
print '<ul class="list">'.PHP_EOL;
foreach ($categories_ids as $cat_id)
{
	$count = count($qm->get_questions_by_cat($cat_id));
	print '<li><a href="category.php?c='.$cat_id.'">'.$cm->category_name($cat_id);
	print ' <span class="small">('.$count.')</span></a>';
	print '&nbsp;&nbsp;&nbsp;&nbsp;<a href="rename.php?cat='.$cat_id.'"><img src="'.$root.'images/modify.png" width="15px" alt="modify" /></a>';
	print '&nbsp;&nbsp;&nbsp;&nbsp;<a href="delete.php?cat='.$cat_id.'">';
	print '<img src="'.$root.'images/delete.png" width="15px" alt="delete" /></a></li>'.PHP_EOL;
}
print '<li id="new" style="font-style:italic"><a href="#" onClick="show_form();">+ new category</a></li>'.PHP_EOL;
print '</ul>'.PHP_EOL;
unset($qm); // closing database connection
unset($cm); // closing database connection
?>
<div class="info"><?=$info?></div>
<div>
  <form name="stats" method="post" action="course.php?c=<?=$course_id?>&rst=y" onSubmit="return confirm('Do you really want to reset all statistics from <?=$course_name?> course ?');">
    <input type="submit" name="Reset questions statistics" id="Reset questions statistics" value="Reset questions statistics">
  </form>
</div>
<script>
function show_form() {
	var form = '<form action="#" method="post" name="categories" id="categories">\
	<input name="category_name" type="text" size="30" value="">\
	<input name="button" type="submit" value="Create">\
	</form>';

	document.getElementById('new').innerHTML = form;
}
</script>
</body>
</html>