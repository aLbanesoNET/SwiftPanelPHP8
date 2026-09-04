<?php
/**
 * Minimal client REST API. Authenticate with:  Authorization: Bearer <token>
 *
 *   GET  /api.php/servers                 list your servers
 *   GET  /api.php/servers/<id>            one server
 *   POST /api.php/servers/<id>/power      body {"action":"start|stop|restart"}
 *                                         (rejected for read-only keys)
 */

@set_time_limit(30);
header('Content-Type: application/json');

require __DIR__ . '/configuration.php';
require __DIR__ . '/includes/functions1.php';
require __DIR__ . '/includes/functions3.php';
require __DIR__ . '/includes/mysql.php';

function api_out($data, int $code = 200): void
{
	http_response_code($code);
	echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	exit;
}

/* ---- Auth ---- */

$hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
if ($hdr === '' && function_exists('apache_request_headers')) {
	$h = apache_request_headers();
	$hdr = $h['Authorization'] ?? ($h['authorization'] ?? '');
}
if (!preg_match('/Bearer\s+([A-Za-z0-9_-]{20,80})/', $hdr, $m)) {
	api_out(['error' => 'Missing or malformed Authorization: Bearer <token>'], 401);
}

$token = $m[1];
$key = dbRow("SELECT * FROM `apikey` WHERE `tokenhash` = '" . dbEscape(hash('sha256', $token)) . "' LIMIT 1", true);
if (!is_array($key) || empty($key)) {
	api_out(['error' => 'Invalid API key'], 401);
}
dbExec("UPDATE `apikey` SET `lastused` = NOW() WHERE `keyid` = '" . (int) $key['keyid'] . "'");
$clientId = (int) $key['clientid'];
$readonly = ($key['readonly'] === '1');

/* ---- Routing ---- */

$path = trim((string) ($_GET['_path'] ?? preg_replace('#^.*/api\.php#', '', $_SERVER['REQUEST_URI'] ?? '')), '/');
$path = strtok($path, '?');
$seg  = $path === '' ? [] : explode('/', $path);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function server_public(array $s): array
{
	return [
		'id'      => (int) $s['serverid'],
		'name'    => $s['name'],
		'game'    => $s['game'],
		'status'  => $s['status'],
		'online'  => $s['online'],
		'slots'   => (int) $s['slots'],
		'address' => (!empty($s['ip']) ? $s['ip'] . ':' . (int) $s['port'] : null),
	];
}

if (($seg[0] ?? '') === 'servers' && !isset($seg[1])) {
	$res = dbQuery("SELECT s.*, i.`ip` FROM `server` s LEFT JOIN `ip` i ON i.`ipid` = s.`ipid` WHERE s.`clientid` = '" . $clientId . "' ORDER BY s.`serverid`");
	$out = [];
	while ($r = dbFetch($res)) { $out[] = server_public($r); }
	api_out(['servers' => $out]);
}

if (($seg[0] ?? '') === 'servers' && isset($seg[1]) && ctype_digit($seg[1])) {
	$sid = (int) $seg[1];
	$srv = dbRow("SELECT s.*, i.`ip` FROM `server` s LEFT JOIN `ip` i ON i.`ipid` = s.`ipid` WHERE s.`serverid` = '" . $sid . "' AND s.`clientid` = '" . $clientId . "' LIMIT 1", true);
	if (!is_array($srv) || empty($srv)) {
		api_out(['error' => 'Server not found'], 404);
	}

	if (($seg[2] ?? '') === 'power' && $method === 'POST') {
		if ($readonly) {
			api_out(['error' => 'This API key is read-only'], 403);
		}
		$body = json_decode(file_get_contents('php://input') ?: '[]', true);
		$action = $body['action'] ?? '';
		if (!in_array($action, ['start', 'stop', 'restart'], true)) {
			api_out(['error' => 'action must be start, stop or restart'], 422);
		}

		require_once __DIR__ . '/includes/serverpower.php';
		$box = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv['boxid'] . "' LIMIT 1", true) ?: [];
		$ssh = serverPowerConnect($srv, $box);
		if (!$ssh) {
			api_out(['error' => 'Could not reach the server box'], 502);
		}
		if ($action === 'stop' || $action === 'restart') {
			serverPowerStop($ssh, $srv, $action === 'stop');
		}
		if ($action === 'start' || $action === 'restart') {
			serverPowerStart($ssh, $srv, (string) ($srv['ip'] ?? ''));
		}
		dbExec("UPDATE `server` SET `online` = '" . ($action === 'stop' ? 'Stopped' : 'Started') . "' WHERE `serverid` = '" . $sid . "'");
		dbExec("INSERT INTO `log` SET `clientid` = '" . $clientId . "', `serverid` = '" . $sid . "', `boxid` = '" . (int) $srv['boxid'] . "', `message` = '" . dbEscape('Power ' . $action . ' via API: <a href="serversummary.php?id=' . $sid . '">#' . $sid . '</a>') . "', `name` = 'API', `ip` = '" . dbEscape($_SERVER['REMOTE_ADDR'] ?? '') . "'");
		api_out(['ok' => true, 'action' => $action]);
	}

	$data = server_public($srv);
	if (!empty($srv['ip']) && ($srv['query'] ?? 'none') !== 'none') {
		$q = querySingleServer([$srv['query'], $srv['ip'], !empty($srv['qryport']) ? $srv['qryport'] : $srv['port']]);
		if (is_array($q)) {
			$data['query'] = [
				'name'    => $q['Server Name'] ?? null,
				'map'     => $q['Current Map'] ?? null,
				'players' => $q['Players'] ?? null,
			];
		}
	}
	api_out(['server' => $data]);
}

api_out(['error' => 'Unknown endpoint', 'endpoints' => ['GET /servers', 'GET /servers/{id}', 'POST /servers/{id}/power']], 404);
