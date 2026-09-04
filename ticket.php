<?php
$title = "Support Ticket";
$page  = "support";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$ticketid = (int) ($_GET['id'] ?? 0);
$ticket = dbRow(
	"SELECT * FROM `ticket` WHERE `ticketid` = '" . $ticketid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($ticket) || empty($ticket)) {
	header('Location: tickets.php');
	exit;
}

$posts = [];
$res = dbQuery("SELECT * FROM `ticketpost` WHERE `ticketid` = '" . $ticketid . "' ORDER BY `postid`");
while ($res && ($r = dbFetch($res))) {
	$posts[] = $r;
}

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('ticket');
include tpl('footer');
