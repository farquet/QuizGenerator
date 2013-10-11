<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "../";
require_once($root."lib/config.inc.php");
require_once($root."lib/latex.lib.php");
require_once($root."lib/CourseManager.class.php");
require_once($root."lib/QuestionManager.class.php");

// id and name of the category we are modifying
$cat_id = "";
$cat_name = "";

// Course and Question Managers
$cm = null;
$qm = null;

// Verifying if a specific category id has been set in the URL
if (isset($_GET['c']))
{
	$cat_id = $_GET['c'];
	if (is_numeric($cat_id))
	{
		$cm = new CourseManager();
		$cat_name = $cm->category_name($cat_id);
	}
}
if (!$cat_name)
{
	// redirection to the course list if we have no course specified in URL
	header("Location: index.php");
	exit();
}

// what to display to the user depending on what happened working with the database
$info = "";

$qm = new QuestionManager();

if (isset($_GET['rst']) && $_GET['rst'] == "y") // resetting statistics for this course
{
	
	$questions = $qm->get_questions_by_cat($cat_id);
	
	foreach ($questions as $q)
	{
		$q->reset_stats();
		$qm->update_question($q);
	}
	
	$info = "All question statistics have been reset !<br />";
}
?>
<html>
<head>
<title><?=$cat_name?></title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<script type="text/javascript" src="<?=$root?>MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php

// parsing latex file and saving questions in database
if(isset($_FILES["files"]))
{
	if(!is_dir("upload/"))
		mkdir("upload");
	
	// how much files have been submitted
	$num_files = count($_FILES["files"]["name"]);
	$count = 0; // counting how much questions with no error have been registered
	
	for($i = 0; $i < $num_files; $i++)
	{
		if (strlen($_FILES["files"]["name"][$i]) > 0)
		{
			$filepath = "upload/".$_FILES["files"]["name"][$i];
			move_uploaded_file($_FILES["files"]["tmp_name"][$i], $filepath);
			$latex = readTex($filepath);
			if ($latex)
			{
				$questions = extractQuestions($latex);
				
				foreach ($questions as $question)
				{
					// setting in which category these questions will be inserted
					$question->set_cat_id($cat_id);
					// if question has no bug, we insert it in the database
					if($question->audit_errors() <= 0 && $qm->insert_question($question))
						$count++;
				}
			}
			else
				$info .= "Extension or Mime-Type not allowed (".$_FILES["files"]["name"][$i].").<br />".PHP_EOL;
				
			unlink($filepath); // deleting file when read
		}
	}
	
	if ($count > 0)
	{
		$info .= $count." new question";
		if ($count > 1)
			$info .= "s"; // grammar is always important
		$info .= " inserted.<br />".PHP_EOL;
	}
	else
		$info .= "No new question created.";
}

if (isset($_GET['rm']))
{
	$rm_id = $_GET['rm'];
	$rm_question = $qm->get_question_by_id($rm_id);
	
	// checking if the question exists
	if ($rm_question)
	{
		$qm->delete_question_with_id($rm_id);
		$info = "The question with id ".$rm_id." has been deleted.";
	}
}

include('top.php'); // menu

?>
<h3><?=$cat_name?></h3>
<?php if (strlen($info) > 0) { ?>
<div class="info"><?=$info?></div>
<?php } ?>
<div style="margin-left:20px;width:300px">
<form method="POST" action="#" enctype="multipart/form-data">
	  <p><u>Choose LaTeX file(s)</u></p>
     <input name="files[]" type="file" multiple /> <br />
     <input type="submit" name="Submit" value="Submit">
</form>
</div>

<div>
  <form name="stats" method="post" action="category.php?c=<?=$cat_id?>&rst=y" onSubmit="return confirm('Do you really want to reset all statistics from <?=$cat_name?> category ?');">
    <input type="submit" name="Reset questions statistics" id="Reset questions statistics" value="Reset questions statistics">
  </form>
</div>
<?php
// Displaying all questions from this category
$questions = $qm->get_questions_by_cat($cat_id);

if (count($questions) > 0)
{
?>
<div style="margin-left:180px;font-size:1.4em">
<?php
	if (!isset($_GET['sort_stat']))
		print '<a href="category.php?c='.$cat_id.'&sort_stat=y">| Sort by statistics |</a>';
	else
		print '<a href="category.php?c='.$cat_id.'">| Sort by id |</a>';
?>
</div>
<div style="text-align:left;margin-left:10px;width:500px"><hr /></div>
<?php
}

if (isset($_GET['sort_stat'])) // sorting questions by statistics
	usort($questions, "Question::stat_cmp");
	
if (count($questions) > 0)
{
	foreach ($questions as $question)
	{
		$html_mode_old = $question->html_mode;
		$question->html_mode = true;
		$solution = $question->get_solution();
		$q_id = $question->get_id();
		print '<table cellpadding="2px" class="question_box_admin"><tr>'.PHP_EOL;
		// id, modifiy and delete images
		print '<td width="47px" valign="top" class="border-l" style="text-align:center;padding-top:5px;">';
		print '<span class="small">'.$q_id.'</span><br /><br />';
		print '<a href="category.php?c='.$cat_id.'&rm='.$q_id.'" ';
		print 'onclick="return confirm(\'Are you sure you want to delete permanently this question (id='.$q_id.') ?\')">';
		print '<img src="'.$root.'images/delete.png" width="20px"></a>'.PHP_EOL;
		print '<p></p>';
		print '<a href="question.php?q='.$q_id.'"><img src="'.$root.'images/modify.png" width="20px"></a>'.PHP_EOL;
		print '</td>'.PHP_EOL;
		// delimiter
		print '<td width="15px" class="border-l">&nbsp;</td>'.PHP_EOL;
		// question display
		print '<td><p class="question_title">'.$question->get_title();
		print '</p>'.PHP_EOL;
		foreach ($question->get_answers() as $num => $answer)
		{
			$image = "radio";
			if ($solution == ($num + 1)) // first solution has index 1, but num starts at 0
				$image .= "_correct";
			print '<img src="'.$root.'images/'.$image.'.png" width="12px">&nbsp; '.$answer."<br />".PHP_EOL;
		}
		if ($question->get_stat_total() > 0)
			print  '<br />Statistic : '.$question->get_stat_percent().'%';
		
		print '</td></tr>'.PHP_EOL.PHP_EOL;
		
		// This question contains errors
		if ($question->audit_errors() > 0)
		{
			print '<tr><td class="border-l"></td><td class="border-l"></td><td>'.PHP_EOL;
			print '<div class="f-left"><br /><img src="'.$root.'images/warning.png" width="30px"></div>';
			print '<div class="info f-left"><ul>';
			foreach ($question->get_errors() as $error)
				print "<li>".$error."</li>".PHP_EOL;
			print '</ul></div>';
			print '<div class="f-clear">';
			print '</td></tr>'.PHP_EOL;
		}
		
		$question->html_mode = $html_mode_old;
	}
}

unset($qm); // closing database connection
unset($cm); // closing database connection
?>
</body>
</html>