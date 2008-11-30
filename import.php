<?php
/**
* To create a data file for this script, use project server 2003 web access "Adjust Actuals" with all resources selected for the desired period.
* Make sure the choices are "Project" "resource name" and "None", export to excel.  Open the .xml file in excel and save sheet 2 as
* a csv.
*/


require("include/functions.inc.php");
ini_set('memory_limit',"128M");

$sheet2 = file_get_contents("actuals-q1q2-sheet2.csv");
//$sheet2 = file_get_contents("actuals-q3-November-sheet2.csv");
print "WARNING: All imported values are assumed to be correct and will overwrite existing numbers!\n" . 
	 "WARNING: This script also assumes that the data contains ALL actuals for the given users in the given timeperiod.\n\n\n";

$data = array();

$lines = explode("\n",$sheet2);
foreach($lines as $line) {
	if(preg_match("@^[^\s]@", $line) ) {
	// Found a top level data line.
		$lineData = explode(",",$line);
		if($lineData[0] == "Task Name") {
			// Get Dates out.
			for($i = 2;$i<count($lineData)-1;$i++) {
				if($lineData[$i] != "Total" && $lineData[$i]) {
					$dates[] = strtotime($lineData[$i]);
				}
			}
			//print "Found the following dates:\n";
			//var_dump($dates);
		} else if($lineData[5]) {
			// Project
			$project=$lineData[0];
			if($project == "Total:") {
				continue;
			}
			$projects[$project] = true;
			
			//New project, finish off tmpdata if exists;
			if($tmpdata) {
				$data[] = $tmpdata;
			}
			$previousDepth = 0;
		} else {
			// Probably Ignore
		}
		//print $line . "\n";
	
	
	} else if(preg_match("@^\s{3}[^\s]@", $line) ) {
		// Found a user.
		$lineData = explode(",",$line);
		$user = trim($lineData[0]);
		$users[$user] = true;
		
		//New user, finish off tmpdata if exists;
		if($tmpdata) {
			$data[] = $tmpdata;
		}
		$previousDepth = 0;
			
		//print "$line.\n";
	} else if (preg_match("@^(\s{9,})[^\s]@", $line, $match) ) {
		// Found a task
		$lineData = explode(",",$line);
		
		$currentDepth = strlen($match[1]);
		//Prevent inclusion of summary tasks.
		//If it is a summary task, commit the tmpData to data.
		if($previousDepth && $previousDepth <= $currentDepth) {
			$data[] = $tmpdata;
		}
		$previousDepth = $currentDepth;
		
		if($lineData[2] != "0h") {
			$taskName = trim($lineData[0]);
			$tmpdata = array($project,$user,$taskName,$lineData);
			
			//print $line . "\n";
			//if($max++ > 20) {
				//var_dump($data);
				//exit;
			//}
		}

	
	} else {
		//print "Unrecognized line: $line\n";
	}

}
//Finished!  Finish off tmpdata if exists;
if($tmpdata) {
	$data[] = $tmpdata;
}

$userIds = ensureUsersExist($users);
$projectIds = ensureProjectsExist($projects);

// Erase all existing hours for covered dates and covered users.
$sDate = date("Ymd",$dates[0]);
$eDate = date("Ymd",$dates[count($dates)-1]);
print "Start date is " . $sDate . " end date is " . $eDate . "\n";
$db = new DB();
$sql = "SELECT SUM(hours) from tms_tasklogentry WHERE `date` BETWEEN '$sDate' AND '$eDate' AND (userId='" . implode("' OR userId='", $userIds) . "');";
$db->query($sql);
print floatval($db->getScalar()) . " hours about to be deleted.\n";
$db->query("DELETE FROM tms_tasklogentry WHERE `date` BETWEEN '$sDate' AND '$eDate' AND (userId='" . implode("' OR userId='", $userIds) . "');");


//Iterate through all of the data and insert into the db.
foreach($data as $item) {
	$inserts = array();
	list($project,$user,$task,$lineData) = $item;
	for($i=2;$i<count($lineData)-1;$i++) {
		$value = floatval($lineData[$i]);
		if($value) {
			
			//if($user == "Doug") {
			//	print date("Ymd",$dates[$i-2]) . " is " . $value . " for $user on $project (".$task.")\n";
			//}
			$taskId = ensureTaskExists($projectIds[$project], $task);
			$userId = $userIds[$user];
			$inserts[] = "('$taskId','$userId','" . date("Ymd",$dates[$i-2]) . "', '$value')";

			//exit;
		}
	}
	if(count($inserts) > 0) {
		$sql = "INSERT INTO tms_tasklogentry (taskId,userId,`date`,hours) VALUES " . implode(", ",$inserts) . ";";
		//print $sql;
		$db->query($sql);
	}
}

$sql = "SELECT SUM(hours) from tms_tasklogentry WHERE `date` BETWEEN '$sDate' AND '$eDate' AND (userId='" . implode("' OR userId='", $userIds) . "');";
//print $sql;
$db->query($sql);
print floatval($db->getScalar()) . " hours inserted successfully.\n";








function ensureTaskExists($projectId,$task) {
	$db = new DB();
	$task = $db->escape($task);
	$db->query("SELECT id FROM tms_task WHERE projectId = '$projectId' AND Task='$task' LIMIT 1;");
	$id = $db->getScalar();
	if(!$id) {
		// Task wasn't in DB yet, create it!
		$db->query("INSERT INTO tms_task (projectId, Task) VALUES ('$projectId','$task');");
		$id = $db->getInsertId();
		
	}
	return $id;
}


function ensureUsersExist($names) {
	$db = new DB();
	foreach($names as $name=>$null) {
		$db->query( "SELECT id FROM tms_user WHERE Username='$name' LIMIT 1;");
		$id = $db->getScalar();
		if(!$id) {
			$db->query("INSERT INTO tms_user (Username) VALUES ('$name')");
			$id = $db->getInsertId();
			if(!$id) {
				print "ID could not be found/created for user '$name', ERRROR!";
				exit;
			}
		}
		$users[$name] = $id;
		
		// User $name:  . $id . "\n";
		
		
	}
	return $users;
}
function ensureProjectsExist($projects) {
	$db = new DB();
	foreach($projects as $name=>$null) {
		$db->query( "SELECT id FROM tms_project WHERE importName='$name' LIMIT 1;");
		$id = $db->getScalar();
		if(!$id) {
			$db->query("SELECT id FROM tms_project WHERE project='$name' LIMIT 1;");
			$id = $db->getScalar();
		}
		if(!$id) {
			$sql = "INSERT INTO tms_project (clientId, project, importName) VALUES ('6','$name','$name');" ;
			//print $sql;
			$db->query($sql);
			$id = $db->getInsertId();
			if(!$id) {
				print "ID could not be found/created for project '$name', ERRROR!";
				exit;
			}
		}
		$projectData[$name]=$id;
	}
	return $projectData;
}



