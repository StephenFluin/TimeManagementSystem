<?php

include("include/functions.inc.php");
authenticate();

// Reporting - for everyone
$body .= reporting();

showContent($body);