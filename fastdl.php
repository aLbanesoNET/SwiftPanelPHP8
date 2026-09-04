<?php
/**
 * Public FastDL endpoint. No session, no login — the token in the URL is the
 * capability and only grants read access to one server's fastdl/ directory.
 *   /fastdl/<token>/<path>      (rewritten to /fastdl.php?t=<token>&f=<path>)
 *
 * Loads only the DB layer (not include.php) so asset downloads carry no session
 * overhead or no-cache headers.
 */

require __DIR__ . '/configuration.php';
require __DIR__ . '/includes/functions1.php';
require __DIR__ . '/includes/mysql.php';
require __DIR__ . '/includes/fastdl.php';

$token = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['t'] ?? ''));
$file  = (string) ($_GET['f'] ?? '');

if (strlen($token) < 12 || $file === '') {
	http_response_code(404);
	exit('Not found');
}

$srv = dbRow(
	"SELECT `serverid`, `boxid`, `user`, `password`, `homedir`, `fastdl`
	 FROM `server`
	 WHERE `fastdl` = '" . dbEscape($token) . "'
	 LIMIT 1",
	true
);

if (!is_array($srv) || empty($srv) || (string) $srv['fastdl'] !== $token) {
	http_response_code(404);
	exit('Not found');
}

$box = dbRow(
	"SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv['boxid'] . "' LIMIT 1",
	true
);

if (!is_array($box) || empty($box)) {
	http_response_code(404);
	exit('Not found');
}

fastdlStream($srv, $box, $file);
