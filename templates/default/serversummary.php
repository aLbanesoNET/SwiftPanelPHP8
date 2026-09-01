<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $e_msg1, $e_msg2, $srv (array), $query (array)
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr>
	<td align="left"><h1>Server Details</h1></td>
  </tr>
</table>

<?php if (!empty($e_msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($e_msg1) ?></strong><br /><?= htmlspecialchars($e_msg2 ?? '') ?></div>
<?php endif; ?>

<?php if (($srv['status'] ?? '') === 'Active'): ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td align="left">
	  <?php if (($srv['online'] ?? '') === 'Stopped'): ?>
		<input type="button" value="Start Server"
		  onclick="window.location='servermanage.php?task=start&amp;serverid=<?= (int)($srv['serverid'] ?? 0) ?>'"
		  class="button green start" />
	  <?php elseif (($srv['online'] ?? '') === 'Started'): ?>
		<input type="button" value="Restart Server"
		  onclick="window.location='servermanage.php?task=restart&amp;serverid=<?= (int)($srv['serverid'] ?? 0) ?>'"
		  class="button blue restart" />
		<input type="button" value="Stop Server"
		  onclick="window.location='servermanage.php?task=stop&amp;serverid=<?= (int)($srv['serverid'] ?? 0) ?>'"
		  class="button red stop" />
	  <?php endif; ?>
	</td>

	<?php if (!empty($srv['webftp'])): ?>
	  <td align="right">
		<input type="button" value="Web FTP"
		  onclick="window.location='serverftp.php?id=<?= (int)($srv['serverid'] ?? 0) ?>'"
		  class="button blue" />
	  </td>
	<?php endif; ?>
  </tr>
</table>
<?php endif; ?>

<img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" width="1" height="6" alt="" /><br />

<?php
$status = $srv['status'] ?? '';
if ($status === 'Pending') $statusColor = '#FFAA00';
elseif ($status === 'Active') $statusColor = '#669933';
else $statusColor = '#DD0000';

$online = $srv['online'] ?? '';
if ($online === 'Pending') $onlineColor = '#FFAA00';
elseif ($online === 'Started') $onlineColor = '#669933';
else $onlineColor = '#DD0000';

$anyEditable =
  !empty($srv['cfg1edit']) || !empty($srv['cfg2edit']) || !empty($srv['cfg3edit']) || !empty($srv['cfg4edit']) ||
  !empty($srv['cfg5edit']) || !empty($srv['cfg6edit']) || !empty($srv['cfg7edit']) || !empty($srv['cfg8edit']);
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td width="50%" valign="top">
	  <fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr><td colspan="2" class="fieldheader">Server Information</td></tr>
		  <tr><td class="fieldname" style="height:20px;width:110px;">Name</td><td class="fieldarea"><?= htmlspecialchars($srv['name'] ?? '') ?></td></tr>
		  <tr><td class="fieldname" style="height:20px;">Game</td><td class="fieldarea"><?= htmlspecialchars($srv['game'] ?? '') ?></td></tr>

		  <?php if (!empty($srv['boxlocation'])): ?>
		  <tr><td class="fieldname" style="height:20px;">Location</td><td class="fieldarea"><?= htmlspecialchars($srv['boxlocation']) ?></td></tr>
		  <?php endif; ?>

		  <tr>
			<td class="fieldname" style="height:20px;">Status</td>
			<td class="fieldarea"><font color="<?= $statusColor ?>"><b><?= htmlspecialchars($status) ?></b></font></td>
		  </tr>
		</table>
	  </fieldset>

	  <form method="post" action="serverprocess.php">
		<input type="hidden" name="task" value="serveredit" />
		<input type="hidden" name="serverid" value="<?= (int)($srv['serverid'] ?? 0) ?>" />

		<fieldset>
		  <table width="100%" border="0" cellpadding="2" cellspacing="2">
			<tr><td colspan="2" class="fieldheader">Server Configuration</td></tr>
			<tr><td class="fieldname" style="height:20px;width:110px;">Max Slots</td><td class="fieldarea"><?= htmlspecialchars((string)($srv['slots'] ?? '')) ?></td></tr>
			<tr><td class="fieldname" style="height:20px;">Type</td><td class="fieldarea"><?= htmlspecialchars($srv['type'] ?? '') ?></td></tr>

			<?php for ($i = 1; $i <= 8; $i++): ?>
			  <?php
				$nameKey = "cfg{$i}name";
				$editKey = "cfg{$i}edit";
				$valKey  = "cfg{$i}";
			  ?>
			  <?php if (!empty($srv[$nameKey])): ?>
				<tr>
				  <td class="fieldname" style="height:20px;"><?= htmlspecialchars($srv[$nameKey]) ?></td>
				  <?php if (!empty($srv[$editKey])): ?>
					<td class="fieldarea"><input type="text" name="cfg<?= $i ?>" class="text" size="15" value="<?= htmlspecialchars($srv[$valKey] ?? '') ?>" /></td>
				  <?php else: ?>
					<td class="fieldarea"><?= htmlspecialchars($srv[$valKey] ?? '') ?></td>
				  <?php endif; ?>
				</tr>
			  <?php endif; ?>
			<?php endfor; ?>
		  </table>
		</fieldset>

		<?php if ($anyEditable): ?>
		  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" height="6" width="1" alt="" /><br />
		  <div align="center">
			<input type="submit" value="Save Changes" class="button green" />
			<input type="reset" value="Cancel Changes" class="button red" />
		  </div>
		<?php endif; ?>
	  </form>
	</td>

	<td width="50%" valign="top">
	  <?php if (!empty($srv['showftp']) && !empty($srv['ip'])): ?>
	  <fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr><td colspan="2" class="fieldheader">FTP Details</td></tr>
		  <tr><td class="fieldname" style="height:20px;width:110px;">IP Address</td><td class="fieldarea"><?= htmlspecialchars($srv['ip']) ?></td></tr>
		  <tr><td class="fieldname" style="height:20px;">Port</td><td class="fieldarea"><?= htmlspecialchars((string)($srv['ftpport'] ?? '')) ?></td></tr>
		  <tr><td class="fieldname" style="height:20px;">User</td><td class="fieldarea"><?= htmlspecialchars($srv['user'] ?? '') ?></td></tr>
		  <tr><td class="fieldname" style="height:20px;">Password</td><td class="fieldarea"><?= htmlspecialchars($srv['password'] ?? '') ?></td></tr>
		</table>
	  </fieldset>
	  <?php endif; ?>

	  <fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr><td colspan="2" class="fieldheader">Server Status</td></tr>
		  <tr>
			<td class="fieldname" style="height:20px;width:110px;">Status</td>
			<td class="fieldarea">
			  <font color="<?= $onlineColor ?>"><b><?= htmlspecialchars($online) ?></b></font>
			  (<a href="#" onclick="window.location.reload();return false;">Refresh</a>)
			</td>
		  </tr>

		  <?php if (!empty($query) && is_array($query)): ?>
			<?php foreach ($query as $name => $value): ?>
			  <tr>
				<td class="fieldname" style="height:20px;"><?= htmlspecialchars((string)$name) ?></td>
				<td class="fieldarea"><?= htmlspecialchars((string)$value) ?></td>
			  </tr>
			<?php endforeach; ?>
		  <?php endif; ?>
		</table>
	  </fieldset>
	</td>
  </tr>
</table>
