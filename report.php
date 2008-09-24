<?php

include("include/functions.inc.php");


$type = $_POST["type"];
$userid = $_POST["userid"];
$content = "";


if($type == "user") {
	if($_SESSION["isAdministrator"] == 0 && $_SESSION["username"] != $username) {
		showContent("You don't have access to run reports about other users!");
	}
	
	$content .= "<div class=\"standardBox\"><h1>Time Report for $username</h1><div>";
	$db = new DB();
	$sql = "SELECT t.Client, t.Project, t.Task, SUM(tle.Hours) FROM tms_tasklogentry as tle LEFT JOIN tms_task as t ON t.id = tle.taskId WHERE tle.userId = '$userid' GROUP BY t.projectId, t.id";
	$db->query($sql);
	$content .= $sql;
	$previousClient = $previousProject = "";
	$width = 2;
	$content .= "<table border=\"1\">";
	
	while(list($client, $project, $task, $hours) = $db->fetchrow()) {
		//print "<br/>\n$client | $project | $task | $hours<br/>\n";
		if($client != $previousClient) {
			$content .= "<tr><td colspan=\"$width\">$client</td></tr>";
			$previousClient = $client;
		}
		if($project != $previousProject) {
			$content .= "<tr><td colspan=\"$width\" style=\"padding-left: 10px;\">$project</td></tr>";
			$previousProject = $project;
		}
		$content .= "<tr><td style=\"padding-left: 30px;\">$task</td><td>$hours Hours</td></tr>\n";
	}
	$content .= "</table></div>";
	showContent($content);
}



