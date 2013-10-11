<?php 

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

$tequila_path = "lib/lib_tequila/";

require_once($tequila_path."tequila.php");

session_start();

// true if the project is hosted on a server at EPFL
$EPFL_subnet = false;
if (strpos($_SERVER['REQUEST_URI'], "epfl.ch") !== false)
	$EPFL_subnet = true;

$oClient = null;

if ($EPFL_subnet)
	$oClient = new TequilaClient();

// LOGOUT
if (isset($_GET['logout']) && $_GET['logout'] == 'y')
{
	unset($_SESSION['sciper']); // destroying session variable
	
	if ($EPFL_subnet)
		$oClient->logout();
}
else // LOGIN
{
	if ($EPFL_subnet)
	{
		//Title displayed during authentication
		$oClient->SetApplicationName('Quiz authentication');

		//Set attributes we need for this application
		$oClient->SetWantedAttributes(array('uniqueid')); // uniqueid = Sciper

		// Authenticate
		$oClient->Authenticate();

		//Recover the SCIPER number
		$sciper = $oClient->GetValue('uniqueid');

		$_SESSION['sciper'] = $sciper;
	}
	else
		$_SESSION['sciper'] = "999999";
}

header("Location: choices.php");
?>
