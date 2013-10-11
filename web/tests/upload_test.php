<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/latex.lib.php");
require_once("../lib/Question.class.php");

if(isset($_FILES['file']))
{
	if(!is_dir("upload/"))
	{
		mkdir("upload");
	}
	$filepath = "upload/".$_FILES["file"]["name"];
	move_uploaded_file($_FILES["file"]["tmp_name"], $filepath);
	$tex = readTex($filepath);
	$questions = extractQuestions($tex);
	
	foreach ($questions as $question)
	{
		print $question;
		print "<hr />";
	}
	
	unlink($filepath); // deleting file when read
}
?>
<html>
<head>
<title>Bachelor Project</title>
</head>
<body>
<form method="POST" action="#" enctype="multipart/form-data">
     <p>Upload LaTeX file</p>
	 <input name="file" type="file"><br />
     <input type="submit" name="Submit" value="Submit">
</form>
</body>
</html>