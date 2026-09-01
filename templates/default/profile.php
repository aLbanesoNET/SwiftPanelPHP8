<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $e_msg1, $e_msg2, $first_name, $last_name, $email, $password
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr>
	<td align="left"><h1>My Account</h1></td>
  </tr>
</table>

<form method="post" action="profileprocess.php">
  <input type="hidden" name="task" value="profile" />

  <?php if (!empty($e_msg1)): ?>
	<div id="infobox"><strong><?= htmlspecialchars($e_msg1) ?></strong><br /><?= htmlspecialchars($e_msg2 ?? '') ?></div>
  <?php endif; ?>

  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" width="1" height="6" alt="" /><br />

  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr>
		<td class="fieldname" style="width:140px;">First Name</td>
		<td class="fieldarea"><input type="text" name="firstname" class="text" size="25" value="<?= htmlspecialchars($first_name ?? '') ?>" /></td>
	  </tr>
	  <tr>
		<td class="fieldname">Last Name</td>
		<td class="fieldarea"><input type="text" name="lastname" class="text" size="25" value="<?= htmlspecialchars($last_name ?? '') ?>" /></td>
	  </tr>
	  <tr>
		<td class="fieldname">Email</td>
		<td class="fieldarea"><input type="text" name="email" class="text" size="35" value="<?= htmlspecialchars($email ?? '') ?>" /></td>
	  </tr>
	  <tr>
		<td class="fieldname">Password</td>
		<td class="fieldarea">
		  <input type="text" name="password" class="text" size="20" value="<?= htmlspecialchars($password ?? '') ?>" />
		  <font color="#666666" size="-2">(Leave blank for random password)</font>
		</td>
	  </tr>
	</table>
  </fieldset>

  <img src="templates/<?= htmlspecialchars(TEMPLATE) ?>/images/spacer.gif" height="10" width="1" alt="" /><br />
  <div align="center">
	<input type="submit" value="Save Changes" class="button green" />
	<input type="reset" value="Cancel Changes" class="button red" />
  </div>
</form>
