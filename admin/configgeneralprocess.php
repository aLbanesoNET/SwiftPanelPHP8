<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
requireSameOrigin('index.php');
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "generaledit":
		$panelname = sanitizeInput($_POST["panelname"] ?? "");
		$systemurl = sanitizeInput($_POST["systemurl"] ?? "");
		$template = preg_replace('/[^A-Za-z0-9_-]/', '', sanitizeInput($_POST["template"] ?? ""));
		// Never let a bogus value land — keep the current template if the posted
		// one is not a real theme folder present in both templates/ and admin/templates/.
		if ($template === "" || !is_dir(__DIR__ . "/../templates/" . $template) || !is_dir(__DIR__ . "/templates/" . $template)) {
			$currentTpl = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'template' LIMIT 1", TRUE);
			$template = (string) ($currentTpl["value"] ?? "default");
		}
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

		// Client MySQL databases
		$cdbEnabled = (($_POST["clientdb_enabled"] ?? "0") === "1") ? "1" : "0";
		$cdbMax     = (string) max(0, (int) ($_POST["clientdb_max"] ?? 0));
		$cdbMaxsize = (string) max(0, (int) ($_POST["clientdb_maxsize"] ?? 0));
		$cdbHost    = preg_replace('/[^A-Za-z0-9_.%:\-]/', '', (string) ($_POST["clientdb_host"] ?? "%"));
		if ($cdbHost === "") { $cdbHost = "%"; }
		$cdbPma     = sanitizeInput($_POST["clientdb_pma"] ?? "");
		foreach ([
			"clientdb_enabled" => $cdbEnabled,
			"clientdb_max"     => $cdbMax,
			"clientdb_maxsize" => $cdbMaxsize,
			"clientdb_host"    => $cdbHost,
			"clientdb_pma"     => $cdbPma,
		] as $setting => $value) {
			if (dbCount("SELECT `setting` FROM `config` WHERE `setting` = '" . $setting . "'") > 0) {
				dbExec("UPDATE `config` SET `value` = '" . dbEscape($value) . "' WHERE `setting` = '" . $setting . "'");
			} else {
				dbExec("INSERT INTO `config` SET `setting` = '" . $setting . "', `value` = '" . dbEscape($value) . "'");
			}
		}

		$_SESSION["msg1"] = "Settings Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the settings have been saved.";
		header("Location: configgeneral.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>