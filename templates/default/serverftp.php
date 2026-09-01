<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $e_msg1, $e_msg2, $srv (array), $bread_crumb, $file, $path, $path_decoded,
//		  $folders (array), $files (array), $max_filesize, $file_contents
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr>
	<td align="left"><h1>Server Web FTP</h1></td>
  </tr>
</table>

<?php if (!empty($e_msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($e_msg1) ?></strong><br /><?= htmlspecialchars($e_msg2 ?? '') ?></div>
<?php endif; ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td align="left">
	  <a href="serverftp.php?id=<?= (int)($srv['serverid'] ?? 0) ?>">
		<img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/home_24.png" alt="" align="absmiddle" />
	  </a>
	  <?= $bread_crumb ?? '' ?>
	  <?php if (!empty($file)): ?>
		&gt; <a href="serverftp.php?id=<?= (int)($srv['serverid'] ?? 0) ?>&amp;path=<?= urlencode($path ?? '') ?>&amp;file=<?= urlencode($file) ?>">
		  <?= htmlspecialchars($file) ?>
		</a>
	  <?php endif; ?>
	</td>
	<td align="right">
	  <input type="button" value="Server Details" onclick="window.location='serversummary.php?id=<?= (int)($srv['serverid'] ?? 0) ?>'" class="button blue" />
	</td>
  </tr>
</table>

<img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" width="1" height="5" alt="" /><br />

<?php if (empty($file)): ?>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr>
	<th>File</th>
	<th>Size</th>
	<th>User</th>
	<th>Group</th>
	<th>Perms</th>
	<th width="30"></th>
  </tr>

  <?php if (empty($srv['ipid'])): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td colspan="5"><b>Server Not Installed Yet</b></td>
	</tr>
  <?php endif; ?>

  <?php if (!empty($folders) && is_array($folders)): foreach ($folders as $x): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td style="text-align:left;">
		<img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/folder_24.png" align="absmiddle" alt="" />
		<a href="serverftp.php?id=<?= (int)($srv['serverid'] ?? 0) ?>&path=<?= urlencode($x['path'] ?? '') ?>">
		  <?= htmlspecialchars($x['name'] ?? '') ?>
		</a>
	  </td>
	  <td><?= htmlspecialchars($x['size'] ?? '') ?></td>
	  <td><?= htmlspecialchars($x['owner'] ?? '') ?></td>
	  <td><?= htmlspecialchars($x['group'] ?? '') ?></td>
	  <td><?= htmlspecialchars($x['permsn'] ?? '') ?></td>
	  <td>
		<a href="#" onclick="doDeleteDir('<?= htmlspecialchars($x['name'] ?? '', ENT_QUOTES) ?>', '<?= (int)($srv['serverid'] ?? 0) ?>', '<?= htmlspecialchars($path ?? '', ENT_QUOTES) ?>');return false;">
		  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/buttons/red.png" width="25" height="25" alt="Delete" title="Delete" />
		</a>
	  </td>
	</tr>
  <?php endforeach; endif; ?>

  <?php if (!empty($files) && is_array($files)): foreach ($files as $x): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td style="text-align:left;">
		<img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/preview_24.png" align="absmiddle" alt="" />
		<?= $x['link'] ?? htmlspecialchars($x['name'] ?? '') ?>
	  </td>
	  <td><?= htmlspecialchars($x['size'] ?? '') ?></td>
	  <td><?= htmlspecialchars($x['owner'] ?? '') ?></td>
	  <td><?= htmlspecialchars($x['group'] ?? '') ?></td>
	  <td><?= htmlspecialchars($x['permsn'] ?? '') ?></td>
	  <td>
		<a href="#" onclick="doDeleteFile('<?= htmlspecialchars($x['name'] ?? '', ENT_QUOTES) ?>', '<?= (int)($srv['serverid'] ?? 0) ?>', '<?= htmlspecialchars($path ?? '', ENT_QUOTES) ?>');return false;">
		  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/buttons/red.png" width="25" height="25" alt="Delete" title="Delete" />
		</a>
	  </td>
	</tr>
  <?php endforeach; endif; ?>
</table>

<?php if (!empty($srv['ipid'])): ?>
  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" width="1" height="10" alt="" /><br />

  <table cellpadding="2" cellspacing="0">
	<tr>
	  <td>
		<form method="post" action="serverftpprocess.php" enctype="multipart/form-data">
		  <input type="hidden" name="task" value="fileupload" />
		  <input type="hidden" name="id" value="<?= (int)($srv['serverid'] ?? 0) ?>" />
		  <input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>" />
		  <input type="hidden" name="file" value="<?= htmlspecialchars($file ?? '') ?>" />

		  <table cellpadding="2" cellspacing="1" class="data">
			<tr><th>File Upload (Max: <?= htmlspecialchars($max_filesize ?? '') ?>)</th></tr>
			<tr><td><input type="file" name="file" class="text" size="40" /></td></tr>
			<tr><td><input type="submit" value="Upload" class="button green" /></td></tr>
		  </table>
		</form>
	  </td>

	  <td>
		<form method="post" action="serverftpprocess.php">
		  <input type="hidden" name="task" value="makedir" />
		  <input type="hidden" name="id" value="<?= (int)($srv['serverid'] ?? 0) ?>" />
		  <input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>" />

		  <table cellpadding="2" cellspacing="1" class="data">
			<tr><th>Make New Directory</th></tr>
			<tr><td><input type="text" name="dir" class="text" size="40" /></td></tr>
			<tr><td align="center"><input type="submit" value="Create" class="button" /></td></tr>
		  </table>
		</form>
	  </td>
	</tr>
  </table>

  <script type="text/javascript">
  function doDeleteFile(file, id, path) {
	if (confirm("Are you sure you want to delete file: " + file + "?")) {
	  window.location = 'serverftpprocess.php?task=filedelete&id=' + id + '&path=' + path + '&file=' + file;
	}
  }
  function doDeleteDir(dir, id, path) {
	if (confirm("Are you sure you want to delete directory: " + dir + "?")) {
	  window.location = 'serverftpprocess.php?task=dirdelete&id=' + id + '&path=' + path + '&dir=' + dir;
	}
  }
  </script>
<?php endif; ?>

<?php else: ?>

<div align="center">
  <form method="post" action="serverftpprocess.php">
	<input type="hidden" name="task" value="filesave" />
	<input type="hidden" name="id" value="<?= (int)($srv['serverid'] ?? 0) ?>" />
	<input type="hidden" name="path" value="<?= htmlspecialchars($path_decoded ?? '') ?>" />
	<input type="hidden" name="file" value="<?= htmlspecialchars($file ?? '') ?>" />

	<textarea name="filecontents" class="textarea" rows="30" cols="150"><?= htmlspecialchars($file_contents ?? '') ?></textarea>
	<br />
	<img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" height="10" width="1" alt="" /><br />
	<input type="submit" value="Save" class="button green" />
  </form>
</div>

<?php endif; ?>
