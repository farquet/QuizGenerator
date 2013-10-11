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


// what to display to the user depending on what happened working with the database
$info = "";

// where we will be redirected after deletion
$location = "";
// stores what we are currently deleting (course or category)
$renaming = "";
// name to show in text field
$name = "";

$cm = new CourseManager();

$course_id = 0;
$cat_id = 0;

// handling course deletion
if(isset($_GET['co']))
{
	$renaming = "course";
	$course_id = $_GET['co'];
	$course_name = $cm->course_name($course_id);
	$name = $course_name; // for text field
	
	// if course exists
	if ($course_name && isset($_POST['new_name']))
	{
		$new_name = $_POST['new_name'];
		// checking if length is allowed
		if (strlen($new_name) > 0 && strlen($new_name) < MAX_LENGTH_COURSES)
		{
			$already_exists = false;
			foreach ($cm->get_courses_ids() as $a_course)
			{
				$a_course_name = $cm->course_name($a_course);
				if ($a_course != $course_id && $a_course_name == $new_name)
					$already_exists = true;
			}
			
			if ($already_exists)
				$info = "Not renamed. There is already a course with this name.";
			else
			{
				$cm->rename_course($course_id, $new_name);
				$location = "index.php"; // back to course list
			}
		}
		else
			$info = "Not renamed. Chosen course name is empty or longer than ".MAX_LENGTH_COURSES." chars.";
	}
}
elseif (isset($_GET['cat']))
{
	$renaming = "category";
	$cat_id = $_GET['cat'];
	$cat_name = $cm->category_name($cat_id);
	$name = $cat_name; // for text field
	$course_id = $cm->course_from_category($cat_id);
	
	// if category exists
	if ($cat_name && isset($_POST['new_name']))
	{
		$new_name = $_POST['new_name'];
		// checking if length is allowed
		if (strlen($new_name) > 0 && strlen($new_name) < MAX_LENGTH_COURSES)
		{
			$already_exists = false;
			foreach ($cm->get_categories_ids_from_course($course_id) as $a_category)
			{
				$a_category_name = $cm->category_name($a_category);
				if ($a_category != $cat_id && $a_category_name == $new_name)
					$already_exists = true;
			}
			
			if ($already_exists)
				$info = "Not renamed. There is already a category with this name.";
			else
			{
				$cm->rename_category($cat_id, $new_name);
				$location = "course.php?c=".$course_id; // back to categories list
			}
		}
		else
			$info = "Not renamed. Chosen category name is empty or longer than ".MAX_LENGTH_COURSES." chars.";
	}
}

// redirecting if renaming was ok
if (strlen($location))
	header("Location: ".$location);
?>
<html>
<head>
<title>Rename</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
</head>
<body>
<?php
include('top.php'); // menu
?>
<div class="info"><?=$info?></div>
<form action="#" method="post" name="rename" id="rename">
  New name for <?=$renaming?> :
  <input name="new_name" type="text" size="40" value="<?=$name?>">
  <input name="button" type="submit" value="Rename">
</form>
<?php
unset($cm); // closing database connection
?>
</body>
</html>