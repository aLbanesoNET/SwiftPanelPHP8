<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $srv, $e_msg1, $e_msg2, $bread_crumb, $file, $path, $path_decoded,
//          $folders[], $files[], $max_filesize, $file_contents
$sid     = (int)($srv['serverid'] ?? 0);
$folders = $folders ?? [];
$files   = $files ?? [];
?>
<?php if (!empty($e_msg1)): ?>
	<div class="fp-note fp-note-ok">
		<strong><?= htmlspecialchars($e_msg1) ?></strong>
		<span><?= htmlspecialchars($e_msg2 ?? '') ?></span>
	</div>
<?php endif; ?>

<div class="fp-fb-bar">
	<nav class="fp-crumbs">
		<a href="serverftp.php?id=<?= $sid ?>">~</a>
		<?= $bread_crumb ?? '' ?>
		<?php if (!empty($file)): ?>
			<span class="sep">/</span><span class="cur"><?= htmlspecialchars($file) ?></span>
		<?php endif; ?>
	</nav>
	<a class="fp-btn fp-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
</div>

<?php if (empty($file)): ?>

	<div class="fp-fb">
		<div class="fp-tr fp-th">
			<span class="fp-c-file">Name</span>
			<span class="fp-c-size">Size</span>
			<span class="fp-c-own">Owner</span>
			<span class="fp-c-perm">Perms</span>
			<span class="fp-c-x"></span>
		</div>

		<?php if (empty($srv['ipid'])): ?>
			<div class="fp-tr"><span class="fp-c-file"><strong>Server not installed yet</strong></span></div>
		<?php endif; ?>

		<?php foreach ($folders as $x): ?>
			<div class="fp-tr">
				<span class="fp-c-file fp-is-dir">
					<a href="serverftp.php?id=<?= $sid ?>&amp;path=<?= urlencode($x['path'] ?? '') ?>"><?= htmlspecialchars($x['name'] ?? '') ?></a>
				</span>
				<span class="fp-c-size">&mdash;</span>
				<span class="fp-c-own"><?= htmlspecialchars($x['owner'] ?? '') ?></span>
				<span class="fp-c-perm"><?= htmlspecialchars($x['permsn'] ?? '') ?></span>
				<span class="fp-c-x">
					<a class="fp-ibtn fp-ibtn-stop" href="#" title="Delete"
					   onclick="doDeleteDir('<?= htmlspecialchars(addslashes($x['name'] ?? ''), ENT_QUOTES) ?>','<?= $sid ?>','<?= htmlspecialchars(addslashes($path ?? ''), ENT_QUOTES) ?>');return false;">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>

		<?php foreach ($files as $x): ?>
			<div class="fp-tr">
				<span class="fp-c-file fp-is-file"><?= $x['link'] ?? htmlspecialchars($x['name'] ?? '') ?></span>
				<span class="fp-c-size"><?= htmlspecialchars($x['size'] ?? '') ?></span>
				<span class="fp-c-own"><?= htmlspecialchars($x['owner'] ?? '') ?></span>
				<span class="fp-c-perm"><?= htmlspecialchars($x['permsn'] ?? '') ?></span>
				<span class="fp-c-x">
					<a class="fp-ibtn fp-ibtn-stop" href="#" title="Delete"
					   onclick="doDeleteFile('<?= htmlspecialchars(addslashes($x['name'] ?? ''), ENT_QUOTES) ?>','<?= $sid ?>','<?= htmlspecialchars(addslashes($path ?? ''), ENT_QUOTES) ?>');return false;">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if (!empty($srv['ipid'])): ?>
		<div class="fp-grid fp-grid-2">
			<form method="post" action="serverftpprocess.php" enctype="multipart/form-data" class="fp-card fp-form">
				<input type="hidden" name="task" value="fileupload">
				<input type="hidden" name="id" value="<?= $sid ?>">
				<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>">
				<input type="hidden" name="file" value="<?= htmlspecialchars($file ?? '') ?>">
				<div class="fp-card-head"><h2>Upload file</h2><p>Max <?= htmlspecialchars($max_filesize ?? '') ?></p></div>
				<label class="fp-field"><span>Choose a file</span><input type="file" name="file"></label>
				<div class="fp-form-actions"><button type="submit" class="fp-btn">Upload</button></div>
			</form>

			<form method="post" action="serverftpprocess.php" class="fp-card fp-form">
				<input type="hidden" name="task" value="makedir">
				<input type="hidden" name="id" value="<?= $sid ?>">
				<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>">
				<div class="fp-card-head"><h2>New directory</h2></div>
				<label class="fp-field"><span>Folder name</span><input type="text" name="dir"></label>
				<div class="fp-form-actions"><button type="submit" class="fp-btn">Create</button></div>
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

	<form method="post" action="serverftpprocess.php" class="fp-card fp-form">
		<input type="hidden" name="task" value="filesave">
		<input type="hidden" name="id" value="<?= $sid ?>">
		<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>">
		<input type="hidden" name="file" value="<?= htmlspecialchars($file ?? '') ?>">
		<div class="fp-card-head"><h2>Edit <?= htmlspecialchars($file) ?></h2></div>
		<textarea name="filecontents" rows="26" spellcheck="false"><?= htmlspecialchars($file_contents ?? '') ?></textarea>
		<div class="fp-form-actions"><button type="submit" class="fp-btn">Save file</button></div>
	</form>

<?php endif; ?>
