<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$sid = (int) ($srv['serverid'] ?? 0);
$backups = $backups ?? [];
?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-powerbar">
	<div class="fp-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="fp-pill fp-pill-mono">Backups</span>
	</div>
	<div class="fp-powerbar-act">
		<a class="fp-btn fp-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="fp-section-head"><h2>Backups</h2><span class="fp-count"><?= count($backups) ?></span></div>

<?php if (empty($backups)): ?>
	<div class="fp-empty">No backups yet.</div>
<?php else: ?>
	<div class="fp-table">
		<div class="fp-tr fp-th" style="grid-template-columns:1.6fr 1fr 1.2fr 200px;">
			<span>Name</span><span>Size</span><span>Created</span><span></span>
		</div>
		<?php foreach ($backups as $b): ?>
			<div class="fp-tr" style="grid-template-columns:1.6fr 1fr 1.2fr 200px;">
				<span><strong><?= htmlspecialchars($b['name']) ?></strong></span>
				<span class="fp-srv-meta"><?= number_format(((int) $b['sizebytes']) / 1048576, 1) ?> MB</span>
				<span class="fp-srv-meta"><?= htmlspecialchars(date('D d M, H:i', strtotime((string) $b['created']))) ?></span>
				<span class="fp-c-act">
					<a class="fp-ibtn" href="backupdownload.php?id=<?= (int) $b['backupid'] ?>" title="Download">&#8595;</a>
					<a class="fp-ibtn" href="serverbackupprocess.php?task=restore&amp;serverid=<?= $sid ?>&amp;backupid=<?= (int) $b['backupid'] ?>" title="Restore" onclick="return confirm('Restore this backup? The server will be stopped and its current files replaced.');">&#8635;</a>
					<a class="fp-ibtn fp-ibtn-stop" href="serverbackupprocess.php?task=delete&amp;serverid=<?= $sid ?>&amp;backupid=<?= (int) $b['backupid'] ?>" title="Delete" onclick="return confirm('Delete this backup?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ($canCreate): ?>
	<form method="post" action="serverbackupprocess.php" class="fp-card fp-form" style="max-width:520px;margin-top:20px;">
		<input type="hidden" name="task" value="create">
		<input type="hidden" name="serverid" value="<?= $sid ?>">
		<div class="fp-card-head"><h2>New backup</h2><p>Snapshots your whole server directory. Large servers may take a minute.</p></div>
		<label class="fp-field"><span>Name</span><input type="text" name="name" placeholder="Before map change"></label>
		<div class="fp-form-actions"><button type="submit" class="fp-btn">Create backup</button></div>
	</form>
<?php else: ?>
	<div class="fp-empty" style="margin-top:20px;">Backup limit reached &mdash; delete one to make another.</div>
<?php endif; ?>
