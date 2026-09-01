<?php
$title = "My Account";
$page = "profile";
$return = "profile.php";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$client = dbRow(
	"SELECT * FROM client 
	 WHERE clientid='{$_SESSION["clientid"]}' 
	 LIMIT 1"
);

$firstname = $client["firstname"];
$lastname  = $client["lastname"];
$email	 = $client["email"];
$password  = "";

$msg1 = $_SESSION["msg1"] ?? null;
$msg2 = $_SESSION["msg2"] ?? null;

unset($_SESSION["msg1"], $_SESSION["msg2"]);

include __DIR__ . "/templates/default/header.php";
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

<?php
include __DIR__ . "/templates/default/footer.php";
?>