<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $notifs = $notifs ?? []; ?>
<section class="fp-hero">
	<div class="fp-hero-text">
		<h1>Notifications</h1>
		<p>Alerts about your servers, tickets and account.</p>
	</div>
	<?php if (!empty($NOTIF_UNSEEN)): ?>
		<a class="fp-btn fp-btn-ghost" href="notifications.php?read=1">Mark all read</a>
	<?php endif; ?>
</section>

<?php if (empty($notifs)): ?>
	<div class="fp-empty">Nothing here yet.</div>
<?php else: ?>
	<div class="fp-srv-list">
		<?php foreach ($notifs as $n): ?>
			<?php
			$tone = ['down' => 'bad', 'ticket' => 'ok', 'backup' => 'ok', 'schedule' => 'mono', 'system' => 'mono'][$n['kind']] ?? 'mono';
			$tag = 'fp-pill-' . ($tone === 'bad' ? 'bad' : ($tone === 'ok' ? 'ok' : 'mono'));
			?>
			<article class="fp-srv" style="<?= $n['seen'] === '0' ? 'border-color:var(--line-strong);' : 'opacity:.7;' ?>">
				<span class="fp-pill <?= $tag ?>"><?= htmlspecialchars($n['kind']) ?></span>
				<div class="fp-srv-main">
					<?php if (!empty($n['url'])): ?>
						<a class="fp-srv-name" href="<?= htmlspecialchars($n['url']) ?>"><?= htmlspecialchars($n['title']) ?></a>
					<?php else: ?>
						<span class="fp-srv-name"><?= htmlspecialchars($n['title']) ?></span>
					<?php endif; ?>
					<?php if (!empty($n['body'])): ?><span class="fp-srv-meta"><?= htmlspecialchars($n['body']) ?></span><?php endif; ?>
				</div>
				<span class="fp-srv-meta"><?= htmlspecialchars(date('M j, H:i', strtotime((string) $n['created']))) ?></span>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
