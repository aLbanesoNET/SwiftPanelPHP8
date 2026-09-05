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
	case "configgameadd":
		$name = sanitizeInput($_POST["name"] ?? "");
		$game = sanitizeInput($_POST["game"] ?? "");
		$query = sanitizeInput($_POST["query"] ?? "");
		$priority = sanitizeInput($_POST["priority"] ?? "");
		$slots = sanitizeInput($_POST["slots"] ?? "");
		$type = sanitizeInput($_POST["type"] ?? "");
		$cfg1name = sanitizeInput($_POST["cfg1name"] ?? "");
		$cfg1 = sanitizeInput($_POST["cfg1"] ?? "");
		$cfg1edit = sanitizeInput($_POST["cfg1edit"] ?? "");
		$cfg2name = sanitizeInput($_POST["cfg2name"] ?? "");
		$cfg2 = sanitizeInput($_POST["cfg2"] ?? "");
		$cfg2edit = sanitizeInput($_POST["cfg2edit"] ?? "");
		$cfg3name = sanitizeInput($_POST["cfg3name"] ?? "");
		$cfg3 = sanitizeInput($_POST["cfg3"] ?? "");
		$cfg3edit = sanitizeInput($_POST["cfg3edit"] ?? "");
		$cfg4name = sanitizeInput($_POST["cfg4name"] ?? "");
		$cfg4 = sanitizeInput($_POST["cfg4"] ?? "");
		$cfg4edit = sanitizeInput($_POST["cfg4edit"] ?? "");
		$cfg5name = sanitizeInput($_POST["cfg5name"] ?? "");
		$cfg5 = sanitizeInput($_POST["cfg5"] ?? "");
		$cfg5edit = sanitizeInput($_POST["cfg5edit"] ?? "");
		$cfg6name = sanitizeInput($_POST["cfg6name"] ?? "");
		$cfg6 = sanitizeInput($_POST["cfg6"] ?? "");
		$cfg6edit = sanitizeInput($_POST["cfg6edit"] ?? "");
		$cfg7name = sanitizeInput($_POST["cfg7name"] ?? "");
		$cfg7 = sanitizeInput($_POST["cfg7"] ?? "");
		$cfg7edit = sanitizeInput($_POST["cfg7edit"] ?? "");
		$cfg8name = sanitizeInput($_POST["cfg8name"] ?? "");
		$cfg8 = sanitizeInput($_POST["cfg8"] ?? "");
		$cfg8edit = sanitizeInput($_POST["cfg8edit"] ?? "");
		$startline = sanitizeInput($_POST["startline"] ?? "");
		$port = sanitizeInput($_POST["port"] ?? "");
		$qryport = sanitizeInput($_POST["qryport"] ?? "");
		$gamedir = sanitizeInput($_POST["gamedir"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["name"] = $name;
		$_SESSION["game"] = $game;
		$_SESSION["query"] = $query;
		$_SESSION["priority"] = $priority;
		$_SESSION["slots"] = $slots;
		$_SESSION["type"] = $type;
		$_SESSION["cfg1name"] = $cfg1name;
		$_SESSION["cfg1"] = $cfg1;
		$_SESSION["cfg1edit"] = $cfg1edit;
		$_SESSION["cfg2name"] = $cfg2name;
		$_SESSION["cfg2"] = $cfg2;
		$_SESSION["cfg2edit"] = $cfg2edit;
		$_SESSION["cfg3name"] = $cfg3name;
		$_SESSION["cfg3"] = $cfg3;
		$_SESSION["cfg3edit"] = $cfg3edit;
		$_SESSION["cfg4name"] = $cfg4name;
		$_SESSION["cfg4"] = $cfg4;
		$_SESSION["cfg4edit"] = $cfg4edit;
		$_SESSION["cfg5name"] = $cfg5name;
		$_SESSION["cfg5"] = $cfg5;
		$_SESSION["cfg5edit"] = $cfg5edit;
		$_SESSION["cfg6name"] = $cfg6name;
		$_SESSION["cfg6"] = $cfg6;
		$_SESSION["cfg6edit"] = $cfg6edit;
		$_SESSION["cfg7name"] = $cfg7name;
		$_SESSION["cfg7"] = $cfg7;
		$_SESSION["cfg7edit"] = $cfg7edit;
		$_SESSION["cfg8name"] = $cfg8name;
		$_SESSION["cfg8"] = $cfg8;
		$_SESSION["cfg8edit"] = $cfg8edit;
		$_SESSION["startline"] = $startline;
		$_SESSION["port"] = $port;
		$_SESSION["qryport"] = $qryport;
		$_SESSION["gamedir"] = $gamedir;
		$len = strlen($name);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($game);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Game [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($slots);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Slots [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($slots)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Slots [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($startline);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Start Command [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($qryport);
		if(!is_numeric($qryport) && !empty($qryport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Query Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($port);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Default Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($port)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Default Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($gamedir);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Game Directory [ <b>Not Entered</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: configgameadd.php");
			exit;
		}
		unset($_SESSION["name"]);
		unset($_SESSION["game"]);
		unset($_SESSION["query"]);
		unset($_SESSION["priority"]);
		unset($_SESSION["slots"]);
		unset($_SESSION["type"]);
		unset($_SESSION["cfg1name"]);
		unset($_SESSION["cfg1"]);
		unset($_SESSION["cfg1edit"]);
		unset($_SESSION["cfg2name"]);
		unset($_SESSION["cfg2"]);
		unset($_SESSION["cfg2edit"]);
		unset($_SESSION["cfg3name"]);
		unset($_SESSION["cfg3"]);
		unset($_SESSION["cfg3edit"]);
		unset($_SESSION["cfg4name"]);
		unset($_SESSION["cfg4"]);
		unset($_SESSION["cfg4edit"]);
		unset($_SESSION["cfg5name"]);
		unset($_SESSION["cfg5"]);
		unset($_SESSION["cfg5edit"]);
		unset($_SESSION["cfg6name"]);
		unset($_SESSION["cfg6"]);
		unset($_SESSION["cfg6edit"]);
		unset($_SESSION["cfg7name"]);
		unset($_SESSION["cfg7"]);
		unset($_SESSION["cfg7edit"]);
		unset($_SESSION["cfg8name"]);
		unset($_SESSION["cfg8"]);
		unset($_SESSION["cfg8edit"]);
		unset($_SESSION["startline"]);
		unset($_SESSION["qryport"]);
		unset($_SESSION["port"]);
		unset($_SESSION["gamedir"]);
		dbExec("INSERT INTO `game` SET `name` = '" . $name . "', `game` = '" . $game . "', `query` = '" . $query . "', `priority` = '" . $priority . "', `slots` = '" . $slots . "', `type` = '" . $type . "', `cfg1name` = '" . $cfg1name . "', `cfg1` = '" . $cfg1 . "', `cfg1edit` = '" . $cfg1edit . "', `cfg2name` = '" . $cfg2name . "', `cfg2` = '" . $cfg2 . "', `cfg2edit` = '" . $cfg2edit . "', `cfg3name` = '" . $cfg3name . "', `cfg3` = '" . $cfg3 . "', `cfg3edit` = '" . $cfg3edit . "', `cfg4name` = '" . $cfg4name . "', `cfg4` = '" . $cfg4 . "', `cfg4edit` = '" . $cfg4edit . "', `cfg5name` = '" . $cfg5name . "', `cfg5` = '" . $cfg5 . "', `cfg5edit` = '" . $cfg5edit . "', `cfg6name` = '" . $cfg6name . "', `cfg6` = '" . $cfg6 . "', `cfg6edit` = '" . $cfg6edit . "', `cfg7name` = '" . $cfg7name . "', `cfg7` = '" . $cfg7 . "', `cfg7edit` = '" . $cfg7edit . "', `cfg8name` = '" . $cfg8name . "', `cfg8` = '" . $cfg8 . "', `cfg8edit` = '" . $cfg8edit . "', `startline` = '" . $startline . "', `port` = '" . $port . "', `qryport` = '" . $qryport . "', `gamedir` = '" . $gamedir . "', `status` = 'Active'");
		$_SESSION["msg1"] = "Game Added Successfully!";
		$_SESSION["msg2"] = "The new game has been added and is ready for use.";
		header("Location: configgame.php");
		exit;
	case "configgameedit":
		$gameid = sanitizeInput($_POST["gameid"] ?? "");
		$name = sanitizeInput($_POST["name"] ?? "");
		$game = sanitizeInput($_POST["game"] ?? "");
		$status = sanitizeInput($_POST["status"] ?? "");
		$query = sanitizeInput($_POST["query"] ?? "");
		$priority = sanitizeInput($_POST["priority"] ?? "");
		$slots = sanitizeInput($_POST["slots"] ?? "");
		$type = sanitizeInput($_POST["type"] ?? "");
		$cfg1name = sanitizeInput($_POST["cfg1name"] ?? "");
		$cfg1 = sanitizeInput($_POST["cfg1"] ?? "");
		$cfg1edit = sanitizeInput($_POST["cfg1edit"] ?? "");
		$cfg2name = sanitizeInput($_POST["cfg2name"] ?? "");
		$cfg2 = sanitizeInput($_POST["cfg2"] ?? "");
		$cfg2edit = sanitizeInput($_POST["cfg2edit"] ?? "");
		$cfg3name = sanitizeInput($_POST["cfg3name"] ?? "");
		$cfg3 = sanitizeInput($_POST["cfg3"] ?? "");
		$cfg3edit = sanitizeInput($_POST["cfg3edit"] ?? "");
		$cfg4name = sanitizeInput($_POST["cfg4name"] ?? "");
		$cfg4 = sanitizeInput($_POST["cfg4"] ?? "");
		$cfg4edit = sanitizeInput($_POST["cfg4edit"] ?? "");
		$cfg5name = sanitizeInput($_POST["cfg5name"] ?? "");
		$cfg5 = sanitizeInput($_POST["cfg5"] ?? "");
		$cfg5edit = sanitizeInput($_POST["cfg5edit"] ?? "");
		$cfg6name = sanitizeInput($_POST["cfg6name"] ?? "");
		$cfg6 = sanitizeInput($_POST["cfg6"] ?? "");
		$cfg6edit = sanitizeInput($_POST["cfg6edit"] ?? "");
		$cfg7name = sanitizeInput($_POST["cfg7name"] ?? "");
		$cfg7 = sanitizeInput($_POST["cfg7"] ?? "");
		$cfg7edit = sanitizeInput($_POST["cfg7edit"] ?? "");
		$cfg8name = sanitizeInput($_POST["cfg8name"] ?? "");
		$cfg8 = sanitizeInput($_POST["cfg8"] ?? "");
		$cfg8edit = sanitizeInput($_POST["cfg8edit"] ?? "");
		$startline = sanitizeInput($_POST["startline"] ?? "");
		$port = sanitizeInput($_POST["port"] ?? "");
		$qryport = sanitizeInput($_POST["qryport"] ?? "");
		$gamedir = sanitizeInput($_POST["gamedir"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["name"] = $name;
		$_SESSION["game"] = $game;
		$_SESSION["status"] = $status;
		$_SESSION["query"] = $query;
		$_SESSION["priority"] = $priority;
		$_SESSION["slots"] = $slots;
		$_SESSION["type"] = $type;
		$_SESSION["cfg1name"] = $cfg1name;
		$_SESSION["cfg1"] = $cfg1;
		$_SESSION["cfg1edit"] = $cfg1edit;
		$_SESSION["cfg2name"] = $cfg2name;
		$_SESSION["cfg2"] = $cfg2;
		$_SESSION["cfg2edit"] = $cfg2edit;
		$_SESSION["cfg3name"] = $cfg3name;
		$_SESSION["cfg3"] = $cfg3;
		$_SESSION["cfg3edit"] = $cfg3edit;
		$_SESSION["cfg4name"] = $cfg4name;
		$_SESSION["cfg4"] = $cfg4;
		$_SESSION["cfg4edit"] = $cfg4edit;
		$_SESSION["cfg5name"] = $cfg5name;
		$_SESSION["cfg5"] = $cfg5;
		$_SESSION["cfg5edit"] = $cfg5edit;
		$_SESSION["cfg6name"] = $cfg6name;
		$_SESSION["cfg6"] = $cfg6;
		$_SESSION["cfg6edit"] = $cfg6edit;
		$_SESSION["cfg7name"] = $cfg7name;
		$_SESSION["cfg7"] = $cfg7;
		$_SESSION["cfg7edit"] = $cfg7edit;
		$_SESSION["cfg8name"] = $cfg8name;
		$_SESSION["cfg8"] = $cfg8;
		$_SESSION["cfg8edit"] = $cfg8edit;
		$_SESSION["startline"] = $startline;
		$_SESSION["port"] = $port;
		$_SESSION["qryport"] = $qryport;
		$_SESSION["gamedir"] = $gamedir;
		$len = strlen($name);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($game);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Game [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($slots);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Slots [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($slots)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Slots [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($startline);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Start Command [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($qryport);
		if(!is_numeric($qryport) && !empty($qryport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Query Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($port);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($port)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($gamedir);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Game Directory [ <b>Not Entered</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: configgameedit.php?id=" . urlencode($gameid));
			exit;
		}
		unset($_SESSION["name"]);
		unset($_SESSION["game"]);
		unset($_SESSION["status"]);
		unset($_SESSION["query"]);
		unset($_SESSION["priority"]);
		unset($_SESSION["slots"]);
		unset($_SESSION["type"]);
		unset($_SESSION["cfg1name"]);
		unset($_SESSION["cfg1"]);
		unset($_SESSION["cfg1edit"]);
		unset($_SESSION["cfg2name"]);
		unset($_SESSION["cfg2"]);
		unset($_SESSION["cfg2edit"]);
		unset($_SESSION["cfg3name"]);
		unset($_SESSION["cfg3"]);
		unset($_SESSION["cfg3edit"]);
		unset($_SESSION["cfg4name"]);
		unset($_SESSION["cfg4"]);
		unset($_SESSION["cfg4edit"]);
		unset($_SESSION["cfg5name"]);
		unset($_SESSION["cfg5"]);
		unset($_SESSION["cfg5edit"]);
		unset($_SESSION["cfg6name"]);
		unset($_SESSION["cfg6"]);
		unset($_SESSION["cfg6edit"]);
		unset($_SESSION["cfg7name"]);
		unset($_SESSION["cfg7"]);
		unset($_SESSION["cfg7edit"]);
		unset($_SESSION["cfg8name"]);
		unset($_SESSION["cfg8"]);
		unset($_SESSION["cfg8edit"]);
		unset($_SESSION["startline"]);
		unset($_SESSION["port"]);
		unset($_SESSION["qryport"]);
		unset($_SESSION["gamedir"]);
		dbExec("UPDATE `game` SET `name` = '" . $name . "', `game` = '" . $game . "', `query` = '" . $query . "', `priority` = '" . $priority . "', `slots` = '" . $slots . "', `type` = '" . $type . "', `cfg1name` = '" . $cfg1name . "', `cfg1` = '" . $cfg1 . "', `cfg1edit` = '" . $cfg1edit . "', `cfg2name` = '" . $cfg2name . "', `cfg2` = '" . $cfg2 . "', `cfg2edit` = '" . $cfg2edit . "', `cfg3name` = '" . $cfg3name . "', `cfg3` = '" . $cfg3 . "', `cfg3edit` = '" . $cfg3edit . "', `cfg4name` = '" . $cfg4name . "', `cfg4` = '" . $cfg4 . "', `cfg4edit` = '" . $cfg4edit . "', `cfg5name` = '" . $cfg5name . "', `cfg5` = '" . $cfg5 . "', `cfg5edit` = '" . $cfg5edit . "', `cfg6name` = '" . $cfg6name . "', `cfg6` = '" . $cfg6 . "', `cfg6edit` = '" . $cfg6edit . "', `cfg7name` = '" . $cfg7name . "', `cfg7` = '" . $cfg7 . "', `cfg7edit` = '" . $cfg7edit . "', `cfg8name` = '" . $cfg8name . "', `cfg8` = '" . $cfg8 . "', `cfg8edit` = '" . $cfg8edit . "', `startline` = '" . $startline . "', `port` = '" . $port . "', `qryport` = '" . $qryport . "', `gamedir` = '" . $gamedir . "', `status` = '" . $status . "' WHERE `gameid` = '" . $gameid . "'");
		$_SESSION["msg1"] = "Game Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the game have been saved.";
		header("Location: configgame.php");
		exit;
	case "configgamedelete":
		$gameid = sanitizeInput($_GET["id"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("DELETE FROM `game` WHERE `gameid` = '" . $gameid . "' LIMIT 1");
		$_SESSION["msg1"] = "Game Deleted Successfully!";
		$_SESSION["msg2"] = "The selected game has been removed.";
		header("Location: configgame.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>