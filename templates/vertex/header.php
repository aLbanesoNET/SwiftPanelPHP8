<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$title = $title ?? '';
$page = $page ?? '';
$LOGGED_IN = $LOGGED_IN ?? false;
$SITE_NAME = $SITE_NAME ?? SITENAME;
$SITE_TITLE = $SITE_TITLE ?? SITENAME;
$active = static function (string $keys) use ($page): string {
	return in_array($page, explode('|', $keys), true) ? ' is-active' : '';
};
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= htmlspecialchars($SITE_TITLE) ?></title>
	<link rel="stylesheet" href="templates/vertex/style.css?v=<?= @filemtime(__DIR__ . '/style.css') ?: '1' ?>">
</head>
<body class="vertex <?= $LOGGED_IN ? 'vertex-app' : 'vertex-auth' ?>">
<?php if ($LOGGED_IN): ?>
<button class="vx-menu-toggle" type="button" aria-label="Toggle navigation" onclick="document.body.classList.toggle('vx-nav-open')">☰</button>
<aside class="vx-sidebar">
	<a class="vx-brand" href="index.php"><span class="vx-brand-mark">✦</span><span><b><?= htmlspecialchars($SITE_NAME) ?></b><small>CONTROL CENTER</small></span></a>
	<div class="vx-profile"><span class="vx-avatar"><?= htmlspecialchars(strtoupper(substr((string)($_SESSION['clientfirstname'] ?? 'U'), 0, 1))) ?></span><span><b><?= htmlspecialchars(trim((string)($_SESSION['clientfirstname'] ?? '') . ' ' . (string)($_SESSION['clientlastname'] ?? '')) ?: 'Account') ?></b><small>Client workspace</small></span></div>
	<nav class="vx-nav">
		<span class="vx-nav-label">Workspace</span>
		<a class="vx-nav-item<?= $active('index') ?>" href="index.php"><i>⌂</i><span>Overview</span></a>
		<a class="vx-nav-item<?= $active('server|serverftp') ?>" href="server.php"><i>◈</i><span>Game servers</span></a>
		<?php if (!empty($CLIENTDB_ENABLED)): ?><a class="vx-nav-item<?= $active('database') ?>" href="clientdatabases.php"><i>▦</i><span>Databases</span></a><?php endif; ?>
		<span class="vx-nav-label">Account</span>
		<a class="vx-nav-item<?= $active('support') ?>" href="tickets.php"><i>?</i><span>Support</span></a>
		<a class="vx-nav-item<?= $active('apikeys') ?>" href="apikeys.php"><i>⌁</i><span>Developer API</span></a>
		<a class="vx-nav-item<?= $active('profile') ?>" href="profile.php"><i>◎</i><span>Profile & security</span></a>
	</nav>
	<div class="vx-side-bottom"><a href="notifications.php" class="vx-notify">Notifications <?php if (!empty($NOTIF_UNSEEN)): ?><em><?= (int)$NOTIF_UNSEEN > 9 ? '9+' : (int)$NOTIF_UNSEEN ?></em><?php endif; ?></a><a href="logout.php" class="vx-logout">Sign out <span>→</span></a></div>
</aside>
<div class="vx-main">
	<header class="vx-topbar"><div><span class="vx-kicker">CLIENT CONSOLE / <?= htmlspecialchars(strtoupper($page ?: 'HOME')) ?></span><h1><?= htmlspecialchars($title ?: $SITE_NAME) ?></h1></div><div class="vx-top-actions"><span class="vx-clock"><?= date('D, M j · H:i') ?></span><?= $TOPBAR_ACTIONS ?? '' ?><a class="vx-alert" href="notifications.php">◌<?php if (!empty($NOTIF_UNSEEN)): ?><b><?= (int)$NOTIF_UNSEEN ?></b><?php endif; ?></a></div></header>
	<main class="vx-content"><div class="vx-container">
<?php else: ?>
<div class="vx-auth-wrap"><main class="vx-auth-card">
<?php endif; ?>
