<?php
$return = true;

require "../configuration.php";
require "./include.php";
require "../includes/console.php";

header('Content-Type: application/json; charset=utf-8');

$serverid = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$command  = (string) ($_POST['command'] ?? '');

if ($serverid <= 0) {
	echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
	exit;
}

$server = dbRow(
	"SELECT `serverid`, `boxid`, `user`, `password`, `online`
	 FROM `server`
	 WHERE `serverid` = '{$serverid}'
	 LIMIT 1",
	true
);

if (!$server) {
	echo json_encode(['ok' => false, 'error' => 'Server not found.']);
	exit;
}

$box = dbRow(
	"SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $server['boxid'] . "' LIMIT 1",
	true
);

$message = "Console command on <a href=\"serversummary.php?id=" . (int) $serverid . "\">#" . (int) $serverid . "</a>: " . str_replace(["\r", "\n"], '', $command);
if (trim($command) !== '') {
	dbExec(
		"INSERT INTO `log` SET `serverid` = '" . (int) $serverid . "', "
		. "`message` = '" . sanitizeInput($message) . "', "
		. "`name` = '" . sanitizeInput(($_SESSION['adminfirstname'] ?? '') . ' ' . ($_SESSION['adminlastname'] ?? '')) . "', "
		. "`ip` = '" . sanitizeInput($_SERVER['REMOTE_ADDR'] ?? '') . "'"
	);
}

echo json_encode(
	serverConsoleCommand($server, is_array($box) ? $box : [], $command),
	JSON_INVALID_UTF8_SUBSTITUTE
);
