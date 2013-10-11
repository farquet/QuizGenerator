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
$deleting = "";

$cm = new CourseManager();
$qm = new QuestionManager();

$ask_user = "";
$answer = "";

$course_id = 0;
$cat_id = 0;
$courses_ids = array();
$all_cat_ids = array();

$delete_checked = false;

$top_menu = ""; // code of the header menu

// handling course deletion
if(isset($_GET['co']))
{
	$deleting = "course";
	$course_id = $_GET['co'];
	$course_name = $cm->course_name($course_id);
	$courses_ids = $cm->get_courses_ids();
	
	$cat_ids = $cm->get_categories_ids_from_course($course_id);
	$cat_count = count($cat_ids);
	
	if ($cat_count <= 0)
	{
		// the course contains no category, remove it without prompting
		if (!$course_name)
			$info = "This course doesn't exist.";
		elseif ($cm->delete_course($course_id))
			$info = "'".$course_name."' course has been deleted.";
		else
			$info = "'".$course_name."' course could'nt be deleted !";
		
		$location = "index.php"; // course list
	}
	else
	{
		// Course contains categories, handle response to prompt or prompt user
		if (isset($_POST['cancel']))
		{
			// Deleting this course was a bad idea, we go back to course list
			$location = "index.php";
		}
		elseif (isset($_POST['delete']) && isset($_POST['choice']))
		{
			// handling choice recursive deletion or move to another course
			$choice = $_POST['choice'];
			
			if ($choice == "delete")
			{
				$q_count = 0;
				// deleting all questions from all categories from this course
				foreach ($cat_ids as $cat)
					$q_count += $qm->delete_questions_from_category($cat);
					
				// deleting the course and all his categories
				$cm->delete_course($course_id, true);
				
				$info = "`".$course_name."` (<b>".$cat_count."</b> categories and <b>";
				$info .= $q_count."</b> questions) permanently deleted.";
				
				$location = "index.php"; // redirection
			}
			elseif ($choice == "move" && isset($_POST['course_list']))
			{
				$course_destination = $_POST['course_list'];
				$course_dest_name = $cm->course_name($course_destination);
				
				if ($course_dest_name)
				{
					// moving each category in the course selected
					$count = $cm->move_categories($course_id, $course_destination);
					// deleting the course that is now empty
					$cm->delete_course($course_id);
					
					$info = "`".$course_name."` deleted ! <b>";
					$info .= $count."</b> categories moved to `".$course_dest_name."`.";
					
					$location = "index.php";
				}
				else
				{
					$info = "The destination course doesn't exist ! Course not deleted.";
					$location = "delete.php?co=".$course_id; // asking again
				}
			}
		}
		else
		{
			// prompting user
			$questions_ids = $qm->get_questions_for_cat_array($cat_ids); // questions from several categories
			$questions_count = count($questions_ids);
			$ask_user = "Course <em>".$course_name."</em> contains <b>".$cat_count."</b> ";
			
			if ($cat_count < 2)
				$ask_user .= "category";
			else
				$ask_user .= "categories";
			
			$ask_user .= " (for a total of <b>".$questions_count."</b> question";
			if ($questions_count > 1)
				$ask_user .= "s";
			$ask_user .= ").";
		}
	}
}
elseif (isset($_GET['cat']))
{
	$deleting = "category";
	
	$cat_id = $_GET['cat']; // the category to remove
	
	// This will extract the menu now and not print it to avoid sending stuff before headers
	// We do this to retrieve course from category before category is deleted
	ob_start();
	include('top.php');
	$top_menu = ob_get_contents();
	ob_end_clean();
	
	$cat_name = $cm->category_name($cat_id); // its name
	$course_id = $cm->course_from_category($cat_id); // the course that contains this category
	$course_name = $cm->course_name($course_id); // its name
	
	$questions_ids = $qm->get_questions_by_cat($cat_id);
	$questions_count = count($questions_ids);
	
	if ($questions_count <= 0)
	{
		// the category contains no question, remove it without prompting
		if (!$cat_name)
			$info = "This category doesn't exist.";
		elseif ($cm->delete_category($cat_id))
			$info = "'".$cat_name."' category has been deleted.";
		else
			$info = "'".$cat_name."' category could'nt be deleted !";
		
		$location = "course.php?c=".$course_id; // category list from this course
	}
	else
	{
		if (isset($_POST['cancel']))
		{
			// we go back to the category list of the course
			$location = "course.php?c=".$course_id;
		}
		elseif (isset($_POST['delete']) && isset($_POST['choice']))
		{
			$choice = $_POST['choice'];
			
			if ($choice == "delete")
			{
				// deleting all questions from this category
				$q_count = $qm->delete_questions_from_category($cat_id);
				$cm->delete_category($cat_id);
				
				$location = "course.php?c=".$course_id;
				$info = "`".$cat_name."` (<b>".$q_count."</b> questions) permanently deleted.";
			}
			elseif ($choice == "move" && isset($_POST['cat_list']))
			{
				$cat_destination = $_POST['cat_list'];
				$cat_dest_name = $cm->category_name($cat_destination);
				
				if ($cat_dest_name)
				{
					// moving each question in the category selected
					$count = $qm->move_questions($cat_id, $cat_destination);
					// deleting the category that is now empty
					$cm->delete_category($cat_id);
					
					$info = "`".$cat_name."` deleted ! <b>";
					$info .= $count."</b> questions moved to `".$cm->course_name_from_category($cat_destination);
					$info .= "` -> `".$cat_dest_name."`.";
					
					$location = "course.php?c=".$course_id;
				}
				else
				{
					$info = "The destination category doesn't exist ! Category not deleted.";
					$location = "delete.php?cat=".$cat_id; // asking again
				}
			}
		}
		else
		{
			// prompting user
			$ask_user = "Category <em>".$cat_name."</em> in <span class='small'>".$course_name."</span> contains <b>".$questions_count."</b> question";
			
			if ($questions_count > 1) // grammar is so much important !
				$ask_user .= "s";
				
			$ask_user .= ".";
		}
	}
	$all_cat_ids = $cm->get_categories_ids();
}

// redirecting if necessary and if no message to show to the user
if (strlen($location) > 0 && strlen($info) <= 0)
	header("Location: ".$location);
?>
<html>
<head>
<title>Delete confirmation</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<?php
// if we have a message to display, we redirect after 2 seconds
if (strlen($location) > 0 && strlen($info) > 0) { ?>
<meta http-equiv="refresh" content="<?=REDIRECT_TIME?>;url=<?=$location?>">
<?php } ?>
</head>
<body>
<?php
if (strlen($top_menu) > 0)
	print $top_menu;
else
	include('top.php'); // menu
?>
<div class="info"><?=$info?></div>
<?php
$stuff = "";
if (strlen($ask_user) > 0)
{
	if ($deleting == "course")
		$stuff = "the course, all his categories and questions";
	elseif ($deleting == "category")
		$stuff = "the category and all his questions";
?>
<div style="float:left;width:40px;padding-top:10px"><img src="<?=$root?>images/warning.png" width="30px" alt="warning" /></div>
<div style="float:left">
<form name="confirm" method="post" action="#">
  <p><?=$ask_user?></p>
  <p>What would you like to do with the <?=$deleting?> content ?</p>
  <?php
  if ($deleting == "course")
  {
	  if (count($courses_ids) > 1)
	  {
	  ?>
	  <input type="radio" name="choice" id="2" value="move" onChange="document.getElementById('delete_button').disabled=false;">
	  <label for="2">Move all course content into
	  <select name="course_list">
		<?php
		foreach ($courses_ids as $a_course_id)
		{
			if ($a_course_id != $course_id)
				print "<option value='".$a_course_id."'>".$cm->course_name($a_course_id)."</option>".PHP_EOL;
		}
		?>
	  </select> and then delete it
	  </label><br />
	  <?php
	  }
	  else
	  	$delete_checked = true;
  }
  elseif ($deleting == "category")
  {
	  if (count($all_cat_ids) > 1)
	  {
	  ?>
	  <input type="radio" name="choice" id="2" value="move" onChange="document.getElementById('delete_button').disabled=false;">
	  <label for="2">Move questions from this category to
	  <select name="cat_list">
		<?php
		foreach ($all_cat_ids as $cat)
		{
			if ($cat != $cat_id)
			{
				$course = $cm->course_from_category($cat);
				print "<option value='".$cat."'>";
				print $cm->course_name($course)." -> ".$cm->category_name($cat)."</option>".PHP_EOL;
			}
		}
		?>
	  </select>
	  </label><br />
	  <?php
	  }
	  else
	  	$delete_checked = true;
  }
  ?>
  <input type="radio" name="choice" id="1" value="delete" onChange="document.getElementById('delete_button').disabled=false;" <?php if ($delete_checked) print "checked"; ?>>
  <label for="1">Delete <?=$stuff?> permanently</label>
  <br /><br />
  <input type="submit" name="cancel" value="Cancel">
  <input type="submit" name="delete" value="Delete <?=$deleting?>" id="delete_button" <?php if(!$delete_checked) print "disabled"; ?>>
</form>
</div>
<?php
}

unset($cm); // closing database connection
unset($qm); // closing database connection
?>
</body>
</html>