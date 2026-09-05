<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-serverschedule" data-view="serverschedule">
<?php
$sid = (int) ($srv['serverid'] ?? 0);
$schedules = $schedules ?? [];
?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="graphite-powerbar">
	<div class="graphite-powerbar-id">
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="graphite-pill graphite-pill-mono">Schedules</span>
	</div>
	<div class="graphite-powerbar-act">
		<a class="graphite-btn graphite-btn-ghost" href="serversummary.php?id=<?= $sid ?>">Server details</a>
	</div>
</section>

<div class="graphite-section-head"><h2>Schedules</h2><span class="graphite-count"><?= count($schedules) ?></span></div>

<?php if (empty($schedules)): ?>
	<div class="graphite-empty">No schedules yet. Add one below.</div>
<?php else: ?>
	<div class="graphite-table">
		<div class="graphite-tr graphite-th" style="grid-template-columns:1.4fr 1.6fr 1fr 70px 120px;">
			<span>Label</span><span>When</span><span>Next run</span><span>State</span><span></span>
		</div>
		<?php foreach ($schedules as $s): ?>
			<div class="graphite-tr" style="grid-template-columns:1.4fr 1.6fr 1fr 70px 120px;">
				<span><strong><?= htmlspecialchars($s['label']) ?></strong></span>
				<span class="graphite-srv-meta"><?= htmlspecialchars($s['summary']) ?></span>
				<span class="graphite-srv-meta"><?= $s['enabled'] === '1' && !empty($s['nextrun']) ? htmlspecialchars(date('D H:i', strtotime((string) $s['nextrun']))) : '&mdash;' ?></span>
				<span><span class="graphite-pill <?= $s['enabled'] === '1' ? 'graphite-pill-ok' : 'graphite-pill-mono' ?>"><?= $s['enabled'] === '1' ? 'On' : 'Off' ?></span></span>
				<span class="graphite-c-act">
					<a class="graphite-ibtn" href="serverscheduleprocess.php?task=toggle&amp;serverid=<?= $sid ?>&amp;schedid=<?= (int) $s['schedid'] ?>" title="<?= $s['enabled'] === '1' ? 'Pause' : 'Enable' ?>"><?= $s['enabled'] === '1' ? '&#10073;&#10073;' : '&#9658;' ?></a>
					<a class="graphite-ibtn graphite-ibtn-stop" href="serverscheduleprocess.php?task=delete&amp;serverid=<?= $sid ?>&amp;schedid=<?= (int) $s['schedid'] ?>" title="Delete" onclick="return confirm('Delete this schedule?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="serverscheduleprocess.php" class="graphite-card graphite-form" style="max-width:640px;margin-top:20px;">
	<input type="hidden" name="task" value="create">
	<input type="hidden" name="serverid" value="<?= $sid ?>">
	<div class="graphite-card-head"><h2>New schedule</h2></div>
	<div class="graphite-form-grid">
		<label class="graphite-field graphite-field-wide"><span>Label</span><input type="text" name="label" placeholder="Nightly restart"></label>
		<label class="graphite-field">
			<span>Action</span>
			<select name="action" onchange="document.getElementById('sch-cmd').style.display=this.value=='command'?'flex':'none';">
				<option value="restart">Restart server</option>
				<option value="stop">Stop server</option>
				<option value="start">Start server</option>
				<option value="command">Run console command</option>
				<option value="backup">Create a backup</option>
			</select>
		</label>
		<label class="graphite-field">
			<span>Frequency</span>
			<select name="freq" onchange="var w=this.value=='weekly';document.getElementById('sch-dow').style.display=w?'flex':'none';document.getElementById('sch-hour').style.display=this.value=='hourly'?'none':'flex';">
				<option value="daily">Every day</option>
				<option value="hourly">Every hour</option>
				<option value="weekly">Every week</option>
			</select>
		</label>
		<label class="graphite-field" id="sch-dow" style="display:none;">
			<span>Day</span>
			<select name="dow">
				<option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option>
				<option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option><option value="0">Sunday</option>
			</select>
		</label>
		<label class="graphite-field" id="sch-hour"><span>Hour (0&ndash;23)</span><input type="number" name="at_hour" min="0" max="23" value="5"></label>
		<label class="graphite-field"><span>Minute (0&ndash;59)</span><input type="number" name="at_minute" min="0" max="59" value="0"></label>
		<label class="graphite-field graphite-field-wide" id="sch-cmd" style="display:none;"><span>Console command</span><input type="text" name="command" placeholder="say Server restarting in 60s"></label>
	</div>
	<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Add schedule</button></div>
</form>

</section>
