<?php
/**
 * In-panel notifications. `notifyClient()` is called from cron / process
 * endpoints; `include.php` loads the unseen count + a short list for the bell.
 * All calls no-op silently if the table isn't there yet.
 */

function notifyTableExists(): bool
{
	static $x = null;
	if ($x === null) {
		$x = dbCount("SHOW TABLES LIKE 'notification'") > 0;
	}
	return $x;
}

function notifyClient(int $clientId, string $kind, string $title, string $body = '', string $url = ''): void
{
	if ($clientId <= 0 || !notifyTableExists()) {
		return;
	}
	dbExec(
		"INSERT INTO `notification` SET " .
		"`clientid` = '" . $clientId . "', " .
		"`kind` = '" . dbEscape(substr($kind, 0, 12)) . "', " .
		"`title` = '" . dbEscape($title) . "', " .
		"`body` = '" . dbEscape($body) . "', " .
		"`url` = '" . dbEscape(substr($url, 0, 255)) . "', " .
		"`seen` = '0', `created` = NOW()"
	);
	// keep the table from growing without bound
	dbExec(
		"DELETE FROM `notification` WHERE `clientid` = '" . $clientId . "' AND `notifid` NOT IN " .
		"(SELECT `notifid` FROM (SELECT `notifid` FROM `notification` WHERE `clientid` = '" . $clientId . "' ORDER BY `notifid` DESC LIMIT 40) t)"
	);
}

function notifyUnseenCount(int $clientId): int
{
	if ($clientId <= 0 || !notifyTableExists()) {
		return 0;
	}
	return dbCount("SELECT `notifid` FROM `notification` WHERE `clientid` = '" . $clientId . "' AND `seen` = '0'");
}

function notifyRecent(int $clientId, int $limit = 20): array
{
	$out = [];
	if ($clientId <= 0 || !notifyTableExists()) {
		return $out;
	}
	$res = dbQuery("SELECT * FROM `notification` WHERE `clientid` = '" . $clientId . "' ORDER BY `notifid` DESC LIMIT " . (int) $limit);
	while ($res && ($r = dbFetch($res))) {
		$out[] = $r;
	}
	return $out;
}
