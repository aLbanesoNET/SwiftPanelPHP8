<?php

require_once __DIR__ . '/screenctl.php';   // sshExec()

/* The persistent root shell the box console attaches to. */
const BOX_CONSOLE_SESSION = 'swpanel-console';

/* Box passwords are stored base64-encoded (admin/boxprocess.php). */
function boxDecodePassword(string $stored): string
{
	$d = base64_decode($stored, true);
	return $d === false ? $stored : $d;
}

/*
 * Connect + auth to a box over SSH. Returns the connection or null.
 * A short fsockopen() first bounds the "box is down" case to a few seconds
 * instead of ssh2_connect()'s long default timeout.
 */
function boxSshConnect(array $box)
{
	if (!extension_loaded('ssh2')) {
		return null;
	}

	$ip   = trim((string) ($box['ip'] ?? ''));
	$port = (int) ($box['sshport'] ?? 22) ?: 22;
	if ($ip === '') {
		return null;
	}

	$probe = @fsockopen($ip, $port, $errno, $errstr, 3);
	if (!$probe) {
		return null;
	}
	fclose($probe);

	$conn = @ssh2_connect($ip, $port);
	if (!$conn) {
		return null;
	}

	$login = (string) ($box['login'] ?? '') ?: 'root';
	if (!@ssh2_auth_password($conn, $login, boxDecodePassword((string) ($box['password'] ?? '')))) {
		return null;
	}

	return $conn;
}

/* One SSH round-trip -> parsed system stats. ['ok' => false] on failure. */
function getBoxStats(array $box): array
{
	$conn = boxSshConnect($box);
	if (!$conn) {
		return ['ok' => false];
	}

	return parseBoxStats(sshExec($conn, boxStatsScript(), 20)) + ['ok' => true];
}

function boxStatsScript(): string
{
	return <<<'SH'
{
. /etc/os-release 2>/dev/null || true
printf 'OS=%s\n'      "${PRETTY_NAME:-$(uname -s) $(uname -r)}"
printf 'KERNEL=%s\n'  "$(uname -r)"
printf 'ARCH=%s\n'    "$(uname -m)"
printf 'CPU=%s\n'     "$(sed -n 's/^model name[[:space:]]*: //p' /proc/cpuinfo | head -n1)"
printf 'MHZ=%s\n'     "$(sed -n 's/^cpu MHz[[:space:]]*: //p' /proc/cpuinfo | head -n1)"
printf 'CORESPER=%s\n' "$(sed -n 's/^cpu cores[[:space:]]*: //p' /proc/cpuinfo | head -n1)"
printf 'SOCKETS=%s\n' "$(awk -F: '/^physical id/{print $2}' /proc/cpuinfo | sort -u | grep -c .)"
printf 'THREADS=%s\n' "$(grep -c '^processor' /proc/cpuinfo)"
printf 'LOAD=%s\n'    "$(cut -d' ' -f1-3 /proc/loadavg)"
printf 'UPTIME=%s\n'  "$(cut -d. -f1 /proc/uptime)"
awk '/^MemTotal:/{t=$2}/^MemAvailable:/{a=$2}END{printf "MEMTOTAL=%d\nMEMAVAIL=%d\n",t*1024,a*1024}' /proc/meminfo
df -Pk / 2>/dev/null | awk 'NR==2{printf "DISKTOTAL=%d\nDISKUSED=%d\nDISKFREE=%d\n",$2*1024,$3*1024,$4*1024}'
read -r _ u n s i w q x y _ < /proc/stat; A=$((u+n+s+i+w+q+x+y)); B=$i
sleep 0.3
read -r _ u n s i w q x y _ < /proc/stat; C=$((u+n+s+i+w+q+x+y)); D=$i
awk -v d=$((D-B)) -v t=$((C-A)) 'BEGIN{printf "IDLE=%.1f\n",(t>0?d*100/t:0)}'
} 2>&1
SH;
}

function parseBoxStats(string $raw): array
{
	$out = [];
	foreach (explode("\n", $raw) as $line) {
		if (strpos($line, '=') === false) {
			continue;
		}
		[$k, $v] = explode('=', $line, 2);
		$out[strtolower(trim($k))] = trim($v);
	}

	$sockets  = max(1, (int) ($out['sockets'] ?? 1));
	$coresPer = (int) ($out['coresper'] ?? 0);
	$out['cores'] = $coresPer > 0 ? (string) ($coresPer * $sockets) : ($out['threads'] ?? '');

	return $out;
}

function formatBoxBytes($bytes): string
{
	$bytes = (float) $bytes;
	if ($bytes <= 0) {
		return '&mdash;';
	}
	$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
	$i = 0;
	while ($bytes >= 1024 && $i < count($units) - 1) {
		$bytes /= 1024;
		$i++;
	}
	return round($bytes, $i >= 3 ? 1 : 0) . ' ' . $units[$i];
}

function formatBoxUptime($seconds): string
{
	$s = (int) $seconds;
	if ($s <= 0) {
		return '&mdash;';
	}
	$d = intdiv($s, 86400);
	$h = intdiv($s % 86400, 3600);
	$m = intdiv($s % 3600, 60);
	$parts = [];
	if ($d) $parts[] = $d . 'd';
	if ($h) $parts[] = $h . 'h';
	$parts[] = $m . 'm';
	return implode(' ', $parts);
}

/*
 * Web console for a box: attaches to a persistent root screen session so cd /
 * env persist and there is scrollback, exactly like the server console.
 * Returns ['ok' => bool, 'output' => string, 'error' => string].
 */
function boxConsoleCommand(array $box, string $command = ''): array
{
	$fail = static fn (string $m): array => ['ok' => false, 'output' => '', 'error' => $m];

	if (!extension_loaded('ssh2')) {
		return $fail('The ssh2 PHP extension is not installed on the panel server.');
	}

	$conn = boxSshConnect($box);
	if (!$conn) {
		return $fail('Could not reach the box over SSH — check the box is up and the root login is correct.');
	}

	$sess    = escapeshellarg(BOX_CONSOLE_SESSION);
	$command = str_replace(["\r", "\n"], '', $command);

	// Make sure the shell session is running.
	sshExec(
		$conn,
		'screen -wipe >/dev/null 2>&1; '
		. 'S=${SHELL:-$(command -v bash || command -v sh)}; '
		. 'screen -ls 2>/dev/null | grep -qE "[0-9]+\\.' . BOX_CONSOLE_SESSION . '\\b" '
		. '|| screen -dmS ' . $sess . ' "$S"',
		10
	);

	if ($command !== '') {
		sshExec($conn, 'screen -S ' . $sess . ' -p 0 -X stuff ' . escapeshellarg($command . "\r") . ' 2>&1', 10);
		usleep(700000);
	}

	$buffer = sshExec(
		$conn,
		'{ F=$(mktemp) && screen -S ' . $sess . ' -X hardcopy -h "$F" && cat "$F"; rm -f "$F"; } 2>&1',
		15
	);

	if (stripos($buffer, 'No screen session found') !== false) {
		return $fail('Could not open a shell session on the box (is `screen` installed?).');
	}

	$buffer = preg_replace('/\x1b\[[0-9;?]*[ -\/]*[@-~]/', '', $buffer);
	$buffer = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $buffer);
	$buffer = rtrim($buffer, "\r\n \t");

	return ['ok' => true, 'output' => $buffer !== '' ? $buffer : '(no output)', 'error' => ''];
}
