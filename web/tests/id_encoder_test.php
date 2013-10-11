<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

require_once("../lib/config.inc.php");
require_once("../lib/id_encoder.lib.php");

// Enabling error reports for debugging
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

?>
<html>
<head>
<title>Id encoder</title>
</head>
<body>
<?php

$id = "123456782374589734985423897592483759843278977";

$encoded = basis_encode($id, ID_ENCODER_BASIS);
$decoded = basis_decode($encoded, ID_ENCODER_BASIS);

$encoded_size = strlen($encoded);
$decoded_size = strlen($decoded);

print "id to encode : ".$id."<br /><br />";
print "encoded (".$encoded_size.") : ".$encoded."<br />";
print "decoded (".$decoded_size.") : ".$decoded."<br />";

if ($decoded == $id)
	print "<br />Encoding and decoding in base ".strlen(ID_ENCODER_BASIS)." work properly and you won ".($decoded_size-$encoded_size)." chars doing it !<br />";

print "<br /><hr />";

$ids = array(56, 10324, 23, 12);
$string = array_to_string($ids);
$ids_decoded = string_to_array($string);

print "<br />Array to string with length of item at beginning of each item:<br /><br />";
print_r($ids);
print "<br />Encoded string : ".$string."<br />";
print_r($ids_decoded);
?>
</body>
</html>