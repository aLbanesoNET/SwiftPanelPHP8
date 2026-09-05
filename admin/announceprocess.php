<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
requireSameOrigin('index.php');

$task  = sanitizeInput($_POST["task"] ?? ($_GET["task"] ?? ""));
$annid = (int) ($_POST["annid"] ?? ($_GET["annid"] ?? 0));

if ($task === "save") {
	$titleV = trim(sanitizeInput($_POST["title"] ?? ""));
	$bodyV  = trim((string) ($_POST["body"] ?? ""));
	$active = (($_POST["active"] ?? "1") === "1") ? "1" : "0";

	if ($titleV === "" || $bodyV === "") {
		$_SESSION["msg1"] = "Validation Error!";
		$_SESSION["msg2"] = "<li>A title and message are both required.</li>";
		header("Location: announce.php" . ($annid ? "?edit=" . $annid : ""));
		exit;
	}

	$bodyEsc = dbEscape($bodyV);
	$adminName = trim(($_SESSION["adminfirstname"] ?? "") . " " . ($_SESSION["adminlastname"] ?? ""));

	if ($annid > 0) {
		dbExec("UPDATE `announcement` SET `title` = '" . dbEscape($titleV) . "', `body` = '" . $bodyEsc . "', `active` = '" . $active . "' WHERE `annid` = '" . $annid . "'");
		$_SESSION["msg1"] = "Announcement Updated";
	} else {
		dbExec("INSERT INTO `announcement` SET `title` = '" . dbEscape($titleV) . "', `body` = '" . $bodyEsc . "', `active` = '" . $active . "', `adminname` = '" . dbEscape($adminName) . "', `created` = NOW()");
		$_SESSION["msg1"] = "Announcement Posted";
	}
	$_SESSION["msg2"] = "Your changes have been saved.";
	header("Location: announce.php");
	exit;
}

if ($task === "delete" && $annid > 0) {
	dbExec("DELETE FROM `announcement` WHERE `annid` = '" . $annid . "'");
	$_SESSION["msg1"] = "Announcement Deleted";
	$_SESSION["msg2"] = "The announcement has been removed.";
	header("Location: announce.php");
	exit;
}

header("Location: announce.php");
