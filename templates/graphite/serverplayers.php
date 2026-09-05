<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $sid = (int) ($srv['serverid'] ?? 0); $players = $players ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="graphite-powerbar">
	<div class="graphite-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="graphite-pill graphite-pill-mono">Players</span>
	</div>
	<div class="graphite-powerbar-act">
		<a class="graphite-btn" href="serverplayers.php?id=<?= $sid ?>">Refresh</a>
		<a class="graphite-btn graphite-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="graphite-section-head"><h2>Online now</h2><span class="graphite-count"><?= count($players) ?></span></div>

<?php if (!empty($playerError)): ?>
	<div class="graphite-empty"><?= htmlspecialchars($playerError) ?></div>
<?php elseif (empty($players)): ?>
	<div class="graphite-empty">No players connected.</div>
<?php else: ?>
	<div class="graphite-table">
		<div class="graphite-tr graphite-th" style="grid-template-columns:1.6fr 1.4fr 0.6fr 0.6fr 90px;">
			<span>Name</span><span>ID</span><span>Time</span><span>Ping</span><span></span>
		</div>
		<?php foreach ($players as $p): ?>
			<div class="graphite-tr" style="grid-template-columns:1.6fr 1.4fr 0.6fr 0.6fr 90px;">
				<span><strong><?= htmlspecialchars($p['name']) ?></strong></span>
				<span class="graphite-srv-meta"><code><?= htmlspecialchars($p['uid']) ?></code></span>
				<span class="graphite-srv-meta"><?= htmlspecialchars($p['time']) ?></span>
				<span class="graphite-srv-meta"><?= htmlspecialchars($p['ping']) ?></span>
				<span class="graphite-c-act">
					<a class="graphite-ibtn graphite-ibtn-stop" href="serverplayersprocess.php?task=kick&amp;serverid=<?= $sid ?>&amp;name=<?= urlencode($p['name']) ?>" title="Kick" onclick="return confirm('Kick <?= htmlspecialchars(addslashes($p['name']), ENT_QUOTES) ?>?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
