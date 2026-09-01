<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "serveradd":
		$clientid = sanitizeInput($_POST["clientid"] ?? "");
		$gameid = sanitizeInput($_POST["gameid"] ?? "");
		$name = sanitizeInput($_POST["name"] ?? "");
		$priority = sanitizeInput($_POST["priority"] ?? "");
		$slots = sanitizeInput($_POST["slots"] ?? "");
		$type = sanitizeInput($_POST["type"] ?? "");
		$cfg1 = sanitizeInput($_POST["cfg1"] ?? "");
		$cfg1edit = sanitizeInput($_POST["cfg1edit"] ?? "");
		$cfg2 = sanitizeInput($_POST["cfg2"] ?? "");
		$cfg2edit = sanitizeInput($_POST["cfg2edit"] ?? "");
		$cfg3 = sanitizeInput($_POST["cfg3"] ?? "");
		$cfg3edit = sanitizeInput($_POST["cfg3edit"] ?? "");
		$cfg4 = sanitizeInput($_POST["cfg4"] ?? "");
		$cfg4edit = sanitizeInput($_POST["cfg4edit"] ?? "");
		$cfg5 = sanitizeInput($_POST["cfg5"] ?? "");
		$cfg5edit = sanitizeInput($_POST["cfg5edit"] ?? "");
		$cfg6 = sanitizeInput($_POST["cfg6"] ?? "");
		$cfg6edit = sanitizeInput($_POST["cfg6edit"] ?? "");
		$cfg7 = sanitizeInput($_POST["cfg7"] ?? "");
		$cfg7edit = sanitizeInput($_POST["cfg7edit"] ?? "");
		$cfg8 = sanitizeInput($_POST["cfg8"] ?? "");
		$cfg8edit = sanitizeInput($_POST["cfg8edit"] ?? "");
		$startline = sanitizeInput($_POST["startline"] ?? "");
		$showftp = sanitizeInput($_POST["showftp"] ?? "");
		$webftp = sanitizeInput($_POST["webftp"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["name"] = $name;
		$_SESSION["priority"] = $priority;
		$_SESSION["slots"] = $slots;
		$_SESSION["type"] = $type;
		$_SESSION["cfg1"] = $cfg1;
		$_SESSION["cfg1edit"] = $cfg1edit;
		$_SESSION["cfg2"] = $cfg2;
		$_SESSION["cfg2edit"] = $cfg2edit;
		$_SESSION["cfg3"] = $cfg3;
		$_SESSION["cfg3edit"] = $cfg3edit;
		$_SESSION["cfg4"] = $cfg4;
		$_SESSION["cfg4edit"] = $cfg4edit;
		$_SESSION["cfg5"] = $cfg5;
		$_SESSION["cfg5edit"] = $cfg5edit;
		$_SESSION["cfg6"] = $cfg6;
		$_SESSION["cfg6edit"] = $cfg6edit;
		$_SESSION["cfg7"] = $cfg7;
		$_SESSION["cfg7edit"] = $cfg7edit;
		$_SESSION["cfg8"] = $cfg8;
		$_SESSION["cfg8edit"] = $cfg8edit;
		$_SESSION["startline"] = $startline;
		$_SESSION["showftp"] = $showftp;
		$_SESSION["webftp"] = $webftp;
		$len = strlen($name);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($slots);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Slots [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($slots)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Slots [ <b>Not Numerical</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: serveradd.php?clientid=" . urlencode($clientid) . "&gameid=" . urlencode($gameid));
			exit;
		}
		unset($_SESSION["name"]);
		unset($_SESSION["priority"]);
		unset($_SESSION["slots"]);
		unset($_SESSION["type"]);
		unset($_SESSION["cfg1"]);
		unset($_SESSION["cfg1edit"]);
		unset($_SESSION["cfg2"]);
		unset($_SESSION["cfg2edit"]);
		unset($_SESSION["cfg3"]);
		unset($_SESSION["cfg3edit"]);
		unset($_SESSION["cfg4"]);
		unset($_SESSION["cfg4edit"]);
		unset($_SESSION["cfg5"]);
		unset($_SESSION["cfg5edit"]);
		unset($_SESSION["cfg6"]);
		unset($_SESSION["cfg6edit"]);
		unset($_SESSION["cfg7"]);
		unset($_SESSION["cfg7edit"]);
		unset($_SESSION["cfg8"]);
		unset($_SESSION["cfg8edit"]);
		unset($_SESSION["startline"]);
		unset($_SESSION["showftp"]);
		unset($_SESSION["webftp"]);
		$rows = dbRow("SELECT * FROM `game` WHERE `gameid` = '" . $gameid . "'");
		dbExec("INSERT INTO `server` SET `clientid` = '" . $clientid . "', `name` = '" . $name . "', `game` = '" . $rows["game"] . "', `status` = 'Pending', `query` = '" . $rows["query"] . "', `qryport` = '" . $rows["qryport"] . "', `priority` = '" . $priority . "', `slots` = '" . $slots . "', `type` = '" . $type . "', `cfg1name` = '" . $rows["cfg1name"] . "', `cfg1` = '" . $cfg1 . "', `cfg1edit` = '" . $cfg1edit . "', `cfg2name` = '" . $rows["cfg2name"] . "', `cfg2` = '" . $cfg2 . "', `cfg2edit` = '" . $cfg2edit . "', `cfg3name` = '" . $rows["cfg3name"] . "', `cfg3` = '" . $cfg3 . "', `cfg3edit` = '" . $cfg3edit . "', `cfg4name` = '" . $rows["cfg4name"] . "', `cfg4` = '" . $cfg4 . "', `cfg4edit` = '" . $cfg4edit . "', `cfg5name` = '" . $rows["cfg5name"] . "', `cfg5` = '" . $cfg5 . "', `cfg5edit` = '" . $cfg5edit . "', `cfg6name` = '" . $rows["cfg6name"] . "', `cfg6` = '" . $cfg6 . "', `cfg6edit` = '" . $cfg6edit . "', `cfg7name` = '" . $rows["cfg7name"] . "', `cfg7` = '" . $cfg7 . "', `cfg7edit` = '" . $cfg7edit . "', `cfg8name` = '" . $rows["cfg8name"] . "', `cfg8` = '" . $cfg8 . "', `cfg8edit` = '" . $cfg8edit . "', `startline` = '" . $startline . "', `showftp` = '" . $showftp . "', `webftp` = '" . $webftp . "', `installdir` = '" . $rows["gamedir"] . "', `port` = '" . $rows["port"] . "', `online` = 'Pending'");
		$serverid = dbInsertId();
		$rows1 = dbRow("SELECT `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $clientid . "'");
		$message = "Server Added: <a href=\"serversummary.php?id=" . $serverid . "\">" . $name . "</a> to <a href=\"clientsummary.php?id=" . $clientid . "\">" . $rows1["firstname"] . " " . $rows1["lastname"] . "</a>";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $clientid . "', `serverid` = '" . $serverid . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Server Added Successfully!";
		$_SESSION["msg2"] = "The new server has been added and is ready for use.";
		header("Location: serversummary.php?id=" . urlencode($serverid));
		exit;
	case "serverprofile":
		$serverid = sanitizeInput($_POST["serverid"] ?? "");
		$name = sanitizeInput($_POST["name"] ?? "");
		$game = sanitizeInput($_POST["game"] ?? "");
		$status = sanitizeInput($_POST["status"] ?? "");
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
		$showftp = sanitizeInput($_POST["showftp"] ?? "");
		$webftp = sanitizeInput($_POST["webftp"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["name"] = $name;
		$_SESSION["game"] = $game;
		$_SESSION["status"] = $status;
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
		$_SESSION["showftp"] = $showftp;
		$_SESSION["webftp"] = $webftp;
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
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: serverprofile.php?id=" . urlencode($serverid));
			exit;
		}
		unset($_SESSION["name"]);
		unset($_SESSION["game"]);
		unset($_SESSION["status"]);
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
		unset($_SESSION["showftp"]);
		unset($_SESSION["webftp"]);
		dbExec("UPDATE `server` SET `name` = '" . $name . "', `game` = '" . $game . "', `status` = '" . $status . "', `priority` = '" . $priority . "', `slots` = '" . $slots . "', `type` = '" . $type . "', `cfg1name` = '" . $cfg1name . "', `cfg1` = '" . $cfg1 . "', `cfg1edit` = '" . $cfg1edit . "', `cfg2name` = '" . $cfg2name . "', `cfg2` = '" . $cfg2 . "', `cfg2edit` = '" . $cfg2edit . "', `cfg3name` = '" . $cfg3name . "', `cfg3` = '" . $cfg3 . "', `cfg3edit` = '" . $cfg3edit . "', `cfg4name` = '" . $cfg4name . "', `cfg4` = '" . $cfg4 . "', `cfg4edit` = '" . $cfg4edit . "', `cfg5name` = '" . $cfg5name . "', `cfg5` = '" . $cfg5 . "', `cfg5edit` = '" . $cfg5edit . "', `cfg6name` = '" . $cfg6name . "', `cfg6` = '" . $cfg6 . "', `cfg6edit` = '" . $cfg6edit . "', `cfg7name` = '" . $cfg7name . "', `cfg7` = '" . $cfg7 . "', `cfg7edit` = '" . $cfg7edit . "', `cfg8name` = '" . $cfg8name . "', `cfg8` = '" . $cfg8 . "', `cfg8edit` = '" . $cfg8edit . "', `startline` = '" . $startline . "', `showftp` = '" . $showftp . "', `webftp` = '" . $webftp . "' WHERE `serverid` = '" . $serverid . "'");
		$rows2 = dbRow("SELECT `clientid`, `boxid` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		$message = "Server Edited: <a href=\"serversummary.php?id=" . $serverid . "\">" . $name . "</a>";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $rows2["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows2["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Server Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the server have been saved.";
		header("Location: serversummary.php?id=" . urlencode($serverid));
		exit;
	case "serveradvanced":
		$serverid = sanitizeInput($_POST["serverid"] ?? "");
		$online = sanitizeInput($_POST["online"] ?? "");
		$ipid = sanitizeInput($_POST["ipid"] ?? "");
		$port = sanitizeInput($_POST["port"] ?? "");
		$query = sanitizeInput($_POST["query"] ?? "");
		$qryport = sanitizeInput($_POST["qryport"] ?? "");
		$user = sanitizeInput($_POST["user"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$homedir = sanitizeInput($_POST["homedir"] ?? "");
		$installdir = sanitizeInput($_POST["installdir"] ?? "");
		$modify = sanitizeInput($_POST["modify"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["online"] = $online;
		$_SESSION["ipid"] = $ipid;
		$_SESSION["port"] = $port;
		$_SESSION["query"] = $query;
		$_SESSION["qryport"] = $qryport;
		$_SESSION["user"] = $user;
		$_SESSION["password"] = $password;
		$_SESSION["homedir"] = $homedir;
		$_SESSION["installdir"] = $installdir;
		$_SESSION["modify"] = $modify;
		$len = strlen($port);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($port)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Not Numerical</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `server` WHERE `ipid` = '" . $ipid . "' && `port` = '" . $port . "' && `serverid` != '" . $serverid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($qryport);
		if(!is_numeric($qryport) && !empty($qryport)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Query Port [ <b>Not Numerical</b> ]</li>";
		}
		$len = strlen($user);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>User [ <b>Not Entered</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `server` WHERE `boxid` = '" . $boxid . "' && `user` = '" . $user . "' && `serverid` != '" . $serverid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>User [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($password);
		if("1" <= $len && $len <= "3") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
		}
		$len = strlen($homedir);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Home Directory [ <b>Not Entered</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `server` WHERE `boxid` = '" . $boxid . "' && `homedir` = '" . $homedir . "' && `serverid` != '" . $serverid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Home Directory [ <b>Already Used</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: serveradvanced.php?id=" . urlencode($serverid));
			exit;
		}
		if(isset($_POST["online"])) {
			if(empty($password)) {
				$password = generateRandomString(7);
			}
			$rows = dbRow("SELECT `boxid`, `user`, `password`, `homedir` FROM `server` WHERE `serverid` = '" . $serverid . "'");
			if($modify == "on" && ($user != $rows["user"] || $password != $rows["password"] || $homedir != $rows["homedir"])) {
				if(!extension_loaded("ssh2")) {
					$_SESSION["msg1"] = "SSH2 Extension Error!";
					$_SESSION["msg2"] = "SSH2 Extension not detected!";
					header("Location: serverprofile.php?id=" . urlencode($serverid));
					exit;
				}
				$rows1 = dbRow("SELECT `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "'");
				if(!($sshconnection = @ssh2_connect($rows1["ip"], $rows1["sshport"]))) {
					$_SESSION["msg1"] = "Connection Error!";
					$_SESSION["msg2"] = "Unable to connect to box with SSH.";
					header("Location: serverprofile.php?id=" . urlencode($serverid));
					exit;
				}
				if(!@ssh2_auth_password($sshconnection, $rows1["login"], @base64_decode($rows1["password"]))) {
					$_SESSION["msg1"] = "Authentication Error!";
					$_SESSION["msg2"] = "Unable to login to box with SSH.";
					header("Location: serverprofile.php?id=" . urlencode($serverid));
					exit;
				}
				$sshshell = ssh2_shell($sshconnection, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
				if($user != $rows["user"]) {
					@fwrite($sshshell, "usermod " . $user . "\n");
					sleep(1);
					while ($sshline = fgets($sshshell)) {
						if(preg_match("/no flags given/", $sshline)) {
							$_SESSION["msg1"] = "Command Error!";
							$_SESSION["msg2"] = "User already exist: " . $user;
							header("Location: serverprofile.php?id=" . urlencode($serverid));
							exit;
						}
					}
				}
				if($user != $rows["user"] || $homedir != $rows["homedir"]) {
					@fwrite($sshshell, "usermod -d" . $homedir . " -m -l" . $user . " " . $rows["user"] . "\n");
					sleep(2);
				}
				if($password != $rows["password"]) {
					@fwrite($sshshell, "passwd " . $user . "\n");
					sleep(2);
					@fwrite($sshshell, $password . "\n");
					sleep(2);
					@fwrite($sshshell, $password . "\n");
					sleep(2);
				}
				@fclose($sshshell);
			}
		}
		unset($_SESSION["online"]);
		unset($_SESSION["ipid"]);
		unset($_SESSION["port"]);
		unset($_SESSION["query"]);
		unset($_SESSION["qryport"]);
		unset($_SESSION["user"]);
		unset($_SESSION["password"]);
		unset($_SESSION["homedir"]);
		unset($_SESSION["installdir"]);
		unset($_SESSION["modify"]);
		dbExec("UPDATE `server` SET `ipid` = '" . $ipid . "', `port` = '" . $port . "', `query` = '" . $query . "', `qryport` = '" . $qryport . "', `user` = '" . $user . "', `password` = '" . $password . "', `homedir` = '" . $homedir . "', `installdir` = '" . $installdir . "', `online` = '" . $online . "' WHERE `serverid` = '" . $serverid . "'");
		$rows2 = dbRow("SELECT `clientid`, `boxid` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		$message = "Server Edited: <a href=\"serversummary.php?id=" . $serverid . "\">" . $name . "</a>";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $rows2["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows2["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Server Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the server have been saved.";
		header("Location: serversummary.php?id=" . urlencode($serverid));
		exit;
	case "serverinstall":
		$serverid = sanitizeInput($_POST["serverid"] ?? "");
		$boxid = sanitizeInput($_POST["boxid"] ?? "");
		$ipid = sanitizeInput($_POST["ipid"] ?? "");
		$port = sanitizeInput($_POST["port"] ?? "");
		$user = sanitizeInput($_POST["user"] ?? "");
		$password = sanitizeInput($_POST["password"] ?? "");
		$homedir = sanitizeInput($_POST["homedir"] ?? "");
		$adduser = sanitizeInput($_POST["adduser"] ?? "");
		$installdir = sanitizeInput($_POST["installdir"] ?? "");
		$install = sanitizeInput($_POST["install"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["port"] = $port;
		$_SESSION["user"] = $user;
		$_SESSION["password"] = $password;
		$_SESSION["homedir"] = $homedir;
		$_SESSION["adduser"] = $adduser;
		$_SESSION["installdir"] = $installdir;
		$_SESSION["install"] = $install;
		$len = strlen($port);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Not Entered</b> ]</li>";
		} elseif(!is_numeric($port)) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Not Numerical</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `server` WHERE `ipid` = '" . $ipid . "' && `port` = '" . $port . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Port [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($user);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>User [ <b>Not Entered</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `server` WHERE `boxid` = '" . $boxid . "' && `user` = '" . $user . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>User [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($password);
		if("1" <= $len && $len <= "3") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
		}
		$len = strlen($homedir);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Home Directory [ <b>Not Entered</b> ]</li>";
		} elseif(dbCount("SELECT * FROM `server` WHERE `boxid` = '" . $boxid . "' && `homedir` = '" . $homedir . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Home Directory [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($installdir);
		if($len <= "0" && $install == "on") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Install Directory [ <b>Not Entered</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
			exit;
		}
		if(empty($password)) {
			$password = generateRandomString(7);
		}
		if($adduser == "on" || $install == "on") {
			if(!extension_loaded("ssh2")) {
				$_SESSION["msg1"] = "SSH2 Extension Error!";
				$_SESSION["msg2"] = "SSH2 Extension not detected!";
				header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
				exit;
			}
			$rows = dbRow("SELECT `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '" . $boxid . "'");
			if(!($sshconnection = @ssh2_connect($rows["ip"], $rows["sshport"]))) {
				$_SESSION["msg1"] = "Connection Error!";
				$_SESSION["msg2"] = "Unable to connect to box with SSH.";
				header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
				exit;
			}
			if(!@ssh2_auth_password($sshconnection, $rows["login"], @base64_decode($rows["password"]))) {
				$_SESSION["msg1"] = "Authentication Error!";
				$_SESSION["msg2"] = "Unable to login to box with SSH.";
				header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
				exit;
			}
			error_reporting(E_ALL);
			$sshshell = @ssh2_shell($sshconnection, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
			if($adduser == "on") {
				@fwrite($sshshell, "usermod " . $user . "\n");
				sleep(1);
				while ($sshline = fgets($sshshell)) {
					if(preg_match("/no flags given/", $sshline)) {
						$_SESSION["msg1"] = "Command Error!";
						$_SESSION["msg2"] = "User already exist: " . $user;
						header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
						exit;
					}
				}
			}
			if($install == "on") {
				@fwrite($sshshell, "cd " . $installdir . "\n");
				sleep(1);
				while ($sshline = fgets($sshshell)) {
					if(preg_match("/No such file or directory/", $sshline)) {
						$_SESSION["msg1"] = "Command Error!";
						$_SESSION["msg2"] = "Could not change to directory: " . $installdir;
						header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
						exit;
					}
				}
				if($adduser != "on") {
					@fwrite($sshshell, "usermod " . $user . "\n");
					sleep(1);
					while ($sshline = fgets($sshshell)) {
						if(preg_match("/does not exist/", $sshline)) {
							$_SESSION["msg1"] = "Command Error!";
							$_SESSION["msg2"] = "User does not exist: " . $user;
							header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
							exit;
						}
					}
					@fwrite($sshshell, "cd " . $homedir . "\n");
					sleep(1);
					while ($sshline = fgets($sshshell)) {
						if(preg_match("/No such file or directory/", $sshline)) {
							$_SESSION["msg1"] = "Command Error!";
							$_SESSION["msg2"] = "Could not change to directory: " . $homedir;
							header("Location: serverinstall.php?id=" . urlencode($serverid) . "&boxid=" . urlencode($boxid) . "&ipid=" . urlencode($ipid));
							exit;
						}
					}
				}
				@fwrite($sshshell, "cd\n");
				sleep(1);
			}
			if($adduser == "on") {
				@fwrite($sshshell, "useradd -d" . $homedir . " -m " . $user . "\n");
				sleep(3);
				@fwrite($sshshell, "passwd " . $user . "\n");
				sleep(2);
				@fwrite($sshshell, $password . "\n");
				sleep(2);
				@fwrite($sshshell, $password . "\n");
				sleep(2);
			}
			if($install == "on") {
				@fwrite($sshshell, "screen -m -S serverinstall\n");
				sleep(2);
				@fwrite($sshshell, "nice -n 19 cp -Rf " . $installdir . "/* " . $homedir . " && chown -Rf " . $user . ":" . $user . " " . $homedir . " && exit\n");
				sleep(2);
			}
			@fclose($sshshell);
		}
		unset($_SESSION["port"]);
		unset($_SESSION["user"]);
		unset($_SESSION["password"]);
		unset($_SESSION["homedir"]);
		unset($_SESSION["adduser"]);
		unset($_SESSION["installdir"]);
		unset($_SESSION["install"]);
		dbExec("UPDATE `server` SET `boxid` = '" . $boxid . "', `ipid` = '" . $ipid . "', `status` = 'Active', `user` = '" . $user . "', `password` = '" . $password . "', `homedir` = '" . $homedir . "', `installdir` = '" . $installdir . "', `port` = '" . $port . "', `online` = 'Stopped' WHERE `serverid` = '" . $serverid . "'");
		$rows1 = dbRow("SELECT `clientid`, `name` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		$rows2 = dbRow("SELECT `name` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
		$message = "Server Installed: <a href=\"serversummary.php?id=" . $serverid . "\">" . $rows1["name"] . "</a> on <a href=\"boxsummary.php?id=" . $boxid . "\">" . $rows2["name"] . "</a>";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $rows1["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $boxid . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Install Wizard Successfully!";
		if($install == "on") {
			$_SESSION["msg2"] = "The server has been installed. Allow 5 minutes for server files to transfer before starting.";
		} else {
			$_SESSION["msg2"] = "The server is ready for use.";
		}
		header("Location: serversummary.php?id=" . urlencode($serverid));
		exit;
	case "serverrebuild":
		$serverid = sanitizeInput($_GET["serverid"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$rows = dbRow("SELECT `boxid`, `user`, `password`, `homedir`, `installdir`, `online` FROM `server` WHERE `serverid` = '" . $serverid . "'");
		if(empty($rows["homedir"]) || empty($rows["installdir"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Invalid Directory.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if($rows["online"] == "Started") {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Server must be stopped.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if(!extension_loaded("ssh2")) {
			$_SESSION["msg1"] = "SSH2 Extension Error!";
			$_SESSION["msg2"] = "SSH2 Extension not detected!";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		$rows1 = dbRow("SELECT `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "'");
		if(!($sshconnection = @ssh2_connect($rows1["ip"], $rows1["sshport"]))) {
			$_SESSION["msg1"] = "Connection Error!";
			$_SESSION["msg2"] = "Unable to connect to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if(!@ssh2_auth_password($sshconnection, $rows1["login"], @base64_decode($rows1["password"]))) {
			$_SESSION["msg1"] = "Authentication Error!";
			$_SESSION["msg2"] = "Unable to login to box with SSH.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		$sshshell = @ssh2_shell($sshconnection, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
		@fwrite($sshshell, "cd " . $rows["installdir"] . "\n");
		sleep(1);
		while ($sshline = fgets($sshshell)) {
			if(preg_match("/No such file or directory/", $sshline)) {
				$_SESSION["msg1"] = "Command Error!";
				$_SESSION["msg2"] = "Could not change to directory: " . $rows["installdir"];
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
		}
		@fwrite($sshshell, "usermod " . $rows["user"] . "\n");
		sleep(1);
		while ($sshline = fgets($sshshell)) {
			if(preg_match("/does not exist/", $sshline)) {
				$_SESSION["msg1"] = "Command Error!";
				$_SESSION["msg2"] = "User does not exist: " . $rows["user"];
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
		}
		@fwrite($sshshell, "cd " . $rows["homedir"] . "\n");
		sleep(1);
		while ($sshline = fgets($sshshell)) {
			if(preg_match("/No such file or directory/", $sshline)) {
				$_SESSION["msg1"] = "Command Error!";
				$_SESSION["msg2"] = "Could not change to directory: " . $rows["homedir"];
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
		}
		if(empty($rows["homedir"]) || empty($rows["installdir"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Invalid Directory.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		@fwrite($sshshell, "cd\n");
		sleep(1);
		@fwrite($sshshell, "screen -m -S serverrebuild\n");
		sleep(2);
		@fwrite($sshshell, "nice -n 19 rm -Rf " . $rows["homedir"] . "/* && nice -n 19 cp -Rf " . $rows["installdir"] . "/* " . $rows["homedir"] . " && chown -Rf " . $rows["user"] . ":" . $rows["user"] . " " . $rows["homedir"] . " && exit\n");
		sleep(2);
		@fclose($sshshell);
		$rows2 = dbRow("SELECT `clientid`, `boxid`, `name` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		$rows3 = dbRow("SELECT `name` FROM `box` WHERE `boxid` = '" . $rows2["boxid"] . "' LIMIT 1");
		$message = "Server Rebuilt: <a href=\"serversummary.php?id=" . $serverid . "\">" . $rows2["name"] . "</a> on <a href=\"boxsummary.php?id=" . $rows2["boxid"] . "\">" . $rows3["name"] . "</a>";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $rows2["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows2["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Rebuild Successfully!";
		$_SESSION["msg2"] = "The server has been rebuilt. Allow 5 minutes for server files to transfer before starting.";
		header("Location: serversummary.php?id=" . urlencode($serverid));
		exit;
	case "serverdelete":
		$serverid = sanitizeInput($_GET["serverid"] ?? "");
		$delete = sanitizeInput($_GET["delete"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$rows = dbRow("SELECT `clientid`, `boxid`, `name`, `user`, `online` FROM `server` WHERE `serverid` = '" . $serverid . "'");
		if($rows["online"] == "Started") {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Server must be stopped.";
			header("Location: serversummary.php?id=" . urlencode($serverid));
			exit;
		}
		if($delete == "yes") {
			if(!extension_loaded("ssh2")) {
				$_SESSION["msg1"] = "SSH2 Extension Error!";
				$_SESSION["msg2"] = "SSH2 Extension not detected!";
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
			$rows1 = dbRow("SELECT `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "'");
			if(!($sshconnection = @ssh2_connect($rows1["ip"], $rows1["sshport"]))) {
				$_SESSION["msg1"] = "Connection Error!";
				$_SESSION["msg2"] = "Unable to connect to box with SSH.";
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
			if(!@ssh2_auth_password($sshconnection, $rows1["login"], @base64_decode($rows1["password"]))) {
				$_SESSION["msg1"] = "Authentication Error!";
				$_SESSION["msg2"] = "Unable to login to box with SSH.";
				header("Location: serversummary.php?id=" . urlencode($serverid));
				exit;
			}
			$sshshell = @ssh2_shell($sshconnection, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
			@fwrite($sshshell, "usermod " . $rows["user"] . "\n");
			sleep(1);
			while ($sshline = fgets($sshshell)) {
				if(preg_match("/does not exist/", $sshline)) {
					$_SESSION["msg1"] = "Command Error!";
					$_SESSION["msg2"] = "User does not exist: " . $rows["user"];
					header("Location: serversummary.php?id=" . urlencode($serverid));
					exit;
				}
			}
			@fwrite($sshshell, "screen -m -S serverdelete\n");
			sleep(2);
			@fwrite($sshshell, "nice -n 19 userdel -rf " . $rows["user"] . " && exit\n");
			sleep(2);
			@fclose($sshshell);
		}
		dbExec("DELETE FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
		if($delete == "yes") {
			$rows2 = dbRow("SELECT `name` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
			$rows3 = dbRow("SELECT `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $rows["clientid"] . "' LIMIT 1");
			$message = "Server Deleted: " . $rows["name"] . " on <a href=\"boxsummary.php?id=" . $rows["boxid"] . "\">" . $rows2["name"] . "</a> from <a href=\"clientsummary.php?id=" . $rows["clientid"] . "\">" . $rows3["firstname"] . " " . $rows3["lastname"] . "</a>";
		} else {
			$rows2 = dbRow("SELECT `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $rows["clientid"] . "' LIMIT 1");
			$message = "Server Deleted: " . $rows["name"] . " from <a href=\"clientsummary.php?id=" . $rows["clientid"] . "\">" . $rows2["firstname"] . " " . $rows2["lastname"] . "</a>";
		}
		dbExec("INSERT INTO `log` SET `clientid` = '" . $rows["clientid"] . "', `serverid` = '" . $serverid . "', `boxid` = '" . $rows["boxid"] . "', `message` = '" . $message . "', `name` = '" . $_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"] . "', `ip` = '" . $_SERVER["REMOTE_ADDR"] . "'");
		$_SESSION["msg1"] = "Server Deleted Successfully!";
		$_SESSION["msg2"] = "The selected server has been removed.";
		header("Location: clientsummary.php?id=" . urlencode($rows["clientid"]));
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>