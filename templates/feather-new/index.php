<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $client (first_name,last_name,email,servers), $servers[], $FLASH_MSG1/2
$servers = $servers ?? [];

$running = 0;
foreach ($servers as $s) {
	if (($s['online'] ?? '') === 'Started') { $running++; }
}
?>
<?php if (!empty($FLASH_MSG1)): ?>
	<div class="fp-note fp-note-ok">
		<strong><?= htmlspecialchars($FLASH_MSG1) ?></strong>
		<span><?= htmlspecialchars($FLASH_MSG2 ?? '') ?></span>
	</div>
<?php endif; ?>

<section class="fp-hero">
	<div class="fp-hero-text">
		<h1>Welcome back<?= !empty($client['first_name']) ? ', ' . htmlspecialchars($client['first_name']) : '' ?></h1>
		<p>Monitor and control your game servers from one place.</p>
	</div>
	<a class="fp-btn fp-btn-ghost" href="server.php">Open server list</a>
</section>

<div class="fp-stats">
	<div class="fp-stat">
		<span class="fp-stat-label">Servers</span>
		<span class="fp-stat-value"><?= (int)($client['servers'] ?? count($servers)) ?></span>
	</div>
	<div class="fp-stat">
		<span class="fp-stat-label">Running</span>
		<span class="fp-stat-value"><?= (int)$running ?></span>
	</div>
	<div class="fp-stat">
		<span class="fp-stat-label">Account</span>
		<span class="fp-stat-value fp-stat-sm"><?= htmlspecialchars($client['email'] ?? '') ?></span>
	</div>
</div>

<div class="fp-section-head">
	<h2>My servers</h2>
	<span class="fp-count"><?= count($servers) ?></span>
</div>

<?php if (empty($servers)): ?>
	<div class="fp-empty">No servers on your account yet.</div>
<?php else: ?>
	<div class="fp-srv-list">
		<?php foreach ($servers as $srv): ?>
			<?php
			$online = $srv['online'] ?? '';
			$status = $srv['status'] ?? '';
			$dotCls = $online === 'Started' ? 'ok' : ($online === 'Pending' ? 'warn' : 'off');
			$sid    = (int)($srv['serverid'] ?? 0);
			$addr   = !empty($srv['ip']) ? htmlspecialchars($srv['ip']) . ':' . (int)($srv['port'] ?? 0) : '&mdash;';
			?>
			<article class="fp-srv">
				<span class="fp-dot fp-dot-<?= $dotCls ?>" title="<?= htmlspecialchars($online) ?>"></span>

				<div class="fp-srv-main">
					<a class="fp-srv-name" href="serversummary.php?id=<?= $sid ?>"><?= htmlspecialchars($srv['name'] ?? '') ?></a>
					<span class="fp-srv-meta"><?= htmlspecialchars($srv['game'] ?? '') ?></span>
				</div>

				<code class="fp-srv-addr"><?= $addr ?></code>

				<span class="fp-pill fp-pill-<?= $status === 'Active' ? 'ok' : ($status === 'Pending' ? 'warn' : 'bad') ?>"><?= htmlspecialchars($status) ?></span>

				<div class="fp-srv-act">
					<?php if ($online === 'Stopped' && $status !== 'Suspended'): ?>
						<a class="fp-ibtn fp-ibtn-go" href="servermanage.php?task=start&amp;return=index.php&amp;serverid=<?= $sid ?>" title="Start">&#9658;</a>
					<?php elseif ($online === 'Started' && $status !== 'Suspended'): ?>
						<a class="fp-ibtn" href="servermanage.php?task=restart&amp;return=index.php&amp;serverid=<?= $sid ?>" title="Restart">&#8635;</a>
						<a class="fp-ibtn fp-ibtn-stop" href="servermanage.php?task=stop&amp;return=index.php&amp;serverid=<?= $sid ?>" title="Stop">&#9632;</a>
					<?php endif; ?>
					<a class="fp-ibtn" href="serversummary.php?id=<?= $sid ?>" title="Manage">&#8250;</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
