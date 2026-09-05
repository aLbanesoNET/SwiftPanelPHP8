<?php

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
requireSameOrigin('index.php');
require __DIR__ . '/includes/totp.php';

$task = sanitizeInput($_POST["task"] ?? $_GET["task"] ?? "");

/** Finish a successful login: set the session, remember cookie, redirect. */
function completeClientLogin(array $user, string $return, string $remember, string $method = 'password'): void
{
	session_regenerate_id(true);
	$return = safeReturnPath($return);
	if (dbCount("SHOW TABLES LIKE 'loginlog'") > 0) {
		dbExec(
			"INSERT INTO `loginlog` SET `clientid` = '" . (int) $user["clientid"] . "', " .
			"`ip` = '" . dbEscape(substr($_SERVER["REMOTE_ADDR"] ?? "", 0, 45)) . "', " .
			"`agent` = '" . dbEscape(substr($_SERVER["HTTP_USER_AGENT"] ?? "", 0, 255)) . "', " .
			"`method` = '" . dbEscape($method) . "', `ts` = NOW()"
		);
		dbExec(
			"DELETE FROM `loginlog` WHERE `clientid` = '" . (int) $user["clientid"] . "' AND `logid` NOT IN " .
			"(SELECT `logid` FROM (SELECT `logid` FROM `loginlog` WHERE `clientid` = '" . (int) $user["clientid"] . "' ORDER BY `logid` DESC LIMIT 20) t)"
		);
	}

	dbExec(
		"UPDATE client SET lastlogin=NOW(), lastip='" . dbEscape($_SERVER["REMOTE_ADDR"] ?? "") . "',
		 lasthost='" . dbEscape((string) @gethostbyaddr($_SERVER["REMOTE_ADDR"] ?? "")) . "'
		 WHERE clientid='" . (int) $user["clientid"] . "'"
	);

	$_SESSION["clientid"]        = $user["clientid"];
	$_SESSION["clientemail"]     = $user["email"];
	$_SESSION["clientfirstname"] = $user["firstname"];
	$_SESSION["clientlastname"]  = $user["lastname"];

	if ($remember === "on") {
		setcookie("clientemail", $user["email"], time() + 604800, "/");
	} else {
		setcookie("clientemail", "", time() - 3600, "/");
	}

	unset($_SESSION["loginattempt"], $_SESSION["lockout"], $_SESSION["2fa_client"], $_SESSION["2fa_return"], $_SESSION["2fa_remember"]);

	header("Location: " . ($return ?: "index.php"));
	exit;
}

switch ($task) {

	case "login2fa":
		$pcid   = (int) ($_SESSION["2fa_client"] ?? 0);
		$return = (string) ($_SESSION["2fa_return"] ?? "");

		if ($pcid <= 0) {
			header("Location: login.php");
			exit;
		}

		if (!empty($_SESSION["lockout"]) && time() - 300 < $_SESSION["lockout"]) {
			$_SESSION["loginerror"] = true;
			header("Location: login.php?task=2fa");
			exit;
		}

		$code = sanitizeInput($_POST["totpcode"] ?? "");
		$user = dbRow("SELECT clientid,email,firstname,lastname,password,totp,totp_recovery FROM client WHERE clientid='" . $pcid . "' LIMIT 1", true);
		if (is_array($user) && !empty($user["totp"])) {
			if (totpVerify((string) $user["totp"], $code)) {
				completeClientLogin($user, $return, (string) ($_SESSION["2fa_remember"] ?? ""), "2fa");
			}
			$newRecovery = totpRecoveryConsume((string) ($user["totp_recovery"] ?? ""), $code);
			if ($newRecovery !== null) {
				dbExec("UPDATE `client` SET `totp_recovery` = '" . dbEscape($newRecovery) . "' WHERE `clientid` = '" . (int) $user["clientid"] . "'");
				completeClientLogin($user, $return, (string) ($_SESSION["2fa_remember"] ?? ""), "recovery");
			}
		}

		$_SESSION["loginerror"] = true;
		$_SESSION["loginattempt"] = ($_SESSION["loginattempt"] ?? 0) + 1;
		if ($_SESSION["loginattempt"] > 4) {
			$_SESSION["lockout"] = time();
			$_SESSION["loginattempt"] = 3;
		}
		header("Location: login.php?task=2fa");
		exit;

	case "login":
		$email = sanitizeInput($_POST["email"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$return = sanitizeInput($_POST["return"] ?? "");
		$remember = sanitizeInput($_POST["rememberme"] ?? "");

		unset($_SESSION["loginerror"]);

		setcookie("rememberme", $remember, time() + 2592000, "/");

		if (!empty($_SESSION["lockout"]) && time() - 300 < $_SESSION["lockout"]) {
			redirectLogin($return, $email);
		}

		if ($email !== "" && $password !== "") {
			$user = dbRow(
				"SELECT clientid,email,firstname,lastname,password,status,totp
				 FROM client
				 WHERE email='{$email}'
				 LIMIT 1",
				true
			);

			$ok = $user
				&& in_array($user["status"], ["Active", "Inactive"], true)
				&& verifyPassword($password, $user["password"]);

			if ($ok) {
				if (passwordNeedsRehash($user["password"])) {
					$rehashed = hashPassword($password);
					dbExec("UPDATE client SET password='{$rehashed}' WHERE clientid='{$user["clientid"]}'");
				}

				if (!empty($user["totp"])) {
					// Password is right — now require the authenticator code.
					$_SESSION["2fa_client"]   = (int) $user["clientid"];
					$_SESSION["2fa_return"]   = $return;
					$_SESSION["2fa_remember"] = $remember;
					header("Location: login.php?task=2fa");
					exit;
				}

				completeClientLogin($user, $return, $remember);
			}
		}

		redirectLogin($return, $email);
		exit;

	case "password":
		$email = sanitizeInput($_POST["email"] ?? "");
		unset($_SESSION["success"]);

		if (!empty($_SESSION["lockout"]) && time() - 300 < $_SESSION["lockout"]) {
			failPasswordReset();
		}

		if ($email !== "") {
			$count = dbCount("SELECT clientid FROM client WHERE email='{$email}'");

			if ($count === 1) {
				$password = generateRandomString(8);
				$user = dbRow("SELECT clientid,email,firstname,lastname FROM client WHERE email='{$email}'");

				$hashed = hashPassword($password);
				dbExec("UPDATE client SET password='{$hashed}' WHERE clientid='{$user["clientid"]}'");

				if (is_file(__DIR__ . "/includes/class.phpmailer.php")) {
					require_once __DIR__ . "/includes/class.phpmailer.php";
					$mail = new PHPMailer();
					$mail->IsMail();
					$mail->AddAddress($user["email"], $user["firstname"] . " " . $user["lastname"]);
					$mail->From = $user["email"];
					$mail->FromName = SITENAME;
					$mail->Subject = "Reset Password";
					$mail->Body = "Your new password: {$password}\nIP: {$_SERVER["REMOTE_ADDR"]}";
					$mail->Send();
				}

				unset($_SESSION["loginattempt"], $_SESSION["lockout"]);
				$_SESSION["success"] = "Yes";

				header("Location: login.php?task=password");
				exit;
			}
		}

		failPasswordReset();
		exit;

	case "logout":
		session_destroy();
		header("Location: login.php");
		exit;

	default:
		header("Location: index.php");
		exit;
}

function redirectLogin($return, $email) {
	$_SESSION["loginerror"] = true;

	$_SESSION["loginattempt"] = ($_SESSION["loginattempt"] ?? 0) + 1;
	if ($_SESSION["loginattempt"] > 4) {
		$_SESSION["lockout"] = time();
		$_SESSION["loginattempt"] = 3;
	}

	$query = [];
	if ($return) $query["return"] = $return;
	if ($email) $query["email"] = $email;

	header("Location: login.php" . ($query ? "?" . http_build_query($query) : ""));
	exit;
}

function failPasswordReset() {
	$_SESSION["success"] = "No";

	$_SESSION["loginattempt"] = ($_SESSION["loginattempt"] ?? 0) + 1;
	if ($_SESSION["loginattempt"] > 4) {
		$_SESSION["lockout"] = time();
		$_SESSION["loginattempt"] = 3;
	}

	header("Location: login.php?task=password");
	exit;
}
?>