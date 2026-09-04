<?php
$title = "Sharing";
$page  = "server";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/access.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$serverid = (int) ($_GET['id'] ?? 0);

// Owner only.
if (!clientOwnsServer($clientId, $serverid)) {
	header('Location: serversummary.php?id=' . $serverid);
	exit;
}

$srv = dbRow("SELECT `serverid`, `name` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1", true);

$subs = [];
$res = dbQuery(
	"SELECT s.*, c.`firstname`, c.`lastname` FROM `subuser` s
	 LEFT JOIN `client` c ON c.`clientid` = s.`subclientid`
	 WHERE s.`serverid` = '" . $serverid . "' ORDER BY s.`subid`"
);
while ($res && ($r = dbFetch($res))) {
	$subs[] = $r;
}

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('serversubusers');
include tpl('footer');
