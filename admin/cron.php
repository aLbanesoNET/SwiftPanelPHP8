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
// Run due server schedules.
if (dbCount("SHOW TABLES LIKE 'schedule'") > 0) {
	require_once $path . "includes/serverpower.php";
	require_once $path . "includes/console.php";
	require_once $path . "includes/schedule.php";

	$schedRes = dbQuery("SELECT * FROM `schedule` WHERE `enabled` = '1' AND `nextrun` IS NOT NULL AND `nextrun` <= NOW()");
	while ($sc = dbFetch($schedRes)) {
		$srv = dbRow("SELECT * FROM `server` WHERE `serverid` = '" . (int) $sc["serverid"] . "' LIMIT 1", TRUE);
		if (!is_array($srv) || empty($srv)) {
			dbExec("DELETE FROM `schedule` WHERE `schedid` = '" . (int) $sc["schedid"] . "'");
			continue;
		}

		$sbox = dbRow("SELECT `ip`, `sshport` FROM `box` WHERE `boxid` = '" . (int) $srv["boxid"] . "' LIMIT 1", TRUE);
		$sip  = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . (int) $srv["ipid"] . "' LIMIT 1", TRUE);
		$serverIp = $sip["ip"] ?? "";

		$done = "skipped (connection failed)";
		$ssh  = serverPowerConnect($srv, is_array($sbox) ? $sbox : []);
		if ($ssh) {
			switch ($sc["action"]) {
				case "stop":
					serverPowerStop($ssh, $srv, true);
					dbExec("UPDATE `server` SET `online` = 'Stopped' WHERE `serverid` = '" . (int) $srv["serverid"] . "'");
					$done = "stopped";
					break;
				case "start":
					serverPowerStart($ssh, $srv, $serverIp);
					dbExec("UPDATE `server` SET `online` = 'Started' WHERE `serverid` = '" . (int) $srv["serverid"] . "'");
					$done = "started";
					break;
				case "restart":
					serverPowerStop($ssh, $srv, false);
					serverPowerStart($ssh, $srv, $serverIp);
					dbExec("UPDATE `server` SET `online` = 'Started' WHERE `serverid` = '" . (int) $srv["serverid"] . "'");
					$done = "restarted";
					break;
				case "command":
					serverConsoleCommand($srv, is_array($sbox) ? $sbox : [], (string) $sc["command"]);
					$done = "ran command";
					break;
			}
		}

		$msg = "Scheduled " . htmlspecialchars((string) $sc["label"], ENT_QUOTES, "UTF-8")
			. ' on <a href="serversummary.php?id=' . (int) $srv["serverid"] . '">#' . (int) $srv["serverid"] . '</a>: ' . $done;
		dbExec(
			"INSERT INTO `log` SET `clientid` = '" . (int) $srv["clientid"] . "', `serverid` = '" . (int) $srv["serverid"] . "', " .
			"`boxid` = '" . (int) $srv["boxid"] . "', `message` = '" . dbEscape($msg) . "', `name` = 'Scheduler', `ip` = 'cron'"
		);

		$next = scheduleNextRun($sc, time());
		dbExec("UPDATE `schedule` SET `lastrun` = NOW(), `nextrun` = '" . date("Y-m-d H:i:s", $next) . "' WHERE `schedid` = '" . (int) $sc["schedid"] . "'");
	}
	dbFreeResult($schedRes);
}

// Sample player counts for running servers (7-day history for sparklines).
if (dbCount("SHOW TABLES LIKE 'serverstat'") > 0) {
	$statRes = dbQuery("SELECT `serverid`, `ipid`, `query`, `port`, `qryport`, `slots` FROM `server` WHERE `online` = 'Started' AND `query` != '' AND `query` != 'none'");
	while ($ss = dbFetch($statRes)) {
		$sipRow = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . (int) $ss["ipid"] . "' LIMIT 1", TRUE);
		$sQryIp = $sipRow["ip"] ?? "";
		if ($sQryIp === "") { continue; }
		$sQryPort = !empty($ss["qryport"]) ? $ss["qryport"] : $ss["port"];
		$info = querySingleServer([$ss["query"], $sQryIp, $sQryPort]);
		if (!is_array($info)) { continue; }
		$players = (int) preg_replace('/\D.*/', '', (string) ($info["Players"] ?? "0"));
		$maxp    = (int) ($info["Max Players"] ?? $ss["slots"] ?? 0);
		dbExec("INSERT INTO `serverstat` SET `serverid` = '" . (int) $ss["serverid"] . "', `ts` = NOW(), `players` = '" . $players . "', `maxplayers` = '" . $maxp . "'");
	}
	dbFreeResult($statRes);
	dbExec("DELETE FROM `serverstat` WHERE `ts` < DATE_SUB(NOW(), INTERVAL 7 DAY)");
}

// Measure client-database disk usage (display only — not enforced).
if (dbCount("SHOW TABLES LIKE 'clientdatabase'") > 0) {
	$usage = [];
	$ures = @mysqli_query($connection, "SELECT `table_schema` AS s, ROUND(SUM(`data_length` + `index_length`) / 1048576, 2) AS mb FROM `information_schema`.`tables` GROUP BY `table_schema`");
	if ($ures instanceof mysqli_result) {
		while ($urow = mysqli_fetch_assoc($ures)) {
			$usage[$urow["s"]] = (float) $urow["mb"];
		}
		mysqli_free_result($ures);
	}
	$cdres = dbQuery("SELECT `dbid`, `dbname` FROM `clientdatabase`");
	while ($cdrow = dbFetch($cdres)) {
		$mb = $usage[$cdrow["dbname"]] ?? 0;
		dbExec("UPDATE `clientdatabase` SET `disksize` = '" . (float) $mb . "' WHERE `dbid` = '" . (int) $cdrow["dbid"] . "'");
	}
	dbFreeResult($cdres);
}

dbExec("UPDATE `config` SET `value` = NOW() WHERE `setting` = 'lastcronrun'");