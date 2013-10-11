<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("config.inc.php");
require_once("latex.lib.php");

/* Represents a question.
 * Returns latex content by default, or html if html_mode is set to true.
 * Note that only text-formatting latex command are converted.
 */
class Question {
	public $html_mode = HTML_MODE;
	
	private $m_id = -1;
	private $m_solution = 0; // answer range = [1, count(answers)]. 0 is undefined
	private $m_title = "";
	private $m_answers = array();
	private $m_cat_id = -1;
	private $m_stat_correct = 0;
	private $m_stat_total = 0;
	
	private $m_error_log = array();
	
	function __construct($title = "",
						 array $answers = array(),
						 $solution_index = 0,
						 $id = -1,
						 $cat_id = -1,
						 $stat_correct = 0,
						 $stat_total = 0)
	{
		$this->set_title($title);
		$this->set_answers($answers);
		$this->set_solution($solution_index);
		$this->set_id($id);
		$this->set_cat_id($cat_id);
		$this->set_stat_correct($stat_correct);
		$this->set_stat_total($stat_total);
	}
	
	function is_correct($solution)
	{
		return ($solution > 0 && $solution == $this->get_solution());
	}
	
	/* Returns an integer, the number of errors
	 * malformed question or answers, solution not in range, ...
	 * All errors are stored and are returned by get_errors
	 * doesn't check id, cat_id or stats because it isn't necessary for a good constructed question
	 */
	function audit_errors()
	{
		$error_count = 0;
		$this->m_error_log = array();
		
		// checking title length
		if (strlen($this->m_title) < 1)
		{
			$error_count++;
			$this->m_error_log[] =  "Question title cannot be empty.";
		}
		elseif (strlen($this->m_title) > MAX_STRING_LENGTH)
		{
			$error_count++;
			$this->m_error_log[] = "Question title cannot be longer than ".MAX_STRING_LENGTH." characters.";
		}
		
		// checking if there is at least 2 answers
		if (count($this->m_answers) < 2)
		{
			$error_count++;
			$this->m_error_log[] = "There must be at least 2 answers.";
		}
		
		for ($i=0; $i < count($this->m_answers); $i++)
		{
			$answer = $this->m_answers[$i];
			// checking each answer length
			if (strlen($answer) < 1)
			{
				$error_count++;
				$this->m_error_log[] = "Answer ".($i+1)." cannot be empty.";
			}
			elseif (strlen($answer) > MAX_STRING_LENGTH)
			{
				$error_count++;
				$this->m_error_log[] = "Answer ".($i+1)." cannot be longer than ". MAX_STRING_LENGTH." characters.";
			}
		}
		
		// checking if the solution is an int and is in answers range
		if (!is_int($this->m_solution))
		{
			$error_count++;
			$this->m_error_log[] = "Solution index should be an integer.";
		}
		elseif ($this->m_solution < 1 || $this->m_solution > count($this->m_answers))
		{
			$error_count++;
			$this->m_error_log[] = "Solution index isn't in the answers range (starting at 1).";
		}
		
		return $error_count;
	}
	
	function html_form_field($index, $random = false)
	{
		// we need html mode to be on so we store current state and activate it
		$html_mode = $this->html_mode;
		$this->html_mode = true;
		
		$answers = $this->get_answers();
		
		$html  = '<p style="font-size:1.2em">'.$index.'. '.$this->get_title().'</p>'.PHP_EOL;
		
		$answers_html = array();
		foreach ($answers as $key => $answer)
		{
			$sol = $key + 1; // solutions start at 1
			$temp = '<input type="radio" name="'.$this->get_id().'" id="'.$this->get_id().'_'.$sol.'" value="'.$sol.'" />'.PHP_EOL;
			$temp .= '<label for="'.$this->get_id().'_'.$sol.'"> '.$answer.'</label><br />'.PHP_EOL;
			$answers_html[] = $temp;
		}
		
		// shuffling after answer numerotation
		if ($random)
			shuffle($answers_html);
		
		$html .= join($answers_html);
		
		// restoring html mode to previous state
		$this->html_mode = $html_mode;
		
		return $html;
	}
	
	function html_correction($index, $user_choice = -1, $root="./")
	{
		// we need html mode to be on so we store current state and activate it
		$html_mode = $this->html_mode;
		$this->html_mode = true;
		
		$answers = $this->get_answers();
		
		$html  = '<p style="font-size:1.2em">'.$index.'. '.$this->get_title().'</p>'.PHP_EOL;
		
		foreach ($answers as $key => $answer)
		{
			$correctness = '';
			$sol = $key + 1; // solutions start at 1
			
			if ($this->get_solution() == $sol) // if this is the correct solution
			{
				$correctness = "_correct";
				if ($user_choice != $sol) // but it's not the user choice
					$correctness .= "_yellow";
			}
			elseif ($user_choice == $sol) // if it's the wrong solution but is the user choice
				$correctness = "_wrong";
				
			$html .= '<div class="solution"><img src="'.$root.'images/radio'.$correctness.'.png" width="16px">'.PHP_EOL;
			$html .= ' '.$answer.'</div>'.PHP_EOL;
		}
		
		// restoring html mode to previous state
		$this->html_mode = $html_mode;
		
		return $html;
	}
	
	function __toString()
	{
		$nl = "<br />".PHP_EOL; // new line
		
		$string  = "id : ".$this->get_id()." (category id : ".$this->get_cat_id().")".$nl;
		$string .= "solution : ".$this->get_solution().$nl;
		$string .= "title : ".$this->get_title().$nl;
		
		foreach ($this->get_answers() as $index => $answer)
			$string .= "answer ".($index+1)." : $answer".$nl;
		
		return $string;
	}
	
	function get_latex()
	{
		$latex = '\question['.$this->get_solution().']{'.$this->get_title().'}'.PHP_EOL;
		foreach ($this->get_answers() as $answer)
			$latex .= '{'.$answer.'}'.PHP_EOL;
		return $latex;
	}
	
	// comparison function to sort arrays of questions depending on statistics
	static function stat_cmp(Question $a, Question $b)
	{
		if ($a->get_stat_percent() < $b->get_stat_percent())
			return -1;
		elseif ($a->get_stat_percent() > $b->get_stat_percent())
			return 1;
		else
			return 0;
	}
	
	// Setters
	function set_title($title)
	{
		$this->m_title=$title;
	}
	
	function set_answers(array $answers)
	{
		$this->m_answers = $answers;
	}
	
	function set_solution($solution)
	{
		if (is_numeric($solution))
		{
			$this->m_solution = (int) $solution;
		}
	}
	
	function set_id($id)
	{
		if (is_numeric($id))
		{
			$this->m_id = (int) $id;
		}
	}
	
	function set_cat_id($cat_id)
	{
		if (is_numeric($cat_id))
		{
			$this->m_cat_id = (int) $cat_id;
		}
	}
	
	function set_answered_correctly()
	{
		$this->set_stat_correct($this->get_stat_correct()+1);
		$this->set_stat_total($this->get_stat_total()+1);
	}
	
	function set_answered_badly()
	{
		$this->set_stat_total($this->get_stat_total()+1);
	}
	
	function set_stat_correct($stat_correct)
	{
		if(is_numeric($stat_correct))
		{
			if($stat_correct < 0)
				$this->m_stat_correct = 0;
			else
				$this->m_stat_correct = $stat_correct;
		}
	}
	
	function set_stat_total($stat_total)
	{
		if(is_numeric($stat_total))
		{
			if($stat_total < 0)
				$this->m_stat_total = 0;
			else
				$this->m_stat_total = $stat_total;
		}
	}
	
	function reset_stats()
	{
		$this->m_stat_correct = 0;
		$this->m_stat_total = 0;
		return true;
	}
	
	// Getters
	function get_title()
	{
		if ($this->html_mode)
			return tex2html($this->m_title);
		
		return $this->m_title;
	}
	
	function get_answers()
	{
		if ($this->html_mode)
		{
			$html_answers = array();
			foreach ($this->m_answers as $answer)
				array_push($html_answers, tex2html($answer));
			
			return $html_answers;
		}
		return $this->m_answers;
	}
	
	function get_solution()
	{
		return $this->m_solution;
	}
	
	function get_id()
	{
		return $this->m_id;
	}
	
	function get_cat_id()
	{
		return $this->m_cat_id;
	}
	
	function get_stat_correct()
	{
		return $this->m_stat_correct;
	}
	
	function get_stat_total()
	{
		return $this->m_stat_total;
	}
	
	function get_stat_percent()
	{
		// if we have no statistics, we return 100
		if ($this->m_stat_total <= 0)
			return 100;
		
		return round($this->m_stat_correct * 100 / $this->m_stat_total, 1);
	}
	
	function get_errors()
	{
		return $this->m_error_log;
	}
}
?>