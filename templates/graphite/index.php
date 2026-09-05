<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-index" data-view="index">
<?php
// expects: $client (first_name,last_name,email,servers), $servers[], $FLASH_MSG1/2
$servers = $servers ?? [];

$running = 0;
foreach ($servers as $s) {
	if (($s['online'] ?? '') === 'Started') { $running++; }
}
?>
<?php if (!empty($FLASH_MSG1)): ?>
		<div class="graphite-notice graphite-notice-ok">
		<strong><?= htmlspecialchars($FLASH_MSG1) ?></strong>
		<span><?= htmlspecialchars($FLASH_MSG2 ?? '') ?></span>
	</div>
<?php endif; ?>

<section class="graphite-welcome">
	<div class="graphite-welcome-copy">
		<h1>Welcome back<?= !empty($client['first_name']) ? ', ' . htmlspecialchars($client['first_name']) : '' ?></h1>
		<p>Monitor and control your game servers from one place.</p>
	</div>
	<a class="graphite-action graphite-action-quiet" href="server.php">Open server list</a>
</section>

<?php if (!empty($ANNOUNCEMENTS)): ?>
	<div class="graphite-section-bar"><h2>Announcements</h2><span class="graphite-count"><?= count($ANNOUNCEMENTS) ?></span></div>
	<div class="graphite-announcements">
		<?php foreach ($ANNOUNCEMENTS as $a): ?>
			<article class="graphite-announcement">
				<div class="graphite-announcement-head">
					<h2><?= htmlspecialchars($a['title']) ?></h2>
					<span class="graphite-card-link"><?= htmlspecialchars(date('M j', strtotime((string) $a['created']))) ?></span>
				</div>
				<?php foreach (preg_split('/\n\s*\n/', trim((string) $a['body'])) as $para): ?>
					<p><?= nl2br(htmlspecialchars($para)) ?></p>
				<?php endforeach; ?>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<div class="graphite-overview">
	<div class="graphite-overview-item">
		<span class="graphite-overview-label">Servers</span>
		<span class="graphite-overview-value"><?= (int)($client['servers'] ?? count($servers)) ?></span>
	</div>
	<div class="graphite-overview-item">
		<span class="graphite-overview-label">Running</span>
		<span class="graphite-overview-value"><?= (int)$running ?></span>
	</div>
	<div class="graphite-overview-item graphite-overview-account">
		<span class="graphite-overview-label">Account</span>
		<span class="graphite-overview-value graphite-overview-small"><?= htmlspecialchars($client['email'] ?? '') ?></span>
	</div>
</div>


<div class="graphite-section-bar">
	<h2>My servers</h2>
	<span class="graphite-count"><?= count($servers) ?></span>
</div>

<?php if (empty($servers)): ?>
	<div class="graphite-empty">No servers on your account yet.</div>
<?php else: ?>
		<div class="graphite-server-list">
		<?php foreach ($servers as $srv): ?>
			<?php
			$online = $srv['online'] ?? '';
			$status = $srv['status'] ?? '';
			$dotCls = $online === 'Started' ? 'ok' : ($online === 'Pending' ? 'warn' : 'off');
			$sid    = (int)($srv['serverid'] ?? 0);
			$addr   = !empty($srv['ip']) ? htmlspecialchars($srv['ip']) . ':' . (int)($srv['port'] ?? 0) : '&mdash;';
			?>
			<article class="graphite-server-row">
				<span class="graphite-state graphite-state-<?= $dotCls ?>" title="<?= htmlspecialchars($online) ?>"></span>

				<div class="graphite-server-identity">
					<a class="graphite-server-name" href="serversummary.php?id=<?= $sid ?>"><?= htmlspecialchars($srv['name'] ?? '') ?></a>
					<span class="graphite-server-meta"><?= htmlspecialchars($srv['game'] ?? '') ?></span>
				</div>

				<code class="graphite-server-address"><?= $addr ?></code>

				<span class="graphite-badge graphite-badge-<?= $status === 'Active' ? 'ok' : ($status === 'Pending' ? 'warn' : 'bad') ?>"><?= htmlspecialchars($status) ?></span>

				<div class="graphite-server-actions">
					<?php if ($online === 'Stopped' && $status !== 'Suspended'): ?>
						<a class="graphite-ibtn graphite-ibtn-go" href="servermanage.php?task=start&amp;return=index.php&amp;serverid=<?= $sid ?>" title="Start">&#9658;</a>
					<?php elseif ($online === 'Started' && $status !== 'Suspended'): ?>
						<a class="graphite-ibtn" href="servermanage.php?task=restart&amp;return=index.php&amp;serverid=<?= $sid ?>" title="Restart">&#8635;</a>
						<a class="graphite-ibtn graphite-ibtn-stop" href="servermanage.php?task=stop&amp;return=index.php&amp;serverid=<?= $sid ?>" title="Stop">&#9632;</a>
					<?php endif; ?>
					<a class="graphite-ibtn" href="serversummary.php?id=<?= $sid ?>" title="Manage">&#8250;</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

</section>
