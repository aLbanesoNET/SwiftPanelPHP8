<?php
@set_time_limit(0);
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/backup.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$task     = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));
$serverid = (int) ($_POST['serverid'] ?? ($_GET['serverid'] ?? 0));

$srv = dbRow(
	"SELECT * FROM `server` WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$box = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv['boxid'] . "' LIMIT 1", true) ?: [];
$who = trim(($_SESSION['clientfirstname'] ?? '') . ' ' . ($_SESSION['clientlastname'] ?? ''));
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';

function bk_flash(string $a, string $b, int $serverid): void
{
	$_SESSION['msg1'] = $a;
	$_SESSION['msg2'] = $b;
	header('Location: serverbackup.php?id=' . $serverid);
	exit;
}

function bk_log(array $srv, int $clientId, string $message, string $who, string $ip): void
{
	dbExec(
		"INSERT INTO `log` SET `clientid` = '" . $clientId . "', `serverid` = '" . (int) $srv['serverid'] . "', " .
		"`boxid` = '" . (int) $srv['boxid'] . "', `message` = '" . dbEscape($message) . "', " .
		"`name` = '" . dbEscape($who) . "', `ip` = '" . dbEscape($ip) . "'"
	);
}

if ($task === 'create') {
	if (dbCount("SELECT `backupid` FROM `backup` WHERE `serverid` = '" . $serverid . "'") >= backupMaxPerServer()) {
		bk_flash('Limit reached', 'Delete an old backup before making a new one (max ' . backupMaxPerServer() . ').', $serverid);
	}

	$label = trim(sanitizeInput($_POST['name'] ?? '')) ?: ('Backup ' . date('Y-m-d H:i'));
	$res = backupCreate($srv, $box, $label);

	if (empty($res['ok'])) {
		bk_flash('Backup failed', $res['error'] ?? 'Unknown error.', $serverid);
	}

	dbExec(
		"INSERT INTO `backup` SET `serverid` = '" . $serverid . "', `clientid` = '" . $clientId . "', " .
		"`name` = '" . dbEscape($label) . "', `filename` = '" . dbEscape($res['filename']) . "', " .
		"`sizebytes` = '" . (int) $res['size'] . "', `status` = 'done', `created` = NOW()"
	);
	bk_log($srv, $clientId, 'Backup created: <b>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</b> (Client)', $who, $ip);
	bk_flash('Backup created', number_format($res['size'] / 1048576, 1) . ' MB archived.', $serverid);
}

$backupid = (int) ($_POST['backupid'] ?? ($_GET['backupid'] ?? 0));
$bk = dbRow("SELECT * FROM `backup` WHERE `backupid` = '" . $backupid . "' AND `serverid` = '" . $serverid . "' LIMIT 1", true);
if (!is_array($bk) || !$bk) {
	bk_flash('Not found', 'That backup does not exist.', $serverid);
}

if ($task === 'delete') {
	backupDelete($srv, $box, (string) $bk['filename']);
	dbExec("DELETE FROM `backup` WHERE `backupid` = '" . $backupid . "'");
	bk_log($srv, $clientId, 'Backup deleted: <b>' . htmlspecialchars((string) $bk['name'], ENT_QUOTES, 'UTF-8') . '</b> (Client)', $who, $ip);
	bk_flash('Backup deleted', 'The archive has been removed.', $serverid);
}

if ($task === 'restore') {
	// Stop the server first so files are not in use.
	$ssh = serverPowerConnect($srv, $box);
	if ($ssh) {
		serverPowerStop($ssh, $srv, true);
		dbExec("UPDATE `server` SET `online` = 'Stopped' WHERE `serverid` = '" . $serverid . "'");
	}
	$res = backupRestore($srv, $box, (string) $bk['filename']);
	if (empty($res['ok'])) {
		bk_flash('Restore failed', $res['error'] ?? 'Unknown error.', $serverid);
	}
	bk_log($srv, $clientId, 'Backup restored: <b>' . htmlspecialchars((string) $bk['name'], ENT_QUOTES, 'UTF-8') . '</b> (Client)', $who, $ip);
	bk_flash('Backup restored', 'The server was stopped and its files replaced. Start it when ready.', $serverid);
}

header('Location: serverbackup.php?id=' . $serverid);
