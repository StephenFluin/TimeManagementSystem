<?php
$GLOBALS["extraHead"] = wrap("spreadsheet-headers.html",array());

function updateSheet() {
	$db = new DB();
	$uid = $_SESSION["userid"];
	if($_POST["modified"] == "true" && count($_POST["task"]) > 0) {
	
		foreach($_POST["task"] as $key=>$hours) {
			list($tid,$date) = explode("-",$key);
			if(isset($_POST["comment"][$key]) && $_POST["comment"][$key]) {
				//print "<br/>Accessing: '$key', value: '" . $_POST["comment"][$key] . "'";
				$comment = $db->escape($_POST["comment"][$key]);
			} else {
				$comment = "";
			}
			if($hours > 0) {
				
				$taskEntries[] = "('$uid', '$tid', '$date','$hours','$comment')";	
			}
		}
	
		if(count($taskEntries) > 0) {
			$db = new DB();
			
			list($start,$end,$stime,$etime,$daysInPeriod,$dayLength) = getDatePeriod();
			$db->query("DELETE FROM tms_tasklogentry WHERE userId='$uid' AND date BETWEEN STR_TO_DATE('$start','%m/%d/%Y') AND STR_TO_DATE('$end','%m/%d/%Y')");
			
			
			$sql = "INSERT INTO tms_tasklogentry (userId, taskId, date, hours, comment) VALUES " . join(",",$taskEntries) . ";";
			$db->query($sql);
			
		} else {
			// No non-zero entries to insert.
		}
	} else {
		// Nothing to update.
		//print "Nothing to update!";
	}
		
}

/**
taskData looks like $data[client][project][task] = tid
userData looksl ike $data[tid][date] = hours
*/
function showSheet($taskData) {

	list($start,$end,$stime,$etime,$daysInPeriod,$dayLength) = getDatePeriod();

	//$data[tid][date] = hours
	$db = new DB();
	$db->query("SELECT taskId, date, hours, comment FROM tms_tasklogentry WHERE userId = '" . $_SESSION["userid"] . "' ORDER BY entered ASC;");
	while(list($taskId,$date,$hours,$comment) = $db->fetchrow()) {
		$userData[$taskId][$date] = array($hours,$comment);
	}

	$content = '
	<form id="sheet" name="sheet" method="post">
	Please select a date range: <input id="rangeStart" name="rangeStart" type="text" class="date" value="' . $start . '">-<input id="rangeEnd" name="rangeEnd" type="text" class="date" value="' . $end . '">

	<input type="hidden" name="modified" value="false"/>	
	<button type="button" onclick="sheet.submit()">Update</button>
	
	
	<table cellspacing="0">';
	
	// This method of column naming causes problems with long search terms
	$string = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-=";
	
	// Print Headers
	$content .= "<tr class=\"dateHeader\"><td>Task</td>";
	for($i = 0;$i < $daysInPeriod;$i++) {
		$content .= "<td>" . date("m/d",$stime+$i*$dayLength) . "</td>";
	}
	$content .= "<td>Total</td><td>Comment</td></tr>\n";

	$rows = 0;
	
	// Print Contents
 	foreach($taskData as $client=>$clientData) {
		$content .="<tr class=\"summary client\"><td>$client</td>";
		for($i = 0;$i < $daysInPeriod;$i++) { $content .= "<td></td>"; }
		$content .= "<td></td><td></td></tr>\n";
		
		foreach($clientData as $project=>$projectData) {
			$content .="<tr class=\"summary project\"><td>$project</td>";
			for($i = 0;$i < $daysInPeriod;$i++) { $content .= "<td></td>"; }
			$content .= "<td></td><td></td></tr>\n";
			

			foreach($projectData as $task=>$tid) {
				$j++;
				$comments = "";
				$content .= '<tr><td class="task">' . $task . '</td>';
				for($i = 0;$i < $daysInPeriod;$i++) {
					
					$cell = $string[$i] . $j;
					$dayData = $userData[$tid][date("Y-m-d",$stime+$i*$dayLength)];
					if($dayData) {
						$hours = $dayData[0];
						if($hours != "0") {
							$comment = $dayData[1];
							$comments .= date("m/d",$stime+$i*$dayLength) . "<input type=\"text\" name=\"comment[$tid-" . date("Ymd",$stime+$i*$dayLength) . "]\" value=\"$comment\" onkeyup=\"changed();\"/>";
						} else {
							$comments .= "none.";
						}
						
					} else {
						$hours = 0;
					}
					
					$content .= '<td><input type="text" name="task['. $tid. "-" . date("Ymd",$stime+$i*$dayLength) . ']" id="'. $cell . '" onkeyup="update(event,\''.$string[$i].'\',\''.$j.'\')" onclick="iselect(this)" autocomplete="off" value="' . $hours . '"/></td>';
					
					
				}
				$rows++;
				$content .= "<td id=\"taskTotal".$j."\"></td><td class=\"comment\">$comments</td></tr>\n";
				//$content .= "<div style=\"padding-left: 30px;\">$task<a href=\"edit-task-log-entry.php?action=new&id=$tid\" style=\"position: absolute;left: 500px;\">New Task Log Entry</a></div>\n";
			}
			$content .= "</div>";
		}
		$content .="</tr>";
	}
	
	$content .= "<tr><td><em>Totals</em></td>";
	for($i = 0;$i < $daysInPeriod;$i++) {
		$content .= "<td id=\"dayTotal" . $string[$i] . "\"></td>";
	}
	$content .= "<td></td><td></td></tr>";
	
	$content .= "</table></form><button onclick=\"sheet.submit()\">Save</button>";
	$content .= '<script type="text/javascript"><!--
		var rows = ' . $rows . ';
		var columns = ' .  $daysInPeriod . ';
		var alphabet = "' . $string . '";
		update();
		--></script>';
	return $content;

}

function getDatePeriod() {
	$dayLength = (60*60*24);
	
	if($_POST["rangeStart"] && $_POST["rangeEnd"]) {
		$start = $_POST["rangeStart"];
		$end = $_POST["rangeEnd"];
	
		$stime = strtotime($start);
		$etime = strtotime($end);
	
	} else {
		// Default Monday-Sunday of this week.
		$stime = mktime()-($dayLength)*(intval(date("N"))-1);
		$etime = mktime()-($dayLength)*(intval(date("N"))-7);
		
	
		$start = date("m/d/Y", $stime);
		$end = date("m/d/Y", $etime);
	}
	$daysInPeriod = ($etime - $stime) / ($dayLength) + 1;

	return array($start,$end,$stime,$etime,$daysInPeriod,$dayLength);
}