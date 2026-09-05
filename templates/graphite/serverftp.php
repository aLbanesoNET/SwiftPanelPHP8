<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-serverftp" data-view="serverftp">
<?php
// expects: $srv, $e_msg1, $e_msg2, $bread_crumb, $file, $path, $path_decoded,
//          $folders[], $files[], $max_filesize, $file_contents
$sid     = (int)($srv['serverid'] ?? 0);
$folders = $folders ?? [];
$files   = $files ?? [];
?>
<?php if (!empty($e_msg1)): ?>
	<div class="graphite-note graphite-note-ok">
		<strong><?= htmlspecialchars($e_msg1) ?></strong>
		<span><?= htmlspecialchars($e_msg2 ?? '') ?></span>
	</div>
<?php endif; ?>

<div class="graphite-fb-bar">
	<nav class="graphite-crumbs">
		<a href="serverftp.php?id=<?= $sid ?>">~</a>
		<?= $bread_crumb ?? '' ?>
		<?php if (!empty($file)): ?>
			<span class="sep">/</span><span class="cur"><?= htmlspecialchars($file) ?></span>
		<?php endif; ?>
	</nav>
	<a class="graphite-btn graphite-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
</div>

<?php if (empty($file)): ?>

	<div class="graphite-fb">
		<div class="graphite-tr graphite-th">
			<span class="graphite-c-file">Name</span>
			<span class="graphite-c-size">Size</span>
			<span class="graphite-c-own">Owner</span>
			<span class="graphite-c-perm">Perms</span>
			<span class="graphite-c-x"></span>
		</div>

		<?php if (empty($srv['ipid'])): ?>
			<div class="graphite-tr"><span class="graphite-c-file"><strong>Server not installed yet</strong></span></div>
		<?php endif; ?>

		<?php foreach ($folders as $x): ?>
			<div class="graphite-tr">
				<span class="graphite-c-file graphite-is-dir">
					<a href="serverftp.php?id=<?= $sid ?>&amp;path=<?= urlencode($x['path'] ?? '') ?>"><?= htmlspecialchars($x['name'] ?? '') ?></a>
				</span>
				<span class="graphite-c-size">&mdash;</span>
				<span class="graphite-c-own"><?= htmlspecialchars($x['owner'] ?? '') ?></span>
				<span class="graphite-c-perm"><?= htmlspecialchars($x['permsn'] ?? '') ?></span>
				<span class="graphite-c-x">
					<a class="graphite-ibtn graphite-ibtn-stop" href="#" title="Delete"
					   onclick="doDeleteDir('<?= htmlspecialchars(addslashes($x['name'] ?? ''), ENT_QUOTES) ?>','<?= $sid ?>','<?= htmlspecialchars(addslashes($path ?? ''), ENT_QUOTES) ?>');return false;">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>

		<?php foreach ($files as $x): ?>
			<div class="graphite-tr">
				<span class="graphite-c-file graphite-is-file"><?= $x['link'] ?? htmlspecialchars($x['name'] ?? '') ?></span>
				<span class="graphite-c-size"><?= htmlspecialchars($x['size'] ?? '') ?></span>
				<span class="graphite-c-own"><?= htmlspecialchars($x['owner'] ?? '') ?></span>
				<span class="graphite-c-perm"><?= htmlspecialchars($x['permsn'] ?? '') ?></span>
				<span class="graphite-c-x">
					<a class="graphite-ibtn graphite-ibtn-stop" href="#" title="Delete"
					   onclick="doDeleteFile('<?= htmlspecialchars(addslashes($x['name'] ?? ''), ENT_QUOTES) ?>','<?= $sid ?>','<?= htmlspecialchars(addslashes($path ?? ''), ENT_QUOTES) ?>');return false;">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if (!empty($srv['ipid'])): ?>
		<div class="graphite-grid graphite-grid-2">
			<form method="post" action="serverftpprocess.php" enctype="multipart/form-data" class="graphite-card graphite-form">
				<input type="hidden" name="task" value="fileupload">
				<input type="hidden" name="id" value="<?= $sid ?>">
				<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>">
				<input type="hidden" name="file" value="<?= htmlspecialchars($file ?? '') ?>">
				<div class="graphite-card-head"><h2>Upload file</h2><p>Max <?= htmlspecialchars($max_filesize ?? '') ?></p></div>
				<label class="graphite-field"><span>Choose a file</span><input type="file" name="file"></label>
				<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Upload</button></div>
			</form>

			<form method="post" action="serverftpprocess.php" class="graphite-card graphite-form">
				<input type="hidden" name="task" value="makedir">
				<input type="hidden" name="id" value="<?= $sid ?>">
				<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>">
				<div class="graphite-card-head"><h2>New directory</h2></div>
				<label class="graphite-field"><span>Folder name</span><input type="text" name="dir"></label>
				<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Create</button></div>
			</form>
		</div>

		<script type="text/javascript">
		function doDeleteFile(file, id, path){
			if (confirm("Delete file: " + file + "?")) {
				window.location = 'serverftpprocess.php?task=filedelete&id=' + id + '&path=' + path + '&file=' + file;
			}
		}
		function doDeleteDir(dir, id, path){
			if (confirm("Delete directory: " + dir + "?")) {
				window.location = 'serverftpprocess.php?task=dirdelete&id=' + id + '&path=' + path + '&dir=' + dir;
			}
		}
		</script>
	<?php endif; ?>

<?php else: ?>

	<form method="post" action="serverftpprocess.php" class="graphite-card graphite-form">
		<input type="hidden" name="task" value="filesave">
		<input type="hidden" name="id" value="<?= $sid ?>">
		<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>">
		<input type="hidden" name="file" value="<?= htmlspecialchars($file ?? '') ?>">
		<div class="graphite-card-head"><h2>Edit <?= htmlspecialchars($file) ?></h2></div>
		<textarea name="filecontents" rows="26" spellcheck="false"><?= htmlspecialchars($file_contents ?? '') ?></textarea>
		<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Save file</button></div>
	</form>

<?php endif; ?>

</section>
