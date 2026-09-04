<?php
$title = "Server Web FTP";
$page = "serverftp";

$return = "serverftp.php?id=" . urlencode($_GET["id"] ?? "");

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';
require __DIR__ . '/includes/ftp.php';

$serverid = sanitizeInput($_GET["id"] ?? "");
$path = sanitizeInput($_GET["path"] ?? "");
$file = sanitizeInput($_GET["file"] ?? "");

$rows = dbRow(
	"SELECT serverid, ipid, boxid, user, password, webftp
	 FROM server
	 WHERE serverid='$serverid' AND clientid='{$_SESSION["clientid"]}'
	 LIMIT 1"
);

if (empty($rows) || empty($rows["webftp"])) {
	header("Location: serversummary.php?id=" . urlencode($serverid));
	exit;
}

$folders = [];
$files = [];
$links = [];
$filecontents = "";

if (!empty($rows["ipid"])) {
	$rows1 = dbRow("SELECT ip FROM ip WHERE ipid='{$rows["ipid"]}' LIMIT 1");
	$rows2 = dbRow("SELECT ftpport, passive FROM box WHERE boxid='{$rows["boxid"]}' LIMIT 1");

	$ip = $rows1["ip"] ?? "";
	$ftpport = $rows2["ftpport"] ?? 21;
	$passive = (($rows2["passive"] ?? "") === "On");

	if ($ip !== "") {
		$ftpconnection = get_ftp_connection($ip, $ftpport, $rows["user"], $rows["password"], $passive);

		if ($ftpconnection) {
			if ($file === "") {
				$array = ftp_rawlist($ftpconnection, $path);
				if (!is_array($array)) {
					$path = normalizeFtpPath($path);
					$array = ftp_rawlist($ftpconnection, $path);
				}

				if (is_array($array)) {
					foreach ($array as $entry) {
						$current = preg_split("/[\\s]+/", $entry, 9);
						if (count($current) < 9) {
							continue;
						}

						$name = str_replace("//", "", $current[8]);

						if ($name === "." || $name === "..") {
							continue;
						}

						$struc = [];
						$struc["perms"] = $current[0];
						$struc["permsn"] = permsToChmod($current[0]);
						$struc["number"] = $current[1];
						$struc["owner"] = $current[2];
						$struc["group"] = $current[3];
						$struc["size"] = formatBytesIEC((int)$current[4]);
						$struc["month"] = $current[5];
						$struc["day"] = $current[6];
						$struc["time"] = $current[7];
						$struc["name"] = $name;

						if (substr($path, 0, 1) === "/") {
							$struc["path"] = urlencode(rtrim($path, "/") . "/" . $name);
						} else {
							$struc["path"] = urlencode($path . $name . "/");
						}

						$type = getFtpItemType($struc["perms"]);

						if ($type === "folder") {
							$folders[] = $struc;
						} elseif ($type === "link") {
							$links[] = $struc;
						} else {
							$struc["link"] = makeFtpFileLink($struc["name"]);
							$files[] = $struc;
						}
					}
				}
			} else {
				$tempHandle = fopen("php://temp", "r+");

				if (substr($path, -1) === "/") {
					if (!@ftp_fget($ftpconnection, $tempHandle, $path . $file, FTP_BINARY)) {
						$path = normalizeFtpPath($path);
						@ftp_fget($ftpconnection, $tempHandle, rtrim($path, "/") . "/" . $file, FTP_BINARY);
					}
				} else {
					if (!@ftp_fget($ftpconnection, $tempHandle, $path . "/" . $file, FTP_BINARY)) {
						$path = normalizeFtpPath($path);
						@ftp_fget($ftpconnection, $tempHandle, $path . $file, FTP_BINARY);
					}
				}

				rewind($tempHandle);
				$filecontents = (string)stream_get_contents($tempHandle);
			}
		}
	}
}

$pathEncoded = urlencode($path);
$pathDecoded = $path;
$breadCrumb = buildFtpBreadcrumb($path);

$msg1 = $_SESSION["msg1"] ?? null;
$msg2 = $_SESSION["msg2"] ?? null;
unset($_SESSION["msg1"], $_SESSION["msg2"]);

$maxFilesize = ini_get("upload_max_filesize");

// Bridge to the names the view partial expects.
$srv           = $rows;
$e_msg1        = $msg1;
$e_msg2        = $msg2;
$bread_crumb   = $breadCrumb;
$path_decoded  = $pathDecoded;
$file_contents = $filecontents;
$max_filesize  = $maxFilesize;

include __DIR__ . "/templates/default/header.php";
include __DIR__ . "/templates/default/serverftp.php";
include __DIR__ . "/templates/default/footer.php";