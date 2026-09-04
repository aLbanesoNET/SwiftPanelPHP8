<?php
$title = "My Servers";
$page = "server";
$return = "server.php";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$cid = (int) ($_SESSION["clientid"] ?? 0);
$sharedClause = (dbCount("SHOW TABLES LIKE 'subuser'") > 0)
	? " OR serverid IN (SELECT serverid FROM subuser WHERE subclientid='{$cid}')"
	: "";
$result = dbQuery(
	"SELECT serverid, ipid, name, game, status, online, port, `query`, qryport
	 FROM server
	 WHERE clientid='{$cid}'" . $sharedClause . "
	 ORDER BY serverid"
);

$servers = [];

while ($row = dbFetch($result)) {
	if (!empty($row["ipid"])) {
		$ipRow = dbRow("SELECT ip FROM ip WHERE ipid='{$row["ipid"]}' LIMIT 1");
		$row["ip"] = $ipRow["ip"] ?? null;

		if (!empty($row["ip"]) && !empty($row["port"]) && ($row["query"] ?? "none") !== "none") {
			$qryport = !empty($row["qryport"]) ? $row["qryport"] : $row["port"];

			$info = querySingleServer([$row["query"], $row["ip"], $qryport]);

			if ($info) {
				$row["servername"] = $info["Server Name"] ?? "";
				$row["players"] = $info["Players"] ?? "";
				$row["map"] = $info["Current Map"] ?? "";
			}
		}
	}

	$servers[] = $row;
}

$msg1 = $_SESSION["msg1"] ?? null;
$msg2 = $_SESSION["msg2"] ?? null;
unset($_SESSION["msg1"], $_SESSION["msg2"]);

include tpl('header');
include tpl('server');
include tpl('footer');
