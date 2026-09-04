<?php
// PHP 8+ compatible rewrite of the decoded PHP 5 script.

$return = true;

require __DIR__ . "/configuration.php";
require __DIR__ . "/include.php";
require __DIR__ . "/includes/screenctl.php";
require __DIR__ . "/includes/access.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Helper: fetch param from POST then GET, sanitize once
function input_param(string $key, $default = "") {
	$val = $_POST[$key] ?? ($_GET[$key] ?? $default);
	return sanitizeInput($val);
}

$task	 = input_param("task", "");
$returnTo = input_param("return", "");
$serverid = input_param("serverid", "");

if ($task === "") {
	header("Location: index.php");
	exit;
}

$clientId = $_SESSION["clientid"] ?? null;
if (!$clientId) {
	$_SESSION["msg1"] = "Session Error!";
	$_SESSION["msg2"] = "Please login again.";
	header("Location: index.php");
	exit;
}

if ($serverid === "") {
	$_SESSION["msg1"] = "Input Error!";
	$_SESSION["msg2"] = "Missing server id.";
	header("Location: servers.php");
	exit;
}

switch ($task) {
	case "restart":
	case "stop": {
		$rows = serverForClient((int) $clientId, (int) $serverid);

		if (!is_array($rows) || empty($rows)) {
			$_SESSION["msg1"] = "Server Error!";
			$_SESSION["msg2"] = "Server not found.";
			header("Location: servers.php");
			exit;
		}

		$online = $rows["online"] ?? "";
		$status = $rows["status"] ?? "";

		if ($online === "Stopped" || $status === "Suspended") {
			$_SESSION["msg1"] = "Server Already Stopped!";
			$_SESSION["msg2"] = "Unable to stop server.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$box = dbRow(
			"SELECT `ip`, `sshport`
			 FROM `box`
			 WHERE `boxid` = '" . ($rows["boxid"] ?? "") . "'
			 LIMIT 1"
		);

		$boxIp   = $box["ip"] ?? "";
		$sshPort = (int)($box["sshport"] ?? 22);

		if (!extension_loaded("ssh2")) {
			$_SESSION["msg1"] = "SSH2 Extension Error!";
			$_SESSION["msg2"] = "SSH2 Extension not detected!";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$ssh = @ssh2_connect($boxIp, $sshPort);
		if (!$ssh) {
			$_SESSION["msg1"] = "Connection Error!";
			$_SESSION["msg2"] = "Unable to connect to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$okAuth = @ssh2_auth_password($ssh, (string)($rows["user"] ?? ""), (string)($rows["password"] ?? ""));
		if (!$okAuth) {
			$_SESSION["msg1"] = "Authentication Error!";
			$_SESSION["msg2"] = "Unable to login to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		// Kill every screen session for this server. On a full stop also sweep
		// anything the server left running; on a restart don't (start follows).
		$sessionName = screenSessionName($rows["serverid"] ?? "", $rows["user"] ?? "");
		sshExec($ssh, screenKillCommand($sessionName, $task === "stop" ? (string) ($rows["user"] ?? "") : ""));

		dbExec("UPDATE `server` SET `online` = 'Stopped' WHERE `serverid` = '" . $serverid . "'");

		if ($task === "stop") {
			$name = $rows["name"] ?? ("Server #" . $serverid);

			$clientFirst = $_SESSION["clientfirstname"] ?? "";
			$clientLast  = $_SESSION["clientlastname"] ?? "";
			$ip		  = $_SERVER["REMOTE_ADDR"] ?? "";

			$message = "Server Stopped: <a href=\"serversummary.php?id=" . $serverid . "\">" . $name . "</a> (Client)";

			dbExec(
				"INSERT INTO `log` SET
					`clientid` = '" . ($rows["clientid"] ?? "") . "',
					`serverid` = '" . $serverid . "',
					`boxid`	= '" . ($rows["boxid"] ?? "") . "',
					`message`  = '" . $message . "',
					`name`	 = '" . trim($clientFirst . " " . $clientLast) . "',
					`ip`	   = '" . $ip . "'"
			);

			$_SESSION["msg1"] = "Server Stopped Successfully!";
			$_SESSION["msg2"] = "The server has been stopped.";

			header("Location: " . (!empty($returnTo) ? $returnTo : "serversummary.php?id=" . urlencode($serverid)));
			exit;
		}

		// fall-through into start for "restart"
		// no break here intentionally
	}

	case "start": {
		$rows = serverForClient((int) $clientId, (int) $serverid);

		if (!is_array($rows) || empty($rows)) {
			$_SESSION["msg1"] = "Server Error!";
			$_SESSION["msg2"] = "Server not found.";
			header("Location: servers.php");
			exit;
		}

		$online = $rows["online"] ?? "";
		$status = $rows["status"] ?? "";

		if ($online === "Started" || $status === "Suspended") {
			$_SESSION["msg1"] = "Server Already Started!";
			$_SESSION["msg2"] = "Unable to start server.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$rows1 = dbRow(
			"SELECT * FROM `ip`
			 WHERE `ipid` = '" . ($rows["ipid"] ?? "") . "'
			 LIMIT 1"
		);

		$box = dbRow(
			"SELECT `ip`, `sshport`, `login`, `password`
			 FROM `box`
			 WHERE `boxid` = '" . ($rows["boxid"] ?? "") . "'
			 LIMIT 1"
		);

		$boxIp   = $box["ip"] ?? "";
		$sshPort = (int)($box["sshport"] ?? 22);

		if (!extension_loaded("ssh2")) {
			$_SESSION["msg1"] = "SSH2 Extension Error!";
			$_SESSION["msg2"] = "SSH2 Extension not detected!";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$ssh = @ssh2_connect($boxIp, $sshPort);
		if (!$ssh) {
			$_SESSION["msg1"] = "Connection Error!";
			$_SESSION["msg2"] = "Unable to connect to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$okAuth = @ssh2_auth_password($ssh, (string)($rows["user"] ?? ""), (string)($rows["password"] ?? ""));
		if (!$okAuth) {
			$_SESSION["msg1"] = "Authentication Error!";
			$_SESSION["msg2"] = "Unable to login to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$serverIp = $rows1["ip"] ?? "";
		$startline = buildStartCommand($rows, $serverIp);

		// Clear any old session, then start fresh in one detached screen.
		$sessionName = screenSessionName($rows["serverid"] ?? "", $rows["user"] ?? "");
		$result = sshExec($ssh, screenStartCommand($sessionName, $startline));

		if (preg_match("/not the owner of/i", $result)) {
			$_SESSION["msg1"] = "Session Permission Error!";
			$_SESSION["msg2"] = "Delete /run/screen/S-" . ($rows["user"] ?? "") . " on the box to fix this.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		dbExec("UPDATE `server` SET `online` = 'Started' WHERE `serverid` = '" . ($rows["serverid"] ?? $serverid) . "'");

		$name = $rows["name"] ?? ("Server #" . $serverid);

		$clientFirst = $_SESSION["clientfirstname"] ?? "";
		$clientLast  = $_SESSION["clientlastname"] ?? "";
		$ip		  = $_SERVER["REMOTE_ADDR"] ?? "";

		if ($task === "start") {
			$message = "Server Started: <a href=\"serversummary.php?id=" . $serverid . "\">" . $name . "</a> (Client)";
			$_SESSION["msg1"] = "Server Started Successfully!";
			$_SESSION["msg2"] = "Allow 20 seconds for server status to show!";
		} else { // restart
			$message = "Server Restarted: <a href=\"serversummary.php?id=" . $serverid . "\">" . $name . "</a> (Client)";
			$_SESSION["msg1"] = "Server Restarted Successfully!";
			$_SESSION["msg2"] = "Allow 20 seconds for server status to show!";
		}

		dbExec(
			"INSERT INTO `log` SET
				`clientid` = '" . ($rows["clientid"] ?? "") . "',
				`serverid` = '" . $serverid . "',
				`boxid`	= '" . ($rows["boxid"] ?? "") . "',
				`message`  = '" . $message . "',
				`name`	 = '" . trim($clientFirst . " " . $clientLast) . "',
				`ip`	   = '" . $ip . "'"
		);

		header("Location: " . (!empty($returnTo) ? $returnTo : "serversummary.php?id=" . urlencode($serverid)));
		exit;
	}

	default:
		header("Location: index.php");
		exit;
}
?>