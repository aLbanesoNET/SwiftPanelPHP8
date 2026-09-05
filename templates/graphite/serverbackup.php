<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$sid = (int) ($srv['serverid'] ?? 0);
$backups = $backups ?? [];
?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="graphite-powerbar">
	<div class="graphite-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="graphite-pill graphite-pill-mono">Backups</span>
	</div>
	<div class="graphite-powerbar-act">
		<a class="graphite-btn graphite-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="graphite-section-head"><h2>Backups</h2><span class="graphite-count"><?= count($backups) ?></span></div>

<?php if (empty($backups)): ?>
	<div class="graphite-empty">No backups yet.</div>
<?php else: ?>
	<div class="graphite-table">
		<div class="graphite-tr graphite-th" style="grid-template-columns:1.6fr 1fr 1.2fr 200px;">
			<span>Name</span><span>Size</span><span>Created</span><span></span>
		</div>
		<?php foreach ($backups as $b): ?>
			<div class="graphite-tr" style="grid-template-columns:1.6fr 1fr 1.2fr 200px;">
				<span><strong><?= htmlspecialchars($b['name']) ?></strong></span>
				<span class="graphite-srv-meta"><?= number_format(((int) $b['sizebytes']) / 1048576, 1) ?> MB</span>
				<span class="graphite-srv-meta"><?= htmlspecialchars(date('D d M, H:i', strtotime((string) $b['created']))) ?></span>
				<span class="graphite-c-act">
					<a class="graphite-ibtn" href="backupdownload.php?id=<?= (int) $b['backupid'] ?>" title="Download">&#8595;</a>
					<a class="graphite-ibtn" href="serverbackupprocess.php?task=restore&amp;serverid=<?= $sid ?>&amp;backupid=<?= (int) $b['backupid'] ?>" title="Restore" onclick="return confirm('Restore this backup? The server will be stopped and its current files replaced.');">&#8635;</a>
					<a class="graphite-ibtn graphite-ibtn-stop" href="serverbackupprocess.php?task=delete&amp;serverid=<?= $sid ?>&amp;backupid=<?= (int) $b['backupid'] ?>" title="Delete" onclick="return confirm('Delete this backup?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ($canCreate): ?>
	<form method="post" action="serverbackupprocess.php" class="graphite-card graphite-form" style="max-width:520px;margin-top:20px;">
		<input type="hidden" name="task" value="create">
		<input type="hidden" name="serverid" value="<?= $sid ?>">
		<div class="graphite-card-head"><h2>New backup</h2><p>Snapshots your whole server directory. Large servers may take a minute.</p></div>
		<label class="graphite-field"><span>Name</span><input type="text" name="name" placeholder="Before map change"></label>
		<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Create backup</button></div>
	</form>
<?php else: ?>
	<div class="graphite-empty" style="margin-top:20px;">Backup limit reached &mdash; delete one to make another.</div>
<?php endif; ?>
