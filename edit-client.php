<?php

include("include/functions.inc.php");

if( $_SESSION["timeManagement"] > 1 ) {
	$db = new DB();

	
	// Client Editing Functions
	$clientId = $_GET["client"];
	$action = $_REQUEST["action"];
	$replace["name"] = $replace["new"] = $replace["oldName"]= $replace["projectList"] = "";
	if($action == "new") {
		$replace["new"] = "true";
		showContent(wrap("edit-client.html",$replace));
	} 
	else if($action == "delete" && $client) {
		$sql = "DELETE FROM tms_client WHERE Name = '" . mysql_real_escape_string($client) . "' LIMIT 1;";
		$db->query($sql);
		forward("home.php");	

	} else if($action == "submit") {
		$name = mysql_escape_string($_POST["name"]);
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_client (Name) VALUES ('$name');";
		} else {
			$command = "UPDATE tms_client SET Name = '$name' WHERE Name = '" . $_POST["oldName"] . "' LIMIT 1;";
		}
		
		$db->query($command);
		forward("home.php");
	} else if( $clientId ) {
		
		
		
		$query = "SELECT id, Name FROM tms_client WHERE id = '" . $db->escape($clientId) . "' LIMIT 1;";
		$db->query($query);
		list($replace["id"], $replace["name"]) = $db->fetchrow();
		$replace["oldName"] = $replace["name"];
		$content = wrap("edit-client.html", $replace);
		

		$sql = "SELECT id, Project FROM tms_project WHERE clientId = '$clientId' ORDER BY project";
		$db->query($sql);
		while(list($id, $project) = $db->fetchrow()) {
			$replace["projectList"] .= "<div><a href=\"edit-project.php?project=$id\">$project</a></div>";
		}
		
		$content = wrap("edit-client.html", $replace);
		$content .= wrap("project-list.html", $replace);
	
		
		
		
		
		
		showContent($content);
	} else {
		showContent("Invalid function call.");
	}
		
} else {
	showContent("You don't have permission to do this.");
}
