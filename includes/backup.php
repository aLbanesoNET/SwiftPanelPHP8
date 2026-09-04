<?php
/**
 * Server backups — tar.gz snapshots of a server's home directory, stored on the
 * box under <homedir>/backups/ and streamed to the client on demand over SFTP.
 */

require_once __DIR__ . '/screenctl.php';
require_once __DIR__ . '/serverpower.php';

function backupMaxPerServer(): int { return 3; }

/** Safe, unique-ish backup filename from a user label. */
function backupFilename(string $label): string
{
	$label = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($label)));
	$label = trim($label, '-');
	if ($label === '') {
		$label = 'backup';
	}
	return substr($label, 0, 32) . '-' . date('Ymd-His') . '.tar.gz';
}

/**
 * Create a backup synchronously. Returns ['ok'=>bool, 'filename'=>?, 'size'=>int, 'error'=>?].
 * Excludes the backups/ directory itself.
 */
function backupCreate(array $srv, array $box, string $label): array
{
	$ssh = serverPowerConnect($srv, $box);
	if (!$ssh) {
		return ['ok' => false, 'error' => 'Could not connect to the server.'];
	}

	$home = rtrim((string) ($srv['homedir'] ?? ''), '/');
	if ($home === '' || $home === '/') {
		return ['ok' => false, 'error' => 'Server home directory is not set.'];
	}

	$file  = backupFilename($label);
	$hArg  = escapeshellarg($home);
	$fArg  = escapeshellarg($home . '/backups/' . $file);

	$cmd = 'mkdir -p ' . escapeshellarg($home . '/backups') . ' && '
		. 'nice -n 19 tar czf ' . $fArg . ' --warning=no-file-changed '
		. '--exclude=' . escapeshellarg('./backups') . ' -C ' . $hArg . ' . '
		. '; echo "EXIT:$?"; stat -c "SIZE:%s" ' . $fArg . ' 2>/dev/null';

	$out = sshExec($ssh, $cmd, 300);

	if (!preg_match('/EXIT:([0-9]+)/', $out, $m) || ((int) $m[1] !== 0 && (int) $m[1] !== 1)) {
		// tar exit 1 = "some files changed while reading" — acceptable for a live dir
		@sshExec($ssh, 'rm -f ' . $fArg, 15);
		return ['ok' => false, 'error' => 'Backup command failed.'];
	}

	$size = preg_match('/SIZE:([0-9]+)/', $out, $s) ? (int) $s[1] : 0;
	if ($size <= 0) {
		return ['ok' => false, 'error' => 'Backup archive was not created.'];
	}

	return ['ok' => true, 'filename' => $file, 'size' => $size];
}

/** Restore a backup over the current server files (server should be stopped first). */
function backupRestore(array $srv, array $box, string $filename): array
{
	if (!preg_match('/^[A-Za-z0-9._-]+\.tar\.gz$/', $filename)) {
		return ['ok' => false, 'error' => 'Bad backup name.'];
	}

	$ssh = serverPowerConnect($srv, $box);
	if (!$ssh) {
		return ['ok' => false, 'error' => 'Could not connect to the server.'];
	}

	$home = rtrim((string) ($srv['homedir'] ?? ''), '/');
	$path = $home . '/backups/' . $filename;
	$out  = sshExec(
		$ssh,
		'test -f ' . escapeshellarg($path) . ' && '
		. 'nice -n 19 tar xzf ' . escapeshellarg($path) . ' -C ' . escapeshellarg($home) . ' '
		. '; echo "EXIT:$?"',
		300
	);

	if (!preg_match('/EXIT:0/', $out)) {
		return ['ok' => false, 'error' => 'Restore failed — the backup may be missing.'];
	}
	return ['ok' => true];
}

function backupDelete(array $srv, array $box, string $filename): void
{
	if (!preg_match('/^[A-Za-z0-9._-]+\.tar\.gz$/', $filename)) {
		return;
	}
	$ssh = serverPowerConnect($srv, $box);
	if ($ssh) {
		$home = rtrim((string) ($srv['homedir'] ?? ''), '/');
		@sshExec($ssh, 'rm -f ' . escapeshellarg($home . '/backups/' . $filename), 15);
	}
}

/** Stream a backup archive to the browser over SFTP. Exits. */
function backupStream(array $srv, array $box, string $filename): void
{
	if (!preg_match('/^[A-Za-z0-9._-]+\.tar\.gz$/', $filename)) {
		http_response_code(404);
		exit('Not found');
	}

	$ssh = serverPowerConnect($srv, $box);
	if (!$ssh) {
		http_response_code(502);
		exit('Connection failed');
	}

	$sftp = @ssh2_sftp($ssh);
	if (!$sftp) {
		http_response_code(502);
		exit('Connection failed');
	}

	$home = rtrim((string) ($srv['homedir'] ?? ''), '/');
	$path = $home . '/backups/' . $filename;
	$stat = @ssh2_sftp_stat($sftp, $path);
	if ($stat === false || !isset($stat['size'])) {
		http_response_code(404);
		exit('Not found');
	}

	$fh = @fopen('ssh2.sftp://' . (int) $sftp . $path, 'rb');
	if (!$fh) {
		http_response_code(404);
		exit('Not found');
	}

	header('Content-Type: application/gzip');
	header('Content-Length: ' . (int) $stat['size']);
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	while (!feof($fh)) {
		echo fread($fh, 131072);
	}
	fclose($fh);
	exit;
}
