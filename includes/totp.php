<?php
/**
 * RFC 6238 TOTP (SHA1, 6 digits, 30-second step) with RFC 4648 base32.
 * No dependencies — enough for authenticator-app 2FA on client logins.
 */

function totpBase32Encode(string $bytes): string
{
	$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$out = '';
	$buffer = 0;
	$bits = 0;

	for ($i = 0, $n = strlen($bytes); $i < $n; $i++) {
		$buffer = ($buffer << 8) | ord($bytes[$i]);
		$bits += 8;
		while ($bits >= 5) {
			$bits -= 5;
			$out .= $alphabet[($buffer >> $bits) & 31];
		}
	}
	if ($bits > 0) {
		$out .= $alphabet[($buffer << (5 - $bits)) & 31];
	}

	return $out;
}

function totpBase32Decode(string $b32): string
{
	$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
	$buffer = 0;
	$bits = 0;
	$out = '';

	for ($i = 0, $n = strlen($b32); $i < $n; $i++) {
		$buffer = ($buffer << 5) | strpos($alphabet, $b32[$i]);
		$bits += 5;
		if ($bits >= 8) {
			$bits -= 8;
			$out .= chr(($buffer >> $bits) & 0xFF);
		}
	}

	return $out;
}

/** A fresh 20-byte (160-bit) secret, base32-encoded. */
function totpSecret(): string
{
	return totpBase32Encode(random_bytes(20));
}

/** The 6-digit code for a base32 secret at the given time step. */
function totpCode(string $secret, ?int $timestamp = null, int $step = 30, int $digits = 6): string
{
	$key = totpBase32Decode($secret);
	$counter = intdiv(($timestamp ?? time()), $step);

	$binCounter = pack('N*', 0, $counter);
	$hash = hash_hmac('sha1', $binCounter, $key, true);

	$offset = ord($hash[strlen($hash) - 1]) & 0x0F;
	$part = (
		((ord($hash[$offset]) & 0x7F) << 24) |
		((ord($hash[$offset + 1]) & 0xFF) << 16) |
		((ord($hash[$offset + 2]) & 0xFF) << 8) |
		(ord($hash[$offset + 3]) & 0xFF)
	);

	return str_pad((string) ($part % (10 ** $digits)), $digits, '0', STR_PAD_LEFT);
}

/** True if $code matches within +/- $window steps (clock drift tolerance). */
function totpVerify(string $secret, string $code, int $window = 1, int $step = 30): bool
{
	$code = preg_replace('/\D/', '', $code);
	if (strlen($code) !== 6) {
		return false;
	}

	$now = time();
	for ($i = -$window; $i <= $window; $i++) {
		if (hash_equals(totpCode($secret, $now + ($i * $step), $step), $code)) {
			return true;
		}
	}
	return false;
}

/** otpauth:// URI for QR / manual entry. */
function totpUri(string $secret, string $account, string $issuer): string
{
	return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
		. '?secret=' . $secret
		. '&issuer=' . rawurlencode($issuer)
		. '&algorithm=SHA1&digits=6&period=30';
}
