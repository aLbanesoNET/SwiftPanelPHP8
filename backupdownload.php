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

$backupid = (int) ($_GET['id'] ?? 0);

$bk = dbRow(
	"SELECT b.*, s.`boxid`, s.`user`, s.`password`, s.`homedir`
	 FROM `backup` b
	 JOIN `server` s ON s.`serverid` = b.`serverid`
	 WHERE b.`backupid` = '" . $backupid . "' AND b.`clientid` = '" . $clientId . "'
	 LIMIT 1",
	true
);

if (!is_array($bk) || empty($bk)) {
	http_response_code(404);
	exit('Not found');
}

$box = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $bk['boxid'] . "' LIMIT 1", true);
if (!is_array($box) || empty($box)) {
	http_response_code(404);
	exit('Not found');
}

backupStream($bk, $box, (string) $bk['filename']);
