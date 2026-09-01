<?php
/*
 * Client-side server reinstall / rebuild.
 *
 * Wipes the game-server home directory and re-copies the game's install
 * template (the same operation the admin "Rebuild" performs), for a server the
 * logged-in client owns. The server must be stopped. The copy runs detached in
 * a screen session on the box, so the request returns immediately.
 *
 * Adapted from CyberFX's "SWIFT Panel Rebuild/Reinstall Client Servers" add-on
 * for the mysqli / PHP 8 code base.
 */

$return = true;

require __DIR__ . "/configuration.php";
require __DIR__ . "/include.php";
require __DIR__ . "/includes/boxctl.php"; // pulls includes/screenctl.php

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

function rebuild_input(string $key): string
{
	return sanitizeInput($_POST[$key] ?? ($_GET[$key] ?? ""));
}

/** A game-directory path we are willing to rm -rf: absolute, deep, no metacharacters, not a system root. */
function rebuild_path_ok(string $path): bool
{
	$path = rtrim($path, "/");

	if ($path === "" || $path[0] !== "/" || strlen($path) < 7) {
		return false;
	}
	if (preg_match('#[^A-Za-z0-9/._-]#', $path)) {
		return false;
	}
	$blocked = ["/home", "/root", "/usr", "/var", "/etc", "/opt", "/srv", "/bin", "/lib", "/boot"];

	return !in_array($path, $blocked, true);
}

$task     = rebuild_input("task");
$serverid = rebuild_input("serverid");
$clientId = $_SESSION["clientid"] ?? null;

if (!$clientId) {
	$_SESSION["msg1"] = "Session Error!";
	$_SESSION["msg2"] = "Please login again.";
	header("Location: index.php");
	exit;
}

if ($task !== "serverrebuild") {
	header("Location: index.php");
	exit;
}

$srv = dbRow(
	"SELECT `serverid`, `clientid`, `boxid`, `name`, `user`, `homedir`, `installdir`, `online`, `status`
	 FROM `server`
	 WHERE `serverid` = '" . $serverid . "'
	   AND `clientid` = '" . $clientId . "'
	 LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	$_SESSION["msg1"] = "Server Error!";
	$_SESSION["msg2"] = "Server not found.";
	header("Location: server.php");
	exit;
}

$homedir    = rtrim((string) ($srv["homedir"] ?? ""), "/");
$installdir = rtrim((string) ($srv["installdir"] ?? ""), "/");

if (!rebuild_path_ok($homedir) || !rebuild_path_ok($installdir)) {
	$_SESSION["msg1"] = "Validation Error!";
	$_SESSION["msg2"] = "This server has no valid install directory. Ask an administrator to run the install wizard.";
	header("Location: serversummary.php?id=" . urlencode($serverid));
	exit;
}

if (($srv["online"] ?? "") === "Started") {
	$_SESSION["msg1"] = "Validation Error!";
	$_SESSION["msg2"] = "Stop the server before reinstalling.";
	header("Location: serversummary.php?id=" . urlencode($serverid));
	exit;
}

if (!extension_loaded("ssh2")) {
	$_SESSION["msg1"] = "SSH2 Extension Error!";
	$_SESSION["msg2"] = "SSH2 Extension not detected!";
	header("Location: serversummary.php?id=" . urlencode($serverid));
	exit;
}

$box = dbRow(
	"SELECT `boxid`, `name`, `ip`, `sshport`, `login`, `password`
	 FROM `box`
	 WHERE `boxid` = '" . ($srv["boxid"] ?? "") . "'
	 LIMIT 1",
	true
);

$conn = $box ? boxSshConnect($box) : null;
if (!$conn) {
	$_SESSION["msg1"] = "Connection Error!";
	$_SESSION["msg2"] = "Unable to reach the box over SSH.";
	header("Location: serversummary.php?id=" . urlencode($serverid));
	exit;
}

$homeArg    = escapeshellarg($homedir);
$installArg = escapeshellarg($installdir);

// The install template must exist, or we would wipe the home dir for nothing.
if (trim(sshExec($conn, "test -d $installArg && echo ok", 10)) !== "ok") {
	$_SESSION["msg1"] = "Command Error!";
	$_SESSION["msg2"] = "Install directory not found on the box: " . $installdir;
	header("Location: serversummary.php?id=" . urlencode($serverid));
	exit;
}

sshExec($conn, "mkdir -p $homeArg 2>/dev/null; true", 10);

$user    = preg_replace('/[^A-Za-z0-9._-]/', "", (string) ($srv["user"] ?? ""));
$ownArg  = escapeshellarg(($user !== "" ? $user . ":" . $user : "root:root"));
$session = "rebuild-" . (int) $serverid;

$rebuild = "nice -n 19 find $homeArg -mindepth 1 -delete && "
	. "nice -n 19 cp -a $installArg/. $homeArg/ && "
	. "chown -R $ownArg $homeArg";

sshExec(
	$conn,
	"screen -wipe >/dev/null 2>&1; "
	. "screen -dmS " . escapeshellarg($session) . " /bin/sh -c " . escapeshellarg($rebuild),
	15
);

dbExec("UPDATE `server` SET `online` = 'Stopped' WHERE `serverid` = '" . $serverid . "'");

$clientName = trim(($_SESSION["clientfirstname"] ?? "") . " " . ($_SESSION["clientlastname"] ?? ""));
$ip         = $_SERVER["REMOTE_ADDR"] ?? "";
$message    = 'Server Rebuilt: <a href="serversummary.php?id=' . (int) $serverid . '">'
	. htmlspecialchars((string) ($srv["name"] ?? ("Server #" . (int) $serverid)), ENT_QUOTES)
	. '</a> on <a href="boxsummary.php?id=' . (int) ($srv["boxid"] ?? 0) . '">'
	. htmlspecialchars((string) ($box["name"] ?? ""), ENT_QUOTES) . '</a> (Client)';

dbExec(
	"INSERT INTO `log` SET
		`clientid` = '" . ($srv["clientid"] ?? "") . "',
		`serverid` = '" . $serverid . "',
		`boxid`    = '" . ($srv["boxid"] ?? "") . "',
		`message`  = '" . dbEscape($message) . "',
		`name`     = '" . sanitizeInput($clientName) . "',
		`ip`       = '" . sanitizeInput($ip) . "'"
);

$_SESSION["msg1"] = "Reinstall Started!";
$_SESSION["msg2"] = "The server files are being rebuilt. Allow a few minutes before starting the server.";
header("Location: serversummary.php?id=" . urlencode($serverid));
exit;
