<?php
$title = "My Account";
$page = "myaccount";
$tab = "7";
$return = "myaccount.php";
$image = "edit_48";
require "../configuration.php";
require "./include.php";
require "../includes/totp.php";
$rows = dbRow("SELECT * FROM `admin` WHERE `adminid` = '" . $_SESSION["adminid"] . "' LIMIT 1");

$totpEnabled = !empty($rows["totp"]);
$totpSetup = "";
$totpUri = "";
if (!$totpEnabled) {
	if (empty($_SESSION["a2fa_setup"])) {
		$_SESSION["a2fa_setup"] = totpSecret();
	}
	$totpSetup = (string) $_SESSION["a2fa_setup"];
	$totpUri = totpUri($totpSetup, (string) $rows["username"], (string) SITENAME . " Admin");
}
if(empty($_SESSION["firstname"])) {
	$_SESSION["firstname"] = $rows["firstname"];
}
if(empty($_SESSION["lastname"])) {
	$_SESSION["lastname"] = $rows["lastname"];
}
if(empty($_SESSION["email"])) {
	$_SESSION["email"] = $rows["email"];
}
if(empty($_SESSION["username"])) {
	$_SESSION["username"] = $rows["username"];
}
$hiddens = array("task" => "myaccount", "adminid" => $rows["adminid"]);
$inputs = array("firstname" => array("text", "First Name", 25), "lastname" => array("text", "Last Name", 25), "email" => array("text", "Email", 35), "username" => array("text", "Username", 25), "password" => array("password", "Password", 20, "(Leave blank for no change)"), "password2" => array("password", "Confirm Password", 20));
$buttons = array("Save Changes" => array("submit"), "Cancel Changes" => array("reset"));
$form = array("process.php", $hiddens, $inputs, $buttons, TRUE);
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
renderForm($form);
?>
<fieldset>
  <table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr><td colspan="2" class="fieldheader">Two-Factor Authentication (<?= $totpEnabled ? "Enabled" : "Disabled" ?>)</td></tr>
	<?php if ($totpEnabled): ?>
	<tr><td colspan="2" class="fieldarea">Your admin login asks for an authenticator code.
	  <form method="post" action="process.php" style="margin-top:6px;">
		<input type="hidden" name="task" value="2fa_disable" />
		Current code: <input type="text" name="totpcode" class="text" size="8" maxlength="6" />
		<input type="submit" value="Disable 2FA" class="button red" />
	  </form></td></tr>
	<?php else: ?>
	<tr><td class="fieldname" style="width:140px;">Setup Key</td><td class="fieldarea"><code><?= htmlspecialchars(trim(chunk_split($totpSetup, 4, " "))) ?></code></td></tr>
	<tr><td class="fieldname">otpauth URI</td><td class="fieldarea"><code style="word-break:break-all;"><?= htmlspecialchars($totpUri) ?></code></td></tr>
	<tr><td colspan="2" class="fieldarea">
	  <form method="post" action="process.php">
		<input type="hidden" name="task" value="2fa_enable" />
		Add the key to your app, then enter a code: <input type="text" name="totpcode" class="text" size="8" maxlength="6" />
		<input type="submit" value="Enable 2FA" class="button green" />
	  </form></td></tr>
	<?php endif; ?>
  </table>
</fieldset>
<?php
include "./templates/" . TEMPLATE . "/footer.php";

?>