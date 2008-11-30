<?php

include("include/functions.inc.php");
authenticate();

if( $_SESSION["isProjectManager"] > 0 ) {
	$db = new DB();
	$replace["name"] = $replace["new"] = 
	$replace["client"] = $replace["expectedHours"] = 
	$replace["taskId"] = "";
	$replace["complete"] = "";
	
	// Project Editing Functions
	$projectId = $db->escape($_GET["project"]);
	$taskId = $db->escape($_POST["taskId"]);
	$task = $db->escape($_GET["task"]);
	$completed = $db->escape($_GET["completed"]);
	$action = $_REQUEST["action"];
	
	$replace["projectId"] = $projectId;
	
	if($action == "new") {

		$replace["new"] = "true";
		showContent(wrap("edit-task.html",$replace));
	} else if($action == "submit") {
	
		// "Inserting a new task.";
		$task = mysql_escape_string($_POST["name"]);
		
		
		$expectedHours = $db->escape( $_POST["expectedHours"]);
		$project = $db->escape($_POST["projectId"]);
		$complete = $_POST["complete"] == "on" ? 1 : 0;
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_task (projectId, Task,  ExpectedHours) VALUES ('$projectId', '$task',  '$expectedHours');";
		} else {
			$command = "UPDATE tms_task SET Task = '$task', ExpectedHours = '$expectedHours', complete='$complete' WHERE id = '$taskId' LIMIT 1;";
		}
	
		$db->query($command);
		if(!$taskId) {
			$taskId = $db->getInsertId();
		}
		forward("edit-project.php?project=$project");
	} else if( $task) {
		
		
		
		$query = "SELECT id, Task, projectId,  ExpectedHours, complete FROM tms_task WHERE  id = '$task' LIMIT 1;";
		$db->query($query);
		list($replace["taskId"], $replace["name"], $replace["projectId"], $replace["expectedHours"], $completed) = $db->fetchrow();
		$replace["oldName"] = $replace["name"];
		$replace["complete"] = "<input type=\"checkbox\"";
		if($completed) {
			$replace["complete"] .= " checked=\"checked\"";
		}
		$replace["complete"] .= "/>";
		
		
		
		$content = wrap("edit-task.html", $replace);
				
		showContent($content);
	} else {
		showContent("Invalid function call.");
	}
		
} else {
	showContent("You don't have permission to do this.");
}
