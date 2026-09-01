<?php

function sanitizeInput(string $data): string
{
	global $connection;

	$data = htmlentities($data, ENT_NOQUOTES, 'UTF-8');
	$data = str_replace(['#', '%'], ['&#35;', '&#37;'], $data);
	$data = trim($data);

	if ($connection instanceof mysqli) {
		$data = mysqli_real_escape_string($connection, $data);
	} else {
		$data = addslashes($data);
	}

	return $data;
}

/*
 * Password handling.
 *
 * New passwords are stored with password_hash() (bcrypt). verifyPassword()
 * also accepts the two legacy formats this panel used to store — unsalted
 * SHA1 (admins) and plain text (clients) — so existing accounts keep working;
 * callers upgrade a matched legacy hash to bcrypt on the next successful login.
 *
 * Pass the value AFTER sanitizeInput(), the same way the legacy code did, so
 * hashing and verifying always see the identical string.
 */
function hashPassword(string $plain): string
{
	return password_hash($plain, PASSWORD_DEFAULT);
}

function verifyPassword(string $plain, string $stored): bool
{
	if ($stored === '') {
		return false;
	}

	if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2')) {
		return password_verify($plain, $stored);
	}

	// Legacy: unsalted SHA1 (40 hex chars) — admin accounts.
	if (preg_match('/^[0-9a-f]{40}$/i', $stored) && hash_equals(strtolower($stored), sha1($plain))) {
		return true;
	}

	// Legacy: plain text — client accounts.
	return hash_equals($stored, $plain);
}

function passwordNeedsRehash(string $stored): bool
{
	if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
		return password_needs_rehash($stored, PASSWORD_DEFAULT);
	}

	return true; // legacy SHA1 / plain text
}

function formatDate(string $date, int $style = 0): string
{
	if (
		trim($date) === '' ||
		$date === '0000-00-00' ||
		$date === '0000-00-00 00:00:00' ||
		$date === 'Never'
	) {
		return 'Never';
	}

	if ($style === 0) {
		$timestamp = strtotime($date);
		return strlen($date) === 10
			? date('M j, Y', $timestamp)
			: date('M j, Y | g:i A', $timestamp);
	}

	if ($style === 1) {
		$timestamp = strtotime($date);
		return date('n/j/Y', $timestamp);
	}

	return 'Never';
}

function generateRandomString(int $len): string
{
	$chars = '0123456789bcdfghjkmnpqrstvwxyz';
	$out = '';

	while (strlen($out) < $len) {
		$c = $chars[random_int(0, strlen($chars) - 1)];
		$out .= $c;

		if (is_numeric($out)) {
			$out = '';
		}
	}

	return $out;
}

function buildStartCommand(array $row, string $ip, bool $escape = false): string
{
	$cmd = html_entity_decode($row['startline'], ENT_NOQUOTES, 'UTF-8');
	$cmd = str_replace(['&#35;', '&#37;'], ['#', '%'], $cmd);

	$niceMap = [
		'Very High'   => -18,
		'High'		=> -12,
		'Above Normal'=> -6,
		'Normal'	  => 0,
		'Below Normal'=> 6,
		'Low'		 => 12,
		'Very Low'	=> 18,
	];

	$nice = '';
	if ($row['priority'] !== 'None' && isset($niceMap[$row['priority']])) {
		$nice = 'nice -n ' . $niceMap[$row['priority']];
	}

	if (!str_contains($cmd, '{nice}')) {
		$cmd = '{nice} ' . $cmd;
	}

	$search = [
		'{ip}', '{port}', '{slots}',
		'{cfg1}', '{cfg2}', '{cfg3}', '{cfg4}',
		'{cfg5}', '{cfg6}', '{cfg7}', '{cfg8}',
		'{user}', '{homedir}', '{nice}'
	];

	$replace = [
		$ip,
		$row['port'],
		$row['slots'],
		$row['cfg1'], $row['cfg2'], $row['cfg3'], $row['cfg4'],
		$row['cfg5'], $row['cfg6'], $row['cfg7'], $row['cfg8'],
		$row['user'],
		$row['homedir'],
		$nice
	];

	$cmd = str_replace($search, $replace, $cmd);

	if ($escape) {
		$cmd = htmlentities($cmd, ENT_NOQUOTES, 'UTF-8');
		$cmd = str_replace(['#', '%'], ['&#35;', '&#37;'], $cmd);
	}

	return trim($cmd);
}

function formatStatusText(string $status): string
{
	return match ($status) {
		'Active', 'Online', 'Started'
			=> "<font color=\"#669933\"><b>{$status}</b></font>",
		'Inactive', 'Pending'
			=> "<font color=\"#FFAA00\"><b>{$status}</b></font>",
		'Suspended', 'Offline', 'Stopped'
			=> "<font color=\"#DD0000\"><b>{$status}</b></font>",
		default => $status,
	};
}

function formatStatusIcon(string $status): string
{
	$color = match ($status) {
		'Active', 'Online', 'Started' => 'green',
		'Inactive', 'Pending'		 => 'yellow',
		'Suspended', 'Offline', 'Stopped' => 'red',
		default => null,
	};

	if (!$color) {
		return '';
	}

	return "<img src=\"templates/default/images/status/{$color}.png\" width=\"25\" height=\"25\" align=\"middle\" alt=\"{$status}\" />";
}

function dbCount(string $query): int
{
	global $connection;

	$result = mysqli_query($connection, $query);
	$count = ($result instanceof mysqli_result) ? mysqli_num_rows($result) : 0;

	if ($result instanceof mysqli_result) {
		mysqli_free_result($result);
	}

	return $count;
}

function dbRow(string $query, bool $allowEmpty = false): array
{
	global $connection;

	$result = mysqli_query($connection, $query);

	if (!$allowEmpty && (!($result instanceof mysqli_result) || mysqli_num_rows($result) === 0)) {
		if ($result instanceof mysqli_result) {
			mysqli_free_result($result);
		}
		echo '<p><b>No Results Found.</b></p>';
		exit;
	}

	$row = ($result instanceof mysqli_result) ? (mysqli_fetch_assoc($result) ?: []) : [];

	if ($result instanceof mysqli_result) {
		mysqli_free_result($result);
	}

	return $row;
}

function dbQuery(string $query)
{
	global $connection;

	return mysqli_query($connection, $query);
}

function dbExec(string $query): void
{
	global $connection;

	mysqli_query($connection, $query);
}

function dbFetch($result): ?array
{
	if (!($result instanceof mysqli_result)) {
		return null;
	}

	$row = mysqli_fetch_assoc($result);

	return is_array($row) ? $row : null;
}

function dbNumRows($result): int
{
	return ($result instanceof mysqli_result) ? mysqli_num_rows($result) : 0;
}

function dbFreeResult($result): void
{
	if ($result instanceof mysqli_result) {
		mysqli_free_result($result);
	}
}

function dbInsertId(): int|string
{
	global $connection;

	return mysqli_insert_id($connection);
}

function isInternetExplorer(int $version = 0): bool
{
	$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
	$pos = strpos($ua, 'MSIE ');

	if ($pos === false) {
		return false;
	}

	if ($version > 0) {
		$v = (int)substr($ua, $pos + 5, 1);
		return $v === $version;
	}

	return true;
}