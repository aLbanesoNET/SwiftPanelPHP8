<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-tickets" data-view="tickets">
<?php $tickets = $tickets ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="graphite-hero">
	<div class="graphite-hero-text">
		<h1>Support</h1>
		<p>Open a ticket and our team will get back to you here.</p>
	</div>
</section>

<div class="graphite-section-head"><h2>Your tickets</h2><span class="graphite-count"><?= count($tickets) ?></span></div>

<?php if (empty($tickets)): ?>
	<div class="graphite-empty">No tickets yet.</div>
<?php else: ?>
	<div class="graphite-srv-list">
		<?php foreach ($tickets as $t): ?>
			<?php
			$st = $t['status'];
			$cls = $st === 'answered' ? 'graphite-pill-ok' : ($st === 'closed' ? 'graphite-pill-mono' : 'graphite-pill-warn');
			?>
			<article class="graphite-srv">
				<div class="graphite-srv-main">
					<a class="graphite-srv-name" href="ticket.php?id=<?= (int) $t['ticketid'] ?>"><?= htmlspecialchars($t['subject']) ?></a>
					<span class="graphite-srv-meta">#<?= (int) $t['ticketid'] ?> &middot; <?= (int) $t['posts'] ?> message<?= (int) $t['posts'] === 1 ? '' : 's' ?> &middot; updated <?= htmlspecialchars(date('M j, H:i', strtotime((string) $t['updated']))) ?></span>
				</div>
				<span class="graphite-pill <?= $t['priority'] === 'high' ? 'graphite-pill-bad' : 'graphite-pill-mono' ?>"><?= htmlspecialchars($t['priority']) ?></span>
				<span class="graphite-pill <?= $cls ?>"><?= htmlspecialchars($st) ?></span>
				<a class="graphite-ibtn" href="ticket.php?id=<?= (int) $t['ticketid'] ?>" title="Open">&#8250;</a>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="ticketprocess.php" class="graphite-card graphite-form" style="max-width:640px;margin-top:20px;">
	<input type="hidden" name="task" value="create">
	<div class="graphite-card-head"><h2>New ticket</h2></div>
	<div class="graphite-form-grid">
		<label class="graphite-field graphite-field-wide"><span>Subject</span><input type="text" name="subject" placeholder="What do you need help with?"></label>
		<label class="graphite-field"><span>Priority</span>
			<select name="priority"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option></select>
		</label>
	</div>
	<label class="graphite-field" style="margin-top:12px;"><span>Message</span><textarea name="body" rows="6"></textarea></label>
	<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Open ticket</button></div>
</form>

</section>
