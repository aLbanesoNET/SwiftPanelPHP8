<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $srv (array), $query (array|null), $e_msg1, $e_msg2, $isOwner
$srv     = $srv ?? [];
$isOwner = $isOwner ?? true;
$sid     = (int)($srv['serverid'] ?? 0);
$online = $srv['online'] ?? '';
$status = $srv['status'] ?? '';

$onlineCls = $online === 'Started' ? 'ok' : ($online === 'Pending' ? 'warn' : 'bad');
$statusCls = $status === 'Active'  ? 'ok' : ($status === 'Pending' ? 'warn' : 'bad');

$anyEditable = false;
for ($i = 1; $i <= 8; $i++) {
	if (!empty($srv["cfg{$i}edit"])) { $anyEditable = true; break; }
}
?>
<?php if (!empty($e_msg1)): ?>
	<div class="fp-note fp-note-ok">
		<strong><?= htmlspecialchars($e_msg1) ?></strong>
		<span><?= htmlspecialchars($e_msg2 ?? '') ?></span>
	</div>
<?php endif; ?>

<section class="fp-powerbar">
	<div class="fp-powerbar-id">
		<span class="fp-dot fp-dot-<?= $online === 'Started' ? 'ok' : ($online === 'Pending' ? 'warn' : 'off') ?>"></span>
		<h1><?= htmlspecialchars($srv['name'] ?? ('Server #' . $sid)) ?></h1>
		<span class="fp-pill fp-pill-<?= $statusCls ?>"><?= htmlspecialchars($status) ?></span>
		<span class="fp-pill fp-pill-mono">#<?= $sid ?></span>
	</div>

	<div class="fp-powerbar-act">
		<?php if ($status === 'Active' && $online === 'Stopped'): ?>
			<a class="fp-btn fp-btn-go" href="servermanage.php?task=start&amp;serverid=<?= $sid ?>">&#9658; Start</a>
			<?php if (!empty($srv['installdir'])): ?>
				<a class="fp-btn fp-btn-ghost" href="#"
				   onclick="if(confirm('Reinstall server #<?= $sid ?> - <?= htmlspecialchars(addslashes((string)($srv['name'] ?? '')), ENT_QUOTES) ?>?\n\nEvery file in the server directory will be deleted and replaced with a fresh copy.')){window.location='serverrebuild.php?task=serverrebuild&serverid=<?= $sid ?>';}return false;">Reinstall</a>
			<?php endif; ?>
		<?php elseif ($status === 'Active' && $online === 'Started'): ?>
			<a class="fp-btn" href="servermanage.php?task=restart&amp;serverid=<?= $sid ?>">&#8635; Restart</a>
			<a class="fp-btn fp-btn-stop" href="servermanage.php?task=stop&amp;serverid=<?= $sid ?>">&#9632; Stop</a>
		<?php endif; ?>

		<a class="fp-btn fp-btn-ghost" href="serverplayers.php?id=<?= $sid ?>">Players</a>
		<?php if ($isOwner): ?>
			<?php if (!empty($srv['webftp'])): ?>
				<a class="fp-btn fp-btn-ghost" href="serverftp.php?id=<?= $sid ?>">Web FTP</a>
			<?php endif; ?>
			<a class="fp-btn fp-btn-ghost" href="serverschedule.php?id=<?= $sid ?>">Schedules</a>
			<a class="fp-btn fp-btn-ghost" href="serverbackup.php?id=<?= $sid ?>">Backups</a>
			<a class="fp-btn fp-btn-ghost" href="serversubusers.php?id=<?= $sid ?>">Share</a>
		<?php endif; ?>
	</div>
</section>
<?php if (!$isOwner): ?>
	<div class="fp-note fp-note-ok"><strong>Shared with you</strong><span>You can view this server, use the console and start/stop it.</span></div>
<?php endif; ?>

<div class="fp-grid fp-grid-2">
	<div class="fp-col">
		<div class="fp-card">
			<div class="fp-card-head"><h2>Server information</h2></div>
			<dl class="fp-dl">
				<dt>Name</dt><dd><?= htmlspecialchars($srv['name'] ?? '') ?></dd>
				<dt>Game</dt><dd><?= htmlspecialchars($srv['game'] ?? '') ?></dd>
				<?php if (!empty($srv['boxlocation'])): ?>
					<dt>Location</dt><dd><?= htmlspecialchars($srv['boxlocation']) ?></dd>
				<?php endif; ?>
				<?php if (!empty($srv['disksize'])): ?>
					<dt>Disk used</dt><dd><?= number_format(((int) $srv['disksize']) / 1048576, 1) ?> MB</dd>
				<?php endif; ?>
				<dt>Status</dt><dd><span class="fp-pill fp-pill-<?= $statusCls ?>"><?= htmlspecialchars($status) ?></span></dd>
			</dl>
		</div>

		<form method="post" action="serverprocess.php" class="fp-card">
			<input type="hidden" name="task" value="serveredit">
			<input type="hidden" name="serverid" value="<?= $sid ?>">

			<div class="fp-card-head"><h2>Server configuration</h2></div>
			<dl class="fp-dl">
				<dt>Name</dt><dd><?php if ($isOwner): ?><input type="text" name="name" value="<?= htmlspecialchars($srv['name'] ?? '') ?>"><?php else: ?><?= htmlspecialchars($srv['name'] ?? '') ?><?php endif; ?></dd>
				<dt>Max slots</dt><dd><?= htmlspecialchars((string)($srv['slots'] ?? '')) ?></dd>
				<dt>Type</dt><dd><?= htmlspecialchars($srv['type'] ?? '') ?></dd>

				<?php for ($i = 1; $i <= 8; $i++): ?>
					<?php if (!empty($srv["cfg{$i}name"])): ?>
						<dt><?= htmlspecialchars($srv["cfg{$i}name"]) ?></dt>
						<dd>
							<?php if (!empty($srv["cfg{$i}edit"])): ?>
								<input type="text" name="cfg<?= $i ?>" value="<?= htmlspecialchars($srv["cfg{$i}"] ?? '') ?>">
							<?php else: ?>
								<?= htmlspecialchars($srv["cfg{$i}"] ?? '') ?>
							<?php endif; ?>
						</dd>
					<?php endif; ?>
				<?php endfor; ?>
			</dl>

			<?php if ($isOwner): ?>
			<div class="fp-form-actions">
				<button type="submit" class="fp-btn">Save changes</button>
				<button type="reset" class="fp-btn fp-btn-ghost">Cancel</button>
			</div>
			<?php endif; ?>
		</form>
	</div>

	<div class="fp-col">
		<?php if (!empty($spark)): ?>
			<div class="fp-card">
				<div class="fp-card-head"><h2>Players &mdash; last 24h</h2></div>
				<div class="fp-spark-wrap"><?= $spark ?></div>
			</div>
		<?php endif; ?>

		<div class="fp-card">
			<div class="fp-card-head">
				<h2>Server status</h2>
				<a class="fp-card-link" href="#" onclick="window.location.reload();return false;">Refresh</a>
			</div>
			<dl class="fp-dl">
				<dt>State</dt><dd><span class="fp-pill fp-pill-<?= $onlineCls ?>"><?= htmlspecialchars($online) ?></span></dd>
				<?php if (!empty($query) && is_array($query)): ?>
					<?php foreach ($query as $k => $v): ?>
						<dt><?= htmlspecialchars((string)$k) ?></dt><dd><?= htmlspecialchars((string)$v) ?></dd>
					<?php endforeach; ?>
				<?php endif; ?>
			</dl>
		</div>

		<?php if (!empty($srv['showftp']) && !empty($srv['ip']) && $isOwner): ?>
			<div class="fp-card">
				<div class="fp-card-head"><h2>FTP details</h2></div>
				<dl class="fp-dl">
					<dt>Host</dt><dd><code><?= htmlspecialchars($srv['ip']) ?>:<?= htmlspecialchars((string)($srv['ftpport'] ?? '')) ?></code></dd>
					<dt>User</dt><dd><code><?= htmlspecialchars($srv['user'] ?? '') ?></code></dd>
					<dt>Password</dt><dd><code><?= htmlspecialchars($srv['password'] ?? '') ?></code></dd>
				</dl>
			</div>
		<?php endif; ?>

		<?php if (!empty($srv['ipid']) && $isOwner): ?>
			<?php
			$fdlToken = (string) ($srv['fastdl'] ?? '');
			$fdlBase  = rtrim(preg_replace('~/[^/]*$~', '', (($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
			$fdlUrl   = $fdlBase . '/fastdl/' . $fdlToken . '/';
			?>
			<div class="fp-card">
				<div class="fp-card-head">
					<h2>FastDL</h2>
					<span class="fp-pill <?= $fdlToken !== '' ? 'fp-pill-ok' : 'fp-pill-mono' ?>"><?= $fdlToken !== '' ? 'On' : 'Off' ?></span>
				</div>
				<?php if ($fdlToken !== ''): ?>
					<p>Players download maps and assets over HTTP from the panel instead of the game server. Put this in your server config:</p>
					<p><code style="display:block;padding:9px 11px;border:1px solid var(--line-strong);border-radius:var(--r-sm);background:var(--bg-1);word-break:break-all;">sv_downloadurl "<?= htmlspecialchars($fdlUrl) ?>"</code></p>
					<p style="color:var(--faint);font-size:11px;">Serves from <code>~/fastdl/</code> on your server. Upload compressed files (<code>.bsp.bz2</code>, <code>.wav</code>, &hellip;) there via Web FTP.</p>
					<div class="fp-form-actions">
						<a class="fp-btn fp-btn-stop" href="fastdlprocess.php?task=disable&amp;serverid=<?= $sid ?>" onclick="return confirm('Turn off FastDL for this server?');">Disable FastDL</a>
					</div>
				<?php else: ?>
					<p>Host your server's downloadable content (maps, models, sounds) over HTTP so players join faster.</p>
					<div class="fp-form-actions">
						<a class="fp-btn" href="fastdlprocess.php?task=enable&amp;serverid=<?= $sid ?>">Enable FastDL</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
$cOnline    = ($online === 'Started');
$cInstalled = !empty($srv['ipid']);
if ($cInstalled):
?>
<div class="fp-card fp-console-card">
	<div class="fp-card-head">
		<h2>Console</h2>
		<label class="fp-check fp-check-sm"><input type="checkbox" id="swConsoleAuto"> Auto-refresh</label>
	</div>
	<pre id="swConsoleOut" class="swconsole">Connecting&hellip;</pre>
	<form onsubmit="return swConsoleSend();" class="fp-console-form">
		<input type="text" id="swConsoleCmd" autocomplete="off" placeholder="command, e.g. status" <?= $cOnline ? '' : 'disabled' ?>>
		<button type="submit" class="fp-btn" <?= $cOnline ? '' : 'disabled' ?>>Send</button>
		<button type="button" class="fp-btn fp-btn-ghost" onclick="swConsoleRefresh()">Refresh</button>
	</form>
</div>
<script type="text/javascript">
(function(){
  var SID=<?= $sid ?>, EP="serverconsole.php";
  var out=document.getElementById('swConsoleOut'),
	  inp=document.getElementById('swConsoleCmd'),
	  auto=document.getElementById('swConsoleAuto'),
	  busy=false;
  function parseJSON(s){
	if(window.JSON&&typeof JSON.parse==='function')return JSON.parse(s);
	if(window.JSON&&typeof JSON.decode==='function')return JSON.decode(s);
	return eval('('+s+')');
  }
  function send(cmd){
	if(busy){return;} busy=true;
	var x=new XMLHttpRequest();
	x.open('POST',EP,true);
	x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	x.onreadystatechange=function(){
	  if(x.readyState!==4){return;} busy=false;
	  var r; try{r=parseJSON(x.responseText);}catch(e){out.textContent='Console error: unexpected response.';return;}
	  if(!r.ok){out.textContent=r.error||'Console error.';return;}
	  out.textContent=r.output; out.scrollTop=out.scrollHeight;
	};
	x.send('id='+SID+'&command='+encodeURIComponent(cmd||''));
  }
  window.swConsoleSend=function(){ var c=inp.value; inp.value=''; send(c); if(c){ setTimeout(function(){ if(!busy){ send(''); } },2500); } return false; };
  window.swConsoleRefresh=function(){ send(''); };
  setInterval(function(){ if(auto&&auto.checked&&!busy){ send(''); } },5000);
  send('');
})();
</script>
<?php endif; ?>
