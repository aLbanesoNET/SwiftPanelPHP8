<?php
declare(strict_types=1);

$title = 'Login';
$page  = 'login';

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$task   = isset($_GET['task'])   ? sanitizeInput($_GET['task'] ?? "")   : '';
$return = isset($_GET['return']) ? sanitizeInput($_GET['return'] ?? "") : '';

$email	= '';
$password = '';

if (isset($_GET['email'])) {
	$email = sanitizeInput($_GET['email'] ?? "");
} elseif (isset($_COOKIE['clientemail'])) {
	$email = sanitizeInput($_COOKIE['clientemail'] ?? "");
}

if (isset($_GET['password'])) {
	$password = sanitizeInput($_GET['password'] ?? "");
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

require tpl('header');
require tpl('login');
require tpl('footer');