<?php

include("include/functions.inc.php");

authenticate();

if( $_SESSION["isProjectManager"] == 1 ) {
	$db = new DB();

	$replace["name"] = $replace["billingCode"] = $replace["new"] = 
	$replace["client"] = $replace["oldName"]= $replace["taskList"] = 
	$replace["userList"] = $replace["projectId"] = "";
	
	// Project Editing Functions
	$clientId = $db->escape($_GET["client"]);
	$projectId = $db->escape($_GET["project"]);
	$action = (isset($_REQUEST["action"]) ? $_REQUEST["action"] : null);
	
	$replace["clientId"] = $clientId;
	
	if($action == "new") {
		
		
		$replace["new"] = "true";
		showContent(wrap("edit-project.html",$replace));
	} else if($action == "submit") {
		$projectName =     $db->escape($_POST["projectName"]);
		$clientId = $db->escape($_POST["clientId"]);
		$projectId = $db->escape($_POST["projectId"]);
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_project (clientId, Project) VALUES ('$clientId', '$projectName');";
		} else {
			$command = "UPDATE tms_project SET project = '$projectName' WHERE id='$projectId' LIMIT 1;";
		}
		$db->query($command);
		if(!$projectId) {
			$projectId = $db->getInsertId();
		}
		
		if($_POST["user"]) {
			foreach($_POST["user"] as $key=>$value) {
				$userList[] = "('$projectId', '" . $_POST[$key] . "','$key' )";
				// Using key $key, which found us: $_POST[$key] = ". $_POST[$key] . ".<br/>\n";
			}
		}
		//var_dump($_POST);
		$sql = "DELETE FROM tms_projectuser WHERE projectId='$projectId';";
		$db->query($sql);
		if(count($userList) > 0) {
			$sql = "INSERT INTO tms_projectuser (projectId, Username, userid) VALUES " . join(", ", $userList);
			$db->query($sql);
		}
		
		
		
		forward("edit-project.php?project=$projectId");

	} else if( $projectId) {
		$db->query("SELECT project,clientId FROM tms_project WHERE id='$projectId' LIMIT 1;");
		if(list($projectName,$clientId) = $db->fetchrow()) {
			$replace["projectName"] = $projectName;
			$replace["clientId"] = $clientId;
		} else {
			showError("Project not found!");
		}
		
		$replace["projectId"] = $projectId;
		
		$sql = "SELECT id, Task FROM tms_task WHERE id='$projectId' ORDER BY Task";
		$db->query($sql);
		while(list($taskId, $task) = $db->fetchrow()) {
			$replace["taskList"] .= "<div><a href=\"edit-task.php?task=$taskId\">$task</a></div>";
		}
		$sql = "SELECT Username, userid FROM tms_projectuser WHERE projectId='$projectId' ORDER BY Username;";
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