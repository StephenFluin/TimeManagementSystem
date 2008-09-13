<?php

include("../include/functions.inc.php");

$result = checkTMSLogin();
if($result) {
	showContent($result);
} else {
	forward("home.php");
}

function checkTMSLogin() {
	$db = new DB();
	$u = mysql_escape_string($_POST["username"]);
	$p = mysql_escape_string($_POST["password"]);
	if(!$u) { 
		$return .= "You were missing a username.<br />\n";
	} else if(!$p) {
		$return .= "You were missing a password.<br />\n";
	} else {
		$query = "SELECT id, Username, timeManagement FROM users WHERE `UserName` = '$u' AND `Password` = PASSWORD('$p') LIMIT 1;";
		$db->query( $query );
		if( list($_SESSION["userid"], $_SESSION["username"], $_SESSION["timeManagement"]) = $db->fetchrow()) {
			unset($return);
			
		} else {
			$return .= "Username $u and password $p were not found in the TMS database.<br />\n";
			var_dump($_POST);
		}
	}
	return $return;
}