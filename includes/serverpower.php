<?php
/**
 * Shared game-server power actions over SSH + screen. Used by the scheduler
 * (admin/cron.php); servermanage.php keeps its own inline copy for now.
 */

require_once __DIR__ . '/screenctl.php';

/** SSH to the box as the server's own unix user. Returns the connection or null. */
function serverPowerConnect(array $srv, array $box)
{
	if (!extension_loaded('ssh2')) {
		return null;
	}

	$ip   = trim((string) ($box['ip'] ?? ''));
	$port = (int) ($box['sshport'] ?? 22) ?: 22;
	if ($ip === '') {
		return null;
	}

	$ssh = @ssh2_connect($ip, $port);
	if (!$ssh) {
		return null;
	}
	if (!@ssh2_auth_password($ssh, (string) ($srv['user'] ?? ''), (string) ($srv['password'] ?? ''))) {
		return null;
	}

	return $ssh;
}

/** Kill the server's screen session (and, when $sweep, anything it left running). */
function serverPowerStop($ssh, array $srv, bool $sweep = true): void
{
	$session = screenSessionName($srv['serverid'] ?? '', (string) ($srv['user'] ?? ''));
	sshExec($ssh, screenKillCommand($session, $sweep ? (string) ($srv['user'] ?? '') : ''));
}

/** Start the server in a fresh detached screen. Returns the command output. */
function serverPowerStart($ssh, array $srv, string $serverIp): string
{
	$session   = screenSessionName($srv['serverid'] ?? '', (string) ($srv['user'] ?? ''));
	$startLine = buildStartCommand($srv, $serverIp);
	return sshExec($ssh, screenStartCommand($session, $startLine));
}
