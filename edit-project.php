<?php

include("include/functions.inc.php");

authenticate();

if( $_SESSION["isProjectManager"] == 1 ) {
	$replace["name"] = $replace["billingCode"] = $replace["new"] = 
	$replace["client"] = $replace["oldName"]= $replace["taskList"] = 
	$replace["userList"] = "";
	
	// Project Editing Functions
	$client = (isset($_REQUEST["client"]) ? $_REQUEST["client"] : null);
	$project = (isset($_REQUEST["project"]) ? $_REQUEST["project"] : null);
	$action = (isset($_REQUEST["action"]) ? $_REQUEST["action"] : null);
	
	$replace["client"] = $client;
	
	if($action == "new") {
		$db = new DB();
		$db->query("SELECT BillingCode FROM tms_client WHERE Name = '" . mysql_escape_string($client) . "';");
		list( $replace["billingCode"] ) = $db->fetchrow();
		
		$replace["new"] = "true";
		showContent(wrap("edit-project.html",$replace));
	} else if($action == "submit") {
		$name = mysql_escape_string($_POST["name"]);
		$billingCode =  mysql_escape_string($_POST["billingCode"] );
		$client = mysql_escape_string($_POST["client"]);
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_project (Client, Project, BillingCode) VALUES ('$client', '$name', '$billingCode');";
		} else {
			$command = "UPDATE tms_project SET Project = '$name', BillingCode = '$billingCode' WHERE Project = '" . $_POST["oldName"] . "' AND Client = '" . $_POST["client"] . "' LIMIT 1;";
		}
		$db = new DB();
		$db->query($command);
		
		foreach($_POST["user"] as $key=>$value) {
			$userList[] = "('$client', '$name', '" . $_POST[$key] . "','$key' )";
			// Using key $key, which found us: $_POST[$key] = ". $_POST[$key] . ".<br/>\n";
		}
		//var_dump($_POST);
		$sql = "DELETE FROM tms_projectuser WHERE Client = '$client' AND Project = '$name';";
		$db->query($sql);
		if(count($userList) > 0) {
			$sql = "INSERT INTO tms_projectuser (Client, Project, Username, userid) VALUES " . join(", ", $userList);
			$db->query($sql);
		}
		
		
		
		forward("edit-project.php?client=$client&project=$name");

	} else if( $client && $project) {
		
		
		
		$query = "SELECT BillingCode FROM tms_project WHERE Client = '" . mysql_escape_string($client) . "' AND Project = '" . mysql_escape_string( $project ) . "' LIMIT 1;";
		$db = new DB();
		$db->query($query);
		list( $replace["billingCode"]) = $db->fetchrow();
		$replace["oldName"] = $replace["name"] = $project;
		$replace["client"] = $client;
		$replace["project"] = $project;
		
		$sql = "SELECT Task FROM tms_task WHERE Client = '$client' AND Project = '$project' ORDER BY Task";
		$db->query($sql);
		while(list($task) = $db->fetchrow()) {
			$replace["taskList"] .= "<div><a href=\"edit-task.php?client=$client&amp;project=$project&amp;task=$task\">$task</a></div>";
		}
		$sql = "SELECT Username, userid FROM tms_projectuser WHERE Client = '$client' AND Project = '$project' ORDER BY Username;";
		$db->query($sql);
		$usedUsers = array();
		while(list($user, $id) = $db->fetchrow()) {
			$usedUsers[$user] = $id;
			$replace["userList"] .= "<input type=\"hidden\" name=\"$id\" value=\"$user\"/>" .
					"<label><input type=\"checkbox\" name=\"user[$id]\" checked=\"checked\"/>$user</label><br/>\n";
		}
		
		$sql = "SELECT Username,id FROM tms_user ORDER BY Username";
		$db->query($sql);
		while(list($user, $id) = $db->fetchrow()) {
			if(!array_key_exists($user, $usedUsers)) {
				$replace["userList"] .= "<input type=\"hidden\" name=\"$id\" value=\"$user\"/>" .
					"<label><input type=\"checkbox\" name=\"user[$id]\"/>$user</label><br/>\n";
			}
		}
		
		
		
		$content = wrap("edit-project.html", $replace);
		$content .= wrap("task-list.html", $replace);
		
	
		
		
		
		
		
		showContent($content);
	} else {
		showContent("Invalid function call.");
	}
		
} else {
	showContent("You don't have permission to do this.");
}