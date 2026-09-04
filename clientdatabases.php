<?php
$title = "Databases";
$page  = "database";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/dbctl.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$cfg = clientDbConfig();
if (!$cfg['enabled']) {
	header('Location: index.php');
	exit;
}

$usage     = clientDbUsageMap();
$databases = [];

$res = dbQuery("SELECT * FROM `clientdatabase` WHERE `clientid` = '" . $clientId . "' ORDER BY `dbid`");
while ($res && ($row = dbFetch($res))) {
	$row['plainpass'] = clientDbDecode($row['dbpass']);
	$row['used_mb']   = $usage[$row['dbname']] ?? (float) $row['disksize'];
	$row['limit_mb']  = ((int) $row['maxsize'] > 0) ? (int) $row['maxsize'] : (int) $cfg['maxsize'];
	$databases[]      = $row;
}

$canCreate = ($cfg['max'] === 0) || (count($databases) < $cfg['max']);

// include.php already moved any flash into $FLASH_MSG* and cleared the session.
$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('clientdatabases');
include tpl('footer');
