<?php
$title = "My Servers";
$page = "server";
$return = "server.php";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$result = dbQuery(
	"SELECT serverid, ipid, name, game, status, online, port, `query`, qryport
	 FROM server
	 WHERE clientid='{$_SESSION["clientid"]}'
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

include __DIR__ . "/templates/default/header.php";
include __DIR__ . "/templates/default/server.php";
include __DIR__ . "/templates/default/footer.php";
