<?php

include("include/functions.inc.php");
authenticate();

$uid = $_SESSION["userid"];

foreach($_POST["task"] as $key=>$hours) {
	list($tid,$date) = explode("-",$key);
	if($hours > 0) {
		
		$comments[] = "('$uid', '$tid', '$date','$hours')";	
	}
}

$db = new DB();

$sql = "INSERT INTO tms_tasklogentry (userId, taskId, date, hours) VALUES " . join(",",$comments) . ";";
$db->query($sql);

forward("home.php");



