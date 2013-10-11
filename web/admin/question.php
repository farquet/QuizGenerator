<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "../";

require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/config.inc.php");

// where to redirect if needed
$location = "";

// message to display to the user if something went wrong
$info = "";

$q_id = 0;
$question = null;
$qm = null;

if (isset($_GET['q']))
{
	$qm = new QuestionManager();
	$q_id = $_GET['q'];
	$question = $qm->get_question_by_id($q_id);
	
	if ($question)
	{
		if (isset($_POST['latex']))
		{
		    $latex_field = $_POST['latex'];
		    
		    // if PHP has magic quotes on, it will add unwanted slashes that we remove here
		    if (get_magic_quotes_gpc())
				$latex_field = stripslashes($latex_field);
				//$latex_field = preg_replace('/\\\\\\\\/', '\\' , $latex_field);
				
			$questions = extractQuestions($latex_field);
			if (count($questions) > 0)
			{
				$new_question = $questions[0];
				
				// if the user changed something we save also other values
				if ($new_question->get_latex() != $question->get_latex())
				{
					$new_question->set_id($question->get_id());
					$new_question->set_cat_id($question->get_cat_id());
					$new_question->set_stat_correct($question->get_stat_correct());
					$new_question->set_stat_total($question->get_stat_total());
					
					if($qm->update_question($new_question, SAVE_QUESTIONS_WITH_ERRORS))
					{
						//$info .= "Question has been successfully updated.".PHP_EOL;
						$question = $new_question;
					}
					else
						$info .= "Question contained more errors, so it has not been updated.".PHP_EOL;
				}
			}
			else
				$info .= "Question not updated. The latex you entered had errors.";
		}
	}
	else // no question associated with this id
		$location = "index.php";
}
else // if we have received no question to handle, we redirect
	$location = "index.php";

// redirection if needed
if (strlen($location) > 0)
	header("Location: ".$location);
	
if (isset($_GET['rst']) && $_GET['rst'] == 'y')
{
	$question->reset_stats();
	$qm->update_question($question);
}
?>
<html>
<head>
<title>Modify question</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<script type="text/javascript" src="../MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php
include('top.php'); // menu

if($question->audit_errors() > 0)
{
?>
<table border="0" width="700px"><tr>
<td valign="top" width="90px" style="text-align:center"><br /><img src="<?=$root?>images/warning.png"></td>
<td class="border-l info" style="padding-left:10px"><br />
The question contains this errors.<br />
<ul>
<?php
	foreach ($question->get_errors() as $error)
		print "<li>".$error."</li>".PHP_EOL;
}
?>
</ul>
</td></tr></table>
<div class="info" style="margin-left:20px"><?=$info?></div>
<?php
if ($question)
{
?>
<div style="width:850px;text-align:left;margin-left:20px;">
<div style="width:450px;text-align:center">
<h3>Edit question <?=$question->get_id()?></h3>
<form method="post" action="#">
<textarea rows="7" cols="60" name="latex">
<?php
$latex = $question->get_latex();

print $latex;
?>
</textarea><br /><br />
<input type="submit" value="Modify">
</form>
</div>
<div style="text-align:left">
<br />
<form name="stats" method="post" action="question.php?q=<?=$q_id?>&rst=y" onSubmit="return confirm('Do you really want to reset the statistics for this question ?');">
<p>Statistics : <?=$question->get_stat_correct()?>/<?=$question->get_stat_total()?> (<?=$question->get_stat_percent()?>%)
    &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="Reset" id="Reset" value="Reset statistics">
</p>
</form>
<?php

// question display
$question->html_mode = true;
$solution = $question->get_solution();

print '<p class="question_title">'.$question->get_title().'</p>'.PHP_EOL;
foreach ($question->get_answers() as $num => $answer)
{
	$image = "radio";
	if ($solution == ($num + 1)) // first solution has index 1, but num starts at 0
		$image .= "_correct";
	print '<img src="'.$root.'images/'.$image.'.png" width="12px">&nbsp; '.$answer."<br />".PHP_EOL;
}
?>
</div>
</div>
<?php
}

unset($qm); // closing database connection
?>
</body>
</html>