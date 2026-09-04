<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
require "../includes/screenctl.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
$return = sanitizeInput($_POST["return"] ?? "");
if(empty($return)) {
	$return = sanitizeInput($_GET["return"] ?? "");
}
$serverid = sanitizeInput($_POST["serverid"] ?? "");
if(empty($serverid)) {
	$serverid = sanitizeInput($_GET["serverid"] ?? "");
}
switch ($task) {
	case "restart":
	case "stop":
		$rows = dbRow("SELECT `serverid`, `clientid`, `boxid`, `name`, `user`, `password`, `online` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		if($rows["online"] == "Stopped") {
			$_SESSION["msg1"] = "Server Already Stopped!";
			$_SESSION["msg2"] = "Unable to stop server.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		$rows1 = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
		if(!extension_loaded("ssh2")) {
			$_SESSION["msg1"] = "SSH2 Extension Error!";
			$_SESSION["msg2"] = "SSH2 Extension not detected!";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if(!($sshconnection = @ssh2_connect($rows1["ip"], $rows1["sshport"]))) {
			$_SESSION["msg1"] = "Connection Error!";
			$_SESSION["msg2"] = "Unable to connect to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if(!@ssh2_auth_password($sshconnection, $rows["user"], $rows["password"])) {
			$_SESSION["msg1"] = "Authentication Error!";
			$_SESSION["msg2"] = "Unable to login to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		$sessionName = screenSessionName($rows["serverid"], $rows["user"]);
		sshExec($sshconnection, screenKillCommand($sessionName, $task == "stop" ? (string) $rows["user"] : ""));
		dbExec("UPDATE `server` SET `online` = 'Stopped' WHERE `serverid` = '" . $serverid . "'");
		if($task == "stop") {
			$message = "Server Stopped: <a href=\"serversummary.php?id=" . $serverid . "\">" . $rows["name"] . "</a> (Admin)";
			dbExec("INSERT INTO `log` SET `clientid` = '" . $rows["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
			$_SESSION["msg1"] = "Server Stopped Successfully!";
			$_SESSION["msg2"] = "The server has been stopped.";
			if(!empty($return)) {
				header("Location: " . $return);
			} else {
				header("Location: serversummary.php?id=" . urlencode($serverid));
			}
			exit;
		}
	case "start":
		$rows = dbRow("SELECT * FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		if($rows["online"] == "Started") {
			$_SESSION["msg1"] = "Server Already Started!";
			$_SESSION["msg2"] = "Unable to start server.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		$rows1 = dbRow("SELECT * FROM `ip` WHERE `ipid` = '" . $rows["ipid"] . "' LIMIT 1");
		$rows2 = dbRow("SELECT `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
		if(!extension_loaded("ssh2")) {
			$_SESSION["msg1"] = "SSH2 Extension Error!";
			$_SESSION["msg2"] = "SSH2 Extension not detected!";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if(!($sshconnection = @ssh2_connect($rows2["ip"], $rows2["sshport"]))) {
			$_SESSION["msg1"] = "Connection Error!";
			$_SESSION["msg2"] = "Unable to connect to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if(!@ssh2_auth_password($sshconnection, $rows["user"], $rows["password"])) {
			$_SESSION["msg1"] = "Authentication Error!";
			$_SESSION["msg2"] = "Unable to login to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		$startline = buildStartCommand($rows, $rows1["ip"]);
		$sessionName = screenSessionName($rows["serverid"], $rows["user"]);
		$result = sshExec($sshconnection, screenStartCommand($sessionName, $startline));
		if(preg_match("/not the owner of/i", $result)) {
			$_SESSION["msg1"] = "Session Permission Error!";
			$_SESSION["msg2"] = "Delete /run/screen/S-" . $rows["user"] . " on the box to fix issue.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		dbExec("UPDATE `server` SET `online` = 'Started' WHERE `serverid` = '" . $rows["serverid"] . "'");
		if($task == "start") {
			$message = "Server Started: <a href=\"serversummary.php?id=" . $serverid . "\">" . $rows["name"] . "</a> (Admin)";
			dbExec("INSERT INTO `log` SET `clientid` = '" . $rows["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
			$_SESSION["msg1"] = "Server Started Successfully!";
			$_SESSION["msg2"] = "Allow 20 seconds for server status to show!";
		} elseif($task == "restart") {
			$message = "Server Restarted: <a href=\"serversummary.php?id=" . $serverid . "\">" . $rows["name"] . "</a> (Admin)";
			dbExec("INSERT INTO `log` SET `clientid` = '" . $rows["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
			$_SESSION["msg1"] = "Server Restarted Successfully!";
			$_SESSION["msg2"] = "Allow 20 seconds for server status to show!";
		}
		if(!empty($return)) {
			header("Location:" . $return);
		} else {
			header("Location: serversummary.php?id=" . urlencode($serverid));
		}
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>