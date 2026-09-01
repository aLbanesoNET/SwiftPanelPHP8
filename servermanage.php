<?php
// PHP 8+ compatible rewrite of the decoded PHP 5 script.

$return = true;

require __DIR__ . "/configuration.php";
require __DIR__ . "/include.php";

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
		$rows = dbRow(
			"SELECT `serverid`, `clientid`, `boxid`, `name`, `user`, `password`, `online`, `status`
			 FROM `server`
			 WHERE `serverid` = '" . $serverid . "'
			   AND `clientid` = '" . $clientId . "'
			 LIMIT 1"
		);

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

		$shell = @ssh2_shell($ssh, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
		if (!$shell) {
			$_SESSION["msg1"] = "Shell Error!";
			$_SESSION["msg2"] = "Unable to open SSH shell.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		// Kill screen session for this server safely
		$sessionName = ($rows["serverid"] ?? "") . "-" . ($rows["user"] ?? "");
		$sessionNameEsc = str_replace('"', '\"', $sessionName);

		fwrite($shell, "kill -9 \$(screen -list | grep \"" . $sessionNameEsc . "\" | awk '{print \\$1}' | cut -d . -f1)\n");
		sleep(2);
		fwrite($shell, "screen -wipe\n");
		sleep(2);
		fclose($shell);

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
			$_SESSION["msg2"] = "<br />";

			header("Location: " . (!empty($returnTo) ? $returnTo : "serversummary.php?id=" . urlencode($serverid)));
			exit;
		}

		// fall-through into start for "restart"
		// no break here intentionally
	}

	case "start": {
		$rows = dbRow(
			"SELECT * FROM `server`
			 WHERE `serverid` = '" . $serverid . "'
			   AND `clientid` = '" . $clientId . "'
			 LIMIT 1"
		);

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

		$shell = @ssh2_shell($ssh, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
		if (!$shell) {
			$_SESSION["msg1"] = "Shell Error!";
			$_SESSION["msg2"] = "Unable to open SSH shell.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}

		$sessionName = ($rows["serverid"] ?? "") . "-" . ($rows["user"] ?? "");
		fwrite($shell, "screen -A -m -S " . $sessionName . "\n");
		sleep(2);

		// Optional: detect "not the owner of" errors, but don't block forever
		stream_set_timeout($shell, 1);
		$startTime = time();
		while (!feof($shell) && (time() - $startTime) < 2) {
			$line = fgets($shell);
			if ($line === false) break;
			if (preg_match("/not the owner of/i", $line)) {
				$_SESSION["msg1"] = "Session Permission Error!";
				$_SESSION["msg2"] = "Please notify your administrator of this problem.";
				fclose($shell);
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
		}

		fwrite($shell, $startline . "\n");
		sleep(3);
		fclose($shell);

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