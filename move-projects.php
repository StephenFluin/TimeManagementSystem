<?php
require("include/functions.inc.php");

authenticate();
if( $_SESSION["isProjectManager"] == 1) {
	if( $_POST["action"] == "save") {
		$body .= saveProjectList();
	}
	$body .= getProjectMover();
}

showContent($body);

function getProjectMover() {
	
	
	
	$db = new DB();
	$db->query("SELECT p.id,c.id,c.Name,p.project FROM tms_project p JOIN tms_client c ON p.clientId = c.id ORDER BY c.Name, p.project");
	$body .= "<form method=\"post\"><div style=\"overflow:hidden\">";
	while(list($id,$clientId,$client,$project) = $db->fetchrow()) {
		$project = htmlspecialchars($project);
		$client = htmlspecialchars($client);
		if($client != $previousClient) {
			$body .= "<div style=\"clear:both;border-bottom:1px solid #CCC;padding-top:30px;\">$client</div>";
			$previousClient = $client;
		}
		$body .= "<div class=\"columns\"><input type=\"text\" name=\"project[$id]\" value=\"$project\" onchange=\"document.getElementById('modified[$id]').value='true';\"/></td><td><select name=\"client[$id]\" onchange=\"document.getElementById('modified[$id]').value='true';\">" . getClientList($clientId) . "</select><input type=\"hidden\" name=\"modified[$id]\" id=\"modified[$id]\" value=\"false\"/></div>";
	}
	$body .='</div><button type="submit" name="action" value="save" style="clear:both;">Save</button></form>';
	return $body;
}
	
	
	
function getClientList($clientId = 0) {
	if(!$GLOBALS["clientList"]) {
		$db = new DB();
		$db->query("SELECT id,Name FROM tms_client ORDER BY Name");
		while(list($id,$name) = $db->fetchrow()) {
			$GLOBALS["clientList"][] = array($id,$name);
			
		}
	}
	foreach($GLOBALS["clientList"] as $client) {
		list($id,$name) = $client;
		//print "client found! $id $name.<br/>\n";
		
		$name = htmlspecialchars($name);
		$return .= "<option value=\"$id\"";
		if($id == $clientId) {
			$return .= " selected=\"selected\"";
		}
		$return .= ">$name</option>\n";
	}
	return $return;
}
function saveProjectList() {
	
	$db = new DB();
	
	foreach($_POST["modified"] as $id=>$value) {
		if($value == "true") {
			
			$project = $db->escape($_POST["project"][$id]);
			$client = $db->escape($_POST["client"][$id]);
			$sql = "UPDATE tms_project SET project = '$project', clientId='$client' WHERE id='$id' LIMIT 1;";
			$db->query($sql);
			//print $sql."<br/>\n";
			
		}
	}
}



