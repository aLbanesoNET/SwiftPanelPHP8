<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $msg1, $msg2, $firstname, $lastname, $email
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr>
	<td align="left"><h1>My Account</h1></td>
  </tr>
</table>

<form method="post" action="profileprocess.php">
  <input type="hidden" name="task" value="profile" />

  <?php if ($msg1): ?>
	<div id="infobox">
	  <strong><?= htmlspecialchars($msg1) ?></strong><br />
	  <?= htmlspecialchars($msg2) ?>
	</div>
  <?php endif; ?>

  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />

  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr>
		<td class="fieldname" style="width:140px;">First Name</td>
		<td class="fieldarea">
		  <input type="text" name="firstname" class="text" size="25" value="<?= htmlspecialchars($firstname) ?>" />
		</td>
	  </tr>

	  <tr>
		<td class="fieldname">Last Name</td>
		<td class="fieldarea">
		  <input type="text" name="lastname" class="text" size="25" value="<?= htmlspecialchars($lastname) ?>" />
		</td>
	  </tr>

	  <tr>
		<td class="fieldname">Email</td>
		<td class="fieldarea">
		  <input type="text" name="email" class="text" size="35" value="<?= htmlspecialchars($email) ?>" />
		</td>
	  </tr>

	  <tr>
		<td class="fieldname">Password</td>
		<td class="fieldarea">
		  <input type="password" name="password" class="text" size="20" value="" autocomplete="new-password" />
		  <font color="#666666" size="-2">(Leave blank to keep your current password)</font>
		</td>
	  </tr>
	</table>
  </fieldset>

  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1" alt="" /><br />

  <div align="center">
	<input type="submit" value="Save Changes" class="button green" />
	<input type="reset" value="Cancel Changes" class="button red" />
  </div>
</form>

<fieldset>
  <table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr><td colspan="2" class="fieldheader">Two-Factor Authentication (<?= !empty($totpEnabled) ? 'Enabled' : 'Disabled' ?>)</td></tr>
	<?php if (!empty($totpEnabled)): ?>
	<tr><td colspan="2" class="fieldarea">Your account asks for an authenticator code at sign in.
	  <form method="post" action="profileprocess.php" style="margin-top:6px;">
		<input type="hidden" name="task" value="2fa_disable" />
		Current code: <input type="text" name="totpcode" class="text" size="8" maxlength="6" />
		<input type="submit" value="Disable 2FA" class="button red" />
	  </form></td></tr>
	<?php else: ?>
	<tr><td class="fieldname" style="width:140px;">Setup Key</td><td class="fieldarea"><code><?= htmlspecialchars(trim(chunk_split($totpSetup, 4, ' '))) ?></code></td></tr>
	<tr><td class="fieldname">otpauth URI</td><td class="fieldarea"><code style="word-break:break-all;"><?= htmlspecialchars($totpUri) ?></code></td></tr>
	<tr><td colspan="2" class="fieldarea">
	  <form method="post" action="profileprocess.php">
		<input type="hidden" name="task" value="2fa_enable" />
		Add the key to your authenticator app, then enter a code: <input type="text" name="totpcode" class="text" size="8" maxlength="6" />
		<input type="submit" value="Enable 2FA" class="button green" />
	  </form></td></tr>
	<?php endif; ?>
  </table>
</fieldset>

<?php if (!empty($loginHistory)): ?>
<fieldset>
  <table width="100%" cellpadding="2" cellspacing="1" class="data">
	<tr><td colspan="3" class="fieldheader">Recent Sign-ins</td></tr>
	<?php foreach ($loginHistory as $h): ?>
	<tr><td width="140"><?= htmlspecialchars(date('M j, Y H:i', strtotime((string) $h['ts']))) ?></td>
		<td width="120"><code><?= htmlspecialchars($h['ip']) ?></code></td>
		<td><?= $h['method'] === '2fa' ? '2FA' : 'password' ?></td></tr>
	<?php endforeach; ?>
  </table>
</fieldset>
<?php endif; ?>
