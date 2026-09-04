<?php
$title = "My Account";
$page = "profile";
$return = "profile.php";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/totp.php';

$client = dbRow(
	"SELECT * FROM client
	 WHERE clientid='{$_SESSION["clientid"]}'
	 LIMIT 1"
);

$firstname = $client["firstname"];
$lastname  = $client["lastname"];
$email	 = $client["email"];
$password  = "";

$totpEnabled   = !empty($client["totp"]);
$totpRecovery  = totpRecoveryRemaining((string) ($client["totp_recovery"] ?? ''));
$totpNewCodes  = $_SESSION['2fa_codes'] ?? [];
unset($_SESSION['2fa_codes']);
$totpSetup   = '';
$totpUri     = '';
if (!$totpEnabled) {
	if (empty($_SESSION["2fa_setup"])) {
		$_SESSION["2fa_setup"] = totpSecret();
	}
	$totpSetup = (string) $_SESSION["2fa_setup"];
	$totpUri   = totpUri($totpSetup, (string) $email, (string) SITENAME);
}

$msg1 = $_SESSION["msg1"] ?? null;
$msg2 = $_SESSION["msg2"] ?? null;

unset($_SESSION["msg1"], $_SESSION["msg2"]);

$loginHistory = [];
if (dbCount("SHOW TABLES LIKE 'loginlog'") > 0) {
	$lh = dbQuery("SELECT * FROM `loginlog` WHERE `clientid` = '" . (int) $_SESSION["clientid"] . "' ORDER BY `logid` DESC LIMIT 8");
	while ($lh && ($r = dbFetch($lh))) { $loginHistory[] = $r; }
}

include tpl('header');
include tpl('profile');
include tpl('footer');
