<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$root = "./";

require_once($root."lib/QuestionManager.class.php");
require_once($root."lib/UserStatsManager.class.php");
require_once($root."lib/config.inc.php");
require_once($root."lib/id_encoder.lib.php");
require_once($root."lib/grade.lib.php");

session_start();

$qm = new QuestionManager();

?>
<html>
<head>
<title>Quiz correction</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>styles/styles.css">
<script type="text/javascript" src="<?=$root?>MathJax/MathJax.js?config=default"></script>
</head>
<body>
<?php include_once("top.php"); ?>
<h3>Quiz correction</h3>
<p></p>
<?php

$saved_quiz = false; // state if the displayed quiz is loaded from the account page or not
$user_choices_saved = array();
$questions_ids = array();

// if we receive an encoded quiz in URL (this is the solution displayed from the account page)
if (isset($_GET['q']) && isset($_GET['uc']))
{
	$saved_quiz = true;
	
	$ids_string = basis_decode($_GET['q'], ID_ENCODER_BASIS);
	
	if ($ids_string)
	{
		$questions_ids = string_to_array($ids_string);
		
		if (!$questions_ids)
		{
			$questions_ids = array();
			$saved_quiz = false;
		}
	}
	else
		$saved_quiz = false;
		
	
	// retrieving user choices
	$user_choices_saved = string_to_array(basis_decode($_GET['uc'], ID_ENCODER_BASIS));
	if (!$user_choices_saved)
	{
		$user_choices_saved = array(); // in case of decoding error, we just set user choice to nothing
		for($i=0; $i < count($questions_ids); $i++) $user_choices_saved[] = 0;
	}
	
	if (!$saved_quiz) // if there was a problem decoding quiz, we go to account page
	{ ?>
		<script type="text/javascript">
        <!--
        //window.location = "account.php"
        //-->
        </script>
	<?php }
}

// displaying results
if (isset($_POST['ids']) || $saved_quiz)
{
	$correction = ""; // will contain HTML code of the correction
	
	$existing_ids = array(); // array of ids that we have checked that they exist
	$user_choices = array();
	
	$difficulty = "normal";
	
	if (isset($_POST['difficulty']))
		$difficulty = $_POST['difficulty'];
	
	if ($saved_quiz)
		$difficulty = ""; // we don't display it in this case
	
	// getting question ids from post form or from encoded data in URL
	if ($saved_quiz)
		$ids = $questions_ids;
	else
		$ids = explode("-", $_POST['ids']);
	
	// limiting at QUIZ_SIZE to avoid hacking that will download all answers for all questions
	$questions_count = 0;
	$correct_count = 0;
	$left_blank = 0;
	foreach ($ids as $key => $id)
	{
		// limiting at the max size we chose for a quiz to avoid a hack that will download all questions
		if ($questions_count > QUIZ_SIZE)
			break; // if HTML form is not hacked this will never be reached
		
		$question = $qm->get_question_by_id($id);
		
		if ($question) // if there is a question that exists with this id
		{
			$existing_ids[] = (int) $id;
			
			$user_choice = 0;
			
			if ($saved_quiz)
				$user_choice = $user_choices_saved[$key];
			elseif (isset($_POST[$id])) // if user has checked something
				$user_choice = $_POST[$id];
			
			if ($user_choice <= 0)
				$left_blank++;
			
			$user_choices[] = (int) $user_choice;
			
			if ($user_choice == $question->get_solution())
			{
				$correct_count++;
				$question->set_answered_correctly(); // for stats
			}
			else
				$question->set_answered_badly(); // for stats
			
			$qm->update_question($question, true);
			
			$correction .= '<div class="question_box">'.$question->html_correction($key+1, $user_choice).'</div>';
			$correction .= "<br />";
			
			$questions_count++;
		}
	}
?>
<h3>You scored <?=$correct_count?>/<?=$questions_count?>. Your grade is <?=lasec_grade($correct_count,$left_blank,$questions_count)?></h3>

<?=$correction;?>

<div class="menu" style="text-align:right;font-size:smaller;">Quiz reference : <?=basis_encode(array_to_string($ids), ID_ENCODER_BASIS)?></div>
<?php

if (isset($_SESSION['sciper']) && !$saved_quiz) // we save this quiz if user is authenticated and he is not looking at a solution
	{
	$course_id = 0;
	if (isset($_GET['co']))
		$course_id = $_GET['co'];
	
	// updating user statistics
	$user_stats = new UserStatsManager();
	$result = $user_stats->insert_stat($_SESSION['sciper'], $existing_ids, $user_choices, $course_id, $difficulty);
	unset($user_stats); // closing database connection
	}
}
unset($qm); // closing database connection
?>
</body>
</html>