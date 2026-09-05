<?php
declare(strict_types=1);

$title = 'Login';
$page  = 'login';

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$task   = isset($_GET['task'])   ? sanitizeInput($_GET['task'] ?? "")   : '';
$return = safeReturnPath($_GET['return'] ?? '');

$email = '';

if (isset($_GET['email'])) {
	$email = sanitizeInput($_GET['email'] ?? "");
} elseif (isset($_COOKIE['clientemail'])) {
	$email = sanitizeInput($_COOKIE['clientemail'] ?? "");
}

$lockout = false;
if (!empty($_SESSION['lockout']) && time() - (60 * 5) < $_SESSION['lockout']) {
	$lockout = true;
}

$login_error = !empty($_SESSION['loginerror']);
unset($_SESSION['loginerror']);

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

$remember_me = (($_COOKIE['rememberme'] ?? '') === 'on');

// Second login step: authenticator code.
$twofa = ($task === '2fa' && !empty($_SESSION['2fa_client']));
if ($task === '2fa' && empty($_SESSION['2fa_client'])) {
	header('Location: login.php');
	exit;
}

require tpl('header');
require tpl('login');
require tpl('footer');