<?php

require_once __DIR__ . '/screenctl.php';

/*
 * Web console for a running game server.
 *
 * Re-uses the panel's SSH model: it logs into the box as the server's own user
 * and drives the GNU screen session the panel started the server in (named
 * "<serverid>-<user>"). A command is typed into the session with
 * `screen -X stuff`; output is read back with `screen -X hardcopy -h`
 * (scrollback + visible buffer).
 *
 *   $server : needs serverid, user, password, online
 *   $box    : needs ip, sshport
 *   $command: line to send (empty = just refresh the buffer)
 *
 * Returns ['ok' => bool, 'output' => string, 'error' => string].
 */
function serverConsoleCommand(array $server, array $box, string $command = ''): array
{
	$fail = static fn (string $m): array => ['ok' => false, 'output' => '', 'error' => $m];

	if (!extension_loaded('ssh2')) {
		return $fail('The ssh2 PHP extension is not installed on the panel server.');
	}

	$ip      = trim((string) ($box['ip'] ?? ''));
	$sshPort = (int) ($box['sshport'] ?? 22) ?: 22;
	$user    = (string) ($server['user'] ?? '');
	$pass    = (string) ($server['password'] ?? '');
	$session = screenSessionName($server['serverid'] ?? '', $user);

	if ($ip === '' || $user === '') {
		return $fail('This server is not installed on a box yet.');
	}
	if (($server['online'] ?? '') !== 'Started') {
		return $fail('The server is stopped — start it to open the console.');
	}

	$conn = @ssh2_connect($ip, $sshPort);
	if (!$conn) {
		return $fail('Could not reach the box over SSH.');
	}
	if (!@ssh2_auth_password($conn, $user, $pass)) {
		return $fail('SSH login to the box failed.');
	}

	// Resolve to the exact "PID.name" so a stale duplicate can never confuse it.
	$full = screenResolve($conn, $session);
	if ($full === '') {
		return $fail('No running screen session for this server on the box.');
	}
	$sess = escapeshellarg($full);

	// Only allow a single line; the trailing CR is what "presses Enter".
	$command = str_replace(["\r", "\n"], '', $command);

	if ($command !== '') {
		sshExec($conn, 'screen -S ' . $sess . ' -p 0 -X stuff ' . escapeshellarg($command . "\r") . ' 2>&1', 10);
		usleep(700000); // give the server a beat to respond before the snapshot
	}

	$buffer = sshExec(
		$conn,
		'{ F=$(mktemp) && screen -S ' . $sess . ' -X hardcopy -h "$F" && cat "$F"; rm -f "$F"; } 2>&1',
		15
	);

	if (stripos($buffer, 'No screen session found') !== false) {
		return $fail('No running screen session for this server on the box.');
	}

	// Strip ANSI escapes and stray control bytes, keep newlines/tabs.
	$buffer = preg_replace('/\x1b\[[0-9;?]*[ -\/]*[@-~]/', '', $buffer);
	$buffer = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $buffer);
	$buffer = rtrim($buffer, "\r\n \t");

	return ['ok' => true, 'output' => $buffer !== '' ? $buffer : '(no output)', 'error' => ''];
}
