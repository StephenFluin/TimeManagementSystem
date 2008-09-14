<?php

include("include/functions.inc.php");
authenticate();

// Provide admin capabilities to administrators
if( $_SESSION["isAdministrator"] == 1 ) {
	$body .= administration();
}

showContent($body);