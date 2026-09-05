<?php
$title = "Admin Login";
$page = "login";
require "../configuration.php";
include "./include.php";
$task = sanitizeInput($_GET["task"] ?? "");
$formReturn   = safeReturnPath($_GET["return"] ?? "");
$formUsername = (string) ($_GET["username"] ?? ($_COOKIE["adminusername"] ?? ""));
$rememberChecked = (($_COOKIE["rememberme"] ?? "") == "on");
$lockout = (!empty($_SESSION["lockout"]) && time() - 60 * 10 < $_SESSION["lockout"]);
$loginError = isset($_SESSION["loginerror"]);
if ($loginError) {
	unset($_SESSION["loginerror"]);
}
$success = $_SESSION["success"] ?? "";
if ($success === "No") {
	unset($_SESSION["success"]);
}
$twofa = ($task === "2fa" && !empty($_SESSION["a2fa_id"]));
if ($task === "2fa" && empty($_SESSION["a2fa_id"])) {
	header("Location: login.php");
	exit;
}
include "./templates/" . TEMPLATE . "/header.php";
?>
<?php if ($twofa && $lockout): ?>
<div align="center">
<div align="center" style="width:400px;background-color:#FCF9D2;border:1px solid #F9D43E;padding:10px;"><strong>Too Many Incorrect Codes</strong><br />
	  Please wait 10 minutes before trying again.</div>
</div>
<?php elseif ($twofa): ?>
<div align="center">
  <?php if ($loginError): ?>
	<div align="center" style="width:400px;background-color:#FCF9D2;border:1px solid #F9D43E;padding:10px;"><strong>Wrong or expired code.</strong><br />Enter the current 6-digit code from your authenticator app.</div><br />
  <?php endif; ?>
  <form action="process.php" method="post">
	<input type="hidden" name="task" value="login2fa" />
	<table border="0" cellpadding="0" cellspacing="10">
	  <tr><td align="right">Code:</td><td><input type="text" name="totpcode" class="text" size="12" maxlength="6" autocomplete="one-time-code" autofocus /></td></tr>
	  <tr><td colspan="2" align="center"><input type="submit" value="Verify" class="button" /></td></tr>
	</table>
  </form>
  <br /><a href="login.php">Cancel</a>
</div>
<?php elseif ($lockout): ?>
<div align="center">
<div align="center" style="width:400px;background-color:#FCF9D2;border:1px solid #F9D43E;padding:10px;"><strong>Too Many Incorrect Login Attempts</strong><br />
	  Please wait 10 minutes before trying again.</div>
</div>
<?php elseif ($task != "password"): ?>
<div align="center">
  <?php if ($loginError): ?>
	<div align="center" style="width:500px;background-color: #FCF9D2;border: 1px solid #F9D43E;padding:10px;"><strong>Login Failed. Please Try Again.</strong><br />
	  Your IP (<?= htmlspecialchars($_SERVER["REMOTE_ADDR"] ?? "", ENT_QUOTES, "UTF-8") ?>) has been logged and admins notified of this failed attempt.</div>
	<br />
  <?php endif; ?>
  <form action="process.php" method="post">
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?= htmlspecialchars($formReturn, ENT_QUOTES, "UTF-8") ?>" />
	<table border="0" cellpadding="0" cellspacing="10">
	  <tr>
		<td align="right">Username:</td>
		<td><input type="text" name="username" class="text" size="30" value="<?= htmlspecialchars($formUsername, ENT_QUOTES, "UTF-8") ?>" /></td>
	  </tr>
	  <tr>
		<td align="right">Password:</td>
		<td><input type="password" name="password" class="text" size="30" value="" /></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center"><label for="rememberme"><input type="checkbox" name="rememberme" id="rememberme"<?= $rememberChecked ? ' checked="checked"' : '' ?> /> Remember my username</label></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center"><input type="submit" value="Login" class="button" /></td>
	  </tr>
	</table>
  </form>
  <br />
  <a href="login.php?task=password">Forgot Password?</a>
</div>
<?php else: ?>
<div align="center">
  <?php if ($success == "Yes"): ?>
	<div align="center" style="width:400px;background-color: #FCF9D2;border: 1px solid #F9D43E;padding:10px;"><strong>Password Sent.</strong><br />
	  Your password has been reset and emailed to you.</div>
	<br />
  <?php elseif ($success == "No"): ?>
	<div align="center" style="width:500px;background-color: #FCF9D2;border: 1px solid #F9D43E;padding:10px;"><strong>Username Not Found.</strong><br />
	  Your IP (<?= htmlspecialchars($_SERVER["REMOTE_ADDR"] ?? "", ENT_QUOTES, "UTF-8") ?>) has been logged and admins notified of this failed attempt.</div>
	<br />
  <?php endif; ?>
  <form action="process.php" method="post">
	<input type="hidden" name="task" value="password" />
	<table border="0" cellpadding="0" cellspacing="10">
	  <tr>
		<td align="right">Username:</td>
		<td><input type="text" name="username" class="text" size="30" value="" /></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center"><input type="submit" value="Send Password" class="button" /></td>
	  </tr>
	</table>
  </form>
  <br />
  <a href="login.php">Back to Login</a>
</div>
<?php endif; ?>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
