<?php
/*
 * Shared request-hardening helpers. No dependencies on the rest of the app
 * (safe to require before or after configuration.php) so it can be pulled
 * into both the client and admin bootstraps and every *process.php entry
 * point without reordering existing includes.
 */

/** Call before session_start() so the session cookie itself carries the flags. */
function hardenSessionCookieParams(): void
{
	$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

	session_set_cookie_params([
		'lifetime' => 0,
		'path'     => '/',
		'domain'   => '',
		'secure'   => $https,
		'httponly' => true,
		'samesite' => 'Strict',
	]);
}

/**
 * True if this request's Origin/Referer (when the browser sent one) points
 * back at this same host. Browsers attach Origin to every state-changing
 * fetch/form POST and same-site navigations already carry it or a matching
 * Referer; a forged cross-site request either omits both or points elsewhere.
 * No token, no template changes, no cookie required — a pure server-side check.
 */
function isSameOriginRequest(): bool
{
	$host = $_SERVER['HTTP_HOST'] ?? '';
	if ($host === '') {
		return true; // can't evaluate — don't block on an unusual server config
	}

	$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
	if ($origin !== '') {
		$originHost = parse_url($origin, PHP_URL_HOST);
		return $originHost !== null && strcasecmp($originHost, explode(':', $host)[0]) === 0;
	}

	$referer = $_SERVER['HTTP_REFERER'] ?? '';
	if ($referer !== '') {
		$refHost = parse_url($referer, PHP_URL_HOST);
		return $refHost !== null && strcasecmp($refHost, explode(':', $host)[0]) === 0;
	}

	// Neither header present. Real browsers always send at least a Referer on
	// same-site form posts/link clicks; a bare cross-site CSRF PoC (<img>,
	// fetch with no-referrer, etc.) commonly strips both. Treat as a mismatch
	// for state-changing requests rather than fail open.
	return false;
}

/**
 * Guard for the top of every *process.php state-changing dispatcher. Only
 * enforced for POST — legacy GET-fallback task links still work for normal
 * same-site navigation (Referer present) and this stays a no-op for them
 * when it IS same-origin, so it only ever blocks the forged case.
 */
function requireSameOrigin(string $redirectTo): void
{
	if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && empty($_GET['task']) && empty($_POST['task'])) {
		return;
	}
	if (isSameOriginRequest()) {
		return;
	}
	header('Location: ' . $redirectTo);
	exit;
}

/**
 * Resolve a user-supplied "sort by" column/direction pair against a fixed
 * allow-list before it's concatenated into an identifier context (backticks
 * don't stop injection the way value quoting does — sanitizeInput() escapes
 * quotes, not backticks). Returns ['col' => <safe column>, 'dir' => 'ASC'|'DESC'].
 */
function sqlSortColumn(string $requested, array $allowedColumns, string $default): string
{
	return in_array($requested, $allowedColumns, true) ? $requested : $default;
}

function sqlSortDir(string $requested): string
{
	return strcasecmp($requested, 'desc') === 0 ? 'DESC' : 'ASC';
}

/**
 * Strip path separators, control bytes and NUL from a client-supplied upload
 * filename before it's written to the remote FTP path or a filesystem path.
 * Output encoding (htmlspecialchars at render time) still applies separately —
 * this only prevents directory traversal / control-character smuggling in the
 * stored name itself.
 */
function sanitizeUploadFilename(string $name): string
{
	$name = basename(str_replace('\\', '/', $name));
	$name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';
	$name = trim($name);
	return $name === '' ? 'upload.bin' : substr($name, 0, 255);
}

/** Reject a post-login redirect target unless it's a local relative page. */
function safeReturnPath(?string $return): string
{
	$return = (string) $return;
	if ($return === '') {
		return '';
	}
	// Block absolute/protocol-relative/scheme URLs — only "name.php[...]" local paths.
	if (preg_match('#^[A-Za-z0-9_./?=&%-]+\.php(?:[?][A-Za-z0-9_./?=&%-]*)?$#', $return) !== 1) {
		return '';
	}
	if (str_starts_with($return, '//') || str_contains($return, '://')) {
		return '';
	}
	return $return;
}
