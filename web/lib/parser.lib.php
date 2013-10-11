<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("config.inc.php");
require_once("Question.class.php");

/* function that takes latex code and returns the same code without the inline
** comments (starting with %) and long comments starting with \begin{comment}
*/
function removeComments($latex)
{
	$process = $latex;
	$start = 0;
	
	// Removing comments starting with %
	$commentPos = strpos($process, "%");
	
	// if we found a % that starts a comment (not \%) we remove the end of line
	while ($commentPos)
	{
		if ($commentPos == 0 || $process{$commentPos-1} != "\\")
		{
			$EOLPos = strpos($process, PHP_EOL, $commentPos); // finding end of line
			if (!$EOLPos) // setting EOL to the end of document if no return found
				$EOLPos = strlen($process)-1;
			
			// removing end of line if % found
			$process = substr_replace($process, "", $commentPos, $EOLPos - $commentPos + 1);
			$start = $commentPos;
		}
		elseif ($process{$commentPos-1} == "\\")
		{
			$start = $commentPos + 1;
		}
		// trying to find another one % and starting again
		$commentPos = strpos($process, "%", $start);
	}
	
	// Removing comments between \begin{comment} and \end{comment}
	$beginCommentPos = strpos($process, "\\begin{comment}");
	while ($beginCommentPos)
	{
		$endCommentPos = strpos($process, "\\end{comment}", $beginCommentPos);
		if (!$endCommentPos) // comment not closed
			$endCommentPos = strlen($process)-1;
		
		// removing comment
		$process = substr_replace($process, "", $beginCommentPos, $endCommentPos - $beginCommentPos + 13);
			
		$beginCommentPos = strpos($process, "\\begin{comment}");
	}
	
	return $process;
}

function getNextBrace($string)
{
	$pos = 0;
	$balance = 0; // brace brackets balance
	
	if ($string{0} != "{")
	{
		return false;
	} else {
		$pos++;
		// moving char by char. Stopping only if closing brace bracket found and the rest is balanced
		while($pos < strlen($string) && ($string{$pos} != "}" || $balance != 0))
		{
			if ($string{$pos} == "{")
				$balance++;
			if ($string{$pos} == "}")
				$balance--;
			
			$pos ++;
		}
		
		// if we are at the end of the string and didn't find a closing brace
		if ($pos == strlen($string) || $string{$pos} != "}")
			return false;
		$pos++;
		
		return substr($string, 0, $pos);
	}
}

/* Returns an array of questions parsing latex file
** Questions have this form : \question[<solution index>]{<question title>}{<answer>}{<answer>}...
*/
function extractQuestions($latex)
{
	$questions = array();
	
	// before starting we remove all comments to avoid undesired questions or content
	$process = removeComments($latex);
	$pos = 0; // position of the iterator through latex code
	
	// This is to avoid that strpos returns 0 (if file starts with \question)
	// and to be interpreted as FALSE
	$process = " ".$process;
	
	$questionPos = strpos($process, "\\question[");
	
	while ($questionPos)
	{
		// resetting variables for new question
		$malformed_question = false;
		$end_of_question = false;
		$question_solution = -1;
		$question_title = "";
		$question_answers = array();
		$pos = $questionPos + 10; // position of first digit of right answer number
		
		// getting solution index
		if (is_numeric($process{$pos}))
		{
			$question_solution = $process{$pos};
			
			$pos++;
			// handling several digits solution (only possible if 10 or more answers)
			while (is_numeric($process{$pos}))
			{
				$question_solution = 10 * $question_solution + $process{$pos};
				$pos++;
			}
			
			if ($process{$pos} == "]")
			{
				$pos++;
				// creating a fresh substring from current position
				$string_to_match = substr($process, $pos);
				// getting rid of carriage returns and spaces at the beginning of the string
				$count = strlen($string_to_match); // count stores how much spaces will be removed
				//$string_to_match = preg_replace('/^[\r\n ]+/', '', $string_to_match);
				$string_to_match = ltrim($string_to_match);
				$count -= strlen($string_to_match);
				$pos += $count; // increasing pos counter with the number of spaces removed
				if ($string_to_match{0} == "{")
				{
					// getting title of question by finding balanced brace brackets
					$question_title = getNextBrace($string_to_match);
					if ($question_title)
					{
						$pos += strlen($question_title); // moving pos to the end of the question title
						$string_to_match = substr($process, $pos);
						
						$count = strlen($string_to_match);
						$string_to_match = ltrim($string_to_match); // remove spaces at beginning of string
						$count -= strlen($string_to_match);
						$pos += $count; // moving pos to the beginning of the first answer brace
						
						// iterating on answers
						while(strlen($string_to_match) > 0 && $string_to_match{0} == "{" && !$end_of_question)
						{
							$question_answers[] = getNextBrace($string_to_match);
							
							if (end($question_answers)) // if we found a opening and matching closing brace
								$pos += strlen(end($question_answers)); // we move pos to the end of this brace
							else // if there is an error, we skip this brace
							{
								$pos++;
								$end_of_question = true;
							}
							
							$string_to_match = substr($process, $pos);
							
							$count = strlen($string_to_match);
							$string_to_match = ltrim($string_to_match); // remove spaces at beginning of string
							$count -= strlen($string_to_match); // returns the number of spaces removed
							$pos += $count; // moving pos after the spaces we removed
						}
					}
				} else { // no opening brace bracket for question title
					$malformed_question = true;
				}
			} else { // missing closing brace after solution index
				$malformed_question = true;
			}
		} else { // no integer at the solution index position
			$malformed_question = true;
		}
		
		if (!$malformed_question)
		{
			// removing brace brackets : {abc} -> abc
			// and removing useless spaces or carriage return (multiple spaces replaced by one, spaces at beginning and end removed)
			$question_title = trim(preg_replace('!\s+!', ' ', substr($question_title, 1, strlen($question_title)-2)));
			for ($i=0; $i < count($question_answers); $i++)
				$question_answers[$i] = trim(preg_replace('!\s+!', ' ', substr($question_answers[$i], 1, strlen($question_answers[$i])-2)));
			
			// creating a question object with parsed data
			$question = new Question($question_title, $question_answers, (int) $question_solution);
			array_push($questions, $question);
		}
		
		if ($pos < strlen($process))
			$questionPos = strpos($process, "\\question[", $pos); // finding next question
		else // in case we consumed all latex code, strpos would generate a warning
			$questionPos = FALSE;
	}
	
	return $questions;
}

?>