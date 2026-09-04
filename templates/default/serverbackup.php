<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$sid = (int) ($srv['serverid'] ?? 0);
$backups = $backups ?? [];
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Backups &mdash; <?= htmlspecialchars($srv['name'] ?? '') ?></h1></td>
	  <td align="right"><input type="button" value="Server Details" class="button blue" onclick="window.location='serversummary.php?id=<?= $sid ?>'" /></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th>Name</th><th width="90">Size</th><th width="150">Created</th><th width="180"></th></tr>
  <?php if (empty($backups)): ?>
	<tr><td colspan="4"><b>No backups yet.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($backups as $b): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td><b><?= htmlspecialchars($b['name']) ?></b></td>
	  <td><?= number_format(((int) $b['sizebytes']) / 1048576, 1) ?> MB</td>
	  <td><?= htmlspecialchars(date('D d M, H:i', strtotime((string) $b['created']))) ?></td>
	  <td>
		<a href="backupdownload.php?id=<?= (int) $b['backupid'] ?>">Download</a> |
		<a href="serverbackupprocess.php?task=restore&amp;serverid=<?= $sid ?>&amp;backupid=<?= (int) $b['backupid'] ?>" onclick="return confirm('Restore this backup? The server will be stopped and its current files replaced.');">Restore</a> |
		<a href="serverbackupprocess.php?task=delete&amp;serverid=<?= $sid ?>&amp;backupid=<?= (int) $b['backupid'] ?>" onclick="return confirm('Delete this backup?');">Delete</a>
	  </td>
	</tr>
  <?php endforeach; ?>
</table>

<br />

<?php if ($canCreate): ?>
  <form method="post" action="serverbackupprocess.php">
	<input type="hidden" name="task" value="create" />
	<input type="hidden" name="serverid" value="<?= $sid ?>" />
	<fieldset>
	  <table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr><td colspan="2" class="fieldheader">New Backup</td></tr>
		<tr><td class="fieldname" style="width:120px;">Name</td><td class="fieldarea">
		  <input type="text" name="name" class="text" size="30" placeholder="Before map change" />
		  <input type="submit" value="Create Backup" class="button green" />
		  <br /><font color="#666666" size="-2">Archives the whole server directory. Large servers may take a minute.</font>
		</td></tr>
	  </table>
	</fieldset>
  </form>
<?php else: ?>
  <div id="infobox2"><strong>Backup limit reached</strong><br />Delete an old backup to create another.</div>
<?php endif; ?>
