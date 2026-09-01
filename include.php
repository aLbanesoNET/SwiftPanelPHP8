<?php
declare(strict_types=1);

/*
 * Production error posture: log everything, display nothing. Displaying
 * warnings/notices would also corrupt redirects (headers already sent) and
 * leak internals. Override in php.ini during development if needed.
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

session_name('PHPSESSION');
session_cache_expire(30);
session_start();

$page   = $page   ?? '';
$title  = $title  ?? '';
$return = $return ?? null;

if (empty($_SESSION['clientid']) && !empty($return)) {
	if ($return === true) {
		header('Location: login.php');
		exit;
	}
	header('Location: login.php?return=' . urlencode((string) $return));
	exit;
}

require_once __DIR__ . '/includes/functions1.php';
require_once __DIR__ . '/includes/functions3.php';
require_once __DIR__ . '/includes/mysql.php';

$panelName = dbRow(
	"SELECT `value` FROM `config` WHERE `setting` = 'panelname' LIMIT 1",
	true
);

define('SITENAME', $panelName['value'] ?? 'Swift Panel');
define('TEMPLATE', 'default');

$SITE_NAME  = SITENAME;
$SITE_TITLE = $title !== '' ? $title . ' - ' . SITENAME : SITENAME;
$LOGGED_IN  = ($page !== 'login');

$FLASH_MSG1 = $_SESSION['msg1'] ?? null;
$FLASH_MSG2 = $_SESSION['msg2'] ?? null;

unset($_SESSION['msg1'], $_SESSION['msg2']);
