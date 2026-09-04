<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $notifs = $notifs ?? []; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Notifications</h1></td>
	  <td align="right"><?php if (!empty($NOTIF_UNSEEN)): ?><input type="button" value="Mark All Read" class="button" onclick="window.location='notifications.php?read=1'" /><?php endif; ?></td></tr>
</table>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th width="80">Kind</th><th>Notification</th><th width="140">When</th></tr>
  <?php if (empty($notifs)): ?>
	<tr><td colspan="3"><b>Nothing here yet.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($notifs as $n): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''"<?= $n['seen'] === '0' ? ' style="font-weight:bold;"' : '' ?>>
	  <td><?= htmlspecialchars($n['kind']) ?></td>
	  <td><?php if (!empty($n['url'])): ?><a href="<?= htmlspecialchars($n['url']) ?>"><?= htmlspecialchars($n['title']) ?></a><?php else: ?><?= htmlspecialchars($n['title']) ?><?php endif; ?>
		<?= !empty($n['body']) ? '<br /><span style="font-weight:normal;color:#888;">' . htmlspecialchars($n['body']) . '</span>' : '' ?></td>
	  <td><?= htmlspecialchars(date('M j, Y H:i', strtotime((string) $n['created']))) ?></td>
	</tr>
  <?php endforeach; ?>
</table>
