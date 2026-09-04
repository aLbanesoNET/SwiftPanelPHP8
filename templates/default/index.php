<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// this file assumes header.php already included
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr>
	<td align="left"><h1>Home</h1></td>
  </tr>
</table>

<?php if (!empty($FLASH_MSG1)): ?>
<div id="infobox">
	<strong><?= htmlspecialchars($FLASH_MSG1) ?></strong><br>
	<?= htmlspecialchars($FLASH_MSG2) ?>
</div>
<?php endif; ?>

<p>
Welcome to your server control panel. This panel allows you to remotely control
and monitor all your servers.
</p>

<table width="100%">
<tr>
<td width="50%" valign="top">
	<table width="100%" cellpadding="0" cellspacing="1" class="data">
		<tr>
			<td align="center" style="padding:10px;">
				<strong><?= htmlspecialchars($client['first_name']) ?> <?= htmlspecialchars($client['last_name']) ?></strong><br>
				<?= htmlspecialchars($client['email']) ?><br>
			</td>
		</tr>
	</table>
</td>

<td width="50%" valign="top">
	<table width="100%" cellpadding="0" cellspacing="1" class="data">
		<tr>
			<td align="center" style="padding:10px;">
				Number of Servers: <strong><?= (int)$client['servers'] ?></strong>
			</td>
		</tr>
	</table>
</td>
</tr>
</table>

<p><strong>My Servers</strong></p>

<table width="95%" align="center" cellpadding="1" cellspacing="1" class="data">
<tr>
	<th width="40"></th>
	<th>Name</th>
	<th>Game</th>
	<th>IP Address</th>
	<th>Status</th>
	<th width="30"></th>
</tr>

<?php if (!empty($servers)): ?>
<?php foreach ($servers as $srv): ?>

<?php
	if ($srv['online'] === 'Pending') {
		$img = 'yellow';
	} elseif ($srv['online'] === 'Started') {
		$img = 'green';
	} else {
		$img = 'red';
	}

	if ($srv['status'] === 'Pending') {
		$color = '#FFAA00';
	} elseif ($srv['status'] === 'Active') {
		$color = '#669933';
	} else {
		$color = '#DD0000';
	}
?>

<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	<td>
		<img src="templates/<?= TEMPLATE ?>/images/buttons/<?= $img ?>.png"
			 width="25" height="25"
			 alt="<?= htmlspecialchars($srv['online']) ?>"
			 title="<?= htmlspecialchars($srv['online']) ?>">
	</td>

	<td>
		<a href="serversummary.php?id=<?= (int)$srv['serverid'] ?>">
			<?= htmlspecialchars($srv['name']) ?>
		</a>
	</td>

	<td><?= htmlspecialchars($srv['game']) ?></td>

	<td>
		<?php if (!empty($srv['ip'])): ?><?= htmlspecialchars($srv['ip']) ?>:<?= (int)$srv['port'] ?><?php else: ?>~<?php endif; ?>
	</td>

	<td>
		<span style="color:<?= $color ?>; font-weight:bold;">
			<?= htmlspecialchars($srv['status']) ?>
		</span>
	</td>

	<td>
		<a href="serversummary.php?id=<?= (int)$srv['serverid'] ?>">
			<img src="templates/<?= TEMPLATE ?>/images/edit24.png"
				 width="24" height="24" border="0" alt="Edit">
		</a>
	</td>
</tr>

<?php endforeach; ?>
<?php else: ?>

<tr>
	<td colspan="6"><b>No Servers Found</b></td>
</tr>

<?php endif; ?>
</table>

<?php
// footer.php included by controller
?>
