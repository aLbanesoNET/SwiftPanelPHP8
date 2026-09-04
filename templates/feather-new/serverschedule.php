<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$sid = (int) ($srv['serverid'] ?? 0);
$schedules = $schedules ?? [];
?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-powerbar">
	<div class="fp-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="fp-pill fp-pill-mono">Schedules</span>
	</div>
	<div class="fp-powerbar-act">
		<a class="fp-btn fp-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="fp-section-head"><h2>Schedules</h2><span class="fp-count"><?= count($schedules) ?></span></div>

<?php if (empty($schedules)): ?>
	<div class="fp-empty">No schedules yet. Add one below.</div>
<?php else: ?>
	<div class="fp-table">
		<div class="fp-tr fp-th" style="grid-template-columns:1.4fr 1.6fr 1fr 70px 120px;">
			<span>Label</span><span>When</span><span>Next run</span><span>State</span><span></span>
		</div>
		<?php foreach ($schedules as $s): ?>
			<div class="fp-tr" style="grid-template-columns:1.4fr 1.6fr 1fr 70px 120px;">
				<span><strong><?= htmlspecialchars($s['label']) ?></strong></span>
				<span class="fp-srv-meta"><?= htmlspecialchars($s['summary']) ?></span>
				<span class="fp-srv-meta"><?= $s['enabled'] === '1' && !empty($s['nextrun']) ? htmlspecialchars(date('D H:i', strtotime((string) $s['nextrun']))) : '&mdash;' ?></span>
				<span><span class="fp-pill <?= $s['enabled'] === '1' ? 'fp-pill-ok' : 'fp-pill-mono' ?>"><?= $s['enabled'] === '1' ? 'On' : 'Off' ?></span></span>
				<span class="fp-c-act">
					<a class="fp-ibtn" href="serverscheduleprocess.php?task=toggle&amp;serverid=<?= $sid ?>&amp;schedid=<?= (int) $s['schedid'] ?>" title="<?= $s['enabled'] === '1' ? 'Pause' : 'Enable' ?>"><?= $s['enabled'] === '1' ? '&#10073;&#10073;' : '&#9658;' ?></a>
					<a class="fp-ibtn fp-ibtn-stop" href="serverscheduleprocess.php?task=delete&amp;serverid=<?= $sid ?>&amp;schedid=<?= (int) $s['schedid'] ?>" title="Delete" onclick="return confirm('Delete this schedule?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="serverscheduleprocess.php" class="fp-card fp-form" style="max-width:640px;margin-top:20px;">
	<input type="hidden" name="task" value="create">
	<input type="hidden" name="serverid" value="<?= $sid ?>">
	<div class="fp-card-head"><h2>New schedule</h2></div>
	<div class="fp-form-grid">
		<label class="fp-field fp-field-wide"><span>Label</span><input type="text" name="label" placeholder="Nightly restart"></label>
		<label class="fp-field">
			<span>Action</span>
			<select name="action" onchange="document.getElementById('sch-cmd').style.display=this.value=='command'?'flex':'none';">
				<option value="restart">Restart server</option>
				<option value="stop">Stop server</option>
				<option value="start">Start server</option>
				<option value="command">Run console command</option>
			</select>
		</label>
		<label class="fp-field">
			<span>Frequency</span>
			<select name="freq" onchange="var w=this.value=='weekly';document.getElementById('sch-dow').style.display=w?'flex':'none';document.getElementById('sch-hour').style.display=this.value=='hourly'?'none':'flex';">
				<option value="daily">Every day</option>
				<option value="hourly">Every hour</option>
				<option value="weekly">Every week</option>
			</select>
		</label>
		<label class="fp-field" id="sch-dow" style="display:none;">
			<span>Day</span>
			<select name="dow">
				<option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option>
				<option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option><option value="0">Sunday</option>
			</select>
		</label>
		<label class="fp-field" id="sch-hour"><span>Hour (0&ndash;23)</span><input type="number" name="at_hour" min="0" max="23" value="5"></label>
		<label class="fp-field"><span>Minute (0&ndash;59)</span><input type="number" name="at_minute" min="0" max="59" value="0"></label>
		<label class="fp-field fp-field-wide" id="sch-cmd" style="display:none;"><span>Console command</span><input type="text" name="command" placeholder="say Server restarting in 60s"></label>
	</div>
	<div class="fp-form-actions"><button type="submit" class="fp-btn">Add schedule</button></div>
</form>
