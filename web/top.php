<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("lib/CourseManager.class.php");
require_once("lib/QuestionManager.class.php");

$filename = basename($_SERVER['PHP_SELF']); // file called in URL

$menu = array();
$menu["Course list"] = "choices.php";

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


if (isset($_GET['co']))
{
	$co_id = $_GET['co'];
	$co_name = $cm->course_name($co_id);
	if ($co_name)
		$menu[$co_name] = "choices.php?co=".$co_id;
}

?>
<div class="menu">
<div class="right-menu">
<?php

// for testing outside of epfl.ch subnet
//$_SESSION['sciper'] = "999999";

if (isset($_SESSION['sciper']))
{
	print '<a href="account.php">My account</a>';
	print ' - <a href="login.php?logout=y">Logout</a> ('.$_SESSION['sciper'].')';
}
else {
?><a href="login.php">Log in with Tequila</a>
<?php
}
?></div>
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
