<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $posts = $posts ?? []; $tid = (int) $ticket['ticketid']; ?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="graphite-powerbar">
	<div class="graphite-powerbar-id">
		<h1><?= htmlspecialchars($ticket['subject']) ?></h1>
		<span class="graphite-pill graphite-pill-mono">#<?= $tid ?></span>
		<span class="graphite-pill <?= $ticket['status'] === 'answered' ? 'graphite-pill-ok' : ($ticket['status'] === 'closed' ? 'graphite-pill-mono' : 'graphite-pill-warn') ?>"><?= htmlspecialchars($ticket['status']) ?></span>
	</div>
	<div class="graphite-powerbar-act">
		<a class="graphite-btn graphite-btn-ghost" href="tickets.php">All tickets</a>
		<?php if ($ticket['status'] !== 'closed'): ?>
			<a class="graphite-btn graphite-btn-stop" href="ticketprocess.php?task=close&amp;ticketid=<?= $tid ?>" onclick="return confirm('Close this ticket?');">Close</a>
		<?php endif; ?>
	</div>
</section>

<div class="graphite-thread">
	<?php foreach ($posts as $p): ?>
		<article class="graphite-card graphite-post graphite-post-<?= $p['author'] === 'staff' ? 'staff' : 'client' ?>">
			<div class="graphite-card-head">
				<h2><?= $p['author'] === 'staff' ? 'Support' : htmlspecialchars($p['name']) ?></h2>
				<span class="graphite-card-link"><?= htmlspecialchars(date('M j, H:i', strtotime((string) $p['created']))) ?></span>
			</div>
			<?php foreach (preg_split('/\n\s*\n/', trim((string) $p['body'])) as $para): ?>
				<p><?= nl2br(htmlspecialchars($para)) ?></p>
			<?php endforeach; ?>
		</article>
	<?php endforeach; ?>
</div>

<form method="post" action="ticketprocess.php" class="graphite-card graphite-form" style="max-width:720px;margin-top:16px;">
	<input type="hidden" name="task" value="reply">
	<input type="hidden" name="ticketid" value="<?= $tid ?>">
	<div class="graphite-card-head"><h2><?= $ticket['status'] === 'closed' ? 'Reopen with a reply' : 'Reply' ?></h2></div>
	<label class="graphite-field"><span>Message</span><textarea name="body" rows="5"></textarea></label>
	<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Send reply</button></div>
</form>
