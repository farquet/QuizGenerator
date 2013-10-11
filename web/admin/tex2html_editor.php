<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "../";
require_once($root."lib/config.inc.php");
require_once($root."lib/latex.lib.php");


// what to display to the user depending on what happened working with the database
$info = "";

$path = $root.TEX2HTML_FOLDER;

// we ensure that tex2html.txt exists. If not, we create it with the content of default.txt or from nothing
if (!file_exists($path."tex2html.txt"))
{
	if (file_exists($path."default.txt"))
		copy($path."default.txt", $path."tex2html.txt");
	else
		touch($path."tex2html.txt");
}

// handling form submission (modification of the rules)
if (isset($_POST['tex2html']))
{
	$received_rules = $_POST['tex2html'];
	// removing the protection of & that we set to avoid special html chars to be rendered
	$received_rules = str_replace("&amp;", "&", $received_rules);
	
	// if magic quotes are enabled in PHP config, backslashes will be doubled and we don't want it
	if (get_magic_quotes_gpc())
		$received_rules = preg_replace('/\\\\\\\\/', '\\' , $received_rules);
	
	$new_tex2html = "";
	
	// inspecting each rule
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $received_rules) as $rule)
	{
		// we skip blank lines without raising an error
  		if (strlen(trim($rule)) > 0)
		{
			// emptying brace brackets just to make them look nicer
			$rule = preg_replace('/\{\s+\}/', '{}', $rule);
			
			if (is_tex2html_rule_ok($rule))
				$new_tex2html .= $rule.PHP_EOL;
			else
				$info .= $rule." doesn't have the right structure, so it has been removed.<br />";
		}
	}
	
	// saving new safe rules
	file_put_contents($path."tex2html.txt", $new_tex2html);
}

$tex2html = file($path."tex2html.txt"); // returns the file in an array of lines
?>
<html>
<head>
<title>Latex2html Editor</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
</head>
<body>
<?php
include('top.php'); // menu
?>
<div class="info"><?=$info?></div>
<form action="#" method="post" name="rename" id="rename">
  <p>Edit the rules to convert Latex text-formatting commands in HTML.</p>
  <p>
    The structure is <em>latex :: html</em><br>
    <br />
    If there are brace brackets in the Latex command,<br>
    you must have $$$ in the html side to match corresponding content.</p>
  <p>
    <textarea name="tex2html" id="tex2html" cols="65" rows="15"><?=join('', str_replace("&", "&amp;", $tex2html))?></textarea>
  </p>
  <input name="button" type="submit" value="Save">
</form>
</body>
</html>