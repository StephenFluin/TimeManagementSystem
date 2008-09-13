<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 STRICT//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html/css; charset=iso-8859-1" />
<title>MortalPowers Spreadsheet</title>
<link href="/spread.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.datepicker.js"></script>
<link rel="stylesheet" href="http://dev.jquery.com/view/tags/ui/latest/themes/flora/flora.datepicker.css" type="text/css" media="screen" title="Flora (Default)">


<style type="text/css">
<!--
* { margin: 0;padding:0;}
#sheet input[type="text"] {width:50px;height:25px;border-style:solid;border: 0 1px 1px 0;padding:0px;margin:0;}
#sheet input:active[type="text"],
#sheet input:focus[type="text"] {background-color:#CCFFFF;}
input.date {width: 95px;}
-->
</style>
<script type="text/javascript">
$(document).ready(function(){
	$('#rangeStart,#rangeEnd').datepicker({
		firstDay: 1,
		beforeShow: customRange
});
  });

function update(event,x,y) {
	if(event && event.keyCode == 13) {
		//alert("got enter key: " + (x + (parseInt(y) + 1)));
		document.getElementById(x + (parseInt(y) + 1)).focus();
	}
}


</script>
</head>
<body>
<form>
Please select a date range: <input id="rangeStart" type="text" class="date">-<input id="rangeEnd" type="text" class="date">
<script type="text/javascript"> 
function customRange(input) { 
    return {minDate: (input.id == "rangeEnd" ? $("#rangeStart").datepicker("getDate") : null), 
        maxDate: (input.id == "rangeStart" ? $("#rangeEnd").datepicker("getDate") : null)}; 
} 
</script>

<button type="submit">Update</button>
</form>
<form id="sheet" onkeyup="update()">
<table cellspacing="0">
<?php
$string = "abcdefghijklmnopqrstuvwxyz";
for($j = 1;$j<20;$j++) {

	echo '<tr>';
	for($i = 0;$i < 7;$i++) {
		$cell = $string[$i] . $j;
		echo '<td><input type="text" name="'. $cell . '" id="'. $cell . '" onkeyup="update(event,\''.$string[$i].'\',\''.$j.'\')"/></td>';
	}
	echo "</tr>\n";
}
?>
</form>
</body>
</html>

