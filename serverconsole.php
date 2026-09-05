<?php
declare(strict_types=1);

$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
requireSameOrigin('index.php');
require __DIR__ . '/includes/console.php';
require __DIR__ . '/includes/access.php';

header('Content-Type: application/json; charset=utf-8');

$serverid = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$command  = (string) ($_POST['command'] ?? '');
$clientid = (int) ($_SESSION['clientid'] ?? 0);

if ($serverid <= 0 || $clientid <= 0) {
	echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
	exit;
}

$server = serverForClient($clientid, $serverid);

if (!$server) {
	echo json_encode(['ok' => false, 'error' => 'Server not found.']);
	exit;
}

$box = dbRow(
	"SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $server['boxid'] . "' LIMIT 1",
	true
);

echo json_encode(
	serverConsoleCommand($server, is_array($box) ? $box : [], $command),
	JSON_INVALID_UTF8_SUBSTITUTE
);
