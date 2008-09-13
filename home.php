<?php

include("include/functions.inc.php");
authenticate();

// Reporting - for everyone
$body .= reporting();

// General Time Logging section - for everone
$body .= timelogger();


// Provide admin capabilities to administrators
if( $_SESSION["isAdministrator"] == 1 ) {
	$body .= administration();
}

// Grant access to all project Manager functions (client list)
if( $_SESSION["isProjectManager"] == 1) {
	$body .= projectManage();
		
}



showContent($body);
