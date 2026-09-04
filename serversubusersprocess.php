<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/access.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$task     = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));
$serverid = (int) ($_POST['serverid'] ?? ($_GET['serverid'] ?? 0));

if (!clientOwnsServer($clientId, $serverid)) {
	header('Location: serversummary.php?id=' . $serverid);
	exit;
}

function su_flash(string $a, string $b, int $serverid): void
{
	$_SESSION['msg1'] = $a;
	$_SESSION['msg2'] = $b;
	header('Location: serversubusers.php?id=' . $serverid);
	exit;
}

if ($task === 'add') {
	$email = strtolower(trim(sanitizeInput($_POST['email'] ?? '')));
	if ($email === '' || !str_contains($email, '@')) {
		su_flash('Invalid email', 'Enter the account email of the person you want to share with.', $serverid);
	}
	if (dbCount("SELECT `subid` FROM `subuser` WHERE `serverid` = '" . $serverid . "' AND `subemail` = '" . dbEscape($email) . "'") > 0) {
		su_flash('Already shared', 'That email is already on this server.', $serverid);
	}

	$c = dbRow("SELECT `clientid`, `email` FROM `client` WHERE `email` = '" . dbEscape($email) . "' LIMIT 1", true);
	$subclientid = (is_array($c) && isset($c['clientid'])) ? (int) $c['clientid'] : 0;

	if ($subclientid === $clientId) {
		su_flash('That is you', 'You already own this server.', $serverid);
	}

	dbExec(
		"INSERT INTO `subuser` SET `serverid` = '" . $serverid . "', `ownerid` = '" . $clientId . "', " .
		"`subclientid` = '" . $subclientid . "', `subemail` = '" . dbEscape($email) . "', `created` = NOW()"
	);

	su_flash(
		'Shared',
		$subclientid > 0
			? $email . ' can now see this server (view, console, power) in their account.'
			: 'No account with that email yet — access starts as soon as they sign up.',
		$serverid
	);
}

if ($task === 'remove') {
	$subid = (int) ($_POST['subid'] ?? ($_GET['subid'] ?? 0));
	dbExec("DELETE FROM `subuser` WHERE `subid` = '" . $subid . "' AND `serverid` = '" . $serverid . "'");
	su_flash('Access removed', 'They can no longer see this server.', $serverid);
}

header('Location: serversubusers.php?id=' . $serverid);
