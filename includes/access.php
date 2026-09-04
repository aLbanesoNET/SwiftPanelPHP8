<?php
/**
 * Server access for a client: they either own the server, or an owner has
 * shared it with them (subuser). Subusers get a fixed capability set:
 * view + console + power. Everything else (files, backups, schedules,
 * databases, rename, sharing) stays owner-only.
 */

/**
 * Return the server row if $clientId may access server $serverid, else null.
 * The returned array carries an extra 'is_owner' bool.
 */
function serverForClient(int $clientId, int $serverid): ?array
{
	if ($clientId <= 0 || $serverid <= 0) {
		return null;
	}

	$srv = dbRow("SELECT * FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1", true);
	if (!is_array($srv) || empty($srv)) {
		return null;
	}

	if ((int) $srv['clientid'] === $clientId) {
		$srv['is_owner'] = true;
		return $srv;
	}

	static $hasTable = null;
	if ($hasTable === null) {
		$hasTable = dbCount("SHOW TABLES LIKE 'subuser'") > 0;
	}
	$shared = $hasTable ? dbCount(
		"SELECT `subid` FROM `subuser` WHERE `serverid` = '" . $serverid . "' AND `subclientid` = '" . $clientId . "'"
	) : 0;
	if ($shared > 0) {
		$srv['is_owner'] = false;
		return $srv;
	}

	return null;
}

function clientOwnsServer(int $clientId, int $serverid): bool
{
	$s = serverForClient($clientId, $serverid);
	return $s !== null && !empty($s['is_owner']);
}
