<?php
session_start();

if(!isset($_GET["action"])) {
	$_GET["action"] = "";
}

include("constants.inc.php");
include("classes/classes.inc.php");

function showContent($content,  $pageTitle = "", $section = "") {

	if($pageTitle == "") {
		$pageTitle = "Time Management System - by Stephen Fluin";
	}
	
	//Primary Replacements:
	$replace["url"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	$replace["ip"] = $_SERVER["REMOTE_ADDR"];
	$replace["pageTitle"] = $pageTitle;
	$replace["content"] = $content;
	if($_SESSION["userid"]) {
		$replace["logout"] = "<a href=\"logout.php\">Logout</a>";
	} else {
		$replace["logout"] = "";
	}
		
	print wrap("primary.html", $replace);
		
}
function showPage($pageName, $pageTitle = "") {
	$filename = $GLOBALS["root_path"] . "pages/$pageName";
	$fileHandle = fopen( $filename, "r");
	if( !$fileHandle ) {
		$content = "Bad Page (pages/$pageName).";
	} else {
		$content = fread( $fileHandle, filesize( $filename ) );
		fclose($fileHandle);
	}
	
	showContent($content, $pageTitle);
}
function wrap( $templateName , $replace = array()) {

	
	$MASTER_PATH = getcwd();
	chdir( dirname(__FILE__) );
	$template = $GLOBALS["root_path"] . "templates/" . $templateName;
	
	$handle = fopen( $template , "r");
	if(!$handle) {
		return "Bad Template ( " . $template . ")!";
	}
	$tContents = fread( $handle, filesize( $template ) );
	fclose( $handle );
	
	foreach( $replace as $key => $value ) {
		if( substr( $key , 0 , 1 ) == "!" ) {

			$key = substr( $key, 1 );
		} else {
			$key = strtr( $key , array("/" => "\/") );
			$key = "/\[" . $key . "\]/";
		}
		$tContents = preg_replace( $key , $value, $tContents );

		
	}
	chdir($MASTER_PATH);
	return $tContents;
}


function getUsername( $userId ) {
	if($GLOBALS["memberList"][$userId]) {
		return $GLOBALS["memberList"][$userId];
	} else {
		$db = new DB();
		$db->query("SELECT Username from users WHERE id = '$userId' LIMIT 1;");
		list( $name ) = $db->fetchrow();
		$GLOBALS["memberList"][$userId] = $name;	
	}
	return $name;
}


function authenticate() {
	if($_SESSION["username"]) {
		return true;
	} else {
		showContent( wrap("not-logged-in.html", array( "destination" =>  $_SERVER["PHP_SELF"] ) ) );
		exit;
	}
}


function movedPermanently( $newUrl ) {
	header ('HTTP/1.1 301 Moved Permanently');
  	forward($newUrl);
  	return true;
}
function forward( $newUrl) {
	header ("Location: " . $newUrl );
	return true;
}

function checkLogin() {
	$db = new DB();
	if( $_POST["action"] == "login" ) {
		$i = mysql_escape_string($_POST["i"]);
		$o = mysql_escape_string($_POST["o"]);
		if(!$i) { 
			$return .= "You were missing a username.<br />\n";
		} else if(!$o) {
			$return .= "You were missing a password.<br />\n";
		} else {
			$query = "SELECT id, Username, SuperAdmin, NewsAdmin, YOAAdmin, ImageAccess, timeManagement FROM `users` WHERE `UserName` = '$i' AND `Password` = SHA1('$o') LIMIT 1;";
			$db->query( $query );
			if( list($_SESSION["userid"], $_SESSION["username"], $_SESSION["superAdmin"],
				$_SESSION["newsAdmin"], $_SESSION["YOAAdmin"] , $_SESSION["imageAccess"],
				$_SESSION["timeManagement"]) = $db->fetchrow()) {
				/*list( $_SESSION["userid"] , 
					$_SESSION["username"] , 
					$_SESSION["superAdmin"] , 
					$_SESSION["newsAdmin"] , 
					$_SESSION["YOAAdmin"] ,  
					$_SESSION["imageAccess"], 
					$_SESSION["timeManagement"]) = $user;*/
				$query = "UPDATE `users` SET `lastlogin` = NOW(), `ip` = '" . $_SERVER["REMOTE_ADDR"] . "' WHERE UserName = '" . $_SESSION["userid"] . "';";
				$results = $db->query( $query );
				$return .= "Login was successful. ";
				//var_dump($_SESSION);
			} else {
				$return .= "Username $i and given password $o were not found in the database.";
			}
		}
	} else {
		// Nothing
	}
	return $return;
}


function showError($msg = "There was a problem with the database.") {
	showContent($msg);
	exit;
}
