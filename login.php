<?php

include("include/functions.inc.php");

$result = checkTMSLogin();
if($_SESSION["userid"]) {
	forward("index.php");
} else if($result) {
	showContent($result . wrap("not-logged-in.html", array( "destination" =>  $_SERVER["PHP_SELF"] ) ));
} else {
	forward($_POST["forward"]);
}

function checkTMSLogin() {
	$db = new DB();
	$u = $db->escape($_POST["username"]);
	$p = $db->escape($_POST["password"]);
	if(!$u) { 
		$return .= "You were missing a username.<br />\n";
	} else if(!$p) {
		$return .= "You were missing a password.<br />\n";
	} else {
		$query = "SELECT id, Username, isProjectManager, isAdministrator FROM tms_user WHERE `UserName` = '$u' AND `Password` = MD5('$p') AND enabled=1 LIMIT 1;";
		$db->query( $query );
		if( list($_SESSION["userid"], $_SESSION["username"], $_SESSION["isProjectManager"], $_SESSION["isAdministrator"]) = $db->fetchrow()) {
			unset($return);
			
		} else {
			$return .= "Username '$u' and password were not found in the TMS database.<br />\n";
			//var_dump($_POST);
		}
	}
	return $return;
}