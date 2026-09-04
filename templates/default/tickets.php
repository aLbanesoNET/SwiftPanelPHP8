<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $tickets = $tickets ?? []; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Support</h1></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th width="50">ID</th><th>Subject</th><th width="80">Priority</th><th width="90">Status</th><th width="140">Updated</th></tr>
  <?php if (empty($tickets)): ?>
	<tr><td colspan="5"><b>No tickets yet.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($tickets as $t): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td>#<?= (int) $t['ticketid'] ?></td>
	  <td><a href="ticket.php?id=<?= (int) $t['ticketid'] ?>"><?= htmlspecialchars($t['subject']) ?></a> <span style="color:#888;">(<?= (int) $t['posts'] ?>)</span></td>
	  <td><?= htmlspecialchars($t['priority']) ?></td>
	  <td><?= htmlspecialchars($t['status']) ?></td>
	  <td><?= htmlspecialchars(date('M j, H:i', strtotime((string) $t['updated']))) ?></td>
	</tr>
  <?php endforeach; ?>
</table>

<br />

<form method="post" action="ticketprocess.php">
  <input type="hidden" name="task" value="create" />
  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td colspan="2" class="fieldheader">New Ticket</td></tr>
	  <tr><td class="fieldname" style="width:110px;">Subject</td><td class="fieldarea"><input type="text" name="subject" class="text" size="50" /></td></tr>
	  <tr><td class="fieldname">Priority</td><td class="fieldarea"><select name="priority" class="select"><option value="low">Low</option><option value="normal" selected="selected">Normal</option><option value="high">High</option></select></td></tr>
	  <tr><td class="fieldname">Message</td><td class="fieldarea"><textarea name="body" class="textarea" rows="6" cols="70"></textarea></td></tr>
	</table>
  </fieldset>
  <div align="center"><input type="submit" value="Open Ticket" class="button green" /></div>
</form>
