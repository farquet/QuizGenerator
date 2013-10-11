<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/


require_once("../lib/CourseManager.class.php");

?>
<html>
<head>
<title>Course test</title>
</head>
<body>
<?php

$manager = new CourseManager();

$course = "Advanced Cryptography";
if($manager->create_course($course))
{
	print $course." course created !<br />The id is ".$manager->course_id($course);
}
else
{
	print $course." course not created.<br />Already in database with id : ".$manager->course_id($course);
}

print "<hr />";

// iterating over all courses id and finding corresponding name
foreach ($manager->get_courses_ids() as $id)
	print $id." -> ".$manager->course_name($id)."<br />\n";

unset($manager); // closing database connection

?>
</body>
</html>