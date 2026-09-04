<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $posts = $posts ?? []; $tid = (int) $ticket['ticketid']; ?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-powerbar">
	<div class="fp-powerbar-id">
		<h1><?= htmlspecialchars($ticket['subject']) ?></h1>
		<span class="fp-pill fp-pill-mono">#<?= $tid ?></span>
		<span class="fp-pill <?= $ticket['status'] === 'answered' ? 'fp-pill-ok' : ($ticket['status'] === 'closed' ? 'fp-pill-mono' : 'fp-pill-warn') ?>"><?= htmlspecialchars($ticket['status']) ?></span>
	</div>
	<div class="fp-powerbar-act">
		<a class="fp-btn fp-btn-ghost" href="tickets.php">All tickets</a>
		<?php if ($ticket['status'] !== 'closed'): ?>
			<a class="fp-btn fp-btn-stop" href="ticketprocess.php?task=close&amp;ticketid=<?= $tid ?>" onclick="return confirm('Close this ticket?');">Close</a>
		<?php endif; ?>
	</div>
</section>

<div class="fp-thread">
	<?php foreach ($posts as $p): ?>
		<article class="fp-card fp-post fp-post-<?= $p['author'] === 'staff' ? 'staff' : 'client' ?>">
			<div class="fp-card-head">
				<h2><?= $p['author'] === 'staff' ? 'Support' : htmlspecialchars($p['name']) ?></h2>
				<span class="fp-card-link"><?= htmlspecialchars(date('M j, H:i', strtotime((string) $p['created']))) ?></span>
			</div>
			<?php foreach (preg_split('/\n\s*\n/', trim((string) $p['body'])) as $para): ?>
				<p><?= nl2br(htmlspecialchars($para)) ?></p>
			<?php endforeach; ?>
		</article>
	<?php endforeach; ?>
</div>

<form method="post" action="ticketprocess.php" class="fp-card fp-form" style="max-width:720px;margin-top:16px;">
	<input type="hidden" name="task" value="reply">
	<input type="hidden" name="ticketid" value="<?= $tid ?>">
	<div class="fp-card-head"><h2><?= $ticket['status'] === 'closed' ? 'Reopen with a reply' : 'Reply' ?></h2></div>
	<label class="fp-field"><span>Message</span><textarea name="body" rows="5"></textarea></label>
	<div class="fp-form-actions"><button type="submit" class="fp-btn">Send reply</button></div>
</form>
