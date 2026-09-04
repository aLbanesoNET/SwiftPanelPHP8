<?php
/**
 * FastDL — an HTTP mirror of a game server's `fastdl/` directory, served by the
 * panel over SFTP so no web server is needed on the game box.
 *
 * A server is FastDL-enabled by storing a random token in `server.fastdl`.
 * The client sets, in their server config:
 *
 *     sv_downloadurl "<panel-url>/fastdl/<token>/"
 *
 * and the game client then fetches e.g. `<panel-url>/fastdl/<token>/maps/x.bsp.bz2`,
 * which the panel streams from `<homedir>/fastdl/maps/x.bsp.bz2` on the box.
 * Read-only, extension-allow-listed, traversal-proof, no auth (the token is the
 * capability and only grants read access to that one directory).
 */

function fastdlToken(): string
{
	return bin2hex(random_bytes(10));
}

/** Content types for the file kinds a game client will ask for. */
function fastdlContentType(string $file): string
{
	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	return [
		'bz2' => 'application/x-bzip2',
		'gz'  => 'application/gzip',
		'zip' => 'application/zip',
		'bsp' => 'application/octet-stream',
		'wad' => 'application/octet-stream',
		'mdl' => 'application/octet-stream',
		'vtf' => 'application/octet-stream',
		'vmt' => 'text/plain',
		'vvd' => 'application/octet-stream',
		'phy' => 'application/octet-stream',
		'spr' => 'application/octet-stream',
		'wav' => 'audio/wav',
		'mp3' => 'audio/mpeg',
		'ogg' => 'audio/ogg',
		'txt' => 'text/plain',
		'cfg' => 'text/plain',
		'res' => 'text/plain',
		'nav' => 'application/octet-stream',
		'ain' => 'application/octet-stream',
		'tga' => 'image/x-tga',
		'png' => 'image/png',
		'jpg' => 'image/jpeg',
	][$ext] ?? 'application/octet-stream';
}

function fastdlExtAllowed(string $file): bool
{
	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	return in_array($ext, [
		'bz2', 'gz', 'zip', 'bsp', 'wad', 'mdl', 'vtf', 'vmt', 'vvd', 'phy',
		'spr', 'wav', 'mp3', 'ogg', 'txt', 'cfg', 'res', 'nav', 'ain', 'tga',
		'png', 'jpg',
	], true);
}

/**
 * Normalise a client-supplied relative path. Returns a clean path (no leading
 * slash, no "." / ".." segments, printable) or null if it is unsafe.
 */
function fastdlSafePath(string $rel): ?string
{
	$rel = str_replace('\\', '/', $rel);
	if ($rel === '' || strlen($rel) > 512) {
		return null;
	}
	if (preg_match('#[\x00-\x1f]#', $rel)) {
		return null;
	}

	$out = [];
	foreach (explode('/', $rel) as $seg) {
		if ($seg === '' || $seg === '.') {
			continue;
		}
		if ($seg === '..') {
			return null;
		}
		if (!preg_match('#^[A-Za-z0-9._@ +\-()\[\]]{1,128}$#', $seg)) {
			return null;
		}
		$out[] = $seg;
	}

	return $out ? implode('/', $out) : null;
}

/**
 * Stream `<homedir>/fastdl/<rel>` from the box to the browser.
 * Sends a 404 (and returns) if anything is missing or not permitted.
 */
function fastdlStream(array $srv, array $box, string $rel): void
{
	$clean = fastdlSafePath($rel);
	if ($clean === null || !fastdlExtAllowed($clean)) {
		http_response_code(404);
		exit('Not found');
	}

	if (!extension_loaded('ssh2')) {
		http_response_code(503);
		exit('FastDL unavailable');
	}

	$ip   = trim((string) ($box['ip'] ?? ''));
	$port = (int) ($box['sshport'] ?? 22) ?: 22;
	$conn = $ip !== '' ? @ssh2_connect($ip, $port) : false;
	if (!$conn || !@ssh2_auth_password($conn, (string) ($srv['user'] ?? ''), (string) ($srv['password'] ?? ''))) {
		http_response_code(502);
		exit('FastDL connection failed');
	}

	$sftp = @ssh2_sftp($conn);
	if (!$sftp) {
		http_response_code(502);
		exit('FastDL connection failed');
	}

	$home = rtrim((string) ($srv['homedir'] ?? ''), '/');
	$path = $home . '/fastdl/' . $clean;
	$wrapped = 'ssh2.sftp://' . (int) $sftp . $path;

	$stat = @ssh2_sftp_stat($sftp, $path);
	if ($stat === false || !isset($stat['size'])) {
		http_response_code(404);
		exit('Not found');
	}

	$fh = @fopen($wrapped, 'rb');
	if (!$fh) {
		http_response_code(404);
		exit('Not found');
	}

	header('Content-Type: ' . fastdlContentType($clean));
	header('Content-Length: ' . (int) $stat['size']);
	header('Cache-Control: public, max-age=86400');
	header('X-Content-Type-Options: nosniff');

	while (!feof($fh)) {
		echo fread($fh, 65536);
	}
	fclose($fh);
	exit;
}
