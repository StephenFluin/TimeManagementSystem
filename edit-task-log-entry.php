<?php

include("include/functions.inc.php");

if($_SESSION["timeManagement"] > 0) {
	$id = $_GET["id"];
	$replace["taskId"] = $id;
	$replace["hours"] = $replace["comment"] = 
	$replace["task"] = "";
	$action = $_REQUEST["action"];
	if(!$id) {
		showContent("No ID selected.");
		exit;
	}
	if($action == "new") {
		$sql = "SELECT Task FROM tms_task WHERE id = '$id';";
		$db = new DB();
		
		$db->query($sql);
		list($replace["task"]) = $db->fetchrow();
		$replace["action"] = "submit";
		$monday = strtotime("last Monday");
		$replace["dates"] = "";
		for($i = 0; $i < 5;$i++) {
			$replace["dates"] .= "<option value=\"" . date('Y-j-n', $monday) . "\">" . date('n/j/Y', $monday) . "</option>\n";
			$monday = strtotime("-1 week", $monday);
		}
		
	} else if($action == "submit") {
		$hours = mysql_escape_string( $_POST["hours"]);
		$comment = mysql_escape_string( $_POST["comment"]);
		$date = mysql_escape_string( $_POST["date"]);
		
		$sql = "INSERT INTO tms_tasklogentry (taskId, Username, Week, Comment, Hours) VALUES ('$id', '" . $_SESSION["username"] . "', '$date', '$comment', '$hours');";
		$db = new DB();
		$db->query($sql);
		forward("home.php");	
	}
	
	
	
	
	showContent(wrap("edit-task-log-entry.html", $replace));
} else {
	$content = "You don't have access to this page.";
	showContent($content);
}