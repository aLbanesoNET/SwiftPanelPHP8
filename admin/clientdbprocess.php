<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
require "../includes/dbctl.php";

$task  = sanitizeInput($_POST["task"] ?? ($_GET["task"] ?? ""));
$dbid  = (int) ($_POST["dbid"] ?? ($_GET["dbid"] ?? 0));
$back  = (int) ($_POST["id"] ?? ($_GET["id"] ?? 0));

$row = dbRow("SELECT * FROM `clientdatabase` WHERE `dbid` = '" . $dbid . "' LIMIT 1", TRUE);

if (is_array($row) && $row && $task === "delete") {
	try {
		clientDbDelete($row);
	} catch (Throwable $e) {
		// still remove the panel row
	}
	dbExec("DELETE FROM `clientdatabase` WHERE `dbid` = '" . $dbid . "'");

	$adminName = trim(($_SESSION["adminfirstname"] ?? "") . " " . ($_SESSION["adminlastname"] ?? ""));
	dbExec(
		"INSERT INTO `log` SET " .
		"`clientid` = '" . (int) $row["clientid"] . "', " .
		"`message` = '" . dbEscape('Database deleted: <b>' . htmlspecialchars($row["dbname"], ENT_QUOTES, "UTF-8") . '</b> (Admin)') . "', " .
		"`name` = '" . dbEscape($adminName) . "', " .
		"`ip` = '" . dbEscape($_SERVER["REMOTE_ADDR"] ?? "") . "'"
	);

	$_SESSION["msg1"] = "Database Deleted";
	$_SESSION["msg2"] = "The database and its user have been removed.";
	$back = (int) $row["clientid"];
}

header("Location: clientsummary.php?id=" . $back);
