<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $sid = (int) ($srv['serverid'] ?? 0); $subs = $subs ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-powerbar">
	<div class="fp-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="fp-pill fp-pill-mono">Sharing</span>
	</div>
	<div class="fp-powerbar-act">
		<a class="fp-btn fp-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="fp-section-head"><h2>People with access</h2><span class="fp-count"><?= count($subs) ?></span></div>
<p style="color:var(--muted);font-size:12px;margin-bottom:12px;">Shared accounts can view the server, use the console and start/stop it. Files, backups, schedules, databases and sharing stay with you.</p>

<?php if (empty($subs)): ?>
	<div class="fp-empty">Not shared with anyone.</div>
<?php else: ?>
	<div class="fp-table">
		<div class="fp-tr fp-th" style="grid-template-columns:1.6fr 1.4fr 1fr 80px;">
			<span>Email</span><span>Account</span><span>Added</span><span></span>
		</div>
		<?php foreach ($subs as $s): ?>
			<div class="fp-tr" style="grid-template-columns:1.6fr 1.4fr 1fr 80px;">
				<span><strong><?= htmlspecialchars($s['subemail']) ?></strong></span>
				<span class="fp-srv-meta"><?= (int) $s['subclientid'] > 0 ? htmlspecialchars(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? '')) ?: 'linked') : '<em>pending signup</em>' ?></span>
				<span class="fp-srv-meta"><?= htmlspecialchars(date('M j', strtotime((string) $s['created']))) ?></span>
				<span class="fp-c-act">
					<a class="fp-ibtn fp-ibtn-stop" href="serversubusersprocess.php?task=remove&amp;serverid=<?= $sid ?>&amp;subid=<?= (int) $s['subid'] ?>" title="Remove" onclick="return confirm('Remove access for <?= htmlspecialchars($s['subemail'], ENT_QUOTES) ?>?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="serversubusersprocess.php" class="fp-card fp-form" style="max-width:480px;margin-top:20px;">
	<input type="hidden" name="task" value="add">
	<input type="hidden" name="serverid" value="<?= $sid ?>">
	<div class="fp-card-head"><h2>Share with someone</h2></div>
	<label class="fp-field"><span>Their account email</span><input type="text" name="email" placeholder="friend@example.com"></label>
	<div class="fp-form-actions"><button type="submit" class="fp-btn">Share</button></div>
</form>
