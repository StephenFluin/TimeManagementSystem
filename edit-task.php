<?php

include("include/functions.inc.php");

if( $_SESSION["timeManagement"] > 1 ) {
	$replace["name"] = $replace["billingCode"] = $replace["new"] = 
	$replace["client"] = $replace["oldName"] = $replace["expectedHours"] = 
	$replace["taskCode"] = "";
	
	// Project Editing Functions
	$client = $_REQUEST["client"];
	$project = $_REQUEST["project"];
	$task = $_REQUEST["task"];
	$action = $_REQUEST["action"];
	
	$replace["client"] = $client;
	$replace["project"] = $project;
	
	if($action == "new") {
		$db = new DB();
		$db->query("SELECT BillingCode FROM tms_project WHERE Client = '" . mysql_escape_string($client) . "' AND Project = '" . mysql_escape_string($project) . "';");
		list( $replace["billingCode"] ) = $db->fetchrow();
		
		$replace["new"] = "true";
		showContent(wrap("edit-task.html",$replace));
	} else if($action == "submit") {
		// "Inserting a new task.";
		$task = mysql_escape_string($_POST["name"]);
		
		$taskCode = mysql_escape_string($_POST["taskCode"] );
		$billingCode =  mysql_escape_string($_POST["billingCode"] );
		$expectedHours = mysql_escape_string( $_POST["expectedHours"]);
		$client = mysql_escape_string($_POST["client"]);
		$project = mysql_escape_string($_POST["project"]);
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_task (Client, Project, Task, TaskCode, BillingCode, ExpectedHours) VALUES ('$client', '$project', '$task', '$taskCode', '$billingCode', '$expectedHours');";
		} else {
			$command = "UPDATE tms_task SET Task = '$task', TaskCode = '$taskCode', BillingCode = '$billingCode', ExpectedHours = '$expectedHours' WHERE Task = '" . $_POST["oldName"] . "' AND Project = '" . $_POST["project"] . "' AND Client = '" . $_POST["client"] . "' LIMIT 1;";
		}
		$db = new DB();
		$db->query($command);
		forward("edit-project.php?client=$client&project=$project");
	} else if( $client && $project && $task) {
		
		
		
		$query = "SELECT Task, TaskCode, BillingCode, ExpectedHours FROM tms_task WHERE Client = '" . mysql_escape_string($client) . "' AND Project = '" . mysql_escape_string( $project ) . "' AND Task = '" . mysql_escape_string( $task ) . "' LIMIT 1;";
		$db = new DB();
		$db->query($query);
		list($replace["name"], $replace["taskCode"], $replace["billingCode"], $replace["expectedHours"]) = $db->fetchrow();
		$replace["oldName"] = $replace["name"];
		
		
		
		$content = wrap("edit-task.html", $replace);
				
		showContent($content);
	} else {
		showContent("Invalid function call.");
	}
		
} else {
	showContent("You don't have permission to do this.");
}
