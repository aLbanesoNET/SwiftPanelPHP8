<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $sid = (int) ($srv['serverid'] ?? 0); $subs = $subs ?? []; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Sharing &mdash; <?= htmlspecialchars($srv['name'] ?? '') ?></h1></td>
	  <td align="right"><input type="button" value="Server Details" class="button blue" onclick="window.location='serversummary.php?id=<?= $sid ?>'" /></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<p>Shared accounts can view the server, use the console and start/stop it. Files, backups, schedules, databases and sharing stay with you.</p>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th>Email</th><th>Account</th><th width="110">Added</th><th width="80"></th></tr>
  <?php if (empty($subs)): ?>
	<tr><td colspan="4"><b>Not shared with anyone.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($subs as $s): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td><b><?= htmlspecialchars($s['subemail']) ?></b></td>
	  <td><?= (int) $s['subclientid'] > 0 ? htmlspecialchars(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? '')) ?: 'linked') : 'pending signup' ?></td>
	  <td><?= htmlspecialchars(date('M j, Y', strtotime((string) $s['created']))) ?></td>
	  <td><a href="serversubusersprocess.php?task=remove&amp;serverid=<?= $sid ?>&amp;subid=<?= (int) $s['subid'] ?>" onclick="return confirm('Remove access?');">Remove</a></td>
	</tr>
  <?php endforeach; ?>
</table>

<br />

<form method="post" action="serversubusersprocess.php">
  <input type="hidden" name="task" value="add" />
  <input type="hidden" name="serverid" value="<?= $sid ?>" />
  <fieldset><table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr><td colspan="2" class="fieldheader">Share With Someone</td></tr>
	<tr><td class="fieldname" style="width:130px;">Account email</td><td class="fieldarea"><input type="text" name="email" class="text" size="35" placeholder="friend@example.com" /> <input type="submit" value="Share" class="button green" /></td></tr>
  </table></fieldset>
</form>
