<?php
$title = "Notifications";
$page  = "notifications";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

if (isset($_GET['read']) && notifyTableExists()) {
	dbExec("UPDATE `notification` SET `seen` = '1' WHERE `clientid` = '" . $clientId . "'");
	header('Location: notifications.php');
	exit;
}

$notifs = notifyRecent($clientId, 30);

include tpl('header');
include tpl('notifications');
include tpl('footer');
