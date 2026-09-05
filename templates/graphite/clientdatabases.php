<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $databases[], $cfg, $canCreate, $msg1, $msg2
$cid = (int) ($_SESSION['clientid'] ?? 0);
?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok">
		<strong><?= htmlspecialchars($msg1) ?></strong>
		<span><?= htmlspecialchars($msg2 ?? '') ?></span>
	</div>
<?php endif; ?>

<section class="graphite-hero">
	<div class="graphite-hero-text">
		<h1>MySQL databases</h1>
		<p>Credentials for stats plugins, web panels and anything else your servers connect to.
		<?php if ((int) $cfg['max'] > 0): ?>Up to <?= (int) $cfg['max'] ?> per account,<?php endif; ?>
		<?= (int) $cfg['maxsize'] ?> MB each.</p>
	</div>
	<?php if (!empty($cfg['pma'])): ?>
		<a class="graphite-btn graphite-btn-ghost" href="<?= htmlspecialchars($cfg['pma']) ?>" target="_blank" rel="noopener">Open phpMyAdmin &#8599;</a>
	<?php endif; ?>
</section>

<div class="graphite-section-head">
	<h2>Databases</h2>
	<span class="graphite-count"><?= count($databases) ?></span>
</div>

<?php if (empty($databases)): ?>
	<div class="graphite-empty">No databases yet. Create one below.</div>
<?php else: ?>
	<div class="graphite-grid" style="grid-template-columns:repeat(auto-fit,minmax(340px,1fr));">
		<?php foreach ($databases as $d): ?>
			<?php
			$limit = (int) $d['limit_mb'];
			$used  = (float) $d['used_mb'];
			$pct   = $limit > 0 ? min(100, ($used / $limit) * 100) : 0;
			$over  = $limit > 0 && $used > $limit;
			?>
			<div class="graphite-card graphite-dbcard">
				<div class="graphite-card-head">
					<h2><?= htmlspecialchars($d['dbname']) ?></h2>
					<span class="graphite-pill <?= $over ? 'graphite-pill-bad' : 'graphite-pill-ok' ?>"><?= number_format($used, 1) ?> MB</span>
				</div>

				<dl class="graphite-dl">
					<dt>User</dt><dd><code><?= htmlspecialchars($d['dbuser']) ?></code></dd>
					<dt>Password</dt>
					<dd>
						<code class="graphite-secret" data-secret="<?= htmlspecialchars($d['plainpass'], ENT_QUOTES) ?>">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</code>
						<button type="button" class="graphite-reveal" onclick="fpReveal(this)">show</button>
					</dd>
					<dt>Host</dt><dd><code><?= htmlspecialchars($d['dbhost']) ?></code></dd>
				</dl>

				<div class="graphite-meter" title="<?= number_format($used, 1) ?> of <?= $limit ?> MB">
					<span style="width:<?= round($pct, 1) ?>%;<?= $over ? 'background:var(--danger);' : '' ?>"></span>
				</div>
				<div class="graphite-meter-label"><?= number_format($used, 1) ?> / <?= $limit ?> MB<?= $over ? ' &mdash; over limit' : '' ?></div>

				<div class="graphite-form-actions">
					<?php if (!empty($cfg['pma'])): ?>
						<a class="graphite-btn graphite-btn-ghost" href="<?= htmlspecialchars(rtrim($cfg['pma'], '/')) ?>/index.php?db=<?= urlencode($d['dbname']) ?>" target="_blank" rel="noopener">Manage &#8599;</a>
					<?php endif; ?>
					<a class="graphite-btn graphite-btn-ghost" href="clientdatabasesprocess.php?task=resetpw&amp;dbid=<?= (int) $d['dbid'] ?>"
					   onclick="return confirm('Reset the password for <?= htmlspecialchars($d['dbname'], ENT_QUOTES) ?>?');">Reset password</a>
					<a class="graphite-btn graphite-btn-stop" href="clientdatabasesprocess.php?task=delete&amp;dbid=<?= (int) $d['dbid'] ?>"
					   onclick="return confirm('Delete <?= htmlspecialchars($d['dbname'], ENT_QUOTES) ?> and all its data? This cannot be undone.');">Delete</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ($canCreate): ?>
	<form method="post" action="clientdatabasesprocess.php" class="graphite-card graphite-form" style="max-width:520px;margin-top:20px;">
		<input type="hidden" name="task" value="create">
		<div class="graphite-card-head"><h2>Create database</h2></div>
		<label class="graphite-field">
			<span>Name <em>&mdash; prefixed with c<?= $cid ?>_</em></span>
			<input type="text" name="name" maxlength="24" placeholder="stats" pattern="[a-z0-9_]{1,24}">
		</label>
		<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Create</button></div>
	</form>
<?php else: ?>
	<div class="graphite-empty" style="margin-top:20px;">Database limit reached &mdash; delete one to create another.</div>
<?php endif; ?>

<script>
function fpReveal(btn){
	var c = btn.previousElementSibling;
	if (btn.dataset.on === '1') { c.textContent = '••••••••••'; btn.textContent = 'show'; btn.dataset.on = '0'; }
	else { c.textContent = c.dataset.secret; btn.textContent = 'hide'; btn.dataset.on = '1'; }
}
</script>
