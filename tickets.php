<?php
$title = "Support";
$page  = "support";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$tickets = [];
$res = dbQuery(
	"SELECT t.*, (SELECT COUNT(*) FROM `ticketpost` p WHERE p.`ticketid` = t.`ticketid`) AS posts
	 FROM `ticket` t WHERE t.`clientid` = '" . $clientId . "' ORDER BY t.`updated` DESC, t.`ticketid` DESC"
);
while ($res && ($r = dbFetch($res))) {
	$tickets[] = $r;
}

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('tickets');
include tpl('footer');
