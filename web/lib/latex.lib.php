<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("parser.lib.php");

function readTex($file)
{
	$allowed_extensions = array("tex", "latex");
	$allowed_mime_types = array("text/x-latex", "text/x-tex", "text/plain", "application/x-latex", "application/x-tex");
	
	// extracting infos from file
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	$mime = mime_content_type($file);
	
	// checking if extension and mime type are allowed for security
	if (in_array($extension, $allowed_extensions) && in_array($mime, $allowed_mime_types))
	{
		// reading file and returning it
		return file_get_contents($file);
	}
	
	return false;
}

// If latex command has an argument (presence of {}), use $$$ in $html_replace to set where the argument goes.
// Protect special chars if you use double quotes and don't if you use simple ones.
function replaceTexCommand($command, $html_replace, $content)
{
	// if no occurence found, we should just return $content
	$result = $content;
	
	// Removing spaces in brace brackets of latex command. {   } -> {}
	// and spaces at the beginning and the end
	$latex = preg_replace('/\{\s+\}/', '{}', $command);
	$latex = trim($latex);
	$html_replace = trim($html_replace);
	
	// checking if the command is not contained in the replaced string to avoid infinite loop
	if(strpos(str_replace(" ","",$html_replace), str_replace(" ","",$command)) === false)
	{
		// !== false to avoid 0 being interpret as false
		if (strpos($latex, "{}") !== false) // there is a {} in the command
		{
			$latex_no_brace = str_replace("{}", "", $latex);
			$html_tags = explode("$$$", $html_replace);
			
			// if there is a {} in the tex command, we need a $$$ in the html
			if (count($html_tags) >= 2)
			{
				$html_open = $html_tags[0];
				$html_close = $html_tags[1];
				
				// there is this latex command in this content
				$c = 0;
				$pos = strpos($result, $latex_no_brace);
				// more than 20 substitution in same strings looks like it is going to infinity
				// so we stop it to avoid crashing the script if this happen once.
				while ($pos !== false && $c <= 20)
				{
					// getting next balanced brace brackets that follows latex command
					$in_bracket = getNextBrace(substr($result, $pos + strlen($latex_no_brace)));
					// removing opening and closing brace brackets
					$in_bracket = substr($in_bracket, 1, strlen($in_bracket)-2);
					
					// replacing latex command by html tag with content inbetween
					$result = str_replace($latex_no_brace."{".$in_bracket."}",
										  $html_open."".$in_bracket."".$html_close,
										  $result);
					
					// looking if there is this command again in code
					$pos = strpos($result, $latex_no_brace."{");
					$c++;
				}
			}
		}
		else // this is just a simple substition. ex : \# -> #
		{
			$result = str_replace($latex, $html_replace, $result);
		}
	}
	
	return $result;
}

function is_tex2html_rule_ok($rule) // takes a single rule in argument as a string
{
	$array = explode("::", $rule);
	if(count($array) == 2)
	{
		list($latex, $html) = $array;
		// Removing spaces in brace brackets of latex commands. {   } -> {}
		$latex = preg_replace('/\{\s+\}/', '{}', $latex);
		
		// verifying that there will be no problem to match {} with $$$
		if (substr_count($latex, "{}") <= substr_count($html, "$$$"))
			return true;
	}
	return false;
}

function tex2html($latex)
{
	$html = $latex;
	
	// finding the path of tex2html folder in a general case,
	// because this lib can be included in script from different folders
	$folder_of_this_script = realpath(dirname(__FILE__))."/";
	$path = $folder_of_this_script."../".TEX2HTML_FOLDER;
	
	$rules = array();
	if (file_exists($path."tex2html.txt"))
		$rules = file($path."tex2html.txt");
	elseif (file_exists($path."default.txt"))
		$rules = file($path."default.txt");
	
	foreach ($rules as $rule)
	{
		if(is_tex2html_rule_ok($rule))
		{
			list($latex, $html_replace) = explode("::", $rule);
			$html = replaceTexCommand($latex, $html_replace, $html);
		}
	}
	
	// or doing it manually wit no rule file
	/*$html = replaceTexCommand('\emph{}', '<em>$$$</em>', $html);
	$html = replaceTexCommand('\textit{}', '<em>$$$</em>', $html);
	$html = replaceTexCommand('\textbf{}', '<strong>$$$</strong>', $html);
	$html = replaceTexCommand('\#', '#', $html);
	$html = replaceTexCommand('\dots', '&hellip;', $html);
	$html = replaceTexCommand('\ldots', '&hellip;', $html);*/
	
	return $html;
}

// this function return an array of latex commands found in this string
function latex_detection($string)
{
	$latex = array();
	
	// finding a backslash
	$pos = strpos($string, "\\");
	if ($pos !== false && $pos != strlen($string) - 1)
	{
		$pos_end = strlen($string) - 1; // initalizing to string from pos until the end
		
		$pos_space = strpos($string, " ", $pos+1); // matching protected chars (\#, \\)
		$pos_brace = strpos($string, "{", $pos+1); // matching commands with args (\emph{}, \textbf{})
		
		// finding smallest pos_end possible
		if ($pos_space)
			$pos_end = $pos_space;
		if ($pos_brace && $pos_brace < $pos_end)
			$pos_end = $pos_brace;
		
		$latex[] = substr($string, $pos, $pos_end - $pos); // latex command found
		
		if ($pos_end + 1 < strlen($string)) // if we haven't consumed all string, we continue recursively
		{
			$next = latex_detection(substr($string, $pos_end + 1, (strlen($string)-1)-$pos_end)); // trying to find others
			$latex = array_merge($latex, $next);
		}
	}
	return $latex;
}

?>