<?php

include("include/functions.inc.php");
authenticate();



// Grant access to all project Manager functions (client list)
if( $_SESSION["isProjectManager"] == 1) {
	$body .= projectManage();
		
}

showContent($body);