<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("config.inc.php");
require_once("param.inc.php");

class CourseManager {
	
	private $db_connect;
	
	function __construct()
	{
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_BASE.";";
		$this->db_connect = new PDO($dsn, DB_USER, DB_PASS);
	}
	
	function course_id($name)
	{
		$request = $this->db_connect->prepare('SELECT id FROM '.TBL_COURSES.' WHERE name = :name');
		$request->execute(array(':name' => $name));
		$result = $request->fetch();
		
		if($result)
			return $result['id'];
		
		return false;
	}
	
	function course_name($id)
	{
		$request = $this->db_connect->prepare('SELECT name FROM '.TBL_COURSES.' WHERE id = :id');
		$request->execute(array(':id' => $id));
		$result = $request->fetch();
		
		if($result)
			return $result['name'];
		
		return false;
	}
	
	function create_course($name)
	{
		// checking if this course already exists
		if(!$this->course_id($name))
		{
			// checking string length
			if (strlen($name) > 1 && strlen($name) <= MAX_LENGTH_COURSES)
			{
			$request = $this->db_connect->prepare('INSERT INTO '.TBL_COURSES.' (name) VALUES (:name)');
			$request->execute(array(':name' => $name));
			
			// we return the id of this course
			return $this->db_connect->lastInsertId();
			}
		}
		
		return false;
	}
	
	function delete_course($id, $delete_categories = false)
	{
		$count = 0;
		
		if ($delete_categories)
		{
			// deleting categories
			$request = $this->db_connect->prepare('DELETE FROM `'.TBL_CATEGORIES.'` WHERE course_id = :id');
			$request->execute(array(':id' => $id));
			$count += $request->rowCount();
		}
		
		// deleting course
		$request = $this->db_connect->prepare('DELETE FROM `'.TBL_COURSES.'` WHERE id = :id');
		$request->execute(array(':id' => $id));
		$count += $request->rowCount();
		
		return $count; // returns the number of deletions
	}
	
	function rename_course($id, $new_name)
	{
		$request = $this->db_connect->prepare('UPDATE '.TBL_COURSES.' SET name = :new_name  WHERE id = :id');
		$request->execute(array(':new_name' => $new_name, ':id' => $id));
		return $request->rowCount(); // returns number of update (greater than 0 if success)
	}
	
	function get_courses_ids()
	{
		$ids = array();
		
		$request = $this->db_connect->prepare('SELECT id FROM '.TBL_COURSES);
		$request->execute();
		
		// filling ids array
		while ($result = $request->fetch())
			$ids[] = $result['id'];
		
		return $ids;
	}
	
	// ***** CATEGORIES *****
	function category_id($name, $course_id)
	{
		$request = $this->db_connect->prepare('SELECT id FROM '.TBL_CATEGORIES.' WHERE name = :name AND course_id = :course_id');
		$request->execute(array(':name' => $name, ':course_id' => $course_id));
		$result = $request->fetch();
		
		if($result)
			return $result['id'];
		
		return false;
	}
	
	function category_name($id)
	{
		$request = $this->db_connect->prepare('SELECT name FROM '.TBL_CATEGORIES.' WHERE id = :id');
		$request->execute(array(':id' => $id));
		$result = $request->fetch();
		
		if($result)
			return $result['name'];
		
		return false;
	}
	
	function course_from_category($cat_id)
	{
		$request = $this->db_connect->prepare('SELECT course_id FROM '.TBL_CATEGORIES.' WHERE id = :id');
		$request->execute(array(':id' => $cat_id));
		$result = $request->fetch();
		
		if($result)
			return $result['course_id'];
		
		return false;
	}
	
	function course_name_from_category($cat_id)
	{
		return $this->course_name($this->course_from_category($cat_id));
	}
	
	function create_category($name, $course_id)
	{
		// checking if this course already exists
		if(!$this->category_id($name, $course_id))
		{
			$request = $this->db_connect->prepare('INSERT INTO '.TBL_CATEGORIES.' (name, course_id) VALUES (:name, :course_id)');
			$request->execute(array(':name' => $name, ':course_id' => $course_id));
			
			// we return the id of this course
			return $this->db_connect->lastInsertId();
		}
		else
			return false;
	}
	
	function delete_category($id)
	{
		$request = $this->db_connect->prepare('DELETE FROM `'.TBL_CATEGORIES.'` WHERE id = :id');
		$request->execute(array(':id' => $id));
		return $request->rowCount(); // returns number of deletions (greater than 0 if success)
	}
	
	function rename_category($id, $new_name)
	{
		$request = $this->db_connect->prepare('UPDATE '.TBL_CATEGORIES.' SET name = :new_name  WHERE id = :id');
		$request->execute(array(':new_name' => $new_name, ':id' => $id));
		return $request->rowCount(); // returns number of update (greater than 0 if success)
	}
	
	function move_categories($from_course_id, $to_course_id)
	{
		// checking if there is a category with the same name in the destination course
		$categories_destination = $this->get_categories_ids_from_course($to_course_id);
		foreach ($this->get_categories_ids_from_course($from_course_id) as $test_cat_id)
		{
			$found_same = false;
			foreach($categories_destination as $cat_dest)
				if($this->category_name($test_cat_id) == $this->category_name($cat_dest))
					$found_same = true;
			// if there is the same category in destination, we rename it with the original course in parenthesis
			if ($found_same)
				$this->rename_category($test_cat_id,
									   $this->category_name($test_cat_id)." (".$this->course_name($from_course_id).")");
				
		}
		$request = $this->db_connect->prepare('UPDATE '.TBL_CATEGORIES.' SET course_id = :to_id  WHERE course_id = :from_id');
		$request->execute(array(':from_id' => $from_course_id, ':to_id' => $to_course_id));
		return $request->rowCount(); // returns number of deletions (greater than 0 if success)
	}
	
	function get_categories_ids()
	{
		$ids = array();
		
		$request = $this->db_connect->prepare('SELECT id FROM '.TBL_CATEGORIES.' ORDER BY course_id');
		$request->execute();
		
		// filling ids array
		while ($result = $request->fetch())
			$ids[] = $result['id'];
		
		return $ids;
	}
	
	function get_categories_ids_from_course($course_id)
	{
		$ids = array();
		
		$request = $this->db_connect->prepare('SELECT id FROM '.TBL_CATEGORIES.' WHERE course_id = :course_id');
		$request->execute(array(':course_id' => $course_id));
		
		// filling ids array
		while ($result = $request->fetch())
			$ids[] = $result['id'];
		
		return $ids;
	}
	
	function __destruct()
	{
		$db_connect = null;
	}
}
?>