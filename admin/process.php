<?php
$postTask = $_POST["task"] ?? "";
if($postTask != "login" && $postTask != "login2fa" && $postTask != "password" && $postTask != "logout") {
	$return = TRUE;
}
require "../configuration.php";
require "./include.php";
require "../includes/totp.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
function adminCompleteLogin(array $rows, string $return, string $rememberme): void
{
	dbExec("UPDATE `admin` SET `lastlogin` = NOW(), `lastip` = '" . dbEscape($_SERVER["REMOTE_ADDR"] ?? "") . "', `lasthost` = '" . dbEscape((string) @gethostbyaddr($_SERVER["REMOTE_ADDR"] ?? "")) . "' WHERE `adminid` = '" . (int) $rows["adminid"] . "'");
	$_SESSION["adminid"] = $rows["adminid"];
	$_SESSION["adminusername"] = $rows["username"];
	$_SESSION["adminfirstname"] = $rows["firstname"];
	$_SESSION["adminlastname"] = $rows["lastname"];
	if ($rememberme == "on") {
		setcookie("adminusername", $rows["username"], time() + 60 * 60 * 24 * 30);
	} else {
		setcookie("adminusername", "", time() + 60 * 60 * 24 * 1);
	}
	unset($_SESSION["loginattempt"], $_SESSION["lockout"], $_SESSION["a2fa_id"], $_SESSION["a2fa_return"], $_SESSION["a2fa_remember"]);
	header("Location: " . (!empty($return) ? $return : "index.php"));
	exit;
}

switch ($task) {
	case "login2fa":
		$code = sanitizeInput($_POST["totpcode"] ?? "");
		$aid  = (int) ($_SESSION["a2fa_id"] ?? 0);
		if ($aid <= 0) { header("Location: login.php"); exit; }
		$rows = dbRow("SELECT `adminid`, `username`, `firstname`, `lastname`, `password`, `totp`, `totp_recovery` FROM `admin` WHERE `adminid` = '" . $aid . "' LIMIT 1", TRUE);
		if ($rows && !empty($rows["totp"])) {
			if (totpVerify((string) $rows["totp"], $code)) {
				adminCompleteLogin($rows, (string) ($_SESSION["a2fa_return"] ?? ""), (string) ($_SESSION["a2fa_remember"] ?? ""));
			}
			$newRec = totpRecoveryConsume((string) ($rows["totp_recovery"] ?? ""), $code);
			if ($newRec !== null) {
				dbExec("UPDATE `admin` SET `totp_recovery` = '" . dbEscape($newRec) . "' WHERE `adminid` = '" . (int) $rows["adminid"] . "'");
				adminCompleteLogin($rows, (string) ($_SESSION["a2fa_return"] ?? ""), (string) ($_SESSION["a2fa_remember"] ?? ""));
			}
		}
		$_SESSION["loginerror"] = TRUE;
		header("Location: login.php?task=2fa");
		exit;

	case "login":
		$username = sanitizeInput($_POST["username"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$return = sanitizeInput($_POST["return"] ?? "");
		$rememberme = sanitizeInput($_POST["rememberme"] ?? "");
		unset($_SESSION["loginerror"]);
		setcookie("rememberme", $rememberme, time() + 60 * 60 * 24 * 30);
		if(!empty($_SESSION["lockout"]) && time() - 60 * 10 < $_SESSION["lockout"]) {
		} elseif(!empty($username) && !empty($password)) {
			$rows = dbRow("SELECT `adminid`, `username`, `firstname`, `lastname`, `password`, `totp` FROM `admin` WHERE `username` = '" . $username . "' AND `status` = 'Active' LIMIT 1", TRUE);
			if($rows && verifyPassword($password, $rows["password"])) {
				if(passwordNeedsRehash($rows["password"])) {
					$rehashed = hashPassword($password);
					dbExec("UPDATE `admin` SET `password` = '" . $rehashed . "' WHERE `adminid` = '" . $rows["adminid"] . "'");
				}
				if (!empty($rows["totp"])) {
					$_SESSION["a2fa_id"]       = (int) $rows["adminid"];
					$_SESSION["a2fa_return"]   = $return;
					$_SESSION["a2fa_remember"] = $rememberme;
					header("Location: login.php?task=2fa");
					exit;
				}
				adminCompleteLogin($rows, $return, $rememberme);
			}
		}
		$_SESSION["loginerror"] = TRUE;
		$_SESSION["loginattempt"] = ($_SESSION["loginattempt"] ?? 0) + 1;
		if(4 < $_SESSION["loginattempt"]) {
			$_SESSION["lockout"] = time();
			$_SESSION["loginattempt"] = 3;
			$message = "5 Incorrect Admin Login Attempts (" . $username . ")";
			dbExec("INSERT INTO `log` SET `message` = '" . $message . "', `name` = 'System Message', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		}
		if(!empty($return) && !empty($username)) {
			header("Location: login.php?return=" . urlencode($return) . "&username=" . urlencode($username));
		} elseif(empty($return) && !empty($username)) {
			header("Location: login.php?username=" . urlencode($username));
		} elseif(!empty($return) && empty($username)) {
			header("Location: login.php?return=" . urlencode($return));
		} else {
			header("Location: login.php");
		}
		exit;
	case "password":
		$username = sanitizeInput($_POST["username"] ?? "");
		unset($_SESSION["success"]);
		if(!empty($_SESSION["lockout"]) && time() - 60 * 10 < $_SESSION["lockout"]) {
		} elseif(!empty($username)) {
			$numrows = dbCount("SELECT `adminid` FROM `admin` WHERE `username` = '" . $username . "'");
			if($numrows == 1) {
				$password = generateRandomString(8);
				$rows = dbRow("SELECT `adminid`, `email`, `firstname`, `lastname` FROM `admin` WHERE `username` = '" . $username . "'");
				dbExec("UPDATE `admin` SET `password` = '" . hashPassword($password) . "' WHERE `adminid` = '" . $rows["adminid"] . "'");
				$message = "" . "Your password has been reset to: " . $password . " \nIP: " . $_SERVER["REMOTE_ADDR"];
				if(is_file(__DIR__ . "/../includes/class.phpmailer.php")) {
					include_once __DIR__ . "/../includes/class.phpmailer.php";
					$mail = new PHPMailer();
					$mail->IsMail();
					$mail->AddAddress($rows["email"], $rows["firstname"] . " " . $rows["lastname"]);
					$mail->From = $rows["email"];
					$mail->FromName = "Swift Panel System";
					$mail->Subject = "Reset Password";
					$mail->Body = $message;
					$mail->Send();
				}
				unset($_SESSION["loginattempt"]);
				unset($_SESSION["lockout"]);
				$_SESSION["success"] = "Yes";
				header("Location: login.php?task=password");
				exit;
			}
		}
		$_SESSION["success"] = "No";
		$_SESSION["loginattempt"] = ($_SESSION["loginattempt"] ?? 0) + 1;
		if(4 < $_SESSION["loginattempt"]) {
			$_SESSION["lockout"] = time();
			$_SESSION["loginattempt"] = 3;
			$message = "5 Incorrect Admin Login Attempts (" . $username . ")";
			dbExec("INSERT INTO `log` SET `message` = '" . $message . "', `name` = 'System Message', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		}
		header("Location: login.php?task=password");
		exit;

	case "2fa_enable":
		$aid    = (int) ($_SESSION["adminid"] ?? 0);
		$secret = (string) ($_SESSION["a2fa_setup"] ?? "");
		if ($aid > 0 && $secret !== "" && totpVerify($secret, sanitizeInput($_POST["totpcode"] ?? ""))) {
			$plain = totpRecoveryCodes(8);
			dbExec("UPDATE `admin` SET `totp` = '" . dbEscape($secret) . "', `totp_recovery` = '" . dbEscape(totpRecoveryStore($plain)) . "' WHERE `adminid` = '" . $aid . "'");
			unset($_SESSION["a2fa_setup"]);
			$_SESSION["a2fa_codes"] = $plain;
			$_SESSION["msg1"] = "Two-factor enabled";
			$_SESSION["msg2"] = "Save your recovery codes below.";
		} else {
			$_SESSION["msg1"] = "Could not enable two-factor";
			$_SESSION["msg2"] = "That code did not match.";
		}
		header("Location: myaccount.php");
		exit;

	case "2fa_disable":
		$aid = (int) ($_SESSION["adminid"] ?? 0);
		$cur = dbRow("SELECT `totp` FROM `admin` WHERE `adminid` = '" . $aid . "' LIMIT 1", TRUE);
		if ($aid > 0 && is_array($cur) && !empty($cur["totp"]) && totpVerify((string) $cur["totp"], sanitizeInput($_POST["totpcode"] ?? ""))) {
			dbExec("UPDATE `admin` SET `totp` = '' WHERE `adminid` = '" . $aid . "'");
			$_SESSION["msg1"] = "Two-factor disabled";
			$_SESSION["msg2"] = "Your account no longer requires a code.";
		} else {
			$_SESSION["msg1"] = "Could not disable two-factor";
			$_SESSION["msg2"] = "Enter a current code to confirm.";
		}
		header("Location: myaccount.php");
		exit;

	case "myaccount":
		$adminid = sanitizeInput($_POST["adminid"] ?? "");
		$firstname = sanitizeInput($_POST["firstname"] ?? "");
		$firstname = ucfirst($firstname);
		$lastname = sanitizeInput($_POST["lastname"] ?? "");
		$lastname = ucfirst($lastname);
		$email = sanitizeInput($_POST["email"] ?? "");
		$email = strtolower($email);
		$username = sanitizeInput($_POST["username"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$password2 = sanitizeInput($_POST["password2"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["firstname"] = $firstname;
		$_SESSION["lastname"] = $lastname;
		$_SESSION["email"] = $email;
		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;
		$_SESSION["password2"] = $password2;
		$len = strlen($firstname);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>First Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($email);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Entered</b> ]</li>";
		} elseif($len <= "2") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Long Enough</b> ]</li>";
		}
		$len = strlen($username);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Username [ <b>Not Entered</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `admin` WHERE `username` = '" . $username . "' && `adminid` != '" . $adminid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Username  [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($password);
		if("1" <= $len && $len <= "3") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password  [ <b>Not Long Enough</b> ]</li>";
		} elseif($password != $password2) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Passwords  [ <b>Do Not Match</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: myaccount.php");
			exit;
		}
		unset($_SESSION["firstname"]);
		unset($_SESSION["lastname"]);
		unset($_SESSION["email"]);
		unset($_SESSION["username"]);
		unset($_SESSION["password"]);
		unset($_SESSION["password2"]);
		if(empty($password)) {
			dbExec("UPDATE `admin` SET `username` = '" . $username . "', `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "' WHERE `adminid` = '" . $adminid . "'");
		} else {
			dbExec("UPDATE `admin` SET `username` = '" . $username . "', `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "', `password` = '" . hashPassword($password) . "' WHERE `adminid` = '" . $adminid . "'");
		}
		$_SESSION["adminusername"] = $username;
		$_SESSION["adminfirstname"] = $firstname;
		$_SESSION["adminlastname"] = $lastname;
		$_SESSION["msg1"] = "Admin Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the admin have been saved.";
		header("Location: index.php");
		exit;
	case "logout":
		session_destroy();
		header("Location: login.php");
		exit;
	case "personalnotes":
		$adminid = sanitizeInput($_POST["adminid"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("UPDATE `admin` SET `notes` = '" . $notes . "' WHERE `adminid` = '" . $adminid . "'");
		$_SESSION["msg1"] = "Personal Notes Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to your personal notes have been saved.";
		header("Location: index.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}