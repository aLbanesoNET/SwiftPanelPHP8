<?php
$return = true;

require "../configuration.php";
require "./include.php";
requireSameOrigin('index.php');
require "../includes/boxctl.php";

header('Content-Type: application/json; charset=utf-8');

$boxid   = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$command = (string) ($_POST['command'] ?? '');

if ($boxid <= 0) {
	echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
	exit;
}

$box = dbRow(
	"SELECT `boxid`, `name`, `ip`, `sshport`, `login`, `password`
	 FROM `box`
	 WHERE `boxid` = '{$boxid}'
	 LIMIT 1",
	true
);

if (!$box) {
	echo json_encode(['ok' => false, 'error' => 'Box not found.']);
	exit;
}

if (trim($command) !== '') {
	$logCommand = htmlspecialchars(str_replace(["\r", "\n"], '', $command), ENT_QUOTES, 'UTF-8');
	$msg = 'Box console on <a href="boxsummary.php?id=' . (int) $boxid . '">#' . (int) $boxid . '</a>: ' . $logCommand;
	dbExec(
		"INSERT INTO `log` SET `boxid` = '" . (int) $boxid . "', "
		. "`message` = '" . dbEscape($msg) . "', "
		. "`name` = '" . sanitizeInput(($_SESSION['adminfirstname'] ?? '') . ' ' . ($_SESSION['adminlastname'] ?? '')) . "', "
		. "`ip` = '" . sanitizeInput($_SERVER['REMOTE_ADDR'] ?? '') . "'"
	);
}

echo json_encode(boxConsoleCommand($box, $command), JSON_INVALID_UTF8_SUBSTITUTE);
