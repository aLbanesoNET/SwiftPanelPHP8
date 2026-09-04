<?php
@set_time_limit(30);
$title = "Players";
$page  = "server";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/console.php';
require __DIR__ . '/includes/players.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$serverid = (int) ($_GET['id'] ?? 0);
$srv = dbRow(
	"SELECT * FROM `server` WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$box = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv['boxid'] . "' LIMIT 1", true) ?: [];

$players = [];
$playerError = '';
if (($srv['online'] ?? '') === 'Started') {
	$r = serverConsoleCommand($srv, $box, 'status');
	if (!empty($r['ok'])) {
		$players = parsePlayerStatus((string) $r['output']);
	} else {
		$playerError = (string) ($r['error'] ?? 'Could not read the server.');
	}
} else {
	$playerError = 'The server is stopped.';
}

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('serverplayers');
include tpl('footer');
