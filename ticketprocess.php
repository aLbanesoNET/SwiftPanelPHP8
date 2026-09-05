<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
requireSameOrigin('index.php');

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$task = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));
$name = trim(($_SESSION['clientfirstname'] ?? '') . ' ' . ($_SESSION['clientlastname'] ?? '')) ?: 'Client';

if ($task === 'create') {
	$subject = trim(sanitizeInput($_POST['subject'] ?? ''));
	$body    = trim((string) ($_POST['body'] ?? ''));
	$priority = in_array($_POST['priority'] ?? '', ['low', 'normal', 'high'], true) ? $_POST['priority'] : 'normal';

	if ($subject === '' || $body === '') {
		$_SESSION['msg1'] = 'Missing details';
		$_SESSION['msg2'] = 'A subject and a message are both required.';
		header('Location: tickets.php');
		exit;
	}

	dbExec(
		"INSERT INTO `ticket` SET `clientid` = '" . $clientId . "', `subject` = '" . dbEscape($subject) . "', " .
		"`status` = 'open', `priority` = '" . dbEscape($priority) . "', `created` = NOW(), `updated` = NOW()"
	);
	$tid = dbInsertId();
	dbExec(
		"INSERT INTO `ticketpost` SET `ticketid` = '" . (int) $tid . "', `author` = 'client', " .
		"`name` = '" . dbEscape($name) . "', `body` = '" . dbEscape($body) . "', `created` = NOW()"
	);

	$_SESSION['msg1'] = 'Ticket opened';
	$_SESSION['msg2'] = 'Our team will reply as soon as possible.';
	header('Location: ticket.php?id=' . (int) $tid);
	exit;
}

$ticketid = (int) ($_POST['ticketid'] ?? ($_GET['ticketid'] ?? 0));
$ticket = dbRow("SELECT * FROM `ticket` WHERE `ticketid` = '" . $ticketid . "' AND `clientid` = '" . $clientId . "' LIMIT 1", true);
if (!is_array($ticket) || empty($ticket)) {
	header('Location: tickets.php');
	exit;
}

if ($task === 'reply') {
	$body = trim((string) ($_POST['body'] ?? ''));
	if ($body !== '') {
		dbExec(
			"INSERT INTO `ticketpost` SET `ticketid` = '" . $ticketid . "', `author` = 'client', " .
			"`name` = '" . dbEscape($name) . "', `body` = '" . dbEscape($body) . "', `created` = NOW()"
		);
		dbExec("UPDATE `ticket` SET `status` = 'open', `updated` = NOW() WHERE `ticketid` = '" . $ticketid . "'");
	}
	header('Location: ticket.php?id=' . $ticketid);
	exit;
}

if ($task === 'close') {
	dbExec("UPDATE `ticket` SET `status` = 'closed', `updated` = NOW() WHERE `ticketid` = '" . $ticketid . "'");
	$_SESSION['msg1'] = 'Ticket closed';
	$_SESSION['msg2'] = 'Reopen it any time by replying.';
	header('Location: ticket.php?id=' . $ticketid);
	exit;
}

header('Location: tickets.php');
