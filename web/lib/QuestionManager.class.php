<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("config.inc.php");
require_once("param.inc.php");
require_once("Question.class.php");

class QuestionManager {
	
	private $db_connect;
	
	function __construct()
	{
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_BASE.";";
		$this->db_connect = new PDO($dsn, DB_USER, DB_PASS);
	}
	
	function already_exists(Question $question)
	{
		// we check if there is the same question in the same category
		$questions_db = $this->get_questions_by_cat($question->get_cat_id());
		
		foreach ($questions_db as $question_db)
		{
			// if question titles are the same we check the rest of the question
			if (trim($question->get_title()) == trim($question_db->get_title()))
			{
				// checking if solution index are the same
				if ($question->get_solution() == $question_db->get_solution())
				{
					$answers = $question->get_answers();
					$size = count($answers);
					$answers_db = $question_db->get_answers();
					// checking if the number of answers are the same
					if ($size == count($answers_db))
					{
						$match = 0;
						
						for ($i=0; $i < $size; $i++)
						{
							// checking if the ith answer are the same in both questions
							if (trim($answers[$i]) == trim($answers_db[$i]))
								$match++;
						}
	
						// checking if the number of same answers equals the total of answers
						if ($match == $size)
						{
							// everything corresponds so we can return the id
							return $question_db->get_id();
						}
					}
				}
			}
		}
		
		return false;
	}
	
	function insert_question(Question $question)
	{
		if (!$this->already_exists($question))
		{
			// preparing variables to insert in the database
			$title = $question->get_title();
			$serialized_answers = serialize($question->get_answers());
			$solution = $question->get_solution();
			$cat_id = $question->get_cat_id();
			
			$request = $this->db_connect->prepare('INSERT INTO '.TBL_QUESTIONS.
					' (question, answers, solution, category_id) VALUES (:question, :ser_answers, :sol, :cat)');
			
			$success = $request->execute(array(':question' => $title,
											   ':ser_answers' => $serialized_answers,
											   ':sol' => $solution,
											   ':cat' => $cat_id));
			
			// if success we return the new id
			if($success)
			{
				$id = $this->db_connect->lastInsertId();
				$question->set_id((int) $id);
				return $id;
			}
		}
		
		return false;
	}
	
	function check_ids(array $ids)  // returns true if all ids exist
	{
		$ids_string = join(', ', $ids);
		$request = $this->db_connect->prepare('SELECT COUNT(id) FROM '.TBL_QUESTIONS.' WHERE id IN (:ids)');
		$result = $request->execute(array(':ids' => $ids_string));
		
		return ($result == count($ids));
	}
	
	function delete_question_with_id($id)
	{
		$request = $this->db_connect->prepare('DELETE FROM `'.TBL_QUESTIONS.'` WHERE id = :id');
		$request->execute(array(':id' => $id));
		return $request->rowCount(); // returns the number of deleted elements (greater than 0 is success)
	}
	
	function delete_questions_from_category($cat_id)
	{
		$request = $this->db_connect->prepare('DELETE FROM `'.TBL_QUESTIONS.'` WHERE category_id = :id');
		$request->execute(array(':id' => $cat_id)); // returns the numbero of deleted elements
		return $request->rowCount();
	}
	
	function move_questions($from_cat_id, $to_cat_id)
	{
		// checking if the question already exists in the destination category
		$from_cat_questions = $this->get_questions_by_cat($from_cat_id);
		foreach ($from_cat_questions as $test_question)
		{
			$test_question->set_cat_id($to_cat_id);
			
			$id_test = $this->already_exists($test_question);
			if($id_test)
			{
				// we add the stats of the question we are going to delete to the corresponding question in new category
				$new_question = $this->get_question_by_id($id_test);
				$new_question->set_stat_correct($new_question->get_stat_correct() + $test_question->get_stat_correct());
				$new_question->set_stat_total($new_question->get_stat_total() + $test_question->get_stat_total());
				$this->update_question($new_question);
				
				// if the question already exists in the destination category, we remove it
				$this->delete_question_with_id($test_question->get_id());
			}
		}
		$request = $this->db_connect->prepare('UPDATE '.TBL_QUESTIONS.' SET category_id = :to_id  WHERE category_id = :from_id');
		$request->execute(array(':from_id' => $from_cat_id, ':to_id' => $to_cat_id));
		return $request->rowCount(); // returns number of deletions (greater than 0 if success)
	}
	
	function reset_stats()
	{
		$request = $this->db_connect->prepare('UPDATE '.TBL_QUESTIONS.' SET stat_correct = :correct, stat_total = :total');
		$request->execute(array(':correct' => 0, ':total' => 0));
		return $request->rowCount();
	}
	
	// Using the question id as a reference it will update all other fields
	function update_question(Question $question, $ignore_audit=false)
	{
		$count = 0;
		
		$old_question = $this->get_question_by_id($question->get_id());
		if ($old_question) // checking if a question with this id exists in the database
		{
			// checking if the given question has less issues than the one in the database
			if ($question->audit_errors() <= $old_question->audit_errors() || $ignore_audit)
			{
				$question_db = $this->get_question_by_id($question->get_id());
				
				if ($question_db)
				{
					$request = $this->db_connect->prepare('UPDATE '.TBL_QUESTIONS.
						' SET question = :question, answers = :ser_answers, solution = :sol, category_id = :cat, stat_correct = :s_correct, stat_total = :s_total  WHERE id = :id');
				
					$request->execute(array(':question' => $question->get_title(),
											':ser_answers' => serialize($question->get_answers()),
											':sol' => $question->get_solution(),
											':cat' => $question->get_cat_id(),
											':id' => $question->get_id(),
											':s_correct' => $question->get_stat_correct(),
											':s_total' => $question->get_stat_total()));
					$count += $request->rowCount();
				}
			}
		}
		
		return $count; // number of questions updated (greater than 0 if success)
	}
	
	// returns a question object from an array received from the SQL server
	private static function question_from_db_result($db_result)
	{
		if ($db_result)
		{
			$answers = unserialize($db_result['answers']);
			if (!is_array($answers))
				$answers = array();
			
			return new Question($db_result['question'],
								$answers,
								(int) $db_result['solution'],
								(int) $db_result['id'],
								(int) $db_result['category_id'],
								(int) $db_result['stat_correct'],
								(int) $db_result['stat_total']);
		}
		else // no match
			return false;
	}
	
	function get_question_by_id($id)
	{
		$request = $this->db_connect->prepare('SELECT * FROM '.TBL_QUESTIONS.' WHERE id = :id');
		$request->execute(array(':id' => $id));
		$result = $request->fetch();
		
		return $this->question_from_db_result($result);
	}
	
	function get_questions($rand=false)
	{
		$questions = array();
		
		$request_string = 'SELECT * FROM '.TBL_QUESTIONS;
		if ($rand)
			$request_string .= ' ORDER BY RAND()';
		$request = $this->db_connect->prepare($request_string);
		$request->execute();
		
		while ($result = $request->fetch())
		{
			$questions[] = $this->question_from_db_result($result);
		}
		return $questions;
	}
	
	function get_category_for_question($id)
	{
		$request = $this->db_connect->prepare('SELECT category_id FROM '.TBL_QUESTIONS.' WHERE id = :id');
		$request->execute(array(':id' => $id));
		$result = $request->fetch();
		if ($result)
			return $result['category_id'];
		
		return false;
	}
	
	function get_questions_by_cat($cat_id, $rand=false)
	{
		$questions = array();
		
		$request_string = 'SELECT * FROM '.TBL_QUESTIONS.' WHERE category_id = :cat_id';
		if ($rand)
			$request_string .= ' ORDER BY RAND()';
		$request = $this->db_connect->prepare($request_string);
		$request->execute(array(':cat_id' => $cat_id));
		
		while ($result = $request->fetch())
		{
			$questions[] = $this->question_from_db_result($result);
		}
		
		return $questions;
	}
	
	function get_questions_for_cat_array(array $cat_ids)
	{
		$questions = array();
		
		foreach ($cat_ids as $cat)
			$questions = array_merge($questions, $this->get_questions_by_cat($cat));
		
		return $questions;
	}
	
	function __destruct()
	{
		$db_connect = null;
	}
}
?>