<?php
$title = "Home";
$page = "index";
$tab = "1";
$return = TRUE;
$image = "home_48";
require "../configuration.php";
require "./include.php";

$numpercent = $numpercent1 = 0;
$numpercent2 = $numpercent3 = $numpercent4 = 0;
$numpercent5 = $numpercent6 = 0;

$numrows = dbCount("SELECT * FROM `client` WHERE `status` = 'Active'");
$numrows1 = dbCount("SELECT * FROM `client` WHERE `status` = 'Inactive'");
$numclients = $numrows + $numrows1;
if($numclients != "0") {
	$numpercent = $numrows / $numclients * 100;
	$numpercent1 = $numrows1 / $numclients * 100;
}
$numrows2 = dbCount("SELECT * FROM `server` WHERE `status` = 'Pending'");
$numrows3 = dbCount("SELECT * FROM `server` WHERE `status` = 'Active'");
$numrows4 = dbCount("SELECT * FROM `server` WHERE `status` = 'Suspended'");
$numservices = $numrows2 + $numrows3 + $numrows4;
if($numservices != "0") {
	$numpercent2 = $numrows2 / $numservices * 100;
	$numpercent3 = $numrows3 / $numservices * 100;
	$numpercent4 = $numrows4 / $numservices * 100;
}
$numrows5 = dbCount("SELECT * FROM `box` WHERE `ssh` = 'Online'");
$numrows6 = dbCount("SELECT * FROM `box` WHERE `ssh` = 'Offline'");
$nummachines = $numrows5 + $numrows6;
if($nummachines != "0") {
	$numpercent5 = $numrows5 / $nummachines * 100;
	$numpercent6 = $numrows6 / $nummachines * 100;
}
$rows = dbRow("SELECT `adminid`, `notes` FROM `admin` WHERE `adminid` = '" . $_SESSION["adminid"] . "' LIMIT 1");
$result1 = dbQuery("SELECT * FROM `log` ORDER BY `logid` DESC LIMIT 15");
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<?php include admin_tpl('dashboard'); ?>
<?php
dbFreeResult($result1);
include "./templates/" . TEMPLATE . "/footer.php";
