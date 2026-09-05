<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $msg1, $msg2, $firstname, $lastname, $email
?>
<?php if (!empty($msg1)): ?>
	<div class="fp-note fp-note-ok">
		<strong><?= htmlspecialchars($msg1) ?></strong>
		<span><?= htmlspecialchars($msg2 ?? '') ?></span>
	</div>
<?php endif; ?>

<form method="post" action="profileprocess.php" class="fp-card fp-form" style="max-width:640px;">
	<input type="hidden" name="task" value="profile">

	<div class="fp-card-head">
		<h2>Profile</h2>
		<p>Your contact details and sign-in password.</p>
	</div>

	<div class="fp-form-grid">
		<label class="fp-field">
			<span>First name</span>
			<input type="text" name="firstname" value="<?= htmlspecialchars($firstname ?? '') ?>">
		</label>
		<label class="fp-field">
			<span>Last name</span>
			<input type="text" name="lastname" value="<?= htmlspecialchars($lastname ?? '') ?>">
		</label>
		<label class="fp-field fp-field-wide">
			<span>Email</span>
			<input type="text" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
		</label>
		<label class="fp-field fp-field-wide">
			<span>Password <em>&mdash; leave blank to keep current</em></span>
			<input type="password" name="password" value="" autocomplete="new-password">
		</label>
	</div>

	<div class="fp-form-actions">
		<button type="submit" class="fp-btn">Save changes</button>
		<button type="reset" class="fp-btn fp-btn-ghost">Cancel</button>
	</div>
</form>

<div class="fp-card fp-form" style="max-width:640px;margin-top:16px;">
	<div class="fp-card-head">
		<h2>Two-factor authentication</h2>
		<span class="fp-pill <?= !empty($totpEnabled) ? 'fp-pill-ok' : 'fp-pill-mono' ?>"><?= !empty($totpEnabled) ? 'On' : 'Off' ?></span>
	</div>

	<?php if (!empty($totpNewCodes)): ?>
		<div class="fp-note fp-note-warn">
			<strong>Recovery codes &mdash; save these now</strong>
			<span>Each works once. This is the only time they are shown.</span>
		</div>
		<div class="fp-recovery">
			<?php foreach ($totpNewCodes as $rc): ?><code><?= htmlspecialchars($rc) ?></code><?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($totpEnabled)): ?>
		<p>Your account asks for an authenticator code at sign in. <strong><?= (int) $totpRecovery ?></strong> recovery code<?= (int) $totpRecovery === 1 ? '' : 's' ?> left.</p>
		<form method="post" action="profileprocess.php" style="margin-bottom:14px;">
			<input type="hidden" name="task" value="2fa_regen">
			<label class="fp-field"><span>Current code &mdash; to get new recovery codes</span><input type="text" name="totpcode" inputmode="numeric" maxlength="6"></label>
			<div class="fp-form-actions"><button type="submit" class="fp-btn fp-btn-ghost">Regenerate recovery codes</button></div>
		</form>
		<form method="post" action="profileprocess.php">
			<input type="hidden" name="task" value="2fa_disable">
			<label class="fp-field"><span>Current code &mdash; to confirm</span><input type="text" name="totpcode" inputmode="numeric" maxlength="6"></label>
			<div class="fp-form-actions"><button type="submit" class="fp-btn fp-btn-stop">Disable 2FA</button></div>
		</form>
	<?php else: ?>
		<p>Add your authenticator app (Google Authenticator, Aegis, 1Password&hellip;), then enter a code to turn it on.</p>
		<div id="totp-qr" style="width:180px;height:180px;margin-bottom:12px;"></div>
		<dl class="fp-dl">
			<dt>Setup key</dt><dd><code><?= htmlspecialchars(trim(chunk_split($totpSetup, 4, ' '))) ?></code></dd>
			<dt>otpauth URI</dt><dd><code style="word-break:break-all;"><?= htmlspecialchars($totpUri) ?></code></dd>
		</dl>
		<form method="post" action="profileprocess.php" style="margin-top:12px;">
			<input type="hidden" name="task" value="2fa_enable">
			<label class="fp-field"><span>Code from your app</span><input type="text" name="totpcode" inputmode="numeric" maxlength="6"></label>
			<div class="fp-form-actions"><button type="submit" class="fp-btn">Enable 2FA</button></div>
		</form>
		<script src="javascript/qrcode.js"></script>
		<script>
		(function () {
			var el = document.getElementById('totp-qr');
			if (!el || typeof qrcode === 'undefined') { return; }
			var qr = qrcode(0, 'M');
			qr.addData(<?= json_encode($totpUri, JSON_HEX_TAG | JSON_HEX_AMP) ?>);
			qr.make();
			el.innerHTML = qr.createSvgTag({cellSize: 5, margin: 4, scalable: true});
			var svg = el.querySelector('svg');
			if (svg) { svg.style.width = '100%'; svg.style.height = '100%'; svg.style.display = 'block'; }
		})();
		</script>
	<?php endif; ?>
</div>

<?php if (!empty($loginHistory)): ?>
	<div class="fp-card" style="max-width:640px;margin-top:16px;">
		<div class="fp-card-head"><h2>Recent sign-ins</h2></div>
		<dl class="fp-dl">
			<?php foreach ($loginHistory as $h): ?>
				<dt><?= htmlspecialchars(date('M j, H:i', strtotime((string) $h['ts']))) ?></dt>
				<dd><code><?= htmlspecialchars($h['ip']) ?></code> &middot; <?= $h['method'] === '2fa' ? '2FA' : 'password' ?><?= !empty($h['agent']) ? ' &middot; ' . htmlspecialchars(substr(preg_replace('/\(.*?\)/', '', $h['agent']), 0, 40)) : '' ?></dd>
			<?php endforeach; ?>
		</dl>
	</div>
<?php endif; ?>
