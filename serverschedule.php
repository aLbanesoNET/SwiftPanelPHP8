<?php
$title = "Schedules";
$page  = "server";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/schedule.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$serverid = (int) ($_GET['id'] ?? 0);
$srv = dbRow(
	"SELECT `serverid`, `name`, `online`, `status` FROM `server`
	 WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$schedules = [];
$res = dbQuery("SELECT * FROM `schedule` WHERE `serverid` = '" . $serverid . "' ORDER BY `schedid`");
while ($res && ($r = dbFetch($res))) {
	$r['summary'] = scheduleDescribe($r);
	$schedules[]  = $r;
}

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('serverschedule');
include tpl('footer');
