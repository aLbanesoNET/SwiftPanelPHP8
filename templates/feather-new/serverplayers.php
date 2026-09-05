<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $sid = (int) ($srv['serverid'] ?? 0); $players = $players ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-powerbar">
	<div class="fp-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="fp-pill fp-pill-mono">Players</span>
	</div>
	<div class="fp-powerbar-act">
		<a class="fp-btn" href="serverplayers.php?id=<?= $sid ?>">Refresh</a>
		<a class="fp-btn fp-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="fp-section-head"><h2>Online now</h2><span class="fp-count"><?= count($players) ?></span></div>

<?php if (!empty($playerError)): ?>
	<div class="fp-empty"><?= htmlspecialchars($playerError) ?></div>
<?php elseif (empty($players)): ?>
	<div class="fp-empty">No players connected.</div>
<?php else: ?>
	<div class="fp-table">
		<div class="fp-tr fp-th" style="grid-template-columns:1.6fr 1.4fr 0.6fr 0.6fr 90px;">
			<span>Name</span><span>ID</span><span>Time</span><span>Ping</span><span></span>
		</div>
		<?php foreach ($players as $p): ?>
			<div class="fp-tr" style="grid-template-columns:1.6fr 1.4fr 0.6fr 0.6fr 90px;">
				<span><strong><?= htmlspecialchars($p['name']) ?></strong></span>
				<span class="fp-srv-meta"><code><?= htmlspecialchars($p['uid']) ?></code></span>
				<span class="fp-srv-meta"><?= htmlspecialchars($p['time']) ?></span>
				<span class="fp-srv-meta"><?= htmlspecialchars($p['ping']) ?></span>
				<span class="fp-c-act">
					<a class="fp-ibtn fp-ibtn-stop" href="serverplayersprocess.php?task=kick&amp;serverid=<?= $sid ?>&amp;name=<?= urlencode($p['name']) ?>" title="Kick" onclick="return confirm('Kick <?= htmlspecialchars(addslashes($p['name']), ENT_QUOTES) ?>?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
