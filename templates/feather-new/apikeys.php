<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $keys = $keys ?? []; ?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok"><strong><?= htmlspecialchars($msg1) ?></strong><span><?= htmlspecialchars($msg2 ?? '') ?></span></div>
<?php endif; ?>

<section class="fp-hero">
	<div class="fp-hero-text">
		<h1>API keys</h1>
		<p>Use a bearer token to control your servers from scripts. Base URL <code><?= htmlspecialchars((($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/'))) ?>/api.php</code>
		&mdash; <a href="api-test.php">try it out &rsaquo;</a></p>
	</div>
</section>

<?php if (!empty($newToken)): ?>
	<div class="fp-card" style="border-color:rgba(var(--glow),.4);">
		<div class="fp-card-head"><h2>New token &mdash; copy it now</h2></div>
		<p><code style="display:block;padding:10px 12px;border:1px solid var(--line-strong);border-radius:var(--r-sm);background:var(--bg-1);word-break:break-all;"><?= htmlspecialchars($newToken) ?></code></p>
		<p style="color:var(--faint);font-size:11px;">This is the only time the full token is shown.</p>
	</div>
<?php endif; ?>

<div class="fp-section-head"><h2>Your keys</h2><span class="fp-count"><?= count($keys) ?></span></div>

<?php if (empty($keys)): ?>
	<div class="fp-empty">No API keys yet.</div>
<?php else: ?>
	<div class="fp-table">
		<div class="fp-tr fp-th" style="grid-template-columns:1.4fr 1fr 0.8fr 1fr 90px;">
			<span>Label</span><span>Prefix</span><span>Scope</span><span>Last used</span><span></span>
		</div>
		<?php foreach ($keys as $k): ?>
			<div class="fp-tr" style="grid-template-columns:1.4fr 1fr 0.8fr 1fr 90px;">
				<span><strong><?= htmlspecialchars($k['label']) ?></strong></span>
				<span><code><?= htmlspecialchars($k['prefix']) ?>&hellip;</code></span>
				<span class="fp-pill fp-pill-mono"><?= $k['readonly'] === '1' ? 'read' : 'read+write' ?></span>
				<span class="fp-srv-meta"><?= $k['lastused'] ? htmlspecialchars(date('M j, H:i', strtotime((string) $k['lastused']))) : 'never' ?></span>
				<span class="fp-c-act">
					<a class="fp-ibtn fp-ibtn-stop" href="apikeysprocess.php?task=revoke&amp;keyid=<?= (int) $k['keyid'] ?>" title="Revoke" onclick="return confirm('Revoke this key?');">&#215;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<form method="post" action="apikeysprocess.php" class="fp-card fp-form" style="max-width:520px;margin-top:20px;">
	<input type="hidden" name="task" value="create">
	<div class="fp-card-head"><h2>New key</h2></div>
	<label class="fp-field"><span>Label</span><input type="text" name="label" placeholder="deploy script"></label>
	<label class="fp-check" style="margin-top:10px;"><input type="checkbox" name="readonly" value="1" checked> Read-only (no power actions)</label>
	<div class="fp-form-actions"><button type="submit" class="fp-btn">Create key</button></div>
</form>
