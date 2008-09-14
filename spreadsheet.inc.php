<?php
$GLOBALS["extraHead"] = wrap("spreadsheet-headers.html",array());

/**
taskData looks like $data[client][project][task] = tid
userData looksl ike $data[tid][date] = hours
*/
function showSheet($taskData) {
	$dayLength = (60*60*24);
	
	if($_POST["rangeStart"] && $_POST["rangeEnd"]) {
		$start = $_POST["rangeStart"];
		$end = $_POST["rangeEnd"];
	
		$stime = strtotime($start);
		$etime = strtotime($end);
	
	} else {
		$stime = mktime()-($dayLength)*(intval(date("w"))-1);
		$etime = mktime()-($dayLength)*(intval(date("w"))-7);
		
	
		$start = date("m/d/Y", $stime);
		$end = date("m/d/Y", $etime);
	}
	$daysInPeriod = ($etime - $stime) / ($dayLength) + 1;

	//$data[tid][date] = hours
	$db = new DB();
	$db->query("SELECT taskId, date, hours FROM tms_tasklogentry WHERE userId = '" . $_SESSION["userid"] . "' ORDER BY entered ASC;");
	while(list($taskId,$date,$hours) = $db->fetchrow()) {
		$userData[$taskId][$date] = $hours;
	}

	$content = '
	<form method="post">
	Please select a date range: <input id="rangeStart" name="rangeStart" type="text" class="date" value="' . $start . '">-<input id="rangeEnd" name="rangeEnd" type="text" class="date" value="' . $end . '">

	
	<button type="submit">Update</button>
	</form>
	
	<form id="sheet" name="sheet" method="post" action="update-timesheet.php">
	<table cellspacing="0">';
	
	
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
				$content .= '<tr><td class="task">' . $task . '</td>';
				for($i = 0;$i < $daysInPeriod;$i++) {
					$cell = $string[$i] . $j;
					if($userData[$tid][date("Y-m-d",$stime+$i*$dayLength)]) {
						$hours = $userData[$tid][date("Y-m-d",$stime+$i*$dayLength)];
						
					} else {
						$hours = 0;
					}
					
					$content .= '<td><input type="text" name="task['. $tid. "-" . date("Ymd",$stime+$i*$dayLength) . ']" id="'. $cell . '" onkeyup="update(event,\''.$string[$i].'\',\''.$j.'\')" onclick="iselect(this)" value="' . $hours . '"/></td>';
					
				}
				$rows++;
				$content .= "<td id=\"taskTotal".$j."\"></td><td></td></tr>\n";
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