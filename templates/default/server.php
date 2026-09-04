<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $e_msg1, $e_msg2, $servers (array)
// each $srv expects: status, online, serverid, name, game, servername?, map?, players?, ip?, port?
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr>
	<td align="left"><h1>My Servers</h1></td>
  </tr>
</table>

<?php if (!empty($e_msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($e_msg1) ?></strong><br /><?= htmlspecialchars($e_msg2 ?? '') ?></div>
<?php endif; ?>

<table width="100%" cellpadding="1" cellspacing="1" class="data">
  <tr>
	<th width="26">#</th>
	<th width="50">Status</th>
	<th>Name &amp; Game</th>
	<th>Real Time Query (<a href="#" onclick="window.location.reload();return false;">Refresh</a>)</th>
	<th width="60"></th>
  </tr>

  <?php if (!empty($servers) && is_array($servers)): ?>
	<?php $i = 0; foreach ($servers as $srv): $i++; ?>
	  <?php
		$statusClass = htmlspecialchars($srv['status'] ?? '');
		$online = $srv['online'] ?? '';

		if ($online === 'Pending') $img = 'yellow';
		elseif ($online === 'Started') $img = 'green';
		else $img = 'red';
	  ?>
	  <tr onmouseover="this.className='mouseover'" class="<?= $statusClass ?>" onmouseout="this.className='<?= $statusClass ?>'">
		<td style="color:#666666;"><?= (int)$i ?></td>
		<td>
		  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/buttons/<?= $img ?>.png"
			   width="25" height="25"
			   alt="<?= htmlspecialchars($online) ?>"
			   title="<?= htmlspecialchars($online) ?>" />
		</td>

		<td>
		  <a href="serversummary.php?id=<?= (int)($srv['serverid'] ?? 0) ?>"><?= htmlspecialchars($srv['name'] ?? '') ?></a><br />
		  <i><?= htmlspecialchars($srv['game'] ?? '') ?></i>
		</td>

		<?php if (!empty($srv['servername'])): ?>
		  <td style="line-height:13px;">
			<b><?= htmlspecialchars($srv['servername']) ?></b><br />
			<?= htmlspecialchars($srv['map'] ?? '') ?> ( <?= htmlspecialchars((string)($srv['players'] ?? '')) ?> )<br />
			<i><?= htmlspecialchars($srv['ip'] ?? '') ?><b>:</b><?= (int)($srv['port'] ?? 0) ?></i>
		  </td>
		<?php elseif (!empty($srv['ip'])): ?>
		  <td><i><?= htmlspecialchars($srv['ip']) ?><b>:</b><?= (int)($srv['port'] ?? 0) ?></i></td>
		<?php else: ?>
		  <td>Not Available</td>
		<?php endif; ?>

		<td>
		  <?php if (($online === 'Stopped') && (($srv['status'] ?? '') !== 'Suspended')): ?>
			<a href="servermanage.php?task=start&amp;return=server.php&amp;serverid=<?= (int)($srv['serverid'] ?? 0) ?>">
			  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/buttons/play.png" width="25" height="25" alt="Start" title="Start" />
			</a>
		  <?php elseif (($online === 'Started') && (($srv['status'] ?? '') !== 'Suspended')): ?>
			<a href="servermanage.php?task=restart&amp;return=server.php&amp;serverid=<?= (int)($srv['serverid'] ?? 0) ?>">
			  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/buttons/refresh.png" width="25" height="25" alt="Restart" title="Restart" />
			</a>
			<a href="servermanage.php?task=stop&amp;return=server.php&amp;serverid=<?= (int)($srv['serverid'] ?? 0) ?>">
			  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/buttons/stop.png" width="25" height="25" alt="Stop" title="Stop" />
			</a>
		  <?php endif; ?>
		</td>
	  </tr>
	<?php endforeach; ?>

  <?php else: ?>
	<tr>
	  <td colspan="8">
		<div id="infobox2"><strong>No Servers Found</strong><br />No servers found.</div>
	  </td>
	</tr>
  <?php endif; ?>
</table>

<br />
<table align="center">
  <tr>
	<td width="12" align="right">
	  <table style="width:12px;height:12px;" cellspacing="1" class="data"><tr class="Pending"><td></td></tr></table>
	</td>
	<td>Pending</td>
	<td width="5"></td>
	<td width="12" align="right">
	  <table style="width:12px;height:12px;" cellspacing="1" class="data"><tr class="Active"><td></td></tr></table>
	</td>
	<td>Active</td>
	<td width="5"></td>
	<td width="12" align="right">
	  <table style="width:12px;height:12px;" cellspacing="1" class="data"><tr class="Suspended"><td></td></tr></table>
	</td>
	<td>Suspended</td>
  </tr>
</table>
