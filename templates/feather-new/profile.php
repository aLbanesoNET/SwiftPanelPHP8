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
