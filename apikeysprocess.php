<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
requireSameOrigin('index.php');

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

$task = sanitizeInput($_POST['task'] ?? ($_GET['task'] ?? ''));

if ($task === 'create') {
	if (dbCount("SELECT `keyid` FROM `apikey` WHERE `clientid` = '" . $clientId . "'") >= 10) {
		$_SESSION['msg1'] = 'Limit reached';
		$_SESSION['msg2'] = 'You can hold up to 10 API keys.';
		header('Location: apikeys.php');
		exit;
	}

	$label    = trim(sanitizeInput($_POST['label'] ?? '')) ?: 'API key';
	// Checkbox present => read-only; absent (unchecked) => read+write.
	$readonly = (($_POST['readonly'] ?? '') === '1') ? '1' : '0';
	$token    = 'sp_' . rtrim(strtr(base64_encode(random_bytes(30)), '+/', '__'), '=');
	$prefix   = substr($token, 0, 11);

	dbExec(
		"INSERT INTO `apikey` SET `clientid` = '" . $clientId . "', `label` = '" . dbEscape($label) . "', " .
		"`tokenhash` = '" . dbEscape(hash('sha256', $token)) . "', `prefix` = '" . dbEscape($prefix) . "', " .
		"`readonly` = '" . $readonly . "', `created` = NOW()"
	);

	$_SESSION['new_apikey'] = $token;
	$_SESSION['msg1'] = 'API key created';
	$_SESSION['msg2'] = 'Copy it now — it will not be shown again.';
	header('Location: apikeys.php');
	exit;
}

if ($task === 'revoke') {
	$keyid = (int) ($_POST['keyid'] ?? ($_GET['keyid'] ?? 0));
	dbExec("DELETE FROM `apikey` WHERE `keyid` = '" . $keyid . "' AND `clientid` = '" . $clientId . "'");
	$_SESSION['msg1'] = 'API key revoked';
	$_SESSION['msg2'] = 'Any client using it will stop working.';
	header('Location: apikeys.php');
	exit;
}

header('Location: apikeys.php');
