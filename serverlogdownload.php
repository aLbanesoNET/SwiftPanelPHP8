<?php
@set_time_limit(60);
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/access.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$serverid = (int) ($_GET['id'] ?? 0);
$srv = serverForClient($clientId, $serverid);
if (!is_array($srv) || empty($srv)) {
	http_response_code(404);
	exit('Not found');
}

if (!extension_loaded('ssh2')) {
	http_response_code(503);
	exit('Unavailable');
}

$box = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv['boxid'] . "' LIMIT 1", true) ?: [];
$ssh = @ssh2_connect($box['ip'] ?? '', (int) ($box['sshport'] ?? 22) ?: 22);
if (!$ssh || !@ssh2_auth_password($ssh, (string) ($srv['user'] ?? ''), (string) ($srv['password'] ?? ''))) {
	http_response_code(502);
	exit('Connection failed');
}

$sftp = @ssh2_sftp($ssh);
$home = rtrim((string) ($srv['homedir'] ?? ''), '/');

// Prefer the screen log; fall back to a common HLDS log location.
$candidates = [$home . '/screenlog.0', $home . '/cstrike/qconsole.log', $home . '/console.log'];
$found = null;
foreach ($candidates as $p) {
	$st = @ssh2_sftp_stat($sftp, $p);
	if ($st !== false && isset($st['size']) && $st['size'] > 0) { $found = [$p, (int) $st['size']]; break; }
}

if ($found === null) {
	http_response_code(404);
	exit('No console log file was found for this server.');
}

$fh = @fopen('ssh2.sftp://' . (int) $sftp . $found[0], 'rb');
if (!$fh) {
	http_response_code(404);
	exit('Not found');
}

header('Content-Type: text/plain; charset=utf-8');
header('Content-Length: ' . $found[1]);
header('Content-Disposition: attachment; filename="server-' . $serverid . '-console.log"');
while (!feof($fh)) {
	echo fread($fh, 131072);
}
fclose($fh);
