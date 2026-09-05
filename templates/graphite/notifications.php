<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $notifs = $notifs ?? []; ?>
<section class="graphite-hero">
	<div class="graphite-hero-text">
		<h1>Notifications</h1>
		<p>Alerts about your servers, tickets and account.</p>
	</div>
	<?php if (!empty($NOTIF_UNSEEN)): ?>
		<a class="graphite-btn graphite-btn-ghost" href="notifications.php?read=1">Mark all read</a>
	<?php endif; ?>
</section>

<?php if (empty($notifs)): ?>
	<div class="graphite-empty">Nothing here yet.</div>
<?php else: ?>
	<div class="graphite-srv-list">
		<?php foreach ($notifs as $n): ?>
			<?php
			$tone = ['down' => 'bad', 'ticket' => 'ok', 'backup' => 'ok', 'schedule' => 'mono', 'system' => 'mono'][$n['kind']] ?? 'mono';
			$tag = 'graphite-pill-' . ($tone === 'bad' ? 'bad' : ($tone === 'ok' ? 'ok' : 'mono'));
			?>
			<article class="graphite-srv" style="<?= $n['seen'] === '0' ? 'border-color:var(--line-strong);' : 'opacity:.7;' ?>">
				<span class="graphite-pill <?= $tag ?>"><?= htmlspecialchars($n['kind']) ?></span>
				<div class="graphite-srv-main">
					<?php if (!empty($n['url'])): ?>
						<a class="graphite-srv-name" href="<?= htmlspecialchars($n['url']) ?>"><?= htmlspecialchars($n['title']) ?></a>
					<?php else: ?>
						<span class="graphite-srv-name"><?= htmlspecialchars($n['title']) ?></span>
					<?php endif; ?>
					<?php if (!empty($n['body'])): ?><span class="graphite-srv-meta"><?= htmlspecialchars($n['body']) ?></span><?php endif; ?>
				</div>
				<span class="graphite-srv-meta"><?= htmlspecialchars(date('M j, H:i', strtotime((string) $n['created']))) ?></span>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
