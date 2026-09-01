<?php
$title = "Add IP Address";
$page = "boxipadd";
$tab = "4";
$return = "boxipadd.php?id=" . ($_GET["id"] ?? "");
$image = "add_48";
require "../configuration.php";
require "./include.php";
$boxid = sanitizeInput($_GET["id"] ?? "");
$rows = dbRow("SELECT `boxid`, `name`, `location` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
if(empty($_SESSION["formerror"])) {
	$_SESSION["usage"] = "Game Servers";
	$_SESSION["verify"] = "on";
}
unset($_SESSION["formerror"]);
$hiddens = array("task" => "boxipadd", "boxid" => $rows["boxid"]);
$inputs = array(array("content", "Box Name", "<a href=\"boxsummary.php?id=" . $rows["boxid"] . "\">" . $rows["name"] . "</a>"), array("content", "Box Location", $rows["location"]), "ip" => array("text", "IP Address", 25), "usage" => array("radio", "Usage", array("Game Servers")), array("divider"), "verify" => array("checkbox", "Verify IP Address Connection"));
$buttons = array("Add IP Address" => array("submit"), "Back to Box" => array("link", "boxsummary.php?id=" . $rows["boxid"]));
$form = array("boxprocess.php", $hiddens, $inputs, $buttons, TRUE);
include "./templates/" . TEMPLATE . "/header.php";
renderForm($form);
include "./templates/" . TEMPLATE . "/footer.php";

?>