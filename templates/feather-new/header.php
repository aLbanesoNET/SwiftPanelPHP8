<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$title      = $title      ?? '';
$page       = $page       ?? '';
$LOGGED_IN  = $LOGGED_IN  ?? false;
$SITE_NAME  = $SITE_NAME  ?? SITENAME;
$SITE_TITLE = $SITE_TITLE ?? SITENAME;

$navItem = static function (string $href, string $key, string $label, string $active) use ($page): string {
	$on = in_array($page, explode('|', $active), true) ? ' class="on"' : '';
	return '<li class="' . $key . '"><a href="' . $href . '"' . $on . '>' . $label . '</a></li>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($SITE_TITLE) ?></title>
<link rel="stylesheet" href="templates/feather-new/style.css?v=<?= @filemtime(__DIR__ . '/style.css') ?: '1' ?>">
</head>
<body class="<?= $LOGGED_IN ? 'app' : 'auth' ?>">

<?php if ($LOGGED_IN): ?>
<button type="button" id="navtoggle" aria-label="Toggle menu" onclick="document.body.classList.toggle('nav-open')">&#9776;</button>

<aside id="nav">
	<a href="index.php" id="home">
		<span class="home-mark">&#9656;</span>
		<span class="home-name"><?= htmlspecialchars($SITE_NAME) ?></span>
		<span class="home-badge">v8</span>
	</a>

	<nav id="left">
		<p class="nav-sec">Overview</p>
		<ul class="menutabs">
			<?= $navItem('index.php',  'home',    'Dashboard',  'index') ?>
			<?= $navItem('server.php', 'servers', 'My Servers', 'server|serverftp') ?>
			<?php if (!empty($CLIENTDB_ENABLED)): ?><?= $navItem('clientdatabases.php', 'database', 'Databases', 'database') ?><?php endif; ?>
		</ul>

		<p class="nav-sec">Account</p>
		<ul class="menutabs">
			<?= $navItem('profile.php', 'account', 'Account', 'profile') ?>
			<li class="logout"><a href="logout.php">Logout</a></li>
		</ul>
	</nav>

	<div id="time"><?= date('D d M') ?> &middot; <?= date('H:i') ?></div>
</aside>

<div id="page">
	<header id="topbar">
		<div id="topbar-title"><?= htmlspecialchars($title !== '' ? $title : $SITE_NAME) ?></div>
		<div id="topbar-actions"><?= $TOPBAR_ACTIONS ?? '' ?></div>
	</header>
	<main id="content">
		<div id="container">
<?php else: ?>
<div id="page">
	<main id="content">
		<div id="container">
<?php endif; ?>
