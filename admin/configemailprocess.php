<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "emailedit":
		$e1name = sanitizeInput($_POST["e1name"] ?? "");
		$e1email = sanitizeInput($_POST["e1email"] ?? "");
		$e1bcc = sanitizeInput($_POST["e1bcc"] ?? "");
		$e1subject = sanitizeInput($_POST["e1subject"] ?? "");
		$e1template = sanitizeInput($_POST["e1template"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["e1name"] = $e1name;
		$_SESSION["e1email"] = $e1email;
		$_SESSION["e1bcc"] = $e1bcc;
		$_SESSION["e1subject"] = $e1subject;
		$_SESSION["e1template"] = $e1template;
		$len = strlen($e1name);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>From [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($e1email);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>From [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($e1subject);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Subject [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($e1template);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Message [ <b>Not Entered</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			header("Location: configemail.php");
			exit;
		}
		unset($_SESSION["e1name"]);
		unset($_SESSION["e1email"]);
		unset($_SESSION["e1bcc"]);
		unset($_SESSION["e1subject"]);
		unset($_SESSION["e1template"]);
		dbExec("UPDATE `emailtemp` SET `name` = '" . $e1name . "', `email` = '" . $e1email . "', `bcc` = '" . $e1bcc . "', `subject` = '" . $e1subject . "', `template` = '" . $e1template . "' WHERE `emailtempid` = '1'");
		$_SESSION["msg1"] = "Email Templates Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the email templates have been saved.";
		header("Location: configemail.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>