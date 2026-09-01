<?php

/*
 * This code base predates mysqli's exception mode. Since PHP 8.1 the default is
 * to THROW on every failed query; the legacy code never checks return values and
 * would fatal on the first soft error. Restore the historical "return false"
 * behaviour so execution continues as it did under the old mysql_* driver.
 */
mysqli_report(MYSQLI_REPORT_OFF);

/*
 * DBHOST may be "host", "host:port" or ":/path/to/socket". Split it so a
 * non-standard port or a UNIX socket can be configured without extra constants.
 */
$dbHost   = DBHOST;
$dbPort   = null;
$dbSocket = null;

if (str_contains($dbHost, ':')) {
	[$dbHost, $dbTail] = explode(':', $dbHost, 2);
	if ($dbTail !== '' && preg_match('/^\d+$/', $dbTail)) {
		$dbPort = (int) $dbTail;
	} elseif ($dbTail !== '') {
		$dbSocket = $dbTail;
	}
}

if ($dbHost === '') {
	$dbHost = 'localhost';
}

$connection = @mysqli_connect($dbHost, DBUSER, DBPASSWORD, DBNAME, $dbPort, $dbSocket);

if (!$connection) {
	http_response_code(500);
	exit(
		'<div style="border:1px dashed #CC0000;
					 font-family:Tahoma;
					 background-color:#FBEEEB;
					 width:100%;
					 padding:10px;
					 color:#CC0000;
					 text-align:center;">
			<b>Critical Error</b><br />
			Database connection failed.
		</div>'
	);
}

mysqli_set_charset($connection, 'utf8mb4');

/*
 * This application was written for MySQL 5.0 (2009): it relies on lenient
 * behaviour that later servers disable by default (implicit '' for missing
 * NOT NULL columns, zero dates, non-aggregated GROUP BY columns). Restore that
 * behaviour per-connection so the legacy queries keep working unchanged on
 * MySQL 5.7+/8.x and MariaDB 10.x/11.x.
 */
@mysqli_query($connection, "SET SESSION sql_mode = ''");

