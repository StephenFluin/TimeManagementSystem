<?php

include("../include/functions.inc.php");

if( $_SESSION["timeManagement"] > 1 ) {
	// Grant access to all project Manager functions (client list, user list)
	$content = "";
	$db = new DB();
	$sql = "SELECT Name FROM tms_client ORDER BY Name";
	
	$content .= "<div style=\"border: 1px solid;width:300px;\"><h1>Client List</h1>";
	$db->query($sql);
	while( list( $client ) = $db->fetchrow() ) {
		$content .=  "<div style=\"padding-left: 10px;\">" .
				"	<a href=\"edit-client.php?client=" . urlencode( $client ) . "\">$client</a>" .
				"	[<a href=\"edit-client.php?action=delete&client=" . urlencode( $client ) . "\">x</a>]" .	
				"</div>";
	}
	$content .=  "</div>\n";
	$content .=  "<a href=\"edit-client.php?action=new\">New Client</a><br/><br/>\n\n";
	
	$sql = "SELECT Username FROM users WHERE timeManagement > 0 ORDER BY Username";
	
	$content .=  "<div style=\"border: 1px solid;width:300px;\"><h1>User List</h1>";
	$db->query($sql);
	$userList = array();
	while( list( $client ) = $db->fetchrow() ) {
		$userList[] = $client;
		$content .=  "<div style=\"padding-left: 10px;\">" .
				"	<a href=\"edit-user.php?user=" . urlencode( $client ) . "\">$client</a>" .
				"	[<a href=\"edit-user.php?action=delete&user=" . urlencode( $client ) . "\">x</a>]" .	
				"</div>";
	}
	$content .=  "</div>\n";
	$content .=  "<a href=\"edit-user.php?action=new\">New User</a><br/><br/>\n\n<br/>\n";
	$content = "<div class=\"standardBox\"><h1>Administration</h1><div>" . $content . "</div><br/><br/>\n<br/>\n";
	
	$content .= "<div class=\"standardBox\"><h1>Reporting</h1><div>\n";
	$content .= "Report by User:<br/>\n";
	$content .= "<form action=\"report.php\" method=\"post\"><input type=\"hidden\" name=\"type\" value=\"user\"/>" .
			"<select name=\"username\">";
	foreach($userList as $user) {
		$content .= "<option>$user</option>\n";
	}
	$content .= "</select><input type=\"submit\" value=\"go\"/></form></div></div><br/><br/>\n<br/>\n";
	
	
}
if( $_SESSION["timeManagement"] > 0) {
	$content .= "<div class=\"standardBox\"><h1>Time Logging</h1><div>";
	$db = new DB();
	$username = mysql_escape_string($_SESSION["username"]);
	$sql = "SELECT t.Client, t.Project, t.Task, t.id FROM tms_projectuser as pu LEFT JOIN tms_task as t ON pu.Client = t.Client AND pu.Project = t.Project WHERE pu.Username = '$username' ORDER BY pu.Client, pu.Project, t.id";
	$db->query($sql);
	//$content .= $sql;
	$previousClient = $previousProject = "";
	while(list($client, $project, $task, $tid) = $db->fetchrow()) {
		if($client != $previousClient) {
			if($previousClient != "") {
				$content .= "</div>";
			}
			$content .="<div><h1>$client</h1>";
			$previousClient = $client;
		}
		if($project != $previousProject) {
			$content .= "<h2>$project</h2>";
			$previousProject = $project;
		}
		$content .= "<div style=\"padding-left: 30px;\">$task<a href=\"edit-task-log-entry.php?action=new&id=$tid\" style=\"position: absolute;left: 500px;\">New Task Log Entry</a></div>\n";
	}
	$content .= "</div>";
}
if($content == "") {
	$content = wrap("login-box.html");
}
showContent($content);
