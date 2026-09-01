<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
include "../includes/ftp.php";
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
$serverid = sanitizeInput($_POST["id"] ?? "");
if(empty($serverid)) {
	$serverid = sanitizeInput($_GET["id"] ?? "");
}
$path = sanitizeInput($_POST["path"] ?? "");
if(empty($path)) {
	$path = sanitizeInput($_GET["path"] ?? "");
}
if(!empty($serverid)) {
	$rows = dbRow("SELECT `ipid`, `boxid`, `user`, `password` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
	if(!empty($rows["ipid"])) {
		$rows1 = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . $rows["ipid"] . "' LIMIT 1");
		$rows2 = dbRow("SELECT `ftpport`, `passive` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
		if($rows2["passive"] == "On") {
			$passive = TRUE;
		} else {
			$passive = FALSE;
		}
		if(!($ftpconnection = get_ftp_connection($rows1["ip"], $rows2["ftpport"], $rows["user"], $rows["password"], $passive))) {
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
	} else {
		$_SESSION["msg1"] = "FTP Connection Failed!";
		$_SESSION["msg2"] = "Could not connect to the FTP.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	}
}
switch ($task) {
	case "filesave":
		$file = $_POST["file"];
		$filecontents = $_POST["filecontents"];
		if(empty($filecontents)) {
			$_SESSION["msg1"] = "File Save Failed!";
			$_SESSION["msg2"] = "The file has not been saved.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$filecontents = str_replace("\\r\\n", "\n", $filecontents);
		$filecontents = str_replace("\\\"", "\"", $filecontents);
		$filecontents = str_replace("\\'", "'", $filecontents);
		$filecontents = str_replace("\\\\", "\\", $filecontents);
		$tempHandle = fopen("php://temp", "r+");
		if($tempHandle == FALSE) {
			$_SESSION["msg1"] = "File Save Failed!";
			$_SESSION["msg2"] = "The file has not been saved.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		if(fwrite($tempHandle, $filecontents) === FALSE) {
			$_SESSION["msg1"] = "File Save Failed!";
			$_SESSION["msg2"] = "The file has not been saved.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		rewind($tempHandle);
		if(substr($path, 0 - 1) == "/" || empty($path)) {
			if(!@ftp_fput($ftpconnection, $path . $file, $tempHandle, FTP_BINARY)) {
				$_SESSION["msg1"] = "File Save Failed!";
				$_SESSION["msg2"] = "The file has not been saved.";
				header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
				exit;
			}
		} elseif(!@ftp_fput($ftpconnection, $path . "/" . $file, $tempHandle, FTP_BINARY)) {
			$_SESSION["msg1"] = "File Save Failed!";
			$_SESSION["msg2"] = "The file has not been saved.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$_SESSION["msg1"] = "File Saved Successfully!";
		$_SESSION["msg2"] = "The file has been saved.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	case "fileupload":
		if(0 < $_FILES["file"]["error"]) {
			$_SESSION["msg1"] = "File Save Failed!";
			$_SESSION["msg2"] = "The file has not been saved.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$filecontents = file_get_contents($_FILES["file"]["tmp_name"]);
		$tempHandle = fopen("php://temp", "r+");
		fwrite($tempHandle, $filecontents);
		rewind($tempHandle);
		if(substr($path, 0 - 1) == "/" || empty($path)) {
			if(!@ftp_fput($ftpconnection, $path . $_FILES["file"]["name"], $tempHandle, FTP_BINARY)) {
				$_SESSION["msg1"] = "File Save Failed!";
				$_SESSION["msg2"] = "The file has not been saved.";
				header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
				exit;
			}
		} elseif(!@ftp_fput($ftpconnection, $path . "/" . $_FILES["file"]["name"], $tempHandle, FTP_BINARY)) {
			$_SESSION["msg1"] = "File Save Failed!";
			$_SESSION["msg2"] = "The file has not been saved.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$_SESSION["msg1"] = "File Saved Successfully!";
		$_SESSION["msg2"] = "The file has been saved.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	case "filedelete":
		$file = $_GET["file"] ?? "";
		if(empty($_GET["file"])) {
			$_SESSION["msg1"] = "File Delete Failed!";
			$_SESSION["msg2"] = "The file has not been deleted.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		if(substr($path, 0 - 1) == "/" || empty($path)) {
			if(!ftpDeleteFile($ftpconnection, $path . $file)) {
				$_SESSION["msg1"] = "File Delete Failed!";
				$_SESSION["msg2"] = "The file has not been deleted.";
				header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
				exit;
			}
		} elseif(!ftpDeleteFile($ftpconnection, $path . "/" . $file)) {
			$_SESSION["msg1"] = "File Delete Failed!";
			$_SESSION["msg2"] = "The file has not been deleted.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$_SESSION["msg1"] = "File Deleted Successfully!";
		$_SESSION["msg2"] = "The file has been deleted.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	case "dirdelete":
		$dir = $_GET["dir"] ?? "";
		if(substr($path, 0 - 1) == "/" || empty($path)) {
			if(!ftpEnsurePath($ftpconnection, $path . $dir)) {
				$_SESSION["msg1"] = "Directory Delete Failed!";
				$_SESSION["msg2"] = "The directory has not been deleted.";
				header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
				exit;
			}
		} elseif(!ftpEnsurePath($ftpconnection, $path . "/" . $dir)) {
			$_SESSION["msg1"] = "Directory Delete Failed!";
			$_SESSION["msg2"] = "The directory has not been deleted.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$_SESSION["msg1"] = "Directory Deleted Successfully!";
		$_SESSION["msg2"] = "The directory has been deleted.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	case "makedir":
		$dir = $_POST["dir"];
		if(substr($path, 0 - 1) == "/" || empty($path)) {
			if(!ftpEnsurePath($ftpconnection, $path . $dir)) {
				$_SESSION["msg1"] = "Directory Creation Failed!";
				$_SESSION["msg2"] = "The directory has not been created.";
				header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
				exit;
			}
		} elseif(!ftpEnsurePath($ftpconnection, $path . "/" . $dir)) {
			$_SESSION["msg1"] = "Directory Creation Failed!";
			$_SESSION["msg2"] = "The directory has not been created.";
			header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
			exit;
		}
		$_SESSION["msg1"] = "Directory Created Successfully!";
		$_SESSION["msg2"] = "The directory has been created.";
		header("Location: serverftp.php?id=" . urlencode($serverid) . "&path=" . urlencode($path));
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>