<?php
// PHP 8+ compatible

$return = true;

require __DIR__ . "/configuration.php";
require __DIR__ . "/include.php";
requireSameOrigin('index.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Helper: POST then GET, sanitized
function input_param(string $key, $default = "")
{
	$val = $_POST[$key] ?? ($_GET[$key] ?? $default);
	return sanitizeInput($val);
}

$task = input_param("task", "");

switch ($task) {
	case "serveredit": {
		$clientid = sanitizeInput($_SESSION["clientid"] ?? "");
		$serverid = input_param("serverid", "");

		// Clear messages like original
		unset($_SESSION["msg1"], $_SESSION["msg2"]);

		if ($clientid === "" || $serverid === "") {
			$_SESSION["msg1"] = "Input Error!";
			$_SESSION["msg2"] = "Missing client or server id.";
			header("Location: index.php");
			exit;
		}

		$rows = dbRow(
			"SELECT *
			 FROM `server`
			 WHERE `serverid` = '" . $serverid . "'
			   AND `clientid` = '" . $clientid . "'
			 LIMIT 1"
		);

		if (!is_array($rows) || empty($rows)) {
			$_SESSION["msg1"] = "Server Error!";
			$_SESSION["msg2"] = "Server not found.";
			header("Location: servers.php");
			exit;
		}

		// Read incoming cfg1..cfg8
		$incoming = [];
		for ($i = 1; $i <= 8; $i++) {
			$incoming[$i] = sanitizeInput($_POST["cfg{$i}"] ?? "");
		}

		// Decide final cfg values:
		// If cfgXedit is true AND user provided non-empty -> take first token
		// else keep existing rows[cfgX]
		$final = [];
		for ($i = 1; $i <= 8; $i++) {
			$editFlag = !empty($rows["cfg{$i}edit"]); // treats "1"/1/true as editable
			$val	  = (string)$incoming[$i];

			if ($editFlag && strlen($val) > 0) {
				$parts = preg_split('/\s+/', trim($val));
				$token = $parts[0] ?? "";
				// The token is substituted straight into a shell command line when the
				// server (re)starts (buildStartCommand()/screenStartCommand()) — block
				// shell metacharacters ($, `, ;, |, &, quotes, braces, ...) rather than
				// only splitting on whitespace.
				$final[$i] = preg_match('/^[A-Za-z0-9_.\-\/]*$/', $token) === 1
					? $token
					: (string)($rows["cfg{$i}"] ?? "");
			} else {
				$final[$i] = (string)($rows["cfg{$i}"] ?? "");
			}
		}

		// Optional server rename (only when a non-empty name is submitted).
		$newName = trim(sanitizeInput($_POST["name"] ?? ""));
		$nameSql = "";
		if ($newName !== "" && $newName !== (string) ($rows["name"] ?? "")) {
			$nameSql = "`name` = '" . $newName . "', ";
		}

		dbExec(
			"UPDATE `server` SET
				" . $nameSql . "
				`cfg1` = '" . $final[1] . "',
				`cfg2` = '" . $final[2] . "',
				`cfg3` = '" . $final[3] . "',
				`cfg4` = '" . $final[4] . "',
				`cfg5` = '" . $final[5] . "',
				`cfg6` = '" . $final[6] . "',
				`cfg7` = '" . $final[7] . "',
				`cfg8` = '" . $final[8] . "'
			 WHERE `serverid` = '" . $serverid . "'
			   AND `clientid` = '" . $clientid . "'
			 LIMIT 1"
		);

		$_SESSION["msg1"] = "Server Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to your server have been saved.";
		header("Location: serversummary.php?id=" . urlencode($serverid));
		exit;
	}

	default:
		header("Location: index.php");
		exit;
}
?>