<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

// encodes the integer $id in the $basis given as a string
function basis_encode($id, $basis)
{
 	if (!is_numeric($id))
	{
		print "Warning : the basis encoder cannot encode non-integer ids (".$id.").";
		return false;
	}
	elseif ($id == 0) // to avoid tricky cases
        return 0;
	
	$basis = str_split($basis); // converting string to char array
    $basis_length = sizeof($basis);
	
	$encoded = array();
	
	// doing integer divisions with the size of the basis until id is zero
    while($id)
	{
		$remainder = bcmod($id, $basis_length); // remainder of the integer division
		$id = bcdiv($id, $basis_length, 0); // integer division
		
        $encoded[] = $basis[$remainder]; // taking the corresponding char in the basis
	}
 
    // we reverse the array because the less significant term has been calculated first
    return implode(array_reverse($encoded));
}
 
function basis_decode($string, $basis)
{
    if ($string == "0")
        return 0;
	
	$basis = str_split($basis); // converting string to char array
 
    $basis_length = sizeof($basis);
    $strlen = strlen($string);
    
	$id = 0;
 	
	// instead of having 12 => a, we have a => 12
	// to convert to an int, we need the index of a given char in the tab
	$basis_indexes = array_flip($basis);
 	
	$power_shift = 0;
	
	// Decoding char after char
    foreach(str_split($string) as $char)
	{
		if (!in_array($char, $basis))
		{
			print "WARNING : encoded char was not in corresponding alphabet.";
			return false;
		}
		
        $power = ($strlen - ($power_shift + 1));
		// id = id + index *  base_length ^ power
        $id = bcadd($id, bcmul($basis_indexes[$char], (bcpow($basis_length, $power))));
		
		// removing .0000 at the end
		$split_id = explode('.',$id);
		if (count($split_id) >= 2)
			$id = $split_id[0];
		
        $power_shift += 1;
	}
    return $id;
}


// converts an array to string joining all items with size at beginning of each item
// NOTE: could be decoded properly only if size of each id is less than 10 chars
function array_to_string(array $ids)
{
	$result = array();
	
	foreach($ids as $id)
		$result[] = strlen($id).$id;
	
	return join($result);
}

// converts a string to an array using first number as the length of the following element
// NOTE: could be decoded properly only if size of each id is less than 10 chars
function string_to_array($string)
{
	$result = array();
	//print $string."<br />";
	
	while(strlen($string) > 0)
	{
		$length = $string{0}; // length of the first array element
		if (!is_numeric($length))
		{
			print "WARNING : should have read a length but had non-numeric content instead (".$length." in ".$string.").";
			return false;
		}
		
		// extracting element
		$result[] = substr($string, 1, $length);
		$string = substr($string, $length + 1);
	}
	
	return $result;
}

?>