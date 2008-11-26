<?php

include("include/functions.inc.php");
authenticate();

$type = $_POST["type"];
$userid = $_POST["userid"];
$username = getUsername($_POST['userid']);
$month = explode("-", $_POST["month"]);  // [0] = year, [1] = month
$content = "";


if($type == "user-month") {
	// "Running a report about a user.";
	if($_SESSION["isAdministrator"] == 0 && $_SESSION["username"] != $username) {
		showContent("You don't have access to run reports about other users!");
	}
	
	$content .= "<div class=\"standardBox\"><h1>Time Report for $username</h1><div>";
	$db = new DB();
	$sql = "SELECT c.Name, p.project, t.Task, SUM(tle.Hours) FROM tms_tasklogentry as tle LEFT JOIN tms_task as t ON t.id = tle.taskId ON p.id = t.projectId JOIN tms_client c ON c.id = p.clientId WHERE tle.userId = '$userid' GROUP BY t.projectId, t.id";
	$db->query($sql);
	
	if($db->size() == 0) {
		$content .= "No data was found for this user and this time period.";
	}
	//$content .= $sql;
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
} else if ($type == 'invoicing') {
	if($_SESSION["isAdministrator"] == 0) {
		showContent("You don't have access to run reports about other users!");
	}
	$db = new DB();
	$month = $_POST["month"];
	$date = strtotime($month);
	list($year,$month) = explode("-",$month);
	$separator = "!|";
	
	$sqlstart = date("Y-m-d",mktime(0,0,0,$month,1,$year));
	$sqlend =   date("Y-m-d",mktime(0,0,0,$month+1,0,$year));
	
	$content .= "<div class=\"standardBox\"><h1>Adjust Actuals - " . date("F-Y",$date) . "</h1><div>";
	$sql = "SELECT c.name, p.project, t.task,u.username, sum(hours), GROUP_CONCAT(le.comment SEPARATOR '$separator') 
		FROM `tms_tasklogentry` le 
			JOIN tms_task t ON t.id = le.taskId 
			JOIN tms_project p ON p.id = t.projectId 
			JOIN tms_client c ON c.id = p.clientId 
			JOIN tms_user u ON u.id = le.userId 
		WHERE le.date BETWEEN '$sqlstart' AND '$sqlend' 
			GROUP BY le.taskId, le.userId 
			ORDER BY c.name,p.project;";
	$db->query($sql);
	if($db->size() == 0) {
		showContent ("No data is available for the requested period.");
	}
	while(list($client, $project, $task, $username, $hours, $comments) = $db->fetchrow()) {
		$data[$client][$project][$task][$username] = array($hours,$comments);
	}
	$content .= "<table>";
	
	foreach($data as $client=>$clientData) {
		$content .= "<tr class=\"summary client\"><td>$client</td><td></td><td></td></tr>";
		foreach($clientData as $project=>$projectData) {
			$content .= "<tr class=\"summary project\"><td>$project</td><td></td><td></td></tr>";
			foreach($projectData as $task=>$taskData) {
				$content .= "<tr class=\"summary task\"><td>$task</td><td></td><td></td></tr>";
				foreach($taskData as $user=>$userData) {
					$content .= "<tr><td class=\"task\">$user</td><td>" . $userData[0] . "h</td><td>";
					foreach(explode($separator, $userData[1]) as $value) {
						if($value != '') {
							$content .= $value . ",";
						}
					}
					$content .= "</td></tr>";
				
				}
			}
		}
	}
	$content .= "</table>";
	$content .= "</div></div>";
	
	showContent($content);
	
	
} else if($type="newinvoice") {
	if($_SESSION["isAdministrator"] == 0 ) {
		showContent("You don't have access to run reports about other users!");
		exit;
	}
	$start = $month[0] . "-" . $month[1];
	$end = $month[0] . '-' . ($month[1] + 1);
	
	$db = new DB();
	$sql = "SELECT  c.Name, p.project, t.Task, tle.userId, SUM(tle.hours) FROM tms_tasklogentry as tle LEFT JOIN tms_task as t ON t.id = tle.taskId JOIN tms_project as p ON p.id = t.projectId JOIN tms_client c ON c.id = p.clientId WHERE tle.date > '$start' AND tle.date < '$end' GROUP BY t.id, tle.userId";
	$db->query($sql);
	//print $sql;
	
	
	if($db->size() == 0) {
		showContent('No time has been logged yet for the selected time period.');
	}
	while(list($client,$project,$task,$user,$hours) = $db->fetchrow()) {
		//print "$client-$project-$task-$hours<br/>\n";
		$data[$client][$project][$task][getUsername($user)] = $hours;
	}
	$content .= "<style>table td {border-bottom: 1px solid black;}</style>";
	$content .= "<table>";
	foreach($data as $client=>$clientData) {
	$content .= "<tr><td>$client</td><td></td><td></td><td></td><td></td></tr>";
		foreach($clientData as $project=>$projectData) {
			$content .= "<tr><td></td><td>$project</td><td></td><td></td><td></td></tr>";
			foreach($projectData as $task=>$taskData) {
				$content .= "<tr><td></td><td></td><td>$task</td><td></td><td></td></tr>";
				foreach($taskData as $user=>$hours) {
					$content .= "<tr><td></td><td></td><td></td><td>$user</td><td>$hours</td></tr>";
				}
			}
		}
	}
	$content .= "</table>";
	showContent($content);
	
	


} else {
	showContent("Unknown report type '$type'.");
}



