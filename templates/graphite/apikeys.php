<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $keys = $keys ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="graphite-note graphite-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="graphite-hero">
	<div class="graphite-hero-text">
		<h1>API keys</h1>
		<p>Use a bearer token to control your servers from scripts. Base URL <code><?= htmlspecialchars((($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/'))) ?>/api.php</code>
		&mdash; <a href="api-test.php">try it out &rsaquo;</a></p>
	</div>
</section>

<?php if (!empty($newToken)): ?>
	<div class="graphite-card" style="border-color:rgba(var(--glow),.4);">
		<div class="graphite-card-head"><h2>New token &mdash; copy it now</h2></div>
		<p><code style="display:block;padding:10px 12px;border:1px solid var(--line-strong);border-radius:var(--r-sm);background:var(--bg-1);word-break:break-all;"><?= htmlspecialchars($newToken) ?></code></p>
		<p style="color:var(--faint);font-size:11px;">This is the only time the full token is shown.</p>
	</div>
<?php endif; ?>

<div class="graphite-section-head"><h2>Your keys</h2><span class="graphite-count"><?= count($keys) ?></span></div>

<?php if (empty($keys)): ?>
	<div class="graphite-empty">No API keys yet.</div>
<?php else: ?>
	<div class="graphite-table">
		<div class="graphite-tr graphite-th" style="grid-template-columns:1.4fr 1fr 0.8fr 1fr 90px;">
			<span>Label</span><span>Prefix</span><span>Scope</span><span>Last used</span><span></span>
		</div>
		<?php foreach ($keys as $k): ?>
			<div class="graphite-tr" style="grid-template-columns:1.4fr 1fr 0.8fr 1fr 90px;">
				<span><strong><?= htmlspecialchars($k['label']) ?></strong></span>
				<span><code><?= htmlspecialchars($k['prefix']) ?>&hellip;</code></span>
				<span class="graphite-pill graphite-pill-mono"><?= $k['readonly'] === '1' ? 'read' : 'read+write' ?></span>
				<span class="graphite-srv-meta"><?= $k['lastused'] ? htmlspecialchars(date('M j, H:i', strtotime((string) $k['lastused']))) : 'never' ?></span>
				<span class="graphite-c-act">
					<a class="graphite-ibtn graphite-ibtn-stop" href="apikeysprocess.php?task=revoke&amp;keyid=<?= (int) $k['keyid'] ?>" title="Revoke" onclick="return confirm('Revoke this key?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="apikeysprocess.php" class="graphite-card graphite-form" style="max-width:520px;margin-top:20px;">
	<input type="hidden" name="task" value="create">
	<div class="graphite-card-head"><h2>New key</h2></div>
	<label class="graphite-field"><span>Label</span><input type="text" name="label" placeholder="deploy script"></label>
	<label class="graphite-check" style="margin-top:10px;"><input type="checkbox" name="readonly" value="1" checked> Read-only (no power actions)</label>
	<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Create key</button></div>
</form>
