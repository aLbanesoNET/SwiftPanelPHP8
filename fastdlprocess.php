<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/fastdl.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$task     = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));
$serverid = (int) ($_POST['serverid'] ?? ($_GET['serverid'] ?? 0));

$srv = dbRow(
	"SELECT `serverid`, `name`, `boxid`, `fastdl` FROM `server`
	 WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$who = trim(($_SESSION['clientfirstname'] ?? '') . ' ' . ($_SESSION['clientlastname'] ?? ''));
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';

function fastdl_log(int $clientId, int $serverid, int $boxid, string $message, string $who, string $ip): void
{
	dbExec(
		"INSERT INTO `log` SET " .
		"`clientid` = '" . $clientId . "', " .
		"`serverid` = '" . $serverid . "', " .
		"`boxid` = '" . $boxid . "', " .
		"`message` = '" . dbEscape($message) . "', " .
		"`name` = '" . dbEscape($who) . "', " .
		"`ip` = '" . dbEscape($ip) . "'"
	);
}

if ($task === 'enable') {
	$token = fastdlToken();
	dbExec("UPDATE `server` SET `fastdl` = '" . dbEscape($token) . "' WHERE `serverid` = '" . $serverid . "'");
	fastdl_log(
		$clientId, $serverid, (int) $srv['boxid'],
		'FastDL enabled: <a href="serversummary.php?id=' . $serverid . '">#' . $serverid . '</a> (Client)',
		$who, $ip
	);
	$_SESSION['msg1'] = 'FastDL enabled';
	$_SESSION['msg2'] = 'Set the sv_downloadurl shown on this page in your server config.';
} elseif ($task === 'disable') {
	dbExec("UPDATE `server` SET `fastdl` = '' WHERE `serverid` = '" . $serverid . "'");
	fastdl_log(
		$clientId, $serverid, (int) $srv['boxid'],
		'FastDL disabled: <a href="serversummary.php?id=' . $serverid . '">#' . $serverid . '</a> (Client)',
		$who, $ip
	);
	$_SESSION['msg1'] = 'FastDL disabled';
	$_SESSION['msg2'] = 'The download URL will stop working.';
}

header('Location: serversummary.php?id=' . $serverid);
