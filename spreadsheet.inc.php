<?php
$GLOBALS["extraHead"] = wrap("spreadsheet-headers.html",array());


function showSheet($data) {
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



	$content = '
	<form method="post">
	Please select a date range: <input id="rangeStart" name="rangeStart" type="text" class="date" value="' . $start . '">-<input id="rangeEnd" name="rangeEnd" type="text" class="date" value="' . $end . '">

	
	<button type="submit">Update</button>
	</form>
	<form id="sheet" onkeyup="update()">
	<table cellspacing="0">';
	
	
	$string = "abcdefghijklmnopqrstuvwxyz";
	
	// Print Headers
	$content .= "<tr class=\"dateHeader\"><td>Task</td>";
	for($i = 0;$i < $daysInPeriod;$i++) {
		$content .= "<td>" . date("m/d",$stime+$i*$dayLength) . "</td>";
	}
	$content .= "<td>Total</td><td>Comment</td></tr>\n";
	print "<pre>";
	var_dump($data);
	print "</pre>";
	
	// Print Contents
	foreach($data as $client=>$clientData) {
		$content .="<tr class=\"summary client\"><td>$client</td>";
		for($i = 0;$i < $daysInPeriod;$i++) { $content .= "<td></td>"; }
		$content .= "<td></td><td></td></tr>\n";
		
		foreach($clientData as $project=>$projectData) {
			$content .="<tr class=\"summary project\"><td>$project</td>";
			for($i = 0;$i < $daysInPeriod;$i++) { $content .= "<td></td>"; }
			$content .= "<td></td><td></td></tr>\n";
			
			foreach($projectData as $task=>$tid) {
				$content .= '<tr><td>' . $task . '</td>';
				for($i = 0;$i < $daysInPeriod;$i++) {
					$cell = $string[$i] . $j;
					$content .= '<td><input type="text" name="'. $cell . '" id="'. $cell . '" onkeyup="update(event,\''.$string[$i].'\',\''.$j.'\')"/></td>';
				}
				$content .= "<td></td><td></td></tr>\n";
				//$content .= "<div style=\"padding-left: 30px;\">$task<a href=\"edit-task-log-entry.php?action=new&id=$tid\" style=\"position: absolute;left: 500px;\">New Task Log Entry</a></div>\n";
			}
			$content .= "</div>";
		}
		$content .="</tr>";
	}
	$content .= "</table></form>";
	return $content;

}