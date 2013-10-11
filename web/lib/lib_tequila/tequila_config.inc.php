<?php
$aConfig = array (
	          'sServer' => 'https://tequila.epfl.ch',
	          'iTimeout' => 86400,
			  'logoutUrl' => "https://localhost/tequila/logout.php",
	);


/********************************************************
          DO NOT EDIT UNDER THIS LINE
********************************************************/
function GetConfigOption($sOption, $sDefault = '') {
  global $aConfig;
  if (!array_key_exists ($sOption, $aConfig))
    return ($sDefault);
  else
    return ($aConfig [$sOption]);
}
?>
