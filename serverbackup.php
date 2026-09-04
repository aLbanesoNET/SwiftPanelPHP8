<?php
$title = "Backups";
$page  = "server";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/backup.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$serverid = (int) ($_GET['id'] ?? 0);
$srv = dbRow(
	"SELECT `serverid`, `name`, `online`, `status`, `boxid`, `homedir` FROM `server`
	 WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$backups = [];
$res = dbQuery("SELECT * FROM `backup` WHERE `serverid` = '" . $serverid . "' ORDER BY `backupid` DESC");
while ($res && ($r = dbFetch($res))) {
	$backups[] = $r;
}

$canCreate = count($backups) < backupMaxPerServer();

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('serverbackup');
include tpl('footer');
