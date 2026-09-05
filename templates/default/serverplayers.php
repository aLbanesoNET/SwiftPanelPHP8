<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $sid = (int) ($srv['serverid'] ?? 0); $players = $players ?? []; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Players &mdash; <?= htmlspecialchars($srv['name'] ?? '') ?></h1></td>
	  <td align="right">
		<input type="button" value="Refresh" class="button" onclick="window.location='serverplayers.php?id=<?= $sid ?>'" />
		<input type="button" value="Server Details" class="button blue" onclick="window.location='serversummary.php?id=<?= $sid ?>'" />
	  </td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<?php if (!empty($playerError)): ?>
  <div id="infobox2"><strong>No player list</strong><br /><?= htmlspecialchars($playerError) ?></div>
<?php else: ?>
<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th>Name</th><th>ID</th><th width="70">Time</th><th width="60">Ping</th><th width="80"></th></tr>
  <?php if (empty($players)): ?>
	<tr><td colspan="5"><b>No players connected.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($players as $p): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td><b><?= htmlspecialchars($p['name']) ?></b></td>
	  <td><code><?= htmlspecialchars($p['uid']) ?></code></td>
	  <td><?= htmlspecialchars($p['time']) ?></td>
	  <td><?= htmlspecialchars($p['ping']) ?></td>
	  <td><a href="serverplayersprocess.php?task=kick&amp;serverid=<?= $sid ?>&amp;name=<?= urlencode($p['name']) ?>" onclick="return confirm('Kick <?= htmlspecialchars(addslashes($p['name']), ENT_QUOTES) ?>?');">Kick</a></td>
	</tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
