<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $lockout, $task, $login_error, $success, $return, $email, $password, $remember_me
$task = $task ?? '';
?>
<div class="fp-auth">
	<div class="fp-auth-brand">
		<span class="mark">&#9656;</span> <?= htmlspecialchars(SITENAME) ?>
	</div>

	<?php if (!empty($lockout)): ?>
		<div class="fp-note fp-note-warn">
			<strong>Too many incorrect attempts</strong>
			<span>Please wait 5 minutes before trying again.</span>
		</div>

	<?php elseif ($task !== 'password'): ?>
		<h1 class="fp-auth-title">Sign in</h1>

		<?php if (!empty($login_error)): ?>
			<div class="fp-note fp-note-bad">
				<strong>Login failed</strong>
				<span>Your IP has been logged and admins notified.</span>
			</div>
		<?php endif; ?>

		<form action="process.php" method="post" class="fp-auth-form">
			<input type="hidden" name="task" value="login">
			<input type="hidden" name="return" value="<?= htmlspecialchars($return ?? '') ?>">

			<label class="fp-field">
				<span>Email</span>
				<input type="text" name="email" autocomplete="username" value="<?= htmlspecialchars($email ?? '') ?>">
			</label>
			<label class="fp-field">
				<span>Password</span>
				<input type="password" name="password" autocomplete="current-password" value="<?= htmlspecialchars($password ?? '') ?>">
			</label>

			<label class="fp-check">
				<input type="checkbox" name="rememberme" <?= !empty($remember_me) ? 'checked' : '' ?>>
				Remember my email
			</label>

			<button type="submit" class="fp-btn fp-btn-full">Sign in</button>
		</form>

		<a class="fp-auth-link" href="login.php?task=password">Forgot password?</a>

	<?php else: ?>
		<h1 class="fp-auth-title">Reset password</h1>

		<?php if (($success ?? '') === 'Yes'): ?>
			<div class="fp-note fp-note-ok"><strong>Password sent</strong><span>Check your inbox for the new password.</span></div>
		<?php elseif (($success ?? '') === 'No'): ?>
			<div class="fp-note fp-note-bad"><strong>Email not found</strong><span>Your IP has been logged.</span></div>
		<?php endif; ?>

		<form action="process.php" method="post" class="fp-auth-form">
			<input type="hidden" name="task" value="password">
			<label class="fp-field">
				<span>Email</span>
				<input type="text" name="email" autocomplete="username" value="">
			</label>
			<button type="submit" class="fp-btn fp-btn-full">Send new password</button>
		</form>

		<a class="fp-auth-link" href="login.php">Back to sign in</a>
	<?php endif; ?>
</div>
