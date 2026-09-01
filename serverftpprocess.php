<?php
$return = true;
require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/ftp.php';

$task = sanitizeInput($_POST["task"] ?? "");
if ($task === "") {
	$task = sanitizeInput($_GET["task"] ?? "");
}

$serverid = sanitizeInput($_POST["id"] ?? "");
if ($serverid === "") {
	$serverid = sanitizeInput($_GET["id"] ?? "");
}

$path = sanitizeInput($_POST["path"] ?? "");
if ($path === "") {
	$path = sanitizeInput($_GET["path"] ?? "");
}

$ftpconnection = null;

if ($serverid !== "") {
	$rows = dbRow(
		"SELECT ipid, boxid, user, password
		 FROM server
		 WHERE serverid='$serverid' AND clientid='{$_SESSION["clientid"]}'
		 LIMIT 1",
		true
	);

	if (empty($rows) || empty($rows["ipid"])) {
		$_SESSION["msg1"] = "FTP Connection Failed!";
		$_SESSION["msg2"] = "Could not connect to the FTP.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	$rows1 = dbRow("SELECT ip FROM ip WHERE ipid='{$rows["ipid"]}' LIMIT 1", true);
	$rows2 = dbRow("SELECT ftpport, passive FROM box WHERE boxid='{$rows["boxid"]}' LIMIT 1", true);

	$ip = $rows1["ip"] ?? "";
	$ftpport = $rows2["ftpport"] ?? 21;
	$passive = (($rows2["passive"] ?? "") === "On");

	if ($ip === "") {
		$_SESSION["msg1"] = "FTP Connection Failed!";
		$_SESSION["msg2"] = "Could not connect to the FTP.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	$ftpconnection = get_ftp_connection($ip, $ftpport, $rows["user"], $rows["password"], $passive);
	if (!$ftpconnection) {
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}
}

function failFtp(string $serverid, string $path, string $title, string $msg): void
{
	$_SESSION["msg1"] = $title;
	$_SESSION["msg2"] = $msg;
	header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
	exit;
}

function joinFtpPath(string $path, string $name): string
{
	$path = (string)$path;
	if ($path === "" || substr($path, -1) === "/") {
		return $path . $name;
	}
	return $path . "/" . $name;
}

switch ($task) {
	case "filesave": {
		$file = sanitizeInput($_POST["file"] ?? "");
		$filecontents = (string)($_POST["filecontents"] ?? "");

		if ($file === "" || $filecontents === "") {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		$filecontents = str_replace("\\r\\n", "\n", $filecontents);
		$filecontents = str_replace("\\\"", "\"", $filecontents);
		$filecontents = str_replace("\\'", "'", $filecontents);
		$filecontents = str_replace("\\\\", "\\", $filecontents);

		$tempHandle = fopen("php://temp", "r+");
		if ($tempHandle === false) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		if (fwrite($tempHandle, $filecontents) === false) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		rewind($tempHandle);

		$remote = joinFtpPath($path, $file);
		if (!@ftp_fput($ftpconnection, $remote, $tempHandle, FTP_BINARY)) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		$_SESSION["msg1"] = "File Saved Successfully!";
		$_SESSION["msg2"] = "The file has been saved.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	case "fileupload": {
		if (!isset($_FILES["file"]) || (int)($_FILES["file"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		$tmp = (string)($_FILES["file"]["tmp_name"] ?? "");
		$name = (string)($_FILES["file"]["name"] ?? "");
		if ($tmp === "" || $name === "") {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		$filecontents = file_get_contents($tmp);
		if ($filecontents === false) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		$tempHandle = fopen("php://temp", "r+");
		if ($tempHandle === false) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		if (fwrite($tempHandle, $filecontents) === false) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}
		rewind($tempHandle);

		$remote = joinFtpPath($path, $name);
		if (!@ftp_fput($ftpconnection, $remote, $tempHandle, FTP_BINARY)) {
			failFtp($serverid, $path, "File Save Failed!", "The file has not been saved.");
		}

		$_SESSION["msg1"] = "File Saved Successfully!";
		$_SESSION["msg2"] = "The file has been saved.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	case "filedelete": {
		$file = sanitizeInput($_GET["file"] ?? "");
		if ($file === "") {
			failFtp($serverid, $path, "File Delete Failed!", "The file has not been deleted.");
		}

		$remote = joinFtpPath($path, $file);
		if (!ftpDeleteFile($ftpconnection, $remote)) {
			failFtp($serverid, $path, "File Delete Failed!", "The file has not been deleted.");
		}

		$_SESSION["msg1"] = "File Deleted Successfully!";
		$_SESSION["msg2"] = "The file has been deleted.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	case "dirdelete": {
		$dir = sanitizeInput($_GET["dir"] ?? "");
		if ($dir === "") {
			failFtp($serverid, $path, "Directory Delete Failed!", "The directory has not been deleted.");
		}

		$remote = joinFtpPath($path, $dir);
		if (!ftpDeleteDirRecursive($ftpconnection, $remote)) {
			failFtp($serverid, $path, "Directory Delete Failed!", "The directory has not been deleted.");
		}

		$_SESSION["msg1"] = "Directory Deleted Successfully!";
		$_SESSION["msg2"] = "The directory has been deleted.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	case "makedir": {
		$dir = sanitizeInput($_POST["dir"] ?? "");
		if ($dir === "") {
			failFtp($serverid, $path, "Directory Creation Failed!", "The directory has not been created.");
		}

		$remote = joinFtpPath($path, $dir);
		if (!ftpEnsurePath($ftpconnection, $remote)) {
			failFtp($serverid, $path, "Directory Creation Failed!", "The directory has not been created.");
		}

		$_SESSION["msg1"] = "Directory Created Successfully!";
		$_SESSION["msg2"] = "The directory has been created.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}

	default:
		header("Location: index.php");
		exit;
}

function ftpDeleteDirRecursive($ftpconnection, string $filepath): bool
{
	$filepath = preg_replace("/(.+?)\\/*$/", "\\1/", $filepath);
	$list = ftp_nlist($ftpconnection, $filepath);

	if ($list !== false && count($list) > 0) {
		foreach ($list as $item) {
			if (!@ftp_delete($ftpconnection, $item)) {
				if (!ftpDeleteDirRecursive($ftpconnection, $item)) {
					return false;
				}
			}
		}
	}

	$result = @ftp_rmdir($ftpconnection, $filepath);
	return $result !== false;
}
