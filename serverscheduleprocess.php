<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/schedule.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$task     = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));
$serverid = (int) ($_POST['serverid'] ?? ($_GET['serverid'] ?? 0));

$srv = dbRow(
	"SELECT `serverid`, `name`, `boxid` FROM `server`
	 WHERE `serverid` = '" . $serverid . "' AND `clientid` = '" . $clientId . "' LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv)) {
	header('Location: server.php');
	exit;
}

$who = trim(($_SESSION['clientfirstname'] ?? '') . ' ' . ($_SESSION['clientlastname'] ?? ''));
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';

function sch_flash(string $a, string $b, int $serverid): void
{
	$_SESSION['msg1'] = $a;
	$_SESSION['msg2'] = $b;
	header('Location: serverschedule.php?id=' . $serverid);
	exit;
}

function sch_log(array $srv, int $clientId, string $message, string $who, string $ip): void
{
	dbExec(
		"INSERT INTO `log` SET `clientid` = '" . $clientId . "', `serverid` = '" . (int) $srv['serverid'] . "', " .
		"`boxid` = '" . (int) $srv['boxid'] . "', `message` = '" . dbEscape($message) . "', " .
		"`name` = '" . dbEscape($who) . "', `ip` = '" . dbEscape($ip) . "'"
	);
}

if ($task === 'create') {
	if (dbCount("SELECT `schedid` FROM `schedule` WHERE `serverid` = '" . $serverid . "'") >= 10) {
		sch_flash('Limit reached', 'A server can have at most 10 schedules.', $serverid);
	}

	$action = in_array($_POST['action'] ?? '', scheduleActions(), true) ? $_POST['action'] : 'restart';
	$freq   = in_array($_POST['freq'] ?? '', scheduleFreqs(), true) ? $_POST['freq'] : 'daily';
	$min    = max(0, min(59, (int) ($_POST['at_minute'] ?? 0)));
	$hour   = max(0, min(23, (int) ($_POST['at_hour'] ?? 0)));
	$dow    = max(0, min(6,  (int) ($_POST['dow'] ?? 0)));
	$command = '';
	if ($action === 'command') {
		$command = trim(str_replace(["\r", "\n"], '', (string) ($_POST['command'] ?? '')));
		if ($command === '') {
			sch_flash('Missing command', 'Enter the console command to run.', $serverid);
		}
	}

	$label = trim(sanitizeInput($_POST['label'] ?? ''));
	if ($label === '') {
		$label = ucfirst($action) . ' (' . $freq . ')';
	}

	$row = [
		'freq' => $freq, 'at_minute' => $min, 'at_hour' => $hour, 'dow' => $dow,
	];
	$next = scheduleNextRun($row, time());

	dbExec(
		"INSERT INTO `schedule` SET " .
		"`serverid` = '" . $serverid . "', `clientid` = '" . $clientId . "', " .
		"`label` = '" . dbEscape($label) . "', `action` = '" . dbEscape($action) . "', " .
		"`command` = '" . dbEscape($command) . "', `freq` = '" . dbEscape($freq) . "', " .
		"`at_minute` = '" . $min . "', `at_hour` = '" . $hour . "', `dow` = '" . $dow . "', " .
		"`enabled` = '1', `lastrun` = NULL, `nextrun` = '" . date('Y-m-d H:i:s', $next) . "'"
	);

	sch_log($srv, $clientId, 'Schedule added: <b>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</b> (Client)', $who, $ip);
	sch_flash('Schedule added', 'Next run: ' . date('D d M, H:i', $next) . '.', $serverid);
}

$schedid = (int) ($_POST['schedid'] ?? ($_GET['schedid'] ?? 0));
$sc = dbRow("SELECT * FROM `schedule` WHERE `schedid` = '" . $schedid . "' AND `serverid` = '" . $serverid . "' LIMIT 1", true);
if (!is_array($sc) || !$sc) {
	sch_flash('Not found', 'That schedule does not exist.', $serverid);
}

if ($task === 'toggle') {
	$new = ($sc['enabled'] === '1') ? '0' : '1';
	$next = $new === '1' ? date('Y-m-d H:i:s', scheduleNextRun($sc, time())) : null;
	dbExec("UPDATE `schedule` SET `enabled` = '" . $new . "', `nextrun` = " . ($next ? "'" . $next . "'" : 'NULL') . " WHERE `schedid` = '" . $schedid . "'");
	sch_flash($new === '1' ? 'Schedule enabled' : 'Schedule paused', $sc['label'], $serverid);
}

if ($task === 'delete') {
	dbExec("DELETE FROM `schedule` WHERE `schedid` = '" . $schedid . "'");
	sch_log($srv, $clientId, 'Schedule removed: <b>' . htmlspecialchars((string) $sc['label'], ENT_QUOTES, 'UTF-8') . '</b> (Client)', $who, $ip);
	sch_flash('Schedule removed', 'The schedule has been deleted.', $serverid);
}

header('Location: serverschedule.php?id=' . $serverid);
