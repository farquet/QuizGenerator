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
<title>Category test</title>
</head>
<body>
<?php

$manager = new CourseManager();

$category = "Conclusion";
$course = "Cryptography and Security";
$course_id = $manager->course_id($course);
if($course_id)
{
	if($manager->create_category($category, $course_id))
	{
		print $category." category created !<br />The id is ".$manager->category_id($category, $course_id);
	}
	else
	{
		print $category." course not created.<br />Already in database with id : ".$manager->category_id($category, $course_id);
	}
}
else
{
	print "Unable to create category because the course ".$course." doesn't exist";
}

print "<hr />";

// iterating over all courses id and finding corresponding name
foreach ($manager->get_categories_ids() as $id)
	print $id." -> '".$manager->category_name($id)."' in course '".$manager->course_name_from_category($id)."'<br />\n";

unset($manager); // closing database connection

?>
</body>
</html>