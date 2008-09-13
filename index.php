<?php

include("../include/functions.inc.php");

if($_SESSION["username"]) {
		forward("home.php");
} else {
	forward("/admin.php");
}

