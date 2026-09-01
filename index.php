<?php
declare(strict_types=1);

$title  = 'Home';
$page   = 'index';
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$client = dbRow(
	"SELECT * FROM `client` WHERE `clientid` = '" . (int)$_SESSION['clientid'] . "' LIMIT 1"
);

if (!$client) {
	session_destroy();
	header('Location: login.php');
	exit;
}

$result = dbQuery(
	"SELECT serverid, ipid, name, game, status, online, slots, type, port
	 FROM `server`
	 WHERE clientid = '" . (int)$_SESSION['clientid'] . "'
	 ORDER BY serverid"
);

$servers = [];

while ($row = dbFetch($result)) {
	if (!empty($row['ipid'])) {
		$ip = dbRow(
			"SELECT ip FROM `ip` WHERE ipid = '" . (int)$row['ipid'] . "' LIMIT 1"
		);
		$row['ip'] = $ip['ip'] ?? null;
	}
	$servers[] = $row;
}

$client = [
	'first_name' => $client['firstname'],
	'last_name'  => $client['lastname'],
	'email'      => $client['email'],
	'servers'    => count($servers),
];

require __DIR__ . '/templates/default/header.php';
require __DIR__ . '/templates/default/index.php';
require __DIR__ . '/templates/default/footer.php';