<?php
declare(strict_types=1);

session_name('PHPSESSION');
session_start();

// Drop every session value, then the session itself.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
	$p = session_get_cookie_params();
	setcookie(session_name(), '', [
		'expires'  => time() - 42000,
		'path'     => $p['path'],
		'domain'   => $p['domain'],
		'secure'   => $p['secure'],
		'httponly' => $p['httponly'],
		'samesite' => $p['samesite'] ?? 'Lax',
	]);
}

session_destroy();

// Clear the "remember me" cookies too.
setcookie('clientemail', '', time() - 3600, '/');
setcookie('rememberme', '', time() - 3600, '/');

header('Location: login.php');
exit;
