<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("config.inc.php");
require_once("Question.class.php");

// The lowest grade you can have
define("MIN_GRADE", 1);

/* The LASEC grade is calculated like this :
 * Start at maximum : 6
 * - Loses 1 point if response is incorrect
 * - Loses 1/2 point if response is left blank
 */
function lasec_grade($correct, $left_blank, $total)
{
	$grade = 6.0;
	
	$incorrect = $total - $correct - $left_blank;
	
	$grade -= $incorrect; // loses 1 point for each wrong answer
	$grade -= 0.5 * $left_blank; // loses half point for each answer left blank
	
	// We avoid negative grades, it's too mean
	if ($grade <= MIN_GRADE)
		$grade = MIN_GRADE;
	
	return $grade;
}

?>