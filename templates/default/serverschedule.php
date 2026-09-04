<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$sid = (int) ($srv['serverid'] ?? 0);
$schedules = $schedules ?? [];
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Schedules &mdash; <?= htmlspecialchars($srv['name'] ?? '') ?></h1></td>
	  <td align="right"><input type="button" value="Server Details" class="button blue" onclick="window.location='serversummary.php?id=<?= $sid ?>'" /></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th>Label</th><th>When</th><th>Next Run</th><th width="70">State</th><th width="140"></th></tr>
  <?php if (empty($schedules)): ?>
	<tr><td colspan="5"><b>No schedules yet.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($schedules as $s): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td><b><?= htmlspecialchars($s['label']) ?></b></td>
	  <td><?= htmlspecialchars($s['summary']) ?></td>
	  <td><?= $s['enabled'] === '1' && !empty($s['nextrun']) ? htmlspecialchars(date('D d M, H:i', strtotime((string) $s['nextrun']))) : '&mdash;' ?></td>
	  <td><?= $s['enabled'] === '1' ? '<span style="color:#669933;font-weight:bold;">On</span>' : '<span style="color:#999;">Off</span>' ?></td>
	  <td>
		<a href="serverscheduleprocess.php?task=toggle&amp;serverid=<?= $sid ?>&amp;schedid=<?= (int) $s['schedid'] ?>"><?= $s['enabled'] === '1' ? 'Pause' : 'Enable' ?></a> |
		<a href="serverscheduleprocess.php?task=delete&amp;serverid=<?= $sid ?>&amp;schedid=<?= (int) $s['schedid'] ?>" onclick="return confirm('Delete this schedule?');">Delete</a>
	  </td>
	</tr>
  <?php endforeach; ?>
</table>

<br />

<form method="post" action="serverscheduleprocess.php">
  <input type="hidden" name="task" value="create" />
  <input type="hidden" name="serverid" value="<?= $sid ?>" />
  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td colspan="2" class="fieldheader">New Schedule</td></tr>
	  <tr><td class="fieldname" style="width:120px;">Label</td><td class="fieldarea"><input type="text" name="label" class="text" size="30" placeholder="Nightly restart" /></td></tr>
	  <tr><td class="fieldname">Action</td><td class="fieldarea"><select name="action" class="select">
		<option value="restart">Restart server</option><option value="stop">Stop server</option>
		<option value="start">Start server</option><option value="command">Run console command</option><option value="backup">Create a backup</option>
	  </select></td></tr>
	  <tr><td class="fieldname">Command</td><td class="fieldarea"><input type="text" name="command" class="text" size="40" placeholder="only for 'Run console command'" /></td></tr>
	  <tr><td class="fieldname">Frequency</td><td class="fieldarea"><select name="freq" class="select">
		<option value="daily">Every day</option><option value="hourly">Every hour</option><option value="weekly">Every week</option>
	  </select></td></tr>
	  <tr><td class="fieldname">Weekly day</td><td class="fieldarea"><select name="dow" class="select">
		<option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option>
		<option value="5">Friday</option><option value="6">Saturday</option><option value="0">Sunday</option>
	  </select> <font color="#666666" size="-2">(weekly only)</font></td></tr>
	  <tr><td class="fieldname">Time</td><td class="fieldarea">
		<input type="text" name="at_hour" class="text" size="3" value="5" /> :
		<input type="text" name="at_minute" class="text" size="3" value="00" />
		<font color="#666666" size="-2">hour (0-23) : minute (0-59). Hourly uses the minute only.</font>
	  </td></tr>
	</table>
  </fieldset>
  <div align="center"><input type="submit" value="Add Schedule" class="button green" /></div>
</form>
