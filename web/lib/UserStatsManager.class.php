<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("config.inc.php");
require_once("param.inc.php");

class UserStatsManager {
	
	private $db_connect;
	
	function __construct()
	{
		$dsn = "mysql:host=".DB_HOST.";dbname=".DB_BASE.";";
		$this->db_connect = new PDO($dsn, DB_USER, DB_PASS);
	}
	
	function insert_stat($sciper, array $ids, array $user_choices, $course_id, $difficulty)
	{
		$request = $this->db_connect->prepare('INSERT INTO '.TBL_USERS_STATS.
						' (sciper, questions_ids, user_choices, course_id, difficulty) VALUES (:sciper, :questions_ids, :user_choices, :course_id, :difficulty)');
						
		$success = $request->execute(array(':sciper' => $sciper,
									       ':questions_ids' => serialize($ids),
									       ':user_choices' => serialize($user_choices),
									       ':course_id' => $course_id,
									       ':difficulty' => $difficulty));
		
		$count = count($this->get_stats($sciper));
		
		// if this user has reached the limit, we delete last row
		if ($count > MAX_SAVED_STATS)
		{
			$delete_count = $count - (int) MAX_SAVED_STATS;
			// delete last row
			$request = $this->db_connect->prepare('DELETE FROM '.TBL_USERS_STATS.' WHERE sciper = :sciper ORDER BY time asc LIMIT '.$delete_count);
			$request->execute(array(':sciper' => $sciper));
		}
		
		return $success;
	}
	
	function get_stats($sciper)
	{
		// retrieving stats from newer to older
		$request = $this->db_connect->prepare('SELECT * FROM '.TBL_USERS_STATS.' WHERE sciper = :sciper ORDER BY time desc');
		$request->execute(array(':sciper' => $sciper));
		$results = $request->fetchAll();
		
		return $results;
	}
	
	function reset_all()
	{
		$request = $this->db_connect->prepare('DELETE FROM '.TBL_USERS_STATS);
		$request->execute();
		return $request->rowCount();
	}
	
	function delete_older_than($months)
	{
		$request = $this->db_connect->prepare('DELETE FROM '.TBL_USERS_STATS.' WHERE time < DATE_SUB(NOW(), INTERVAL :m MONTH)');
		$request->execute(array(':m' => (int) $months));
		return $request->rowCount();
	}
	
	function __destruct()
	{
		$db_connect = null;
	}
}
?>