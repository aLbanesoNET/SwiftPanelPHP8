<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/totp.php';

$task = sanitizeInput($_POST["task"] ?? $_GET["task"] ?? "");

$clientId = (int) ($_SESSION["clientid"] ?? 0);
if (!$clientId) {
	header("Location: login.php");
	exit;
}

if ($task === "2fa_enable") {
	$secret = (string) ($_SESSION["2fa_setup"] ?? "");
	$code   = sanitizeInput($_POST["totpcode"] ?? "");
	if ($secret !== "" && totpVerify($secret, $code)) {
		dbExec("UPDATE `client` SET `totp` = '" . dbEscape($secret) . "' WHERE `clientid` = '" . $clientId . "'");
		unset($_SESSION["2fa_setup"]);
		$_SESSION["msg1"] = "Two-factor enabled";
		$_SESSION["msg2"] = "You will be asked for a code at your next sign in.";
	} else {
		$_SESSION["msg1"] = "Could not enable two-factor";
		$_SESSION["msg2"] = "That code did not match. Try again with the current code.";
	}
	header("Location: profile.php");
	exit;
}

if ($task === "2fa_disable") {
	$cur  = dbRow("SELECT `totp` FROM `client` WHERE `clientid` = '" . $clientId . "' LIMIT 1", true);
	$code = sanitizeInput($_POST["totpcode"] ?? "");
	if (is_array($cur) && !empty($cur["totp"]) && totpVerify((string) $cur["totp"], $code)) {
		dbExec("UPDATE `client` SET `totp` = '' WHERE `clientid` = '" . $clientId . "'");
		$_SESSION["msg1"] = "Two-factor disabled";
		$_SESSION["msg2"] = "Your account no longer requires a code to sign in.";
	} else {
		$_SESSION["msg1"] = "Could not disable two-factor";
		$_SESSION["msg2"] = "Enter a current code from your authenticator app to confirm.";
	}
	header("Location: profile.php");
	exit;
}

if ($task !== "profile") {
	header("Location: index.php");
	exit;
}

$clientid  = $_SESSION["clientid"];
$firstname = ucfirst(sanitizeInput($_POST["firstname"]));
$lastname  = ucfirst(sanitizeInput($_POST["lastname"]));
$email	 = strtolower(sanitizeInput($_POST["email"]));
$password  = sanitizeInput($_POST["password"]);

unset($_SESSION["msg1"], $_SESSION["msg2"]);

$_SESSION["firstname"] = $firstname;
$_SESSION["lastname"]  = $lastname;
$_SESSION["email"]	 = $email;
$_SESSION["password"]  = $password;

if (strlen($firstname) === 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>First Name [ <b>Not Entered</b> ]</li>";
}

if (strlen($lastname) === 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Last Name [ <b>Not Entered</b> ]</li>";
}

if (strlen($email) === 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Entered</b> ]</li>";
} elseif (strlen($email) <= 2) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Long Enough</b> ]</li>";
}

if (dbCount("SELECT clientid FROM client WHERE email='$email' AND clientid!='$clientid'") > 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Already Used</b> ]</li>";
}

if (strlen($password) > 0 && strlen($password) < 4) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
}

if (!empty($_SESSION["msg2"])) {
	$_SESSION["msg1"] = "Validation Error!";
	header("Location: profile.php");
	exit;
}

unset(
	$_SESSION["firstname"],
	$_SESSION["lastname"],
	$_SESSION["email"],
	$_SESSION["password"]
);

if ($password === "") {
	// Leave the password untouched when the field is left blank.
	dbExec(
		"UPDATE client
		 SET firstname='$firstname',
			 lastname='$lastname',
			 email='$email'
		 WHERE clientid='$clientid'"
	);
} else {
	$hashed = hashPassword($password);
	dbExec(
		"UPDATE client
		 SET firstname='$firstname',
			 lastname='$lastname',
			 email='$email',
			 password='$hashed'
		 WHERE clientid='$clientid'"
	);
}

$message = "Client Edited: <a href=\"clientsummary.php?id=$clientid\">$firstname $lastname</a>";

dbExec(
	"INSERT INTO log 
	 SET clientid='$clientid',
		 message='$message',
		 name='{$_SESSION["clientfirstname"]} {$_SESSION["clientlastname"]}',
		 ip='{$_SERVER["REMOTE_ADDR"]}'"
);

$_SESSION["msg1"] = "Profile Updated Successfully!";
$_SESSION["msg2"] = "Your changes to your profile have been saved.";

header("Location: index.php");
exit;
