<?php

include("../include/functions.inc.php");

if( $_SESSION["timeManagement"] > 1 ) {
	

	
	// Client Editing Functions
	$client = $_REQUEST["client"];
	$action = $_REQUEST["action"];
	$replace["name"] = $replace["billingCode"] = $replace["new"] = $replace["oldName"]= $replace["projectList"] = "";
	if($action == "new") {
		$replace["new"] = "true";
		showContent(wrap("edit-client.html",$replace));
	} 
	else if($action == "delete" && $client) {
		$db = new DB();
		$sql = "DELETE FROM tms_client WHERE Name = '" . mysql_real_escape_string($client) . "' LIMIT 1;";
		$db->query($sql);
		forward("home.php");	

	} else if($action == "submit") {
		$name = mysql_escape_string($_POST["name"]);
		$billingCode =  mysql_escape_string($_POST["billingCode"] );
		if($_POST["new"] == "true") {
			$command = 	"INSERT INTO tms_client (Name, BillingCode) VALUES ('$name', '$billingCode');";
		} else {
			$command = "UPDATE tms_client SET Name = '$name', BillingCode = '$billingCode' WHERE Name = '" . $_POST["oldName"] . "' LIMIT 1;";
		}
		$db = new DB();
		$db->query($command);
		forward("home.php");
	} else if( $client ) {
		
		
		
		$query = "SELECT Name, BillingCode FROM tms_client WHERE Name = '" . mysql_escape_string($client) . "' LIMIT 1;";
		$db = new DB();
		$db->query($query);
		list($replace["name"], $replace["billingCode"]) = $db->fetchrow();
		$replace["oldName"] = $replace["name"];
		$content = wrap("edit-client.html", $replace);
		
		$sql = "SELECT Project FROM tms_project WHERE Client = '$client' ORDER BY Project";
		$db->query($sql);
		while(list($project) = $db->fetchrow()) {
			$replace["projectList"] .= "<div><a href=\"edit-project.php?client=$client&project=$project\">$project</a></div>";
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
