<?php
// Production error posture: log, never display (avoids corrupting redirects).
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
require_once __DIR__ . "/../includes/security.php";

session_name("PHPSESSION");
session_cache_expire(30);
hardenSessionCookieParams();
session_start();

$page   = $page   ?? '';
$return = $return ?? null;

if(empty($_SESSION["adminid"]) && !empty($return)) {
	if($return === TRUE) {
		header("Location: login.php");
		exit;
	}
	header("Location: login.php?return=" . urlencode((string) $return));
	exit;
}
if($page == "index" && @chdir("../install")) {
	exit("<html><head></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border:1px dashed #CC0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#CC0000;text-align:center;\"><tr><td><b>Install Directory Detected</b><br />Please delete the install directory after the installation or update is complete.<br />Contact Swift Panel Support if you need help. <a href='http://www.swiftpanel.com/client'>Swift Panel Client Area</a></td></tr></table></body></html>");
}
require_once "../includes/functions1.php";
require_once "../includes/functions3.php";
require_once "../includes/functions4.php";
require_once "../includes/mysql.php";
if(!empty($_SESSION["adminid"])) {
	$adminverify = dbQuery("SELECT `adminid`, `username`, `firstname`, `lastname` FROM `admin` WHERE `adminid` = '" . $_SESSION["adminid"] . "' AND `status` = 'Active'");
	if(dbNumRows($adminverify) != 1) {
		session_destroy();
		header("Location: login.php");
		exit;
	}
	$adminverify = dbFetch($adminverify);
	if($adminverify["username"] != $_SESSION["adminusername"] || $adminverify["firstname"] != $_SESSION["adminfirstname"] || $adminverify["lastname"] != $_SESSION["adminlastname"]) {
		session_destroy();
		header("Location: login.php");
		exit;
	}
}
$panelversion = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'panelversion' LIMIT 1", TRUE);
if($panelversion["value"] != "1.6.1") {
	exit("<html><head></head><body><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border:1px dashed #CC0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#CC0000;text-align:center;\"><tr><td><b>Wrong Database Version Detected</b><br />Make sure you have followed the instructions to install/update the database.<br />Contact Swift Panel Support if you need help. <a href='http://www.swiftpanel.com/client'>Swift Panel Client Area</a></td></tr></table></body></html>");
}
$panelname = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'panelname' LIMIT 1", TRUE);
define("VERSION", $panelversion["value"]);
define("SITENAME", $panelname["value"]);

// Active template — read from the `template` config row (admin General
// Settings). Sanitised to a folder name and checked to exist; 'default' if
// unset or invalid.
$templateRow  = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'template' LIMIT 1", TRUE);
$templateName = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($templateRow["value"] ?? ''));
if ($templateName === "" || !is_dir(__DIR__ . "/templates/" . $templateName)) {
	$templateName = "default";
}
define("TEMPLATE", $templateName);

/**
 * Path to an admin body partial for the active theme, falling back to
 * admin/templates/default. Lets a theme ship its own page markup.
 */
function admin_tpl(string $name): string
{
	$active = __DIR__ . "/templates/" . TEMPLATE . "/" . $name . ".php";
	return is_file($active) ? $active : __DIR__ . "/templates/default/" . $name . ".php";
}
