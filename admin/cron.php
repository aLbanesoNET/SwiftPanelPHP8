<?php
$safemode = @ini_get("safe_mode");
if($safemode != "1" && $safemode != "On" && $safemode != "on") {
	@ini_set("max_execution_time", "120");
	@set_time_limit(120);
}
if(!empty($_SERVER["argv"][0])) {
	$apath = @substr($_SERVER["argv"][0], 0, @strrpos($_SERVER["argv"][0], "/"));
	$path = @substr($apath, 0, @strrpos($apath, "/"));
	$apath = $apath . "/";
	$path = $path . "/";
} elseif(getcwd()) {
	$path = @substr(@getcwd(), 0, @strrpos(@getcwd(), "/"));
	$path = $path . "/";
} else {
	exit("Error. Contact Swift Panel.");
}
require $path . "configuration.php";
require $path . "includes/functions1.php";
require $path . "includes/functions3.php";
require $path . "includes/mysql.php";
include $path . "includes/ftp.php";
error_reporting(0);
if(extension_loaded("ssh2")) {
	$result = dbQuery("SELECT `boxid`, `ip`, `sshport`, `ftpport` FROM `box` ORDER BY `boxid`");
	while ($rows = dbFetch($result)) {
		if(!($sshconnection = @ssh2_connect($rows["ip"], $rows["sshport"]))) {
			$ssh = "Offline";
		} else {
			$ssh = "Online";
		}
		if(!ftp_connect($rows["ip"], $rows["ftpport"], 15)) {
			$ftp = "Offline";
		} else {
			$ftp = "Online";
		}
		dbExec("UPDATE `box` SET `ftp` = '" . $ftp . "', `ssh` = '" . $ssh . "' WHERE `boxid` = '" . $rows["boxid"] . "'");
	}
	dbFreeResult($result);
}
if(extension_loaded("ssh2")) {
	require_once $path . "includes/boxctl.php";
	$result = dbQuery("SELECT `boxid`, `ip`, `login`, `password`, `ssh`, `sshport` FROM `box` ORDER BY `boxid`");
	while ($rows = dbFetch($result)) {
		$load = "~";
		$idle = "~";
		if($rows["ssh"] == "Online") {
			$s = getBoxStats($rows);
			if(!empty($s["ok"])) {
				$load = ($s["load"] ?? "") !== "" ? explode(" ", trim($s["load"]))[0] : "~";
				$idle = ($s["idle"] ?? "") !== "" ? $s["idle"] . "%" : "~";
			}
		}
		dbExec("UPDATE `box` SET `load` = '" . $load . "', `idle` = '" . $idle . "' WHERE `boxid` = '" . $rows["boxid"] . "'");
	}
	dbFreeResult($result);
}
dbExec("UPDATE `config` SET `value` = NOW() WHERE `setting` = 'lastcronrun'");