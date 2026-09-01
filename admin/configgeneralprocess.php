<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "generaledit":
		$panelname = sanitizeInput($_POST["panelname"] ?? "");
		$systemurl = sanitizeInput($_POST["systemurl"] ?? "");
		$template = preg_replace('/[^A-Za-z0-9_-]/', '', sanitizeInput($_POST["template"] ?? ""));
		$country = sanitizeInput($_POST["country"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["panelname"] = $panelname;
		$_SESSION["systemurl"] = $systemurl;
		$_SESSION["template"] = $template;
		$_SESSION["country"] = $country;
		$len = strlen($panelname);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Panel Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($systemurl);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>System URL [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($template);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Template [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($country);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Country [ <b>Not Entered</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			header("Location: configgeneral.php");
			exit;
		}
		unset($_SESSION["panelname"]);
		unset($_SESSION["systemurl"]);
		unset($_SESSION["template"]);
		unset($_SESSION["country"]);
		dbExec("UPDATE `config` SET `value` = '" . $panelname . "' WHERE `setting` = 'panelname'");
		dbExec("UPDATE `config` SET `value` = '" . $systemurl . "' WHERE `setting` = 'systemurl'");
		dbExec("UPDATE `config` SET `value` = '" . $template . "' WHERE `setting` = 'template'");
		dbExec("UPDATE `config` SET `value` = '" . $country . "' WHERE `setting` = 'country'");
		$_SESSION["msg1"] = "Settings Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the settings have been saved.";
		header("Location: configgeneral.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>