<?php
$title = "Admin Login";
$page = "login";
require "../configuration.php";
include "./include.php";
$task = sanitizeInput($_GET["task"] ?? "");
$formReturn   = $_GET["return"] ?? "";
$formUsername = $_GET["username"] ?? ($_COOKIE["adminusername"] ?? "");
$formPassword = $_GET["password"] ?? "";
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
include "./templates/" . TEMPLATE . "/header.php";
?>
<?php if ($lockout): ?>
<div align="center">
<div align="center" style="width:400px;background-color:#FCF9D2;border:1px solid #F9D43E;padding:10px;"><strong>Too Many Incorrect Login Attempts</strong><br />
	  Please wait 10 minutes before trying again.</div>
</div>
<?php elseif ($task != "password"): ?>
<div align="center">
  <?php if ($loginError): ?>
	<div align="center" style="width:500px;background-color: #FCF9D2;border: 1px solid #F9D43E;padding:10px;"><strong>Login Failed. Please Try Again.</strong><br />
	  Your IP (<?= $_SERVER["REMOTE_ADDR"] ?>) has been logged and admins notified of this failed attempt.</div>
	<br />
  <?php endif; ?>
  <form action="process.php" method="post">
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?= $formReturn ?>" />
	<table border="0" cellpadding="0" cellspacing="10">
	  <tr>
		<td align="right">Username:</td>
		<td><input type="text" name="username" class="text" size="30" value="<?= $formUsername ?>" /></td>
	  </tr>
	  <tr>
		<td align="right">Password:</td>
		<td><input type="password" name="password" class="text" size="30" value="<?= $formPassword ?>" /></td>
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
	  Your IP (<?= $_SERVER["REMOTE_ADDR"] ?>) has been logged and admins notified of this failed attempt.</div>
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
