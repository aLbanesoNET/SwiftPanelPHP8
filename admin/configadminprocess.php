<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "configadminadd":
		$access = sanitizeInput($_POST["access"] ?? "");
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
		$_SESSION["access"] = $access;
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
		} elseif(dbCount("SELECT `adminid` FROM `admin` WHERE `username` = '" . $username . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Username [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($password);
		if($len <= "3") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
		} elseif($password != $password2) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Passwords [ <b>Do Not Match</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: configadminadd.php");
			exit;
		}
		unset($_SESSION["access"]);
		unset($_SESSION["firstname"]);
		unset($_SESSION["lastname"]);
		unset($_SESSION["email"]);
		unset($_SESSION["username"]);
		unset($_SESSION["password"]);
		unset($_SESSION["password2"]);
		dbExec("INSERT INTO `admin` SET `username` = '" . $username . "', `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "', `password` = '" . hashPassword($password) . "', `access` = '" . $access . "', `notes` = '" . ($notes ?? "") . "', `status` = 'Active', `lastlogin` = '', `lastip` = '~', `lasthost` = '~'");
		$_SESSION["msg1"] = "Admin Added Successfully!";
		$_SESSION["msg2"] = "The new admin account has been added and is ready for use.";
		header("Location: configadmin.php");
		exit;
	case "configadminedit":
		$adminid = sanitizeInput($_POST["adminid"] ?? "");
		$access = sanitizeInput($_POST["access"] ?? "");
		$firstname = sanitizeInput($_POST["firstname"] ?? "");
		$firstname = ucfirst($firstname);
		$lastname = sanitizeInput($_POST["lastname"] ?? "");
		$lastname = ucfirst($lastname);
		$email = sanitizeInput($_POST["email"] ?? "");
		$email = strtolower($email);
		$username = sanitizeInput($_POST["username"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$password2 = sanitizeInput($_POST["password2"] ?? "");
		$status = sanitizeInput($_POST["status"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["access"] = $access;
		$_SESSION["firstname"] = $firstname;
		$_SESSION["lastname"] = $lastname;
		$_SESSION["email"] = $email;
		$_SESSION["username"] = $username;
		$_SESSION["password"] = $password;
		$_SESSION["password2"] = $password2;
		$_SESSION["status"] = $status;
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
		if($adminid == $_SESSION["adminid"] && $status == "Suspended") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Status [ <b>Can Not Suspended Yourself</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: configadminedit.php?id=" . urlencode($adminid));
			exit;
		}
		unset($_SESSION["access"]);
		unset($_SESSION["firstname"]);
		unset($_SESSION["lastname"]);
		unset($_SESSION["email"]);
		unset($_SESSION["username"]);
		unset($_SESSION["password"]);
		unset($_SESSION["password2"]);
		unset($_SESSION["status"]);
		if(empty($password)) {
			dbExec("UPDATE `admin` SET `username` = '" . $username . "', `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "', `access` = '" . $access . "', `notes` = '" . ($notes ?? "") . "', `status` = '" . $status . "' WHERE `adminid` = '" . $adminid . "'");
		} else {
			dbExec("UPDATE `admin` SET `username` = '" . $username . "', `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "', `password` = '" . hashPassword($password) . "', `access` = '" . $access . "', `notes` = '" . ($notes ?? "") . "', `status` = '" . $status . "' WHERE `adminid` = '" . $adminid . "'");
		}
		if($adminid == $_SESSION["adminid"]) {
			$_SESSION["adminusername"] = $username;
			$_SESSION["adminfirstname"] = $firstname;
			$_SESSION["adminlastname"] = $lastname;
		}
		$_SESSION["msg1"] = "Admin Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the admin have been saved.";
		header("Location: configadmin.php");
		exit;
	case "configadmindelete":
		$adminid = sanitizeInput($_GET["id"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("DELETE FROM `admin` WHERE `adminid` = '" . $adminid . "' LIMIT 1");
		$_SESSION["msg1"] = "Admin Deleted Successfully!";
		$_SESSION["msg2"] = "The selected admin has been removed.";
		header("Location: configadmin.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>