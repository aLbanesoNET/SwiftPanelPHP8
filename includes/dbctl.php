<?php
/**
 * Client MySQL databases.
 *
 * Databases are provisioned on the panel's own MySQL server — the `$connection`
 * handle from includes/mysql.php. For this to work the panel's DB user
 * (DBUSER in configuration.php) must hold, globally:
 *
 *     GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER,
 *           CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW,
 *           SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER,
 *           REFERENCES, CREATE USER
 *       ON *.* TO '<DBUSER>'@'<host>' WITH GRANT OPTION;
 *
 * (broad data access to every schema, but no server-admin privileges — no
 * SUPER/FILE/PROCESS/SHUTDOWN/RELOAD). If these grants are missing every
 * provisioning call fails cleanly with the MySQL error surfaced to the
 * admin/client — nothing else in the panel is affected.
 *
 * Size is measured (information_schema) and displayed; it is NOT enforced.
 * A per-account count limit IS enforced, at creation time (config: clientdb_max).
 */

if (!function_exists('dbEscape')) {
	require_once __DIR__ . '/functions1.php';
}

/** Effective client-database settings, read once from the `config` table. */
function clientDbConfig(): array
{
	static $cache = null;
	if ($cache !== null) {
		return $cache;
	}

	$cache = [
		'enabled' => false,
		'max'     => 2,
		'maxsize' => 200,   // MB, shown as the per-database limit
		'host'    => '%',    // MySQL "allowed from" host for the created user
		'pma'     => '',     // optional phpMyAdmin URL
	];

	$res = dbQuery("SELECT `setting`, `value` FROM `config` WHERE `setting` LIKE 'clientdb\\_%'");
	while ($res && ($row = dbFetch($res))) {
		switch ($row['setting']) {
			case 'clientdb_enabled': $cache['enabled'] = ($row['value'] === '1'); break;
			case 'clientdb_max':     $cache['max']     = max(0, (int) $row['value']); break;
			case 'clientdb_maxsize': $cache['maxsize'] = max(0, (int) $row['value']); break;
			case 'clientdb_host':    $cache['host']    = ($row['value'] !== '') ? $row['value'] : '%'; break;
			case 'clientdb_pma':     $cache['pma']     = (string) $row['value']; break;
		}
	}

	return $cache;
}

/* Passwords are stored base64-encoded, matching box.password (2009 convention). */
function clientDbEncode(string $plain): string { return base64_encode($plain); }
function clientDbDecode(string $stored): string
{
	$d = base64_decode($stored, true);
	return $d === false ? '' : $d;
}

/** Backtick-quote an identifier (doubling any backtick — there are none post-validation). */
function clientDbQuoteIdent(string $id): string
{
	return '`' . str_replace('`', '``', $id) . '`';
}

/** Validate the user-chosen database name segment. Returns the clean value or null. */
function clientDbSanitizeName(string $raw): ?string
{
	$raw = strtolower(trim($raw));
	return preg_match('/^[a-z0-9_]{1,24}$/', $raw) === 1 ? $raw : null;
}

function clientDbValidHost(string $host): bool
{
	return preg_match('/^[A-Za-z0-9_.%:\-]{1,60}$/', $host) === 1;
}

function clientDbGenPassword(): string
{
	return rtrim(strtr(base64_encode(random_bytes(18)), '+/', 'Xx'), '=');
}

/** schema name => size in MB, across the whole server. */
function clientDbUsageMap(): array
{
	global $connection;

	$map = [];
	$res = @mysqli_query(
		$connection,
		"SELECT `table_schema` AS s, ROUND(SUM(`data_length` + `index_length`) / 1048576, 2) AS mb
		 FROM `information_schema`.`tables`
		 GROUP BY `table_schema`"
	);

	if ($res instanceof mysqli_result) {
		while ($row = mysqli_fetch_assoc($res)) {
			$map[$row['s']] = (float) $row['mb'];
		}
		mysqli_free_result($res);
	}

	return $map;
}

/**
 * Create a database + a dedicated user with ALL PRIVILEGES on it.
 * Returns ['dbname','dbuser','dbpass','dbhost']. Throws RuntimeException on failure,
 * rolling back anything it already created.
 */
function clientDbCreate(int $clientId, string $name, string $host): array
{
	global $connection;

	$safe = clientDbSanitizeName($name);
	if ($safe === null) {
		throw new RuntimeException('Database name must be 1-24 characters: lowercase letters, digits or underscore.');
	}
	if (!clientDbValidHost($host)) {
		$host = clientDbConfig()['host'];
	}

	$dbname = 'c' . $clientId . '_' . $safe;
	$dbuser = 'c' . $clientId . '_' . bin2hex(random_bytes(3));
	$pass   = clientDbGenPassword();

	if (strlen($dbname) > 64 || strlen($dbuser) > 32) {
		throw new RuntimeException('Generated identifier is too long — use a shorter name.');
	}
	if (defined('DBNAME') && strcasecmp($dbname, DBNAME) === 0) {
		throw new RuntimeException('That name is reserved.');
	}

	$qDb   = clientDbQuoteIdent($dbname);
	$qUser = clientDbQuoteIdent($dbuser);
	$qHost = "'" . mysqli_real_escape_string($connection, $host) . "'";
	$qPass = "'" . mysqli_real_escape_string($connection, $pass) . "'";

	if (!mysqli_query($connection, "CREATE DATABASE $qDb")) {
		throw new RuntimeException('CREATE DATABASE failed: ' . mysqli_error($connection));
	}

	if (!mysqli_query($connection, "CREATE USER $qUser@$qHost IDENTIFIED BY $qPass")) {
		$err = mysqli_error($connection);
		@mysqli_query($connection, "DROP DATABASE IF EXISTS $qDb");
		throw new RuntimeException('CREATE USER failed: ' . $err);
	}

	// Explicit list (not "ALL PRIVILEGES") so the grant only needs the privileges
	// the panel user is documented to hold — "ALL" also pulls in engine-specific
	// extras (e.g. MariaDB's DELETE HISTORY) that the panel user may not have.
	$grantList = 'SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, '
		. 'CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW, '
		. 'CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER, REFERENCES';

	if (!mysqli_query($connection, "GRANT $grantList ON $qDb.* TO $qUser@$qHost")) {
		$err = mysqli_error($connection);
		@mysqli_query($connection, "DROP USER IF EXISTS $qUser@$qHost");
		@mysqli_query($connection, "DROP DATABASE IF EXISTS $qDb");
		throw new RuntimeException('GRANT failed: ' . $err);
	}

	return ['dbname' => $dbname, 'dbuser' => $dbuser, 'dbpass' => $pass, 'dbhost' => $host];
}

/** Set a fresh password on the database's user. Returns the new plaintext password. */
function clientDbResetPassword(array $row): string
{
	global $connection;

	$pass  = clientDbGenPassword();
	$qUser = clientDbQuoteIdent($row['dbuser']);
	$qHost = "'" . mysqli_real_escape_string($connection, $row['dbhost']) . "'";
	$qPass = "'" . mysqli_real_escape_string($connection, $pass) . "'";

	if (!mysqli_query($connection, "ALTER USER $qUser@$qHost IDENTIFIED BY $qPass")) {
		// Older MySQL / MariaDB
		if (!mysqli_query($connection, "SET PASSWORD FOR $qUser@$qHost = PASSWORD($qPass)")) {
			throw new RuntimeException('Password change failed: ' . mysqli_error($connection));
		}
	}

	return $pass;
}

/** Drop the database and its user. Best-effort — missing objects are ignored. */
function clientDbDelete(array $row): void
{
	global $connection;

	$qDb   = clientDbQuoteIdent($row['dbname']);
	$qUser = clientDbQuoteIdent($row['dbuser']);
	$qHost = "'" . mysqli_real_escape_string($connection, $row['dbhost']) . "'";

	@mysqli_query($connection, "DROP DATABASE IF EXISTS $qDb");
	@mysqli_query($connection, "DROP USER IF EXISTS $qUser@$qHost");
}
