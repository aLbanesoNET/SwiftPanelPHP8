<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-login" data-view="login">
<?php
// expects: $lockout, $task, $login_error, $success, $return, $email, $password, $remember_me
$task = $task ?? '';
?>
<div class="graphite-auth">
	<div class="graphite-auth-brand">
		<span class="mark">&#9656;</span> <?= htmlspecialchars(SITENAME) ?>
	</div>

	<?php if (!empty($twofa) && !empty($lockout)): ?>
		<div class="graphite-note graphite-note-warn">
			<strong>Too many incorrect codes</strong>
			<span>Please wait 5 minutes before trying again.</span>
		</div>

	<?php elseif (!empty($twofa)): ?>
		<h1 class="graphite-auth-title">Verification</h1>
		<?php if (!empty($login_error)): ?>
			<div class="graphite-note graphite-note-bad"><strong>Wrong or expired code</strong><span>Try the current 6-digit code from your app.</span></div>
		<?php endif; ?>
		<form action="process.php" method="post" class="graphite-auth-form">
			<input type="hidden" name="task" value="login2fa">
			<label class="graphite-field">
				<span>Authenticator or recovery code</span>
				<input type="text" name="totpcode" autocomplete="one-time-code" maxlength="9" autofocus>
			</label>
			<button type="submit" class="graphite-btn graphite-btn-full">Verify</button>
		</form>
		<a class="graphite-auth-link" href="login.php">Cancel</a>

	<?php elseif (!empty($lockout)): ?>
		<div class="graphite-note graphite-note-warn">
			<strong>Too many incorrect attempts</strong>
			<span>Please wait 5 minutes before trying again.</span>
		</div>

	<?php elseif ($task !== 'password'): ?>
		<h1 class="graphite-auth-title">Sign in</h1>

		<?php if (!empty($login_error)): ?>
			<div class="graphite-note graphite-note-bad">
				<strong>Login failed</strong>
				<span>Your IP has been logged and admins notified.</span>
			</div>
		<?php endif; ?>

		<form action="process.php" method="post" class="graphite-auth-form">
			<input type="hidden" name="task" value="login">
			<input type="hidden" name="return" value="<?= htmlspecialchars($return ?? '') ?>">

			<label class="graphite-field">
				<span>Email</span>
				<input type="text" name="email" autocomplete="username" value="<?= htmlspecialchars($email ?? '') ?>">
			</label>
			<label class="graphite-field">
				<span>Password</span>
				<input type="password" name="password" autocomplete="current-password" value="">
			</label>

			<label class="graphite-check">
				<input type="checkbox" name="rememberme" <?= !empty($remember_me) ? 'checked' : '' ?>>
				Remember my email
			</label>

			<button type="submit" class="graphite-btn graphite-btn-full">Sign in</button>
		</form>

		<a class="graphite-auth-link" href="login.php?task=password">Forgot password?</a>

	<?php else: ?>
		<h1 class="graphite-auth-title">Reset password</h1>

		<?php if (($success ?? '') === 'Yes'): ?>
			<div class="graphite-note graphite-note-ok"><strong>Password sent</strong><span>Check your inbox for the new password.</span></div>
		<?php elseif (($success ?? '') === 'No'): ?>
			<div class="graphite-note graphite-note-bad"><strong>Email not found</strong><span>Your IP has been logged.</span></div>
		<?php endif; ?>

		<form action="process.php" method="post" class="graphite-auth-form">
			<input type="hidden" name="task" value="password">
			<label class="graphite-field">
				<span>Email</span>
				<input type="text" name="email" autocomplete="username" value="">
			</label>
			<button type="submit" class="graphite-btn graphite-btn-full">Send new password</button>
		</form>

		<a class="graphite-auth-link" href="login.php">Back to sign in</a>
	<?php endif; ?>
</div>

</section>
