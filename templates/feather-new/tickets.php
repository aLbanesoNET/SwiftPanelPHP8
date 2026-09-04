<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $tickets = $tickets ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-hero">
	<div class="fp-hero-text">
		<h1>Support</h1>
		<p>Open a ticket and our team will get back to you here.</p>
	</div>
</section>

<div class="fp-section-head"><h2>Your tickets</h2><span class="fp-count"><?= count($tickets) ?></span></div>

<?php if (empty($tickets)): ?>
	<div class="fp-empty">No tickets yet.</div>
<?php else: ?>
	<div class="fp-srv-list">
		<?php foreach ($tickets as $t): ?>
			<?php
			$st = $t['status'];
			$cls = $st === 'answered' ? 'fp-pill-ok' : ($st === 'closed' ? 'fp-pill-mono' : 'fp-pill-warn');
			?>
			<article class="fp-srv">
				<div class="fp-srv-main">
					<a class="fp-srv-name" href="ticket.php?id=<?= (int) $t['ticketid'] ?>"><?= htmlspecialchars($t['subject']) ?></a>
					<span class="fp-srv-meta">#<?= (int) $t['ticketid'] ?> &middot; <?= (int) $t['posts'] ?> message<?= (int) $t['posts'] === 1 ? '' : 's' ?> &middot; updated <?= htmlspecialchars(date('M j, H:i', strtotime((string) $t['updated']))) ?></span>
				</div>
				<span class="fp-pill <?= $t['priority'] === 'high' ? 'fp-pill-bad' : 'fp-pill-mono' ?>"><?= htmlspecialchars($t['priority']) ?></span>
				<span class="fp-pill <?= $cls ?>"><?= htmlspecialchars($st) ?></span>
				<a class="fp-ibtn" href="ticket.php?id=<?= (int) $t['ticketid'] ?>" title="Open">&#8250;</a>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="ticketprocess.php" class="fp-card fp-form" style="max-width:640px;margin-top:20px;">
	<input type="hidden" name="task" value="create">
	<div class="fp-card-head"><h2>New ticket</h2></div>
	<div class="fp-form-grid">
		<label class="fp-field fp-field-wide"><span>Subject</span><input type="text" name="subject" placeholder="What do you need help with?"></label>
		<label class="fp-field"><span>Priority</span>
			<select name="priority"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option></select>
		</label>
	</div>
	<label class="fp-field" style="margin-top:12px;"><span>Message</span><textarea name="body" rows="6"></textarea></label>
	<div class="fp-form-actions"><button type="submit" class="fp-btn">Open ticket</button></div>
</form>
