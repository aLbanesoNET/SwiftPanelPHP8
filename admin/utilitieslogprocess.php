<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "deletelog":
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("TRUNCATE `log`");
		$_SESSION["msg1"] = "Activity Logs Deleted Successfully!";
		$_SESSION["msg2"] = "All activity logs have been removed.";
		header("Location: utilitieslog.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>