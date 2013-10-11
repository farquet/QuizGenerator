<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/CourseManager.class.php");
require_once("../lib/QuestionManager.class.php");

$filename = basename($_SERVER['PHP_SELF']); // file called in URL

$menu = array();
$menu["Course list"] = "index.php";

$local_cm = false;
if (!isset($cm)) // if there isn't already a manager set by the script that includes this one
{
	$cm=new CourseManager();
	$local_cm = true;
}

$local_qm = false;
if (!isset($qm)) // if there isn't already a manager set by the script that includes this one
{
	$qm=new QuestionManager();
	$local_qm = true;
}

switch($filename)
{
	case "index.php": // course list
		break;
	case "course.php": // categories list
		if (isset($_GET['c']))
		{
			$co_id = $_GET['c'];
			$co_name = $cm->course_name($co_id);
			if ($co_name)
				$menu[$co_name] = "course.php?c=".$co_id;
		}
		break;
	case "category.php":
		if (isset($_GET['c'])) // questions list
		{
			$ca_id = $_GET['c'];
			$ca_name = $cm->category_name($ca_id);
			if ($ca_name)
			{
				$co_id = $cm->course_from_category($ca_id);
				$co_name = $cm-> course_name($co_id);
				if ($co_name)
				{
					$menu[$co_name] = "course.php?c=".$co_id;
					$menu[$ca_name] = "category.php?c=".$ca_id;
				}
			}
		}
		break;
	case "rename.php":
		$page = "rename";
	case "delete.php":
		if (!isset($page))
			$page = "delete";
		if (isset($_GET['co'])) // we are deleting a course
		{
			$co_id = $_GET['co'];
			$co_name = $cm->course_name($co_id);
			if ($co_name)
			{
				if ($page == "delete") $co_name = "<s>".$co_name."</s>";
				$menu[$co_name] = $page.".php?co=".$co_id;
			}
		}
		elseif (isset($_GET['cat'])) // we are deleting a category
		{
			$ca_id = $_GET['cat'];
			$ca_name = $cm->category_name($ca_id);
			if ($ca_name)
			{
				$co_id = $cm->course_from_category($ca_id);
				$co_name = $cm->course_name($co_id);
				if ($co_name)
				{
					$menu[$co_name] = "course.php?c=".$co_id;
					if ($page == "delete") $ca_name = "<s>".$ca_name."</s>";
					$menu[$ca_name] = $page.".php?cat=".$ca_id;
				}
			}
		}
		break;
	case "question.php":
		if (isset($_GET['q']))
		{
			$q_id = $_GET['q'];
			$ca_id = $qm->get_category_for_question($q_id);
			$ca_name = $cm->category_name($ca_id); // finding category name
			if ($ca_name)
			{
				$co_id = $cm->course_from_category($ca_id);
				$co_name = $cm-> course_name($co_id); // finding course name
				if ($co_name)
				{
					$menu[$co_name] = "course.php?c=".$co_id;
					$menu[$ca_name] = "category.php?c=".$ca_id;
					$menu["Question ".$q_id] = "question.php?q=".$q_id;
				}
			}
		}
		break;
}

?>
<div class="menu">
<div class="right-menu"><a href="tex2html_editor.php">Edit text-formatting rules</a></div>
<?php
foreach($menu as $link => $url)
{
	print "&rsaquo;"; // html arrow
	print ' <a href="'.$url.'">'.$link.'</a> ';
}

if ($local_cm) // if we just created the object for this file, we close database connection
	unset($cm);
if ($local_qm)
	unset($qm);
?>
<hr />
</div>