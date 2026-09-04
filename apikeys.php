<?php
$title = "API Keys";
$page  = "apikeys";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$keys = [];
$res = dbQuery("SELECT * FROM `apikey` WHERE `clientid` = '" . $clientId . "' ORDER BY `keyid` DESC");
while ($res && ($r = dbFetch($res))) {
	$keys[] = $r;
}

// A freshly-created token, shown exactly once.
$newToken = $_SESSION['new_apikey'] ?? null;
unset($_SESSION['new_apikey']);

$msg1 = $FLASH_MSG1 ?? null;
$msg2 = $FLASH_MSG2 ?? null;

include tpl('header');
include tpl('apikeys');
include tpl('footer');
