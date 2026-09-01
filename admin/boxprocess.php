<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "boxadd":
		$name = sanitizeInput($_POST["name"] ?? "");
		$location = sanitizeInput($_POST["location"] ?? "");
		$ip = sanitizeInput($_POST["ip"] ?? "");
		$login = sanitizeInput($_POST["login"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$ftpport = sanitizeInput($_POST["ftpport"] ?? "");
		$sshport = sanitizeInput($_POST["sshport"] ?? "");
		$ostype = sanitizeInput($_POST["ostype"] ?? "");
		$cost = sanitizeInput($_POST["cost"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		$verify = sanitizeInput($_POST["verify"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["name"] = $name;
		$_SESSION["location"] = $location;
		$_SESSION["ip"] = $ip;
		$_SESSION["login"] = $login;
		$_SESSION["password"] = $password;
		$_SESSION["ftpport"] = $ftpport;
		$_SESSION["sshport"] = $sshport;
		$_SESSION["ostype"] = $ostype;
		$_SESSION["cost"] = $cost;
		$_SESSION["notes"] = $notes;
		$_SESSION["verify"] = $verify;
		$len = strlen($name);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Server Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($location);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Server Location [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($ip);
		if($len <= "2") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Not Long Enough</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `box` WHERE `ip` = '" . $ip . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Already Used</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `ip` WHERE `ip` = '" . $ip . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($login);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Root Login [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($password);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Root Password [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($ftpport);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>FTP Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($ftpport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>FTP Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($sshport);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>SSH Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($sshport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>SSH Port [ <b>Not Numerical</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: boxadd.php");
			exit;
		}
		if($verify == "on") {
			if(!extension_loaded("ssh2")) {
				$_SESSION["msg1"] = "SSH2 Extension Error!";
				$_SESSION["msg2"] = "SSH2 Extension not detected!";
				header("Location: boxadd.php");
				exit;
			}
			if(!($sshconnection = @ssh2_connect($ip, $sshport))) {
				$_SESSION["msg1"] = "Connection Error!";
				$_SESSION["msg2"] = "Unable to connect to box with SSH.";
				header("Location: boxadd.php");
				exit;
			}
			if(!@ssh2_auth_password($sshconnection, $login, $password)) {
				$_SESSION["msg1"] = "Authentication Error!";
				$_SESSION["msg2"] = "Unable to login to box with SSH.";
				header("Location: boxadd.php");
				exit;
			}
		}
		unset($_SESSION["name"]);
		unset($_SESSION["location"]);
		unset($_SESSION["ip"]);
		unset($_SESSION["login"]);
		unset($_SESSION["password"]);
		unset($_SESSION["ftpport"]);
		unset($_SESSION["sshport"]);
		unset($_SESSION["ostype"]);
		unset($_SESSION["cost"]);
		unset($_SESSION["notes"]);
		unset($_SESSION["verify"]);
		dbExec("INSERT INTO `box` SET `name` = '" . $name . "', `location` = '" . $location . "', `ip` = '" . $ip . "', `login` = '" . $login . "', `password` = '" . @base64_encode($password) . "', `ftpport` = '" . $ftpport . "', `sshport` = '" . $sshport . "', `ostype` = '" . $ostype . "', `cost` = '" . $cost . "', `notes` = '" . $notes . "', `ftp` = 'Online', `ssh` = 'Online', `load` = '~', `idle` = '~', `passive` = 'On'");
		$boxid = dbInsertId();
		$message = "Box Added: <a href=\"boxsummary.php?id=" . $boxid . "\">" . $name . "</a>";
		dbExec("INSERT INTO `log` SET `boxid` = '" . $boxid . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Box Added Successfully!";
		$_SESSION["msg2"] = "The box has been added and is ready for use.";
		header("Location: boxsummary.php?id=" . urlencode($boxid));
		exit;
	case "boxprofile":
		$boxid = sanitizeInput($_POST["boxid"] ?? "");
		$name = sanitizeInput($_POST["name"] ?? "");
		$location = sanitizeInput($_POST["location"] ?? "");
		$ip = sanitizeInput($_POST["ip"] ?? "");
		$login = sanitizeInput($_POST["login"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$ftpport = sanitizeInput($_POST["ftpport"] ?? "");
		$sshport = sanitizeInput($_POST["sshport"] ?? "");
		$ostype = sanitizeInput($_POST["ostype"] ?? "");
		$cost = sanitizeInput($_POST["cost"] ?? "");
		$passive = sanitizeInput($_POST["passive"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		$verify = sanitizeInput($_POST["verify"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["name"] = $name;
		$_SESSION["location"] = $location;
		$_SESSION["ip"] = $ip;
		$_SESSION["login"] = $login;
		$_SESSION["password"] = $password;
		$_SESSION["ftpport"] = $ftpport;
		$_SESSION["sshport"] = $sshport;
		$_SESSION["ostype"] = $ostype;
		$_SESSION["cost"] = $cost;
		$_SESSION["passive"] = $passive;
		$_SESSION["notes"] = $notes;
		$_SESSION["verify"] = $verify;
		$len = strlen($name);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Server Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($location);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Server Location [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($ip);
		if($len <= "2") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Not Long Enough</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `box` WHERE `ip` = '" . $ip . "' && `boxid` != '" . $boxid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Already Used</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `ip` WHERE `ip` = '" . $ip . "' && `boxid` != '" . $boxid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($login);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Root Login [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($ftpport);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>FTP Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($ftpport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>FTP Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($sshport);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>SSH Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($sshport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>SSH Port [ <b>Not Numerical</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: boxprofile.php?id=" . urlencode($boxid));
			exit;
		}
		if(empty($password)) {
			$rows = dbRow("SELECT `password` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
			$password = @base64_decode($rows["password"]);
		}
		if($verify == "on") {
			if(!extension_loaded("ssh2")) {
				$_SESSION["msg1"] = "SSH2 Extension Error!";
				$_SESSION["msg2"] = "SSH2 Extension not detected!";
				header("Location: boxprofile.php?id=" . urlencode($boxid));
				exit;
			}
			if(!($sshconnection = @ssh2_connect($ip, $sshport))) {
				$_SESSION["msg1"] = "Connection Error!";
				$_SESSION["msg2"] = "Unable to connect to box with SSH.";
				header("Location: boxprofile.php?id=" . urlencode($boxid));
				exit;
			}
			if(!@ssh2_auth_password($sshconnection, $login, $password)) {
				$_SESSION["msg1"] = "Authentication Error!";
				$_SESSION["msg2"] = "Unable to login to box with SSH.";
				header("Location: boxprofile.php?id=" . urlencode($boxid));
				exit;
			}
		}
		unset($_SESSION["name"]);
		unset($_SESSION["location"]);
		unset($_SESSION["ip"]);
		unset($_SESSION["login"]);
		unset($_SESSION["password"]);
		unset($_SESSION["ftpport"]);
		unset($_SESSION["sshport"]);
		unset($_SESSION["ostype"]);
		unset($_SESSION["cost"]);
		unset($_SESSION["passive"]);
		unset($_SESSION["notes"]);
		unset($_SESSION["verify"]);
		dbExec("UPDATE `box` SET `name` = '" . $name . "', `location` = '" . $location . "', `ip` = '" . $ip . "', `login` = '" . $login . "', `password` = '" . @base64_encode($password) . "', `ftpport` = '" . $ftpport . "', `sshport` = '" . $sshport . "', `ostype` = '" . $ostype . "', `cost` = '" . $cost . "', `passive` = '" . $passive . "', `notes` = '" . $notes . "' WHERE `boxid` = '" . $boxid . "'");
		$message = "Box Edited: <a href=\"boxsummary.php?id=" . $boxid . "\">" . $name . "</a>";
		dbExec("INSERT INTO `log` SET `boxid` = '" . $boxid . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Box Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the box have been saved.";
		header("Location: boxsummary.php?id=" . urlencode($boxid));
		exit;
	case "boxnotes":
		$boxid = sanitizeInput($_POST["boxid"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("UPDATE `box` SET `notes` = '" . $notes . "' WHERE `boxid` = '" . $boxid . "'");
		$_SESSION["msg1"] = "Admin Notes Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the admin notes have been saved.";
		header("Location: boxsummary.php?id=" . urlencode($boxid));
		exit;
	case "boxdelete":
		$boxid = sanitizeInput($_GET["id"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		if(dbCount("SELECT `ipid` FROM `ip` WHERE `boxid` = '" . $boxid . "'") != 0) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Assigned IP Addresses must be deleted.";
			header("Location: boxsummary.php?id=" . urlencode($boxid));
			exit;
		}
		$rows = dbRow("SELECT `name` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
		dbExec("DELETE FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
		$message = "Box Deleted: " . $rows["name"];
		dbExec("INSERT INTO `log` SET `boxid` = '" . $boxid . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Box Deleted Successfully!";
		$_SESSION["msg2"] = "The selected box has been removed.";
		header("Location: box.php");
		exit;
	case "boxipadd":
		$boxid = sanitizeInput($_POST["boxid"] ?? "");
		$ip = sanitizeInput($_POST["ip"] ?? "");
		$usage = sanitizeInput($_POST["usage"] ?? "");
		$verify = sanitizeInput($_POST["verify"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["ip"] = $ip;
		$_SESSION["usage"] = $usage;
		$_SESSION["verify"] = $verify;
		$len = strlen($ip);
		if($len <= "2") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Not Long Enough</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `box` WHERE `ip` = '" . $ip . "' && `boxid` != '" . $boxid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Already Used</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `ip` WHERE `ip` = '" . $ip . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>IP Address [ <b>Already Used</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: boxipadd.php?id=" . urlencode($boxid));
			exit;
		}
		if($verify == "on") {
			if(!extension_loaded("ssh2")) {
				$_SESSION["msg1"] = "SSH2 Extension Error!";
				$_SESSION["msg2"] = "SSH2 Extension not detected!";
				header("Location: boxprofile.php?id=" . urlencode($boxid));
				exit;
			}
			$rows = dbRow("SELECT `sshport` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
			if(!($sshconnection = @ssh2_connect($ip, $rows["sshport"]))) {
				$_SESSION["msg1"] = "Connection Error!";
				$_SESSION["msg2"] = "Unable to connect to IP Address with SSH.";
				header("Location: boxipadd.php?id=" . urlencode($boxid));
				exit;
			}
		}
		unset($_SESSION["ip"]);
		unset($_SESSION["usage"]);
		unset($_SESSION["verify"]);
		dbExec("INSERT INTO `ip` SET `boxid` = '" . $boxid . "', `ip` = '" . $ip . "', `usage` = '" . $usage . "'");
		$rows1 = dbRow("SELECT `name` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
		$message = "IP Added: " . $ip . " to <a href=\"boxsummary.php?id=" . $boxid . "\">" . $rows1["name"] . "</a>";
		dbExec("INSERT INTO `log` SET `boxid` = '" . $boxid . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "IP Address Added Successfully!";
		$_SESSION["msg2"] = "The IP address has been added and is ready for use.";
		header("Location: boxsummary.php?id=" . urlencode($boxid));
		exit;
	case "boxipedit":
		$ipid = sanitizeInput($_POST["ipid"] ?? "");
		$boxid = sanitizeInput($_POST["boxid"] ?? "");
		$usage = sanitizeInput($_POST["usage"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("UPDATE `ip` SET `usage` = '" . $usage . "' WHERE `ipid` = '" . $ipid . "'");
		$_SESSION["msg1"] = "IP Address Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the IP address have been saved.";
		header("Location: boxsummary.php?id=" . urlencode($boxid));
		exit;
	case "boxipdelete":
		$ipid = sanitizeInput($_GET["ipid"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$rows = dbRow("SELECT `boxid`, `ip` FROM `ip` WHERE `ipid` = '" . $ipid . "'");
		if(dbCount("SELECT * FROM `server` WHERE `ipid` = '" . $ipid . "'") != 0) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Assigned servers must be deleted.";
			header("Location: boxsummary.php?id=" . urlencode($rows["boxid"]));
			exit;
		}
		dbExec("DELETE FROM `ip` WHERE `ipid` = '" . $ipid . "' LIMIT 1");
		$rows1 = dbRow("SELECT `name` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
		$message = "IP Deleted: " . $rows["ip"] . " from <a href=\"boxsummary.php?id=" . $rows["boxid"] . "\">" . $rows1["name"] . "</a>";
		dbExec("INSERT INTO `log` SET `boxid` = '" . $rows["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "IP Address Deleted Successfully!";
		$_SESSION["msg2"] = "The selected IP address has been removed.";
		header("Location: boxsummary.php?id=" . urlencode($rows["boxid"]));
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>