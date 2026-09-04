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

$totpEnabled = !empty($client["totp"]);
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

include tpl('header');
include tpl('profile');
include tpl('footer');
