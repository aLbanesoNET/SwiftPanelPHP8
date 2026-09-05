<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
requireSameOrigin('index.php');
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

$task = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));

$clientName = trim(($_SESSION['clientfirstname'] ?? '') . ' ' . ($_SESSION['clientlastname'] ?? ''));
$clientIp   = $_SERVER['REMOTE_ADDR'] ?? '';

function cdb_flash(string $a, string $b): void
{
	$_SESSION['msg1'] = $a;
	$_SESSION['msg2'] = $b;
	header('Location: clientdatabases.php');
	exit;
}

function cdb_log(int $clientId, string $message, string $name, string $ip): void
{
	dbExec(
		"INSERT INTO `log` SET " .
		"`clientid` = '" . $clientId . "', " .
		"`message` = '" . dbEscape($message) . "', " .
		"`name` = '" . dbEscape($name) . "', " .
		"`ip` = '" . dbEscape($ip) . "'"
	);
}

if ($task === 'create') {
	$count = dbCount("SELECT `dbid` FROM `clientdatabase` WHERE `clientid` = '" . $clientId . "'");
	if ($cfg['max'] > 0 && $count >= $cfg['max']) {
		cdb_flash('Limit reached', 'Your account already has the maximum of ' . (int) $cfg['max'] . ' database(s).');
	}

	$name = clientDbSanitizeName($_POST['name'] ?? '');
	if ($name === null) {
		cdb_flash('Invalid name', 'Use 1-24 characters: lowercase letters, digits or underscore.');
	}

	$dbname = 'c' . $clientId . '_' . $name;
	if (dbCount("SELECT `dbid` FROM `clientdatabase` WHERE `dbname` = '" . dbEscape($dbname) . "'") > 0) {
		cdb_flash('Name in use', 'You already have a database with that name.');
	}

	try {
		$created = clientDbCreate($clientId, $name, $cfg['host']);
	} catch (Throwable $e) {
		cdb_flash('Could not create database', $e->getMessage());
	}

	dbExec(
		"INSERT INTO `clientdatabase` SET " .
		"`clientid` = '" . $clientId . "', " .
		"`dbname` = '"  . dbEscape($created['dbname']) . "', " .
		"`dbuser` = '"  . dbEscape($created['dbuser']) . "', " .
		"`dbpass` = '"  . dbEscape(clientDbEncode($created['dbpass'])) . "', " .
		"`dbhost` = '"  . dbEscape($created['dbhost']) . "', " .
		"`maxsize` = '" . (int) $cfg['maxsize'] . "', " .
		"`disksize` = '0.00', " .
		"`created` = NOW()"
	);

	cdb_log($clientId, 'Database created: <b>' . htmlspecialchars($created['dbname'], ENT_QUOTES, 'UTF-8') . '</b> (Client)', $clientName, $clientIp);
	cdb_flash('Database created', 'Your database and login are ready.');
}

$dbid = (int) ($_POST['dbid'] ?? ($_GET['dbid'] ?? 0));
$row  = dbRow(
	"SELECT * FROM `clientdatabase` WHERE `dbid` = '" . $dbid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($row) || !$row) {
	cdb_flash('Not found', 'That database is not on your account.');
}

if ($task === 'resetpw') {
	try {
		$new = clientDbResetPassword($row);
	} catch (Throwable $e) {
		cdb_flash('Could not reset password', $e->getMessage());
	}
	dbExec("UPDATE `clientdatabase` SET `dbpass` = '" . dbEscape(clientDbEncode($new)) . "' WHERE `dbid` = '" . $dbid . "'");
	cdb_flash('Password reset', 'The new password is shown on the database list.');
}

if ($task === 'delete') {
	try {
		clientDbDelete($row);
	} catch (Throwable $e) {
		// fall through — still drop the panel row so it can't get stuck
	}
	dbExec("DELETE FROM `clientdatabase` WHERE `dbid` = '" . $dbid . "'");
	cdb_log($clientId, 'Database deleted: <b>' . htmlspecialchars($row['dbname'], ENT_QUOTES, 'UTF-8') . '</b> (Client)', $clientName, $clientIp);
	cdb_flash('Database deleted', 'The database and its user have been removed.');
}

header('Location: clientdatabases.php');
