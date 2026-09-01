<?php
$postTask = $_POST["task"] ?? "";
if($postTask != "login" && $postTask != "password" && $postTask != "logout") {
	$return = TRUE;
}
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "login":
		$username = sanitizeInput($_POST["username"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$return = sanitizeInput($_POST["return"] ?? "");
		$rememberme = sanitizeInput($_POST["rememberme"] ?? "");
		unset($_SESSION["loginerror"]);
		setcookie("rememberme", $rememberme, time() + 60 * 60 * 24 * 30);
		if(!empty($_SESSION["lockout"]) && time() - 60 * 10 < $_SESSION["lockout"]) {
		} elseif(!empty($username) && !empty($password)) {
			$rows = dbRow("SELECT `adminid`, `username`, `firstname`, `lastname`, `password` FROM `admin` WHERE `username` = '" . $username . "' AND `status` = 'Active' LIMIT 1", TRUE);
			if($rows && verifyPassword($password, $rows["password"])) {
				if(passwordNeedsRehash($rows["password"])) {
					$rehashed = hashPassword($password);
					dbExec("UPDATE `admin` SET `password` = '" . $rehashed . "' WHERE `adminid` = '" . $rows["adminid"] . "'");
				}
				dbExec("UPDATE `admin` SET `lastlogin` = NOW(), `lastip` = '" . $_SERVER["REMOTE_ADDR"] . "', `lasthost` = '" . gethostbyaddr($_SERVER["REMOTE_ADDR"]) . "' WHERE `adminid` = '" . $rows["adminid"] . "'");
				$_SESSION["adminid"] = $rows["adminid"];
				$_SESSION["adminusername"] = $rows["username"];
				$_SESSION["adminfirstname"] = $rows["firstname"];
				$_SESSION["adminlastname"] = $rows["lastname"];
				if($rememberme == "on") {
					setcookie("adminusername", $rows["username"], time() + 60 * 60 * 24 * 30);
				} else {
					setcookie("adminusername", "", time() + 60 * 60 * 24 * 1);
				}
				unset($_SESSION["loginattempt"]);
				unset($_SESSION["lockout"]);
				if(!empty($return)) {
					header("Location: " . $return);
				} else {
					header("Location: index.php");
				}
				exit;
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