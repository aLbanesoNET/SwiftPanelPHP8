<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $posts = $posts ?? []; $tid = (int) $ticket['ticketid']; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1><?= htmlspecialchars($ticket['subject']) ?> <span style="font-size:13px;color:#888;">#<?= $tid ?> &mdash; <?= htmlspecialchars($ticket['status']) ?></span></h1></td>
	  <td align="right"><input type="button" value="All Tickets" class="button blue" onclick="window.location='tickets.php'" />
	  <?php if ($ticket['status'] !== 'closed'): ?>
	  <input type="button" value="Close" class="button red" onclick="if(confirm('Close this ticket?')){window.location='ticketprocess.php?task=close&amp;ticketid=<?= $tid ?>';}" />
	  <?php endif; ?></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
  <fieldset style="margin-bottom:10px;<?= $p['author'] === 'staff' ? 'background:#f2f7f2;' : '' ?>">
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td class="fieldheader"><?= $p['author'] === 'staff' ? 'Support' : htmlspecialchars($p['name']) ?> &mdash; <?= htmlspecialchars(date('M j, Y H:i', strtotime((string) $p['created']))) ?></td></tr>
	  <tr><td style="padding:8px 10px;"><?= nl2br(htmlspecialchars(trim((string) $p['body']))) ?></td></tr>
	</table>
  </fieldset>
<?php endforeach; ?>

<form method="post" action="ticketprocess.php">
  <input type="hidden" name="task" value="reply" />
  <input type="hidden" name="ticketid" value="<?= $tid ?>" />
  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td class="fieldheader"><?= $ticket['status'] === 'closed' ? 'Reopen With a Reply' : 'Reply' ?></td></tr>
	  <tr><td><textarea name="body" class="textarea" rows="5" cols="80"></textarea></td></tr>
	</table>
  </fieldset>
  <div align="center"><input type="submit" value="Send Reply" class="button green" /></div>
</form>
