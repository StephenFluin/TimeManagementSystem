<?php

include("../include/functions.inc.php");

if( $_SESSION["timeManagement"] > 1 ) {
	

	
	// User Editing Functions
	$username = $_REQUEST["user"];
	$action = $_REQUEST["action"];
	$replace["username"] = $replace["projectManager"] = $replace["new"] = $replace["oldName"] = "";
	if($action == "new") {
		$replace["new"] = "true";
		showContent(wrap("edit-user.html",$replace));
	} else if($action == "submit") {
		$name = mysql_escape_string($_POST["username"]);
		$password = mysql_escape_string($_POST["password"]);
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO users (Username, Password, timeManagement) VALUES ('$name', PASSWORD('$password'), '" . ($_POST["projectManager"] ? "2" : "1") . "');";
		} else {
			$command = "UPDATE users SET Username = '$name', timeManagement = '" . (($_POST["projectManager"] == "on") ? "2" : "1") . "' WHERE Username = '" . $_POST["oldName"] . "' LIMIT 1;";
		}
		$db = new DB();
		$db->query($command);
		forward("home.php");
	} else if( $username ) {
		
				
		$query = "SELECT Username, timeManagement FROM users WHERE Username = '" . mysql_escape_string($username) . "' LIMIT 1;";
		$db = new DB();
		$db->query($query);
		list($replace["username"], $timeManagement) = $db->fetchrow();
		$replace["checked"] = ($timeManagement > 1) ? "checked=\"checked\"" : "";
		$replace["oldName"] = $replace["username"];
		$content = wrap("edit-user.html", $replace);
		
		
		$content = wrap("edit-user.html", $replace);
		
		showContent($content);
	} else {
		showContent("Invalid function call.");
	}
		
} else {
	showContent("You don't have permission to do this.");
}