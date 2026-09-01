<?php

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$task = sanitizeInput($_POST["task"] ?? $_GET["task"] ?? "");

switch ($task) {

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
				"SELECT clientid,email,firstname,lastname,password,status
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

				dbExec(
					"UPDATE client 
					 SET lastlogin=NOW(),
						 lastip='{$_SERVER["REMOTE_ADDR"]}',
						 lasthost='".gethostbyaddr($_SERVER["REMOTE_ADDR"])."'
					 WHERE clientid='{$user["clientid"]}'"
				);

				$_SESSION["clientid"] = $user["clientid"];
				$_SESSION["clientemail"] = $user["email"];
				$_SESSION["clientfirstname"] = $user["firstname"];
				$_SESSION["clientlastname"] = $user["lastname"];

				if ($remember === "on") {
					setcookie("clientemail", $user["email"], time() + 604800, "/");
				} else {
					setcookie("clientemail", "", time() - 3600, "/");
				}

				unset($_SESSION["loginattempt"], $_SESSION["lockout"]);

				header("Location: " . ($return ?: "index.php"));
				exit;
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