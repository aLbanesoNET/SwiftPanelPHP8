<?php
$title  = "Server Details";
$page   = "server";

require __DIR__ . "/configuration.php";
require __DIR__ . "/include.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$return = "server.php?id=" . urlencode($_GET["id"] ?? "");

$serverid = sanitizeInput($_GET["id"] ?? "");
$clientId = $_SESSION["clientid"] ?? "";

if ($serverid === "" || $clientId === "") {
	header("Location: index.php");
	exit;
}

// Main server row
$srv = dbRow(
	"SELECT *
	 FROM `server`
	 WHERE `serverid` = '" . $serverid . "'
	   AND `clientid` = '" . $clientId . "'
	 LIMIT 1"
);

if (!is_array($srv) || empty($srv)) {
	$_SESSION["msg1"] = "Server Error!";
	$_SESSION["msg2"] = "Server not found.";
	header("Location: servers.php");
	exit;
}

$query = null;

// Optional IP/Box + Live query
if (!empty($srv["ipid"])) {
	$ipRow = dbRow(
		"SELECT `ip`
		 FROM `ip`
		 WHERE `ipid` = '" . ($srv["ipid"] ?? "") . "'
		 LIMIT 1"
	);

	$boxRow = dbRow(
		"SELECT `boxid`, `name`, `location`, `ftpport`
		 FROM `box`
		 WHERE `boxid` = '" . ($srv["boxid"] ?? "") . "'
		 LIMIT 1"
	);

	$ip	   = $ipRow["ip"] ?? "";
	$port	 = $srv["port"] ?? "";
	$queryType = $srv["query"] ?? "none";

	if ($ip !== "" && $port !== "" && $queryType !== "none") {
		$qryport = !empty($srv["qryport"]) ? $srv["qryport"] : $port;
		$query = querySingleServer([$queryType, $ip, $qryport]);
	}

	if (is_array($ipRow) && !empty($ipRow)) {
		$srv = array_merge($srv, $ipRow);
	}

	$srv = array_merge($srv, [
		"boxname"	 => $boxRow["name"] ?? "",
		"boxlocation" => $boxRow["location"] ?? "",
		"ftpport"	 => $boxRow["ftpport"] ?? "",
	]);
}

// Surface a flash set by servermanage.php / serverrebuild.php (they redirect
// here; include.php has already moved msg1/msg2 into $FLASH_MSG*).
$e_msg1 = $e_msg1 ?? ($FLASH_MSG1 ?? null);
$e_msg2 = $e_msg2 ?? ($FLASH_MSG2 ?? null);

// Player-count sparkline (empty string when there is not enough history yet).
require __DIR__ . '/includes/spark.php';
$spark = serverSparkline((int) ($srv['serverid'] ?? 0));

include tpl('header');
include tpl('serversummary');
include tpl('footer');