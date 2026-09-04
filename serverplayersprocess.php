<?php
@set_time_limit(30);
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/console.php';
require __DIR__ . '/includes/players.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$serverid = (int) ($_POST['serverid'] ?? ($_GET['serverid'] ?? 0));
$srv = dbRow("SELECT * FROM `server` WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1", true);
if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$name = (string) ($_POST['name'] ?? ($_GET['name'] ?? ''));
if (($_POST['task'] ?? $_GET['task'] ?? '') === 'kick' && $name !== '') {
	$box = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv['boxid'] . "' LIMIT 1", true) ?: [];
	serverConsoleCommand($srv, $box, playerKickCommand($name));
	$logName = htmlspecialchars(str_replace(["\r", "\n"], '', $name), ENT_QUOTES, 'UTF-8');
	dbExec(
		"INSERT INTO `log` SET `clientid` = '" . $clientId . "', `serverid` = '" . $serverid . "', " .
		"`boxid` = '" . (int) $srv['boxid'] . "', `message` = '" . dbEscape('Kicked "' . $logName . '" from <a href="serversummary.php?id=' . $serverid . '">#' . $serverid . '</a>') . "', " .
		"`name` = '" . dbEscape(trim(($_SESSION['clientfirstname'] ?? '') . ' ' . ($_SESSION['clientlastname'] ?? ''))) . "', `ip` = '" . dbEscape($_SERVER['REMOTE_ADDR'] ?? '') . "'"
	);
	$_SESSION['msg1'] = 'Kick sent';
	$_SESSION['msg2'] = 'Sent kick for "' . $logName . '".';
}

header('Location: serverplayers.php?id=' . $serverid);
