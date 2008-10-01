<?php

include("include/functions.inc.php");

if( $_SESSION["isAdministrator"]  == 1 ) {
	$db = new DB();

	
	// User Editing Functions
	$id = $_POST["id"];
	$action = $_POST["action"];
	$replace["username"] = $replace["projectManager"] = $replace["new"] = $replace["oldName"] = $replace["id"] = "";

	if(!$action && !$_GET["user"]) {
		$replace["new"] = "true";
		showContent(wrap("edit-user.html",$replace));

	} else if($action == "save") {
		$name = $db->escape($_POST["username"]);
		$password = $db->escape($_POST["password"]);
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_user (Username, Password, isAdministrator, isProjectManager) VALUES ('$name', MD5('$password'), '" . ($_POST["isAdministrator"] ? "1" : "0") . "', '" . ($_POST["projectManager"] ? "2" : "1") . "');";
		} else {
			if($password != "") {
				$updatePass = ", Password = MD5('$password')";
			} else {
				$updatePass = "";
			}
			$command = "UPDATE tms_user SET Username = '$name', isProjectManager = '" . ($_POST["isProjectManager"] ? "1" : "0") . "', isAdministrator = '" . ($_POST["isAdministrator"] ? "1" : "0") . "'$updatePass WHERE id = '" . $db->escape($_POST["id"]) . "' LIMIT 1;";

			// Allow immediate update of permissions.
			if($_SESSION["userid"] == $_POST["id"]) {
				$_SESSION["isProjectManager"] = $_POST["isProjectManager"] ? 1:0;
				$_SESSION["isAdministrator"] = $_POST["isAdministrator"] ? 1:0;
				$_SESSION["username"] = $_POST["username"];
			}
		}
		
		$db->query($command);
		forward("admin.php");
	} else if ($_GET["action"] == "delete" && $_GET["user"]) {
		$uid = $db->escape($_GET["user"]);
		$db->query("DELETE FROM tms_user WHERE id='$uid' LIMIT 1;");
		forward("admin.php");
	
	} else if( $_GET["user"] ) {
		
				
		$query = "SELECT Username, isProjectManager, isAdministrator, id FROM tms_user WHERE id = '" . $db->escape($_GET["user"]) . "' LIMIT 1;";
		
		$db->query($query);
		list($replace["username"], $isPM, $isAdmin, $id,) = $db->fetchrow();
		$replace["isPM"] = ($isPM == 1) ? "checked=\"checked\"" : "";
		$replace["isAdmin"] = ($isAdmin == 1) ? "checked=\"checked\"" : "";
		$replace["oldName"] = $replace["username"];
		$replace["userid"] = $id;
		$content = wrap("edit-user.html", $replace);
		
		showContent($content);
	} else {
		showContent("Invalid function call.");
	}
		
} else {
	showContent("You don't have permission to do this.");
}