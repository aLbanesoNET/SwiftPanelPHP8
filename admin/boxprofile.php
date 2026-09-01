<?php
$title = "Box Profile";
$page = "boxprofile";
$tab = "4";
$return = "boxprofile.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
$boxid = sanitizeInput($_GET["id"] ?? "");
$rows = dbRow("SELECT * FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
if(empty($_SESSION["name"])) {
	$_SESSION["name"] = $rows["name"];
}
if(empty($_SESSION["location"])) {
	$_SESSION["location"] = $rows["location"];
}
if(empty($_SESSION["ip"])) {
	$_SESSION["ip"] = $rows["ip"];
}
if(empty($_SESSION["login"])) {
	$_SESSION["login"] = $rows["login"];
}
if(empty($_SESSION["ftpport"])) {
	$_SESSION["ftpport"] = $rows["ftpport"];
}
if(empty($_SESSION["sshport"])) {
	$_SESSION["sshport"] = $rows["sshport"];
}
if(empty($_SESSION["ostype"])) {
	$_SESSION["ostype"] = $rows["ostype"];
}
if(empty($_SESSION["cost"])) {
	$_SESSION["cost"] = $rows["cost"];
}
if(empty($_SESSION["notes"])) {
	$_SESSION["notes"] = $rows["notes"];
}
if(empty($_SESSION["passive"])) {
	$_SESSION["passive"] = $rows["passive"];
}
if(empty($_SESSION["formerror"])) {
	$_SESSION["verify"] = "on";
}
unset($_SESSION["formerror"]);
$hiddens = array("task" => "boxprofile", "boxid" => $rows["boxid"]);
$inputs = array("name" => array("text", "Server Name", 35), "location" => array("text", "Server Location", 25, "(City, State)"), "ip" => array("text", "IP Address", 25), "login" => array("text", "Root Login", 20, "(Default: root)"), "password" => array("text", "Root Password", 20), "ftpport" => array("text", "FTP Port", 5, "(Default: 21)"), "sshport" => array("text", "SSH Port", 5, "(Default: 22)"), "ostype" => array("radio", "OS Type", array("Linux")), "cost" => array("text", "Monthly Cost", 10, "(Optional)"), "notes" => array("textarea", "Admin Notes", 70, 4), array("divider"), "passive" => array("radio", "FTP Passive Mode", array("On", "Off")), array("divider"), "verify" => array("checkbox", "Verify Root Login &amp; Password"));
$buttons = array("Save Changes" => array("submit"), "Cancel Changes" => array("reset"));
$form = array("boxprocess.php", $hiddens, $inputs, $buttons, TRUE, "#" . $rows["boxid"] . " - " . $rows["name"]);
$tabs = array("Summary" => "boxsummary.php?id=" . $rows["boxid"], "Profile" => "boxprofile.php?id=" . $rows["boxid"], "Servers" => "boxserver.php?id=" . $rows["boxid"], "Game Files" => "boxgamefile.php?id=" . $rows["boxid"], "Activity Logs" => "boxlog.php?id=" . $rows["boxid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 2);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab">
<?php
renderForm($form);
?>
	</td>
  </tr>
</table>
<?php
include "./templates/" . TEMPLATE . "/footer.php";

?>