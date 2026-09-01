<?php
$title = "Server Web FTP";
$page = "serverftp";
$tab = "3";
$return = "serverftp.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
include "../includes/ftp.php";
$serverid = sanitizeInput($_GET["id"] ?? "");
$path = sanitizeInput($_GET["path"] ?? "");
$file = sanitizeInput($_GET["file"] ?? "");
$folders = array();
$files = array();
$links = array();
$filecontents = "";
$rows1 = array();
$rows2 = array();
$rows = dbRow("SELECT `serverid`, `ipid`, `boxid`, `user`, `password` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
if(!empty($rows["ipid"])) {
	$rows1 = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . $rows["ipid"] . "' LIMIT 1");
	$rows2 = dbRow("SELECT `ftpport`, `passive` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
	if($rows2["passive"] == "On") {
		$passive = TRUE;
	} else {
		$passive = FALSE;
	}
	if($ftpconnection = get_ftp_connection($rows1["ip"], $rows2["ftpport"], $rows["user"], $rows["password"], $passive)) {
		if(empty($file)) {
			$array = ftp_rawlist($ftpconnection, $path);
			if(!is_array($array)) {
				$path = normalizePath($path);
				$array = ftp_rawlist($ftpconnection, $path);
			}
			if(is_array($array)) {
				foreach ($array as $folder) {
					$struc = array();
					$current = preg_split("/[\\s]+/", $folder, 9);
					$struc["perms"] = $current[0];
					$struc["permsn"] = permsToChmod($current[0]);
					$struc["number"] = $current[1];
					$struc["owner"] = $current[2];
					$struc["group"] = $current[3];
					$struc["size"] = formatBytesIEC($current[4]);
					$struc["month"] = $current[5];
					$struc["day"] = $current[6];
					$struc["time"] = $current[7];
					$struc["name"] = str_replace("//", "", $current[8]);
					if($struc["name"] != "." && $struc["name"] != "..") {
						if(getFtpItemType($struc["perms"]) == "folder") {
							$folders[] = $struc;
						} elseif(getFtpItemType($struc["perms"]) == "link") {
							$links[] = $struc;
						} else {
							$files[] = $struc;
						}
					}
				}
			}
		} else {
			$tempHandle = fopen("php://temp", "r+");
			if(substr($path, 0 - 1) == "/") {
				if(!@ftp_fget($ftpconnection, $tempHandle, $path . $file, FTP_BINARY)) {
					$path = normalizePath($path);
					@ftp_fget($ftpconnection, $tempHandle, $path . "/" . $file, FTP_BINARY);
				}
			} elseif(!@ftp_fget($ftpconnection, $tempHandle, $path . "/" . $file, FTP_BINARY)) {
				$path = normalizePath($path);
				@ftp_fget($ftpconnection, $tempHandle, $path . $file, FTP_BINARY);
			}
			rewind($tempHandle);
			$filecontents = stream_get_contents($tempHandle);
		}
	}
}
$tabs = array("Summary" => "serversummary.php?id=" . $rows["serverid"], "Settings" => "serverprofile.php?id=" . $rows["serverid"], "Advanced" => "serveradvanced.php?id=" . $rows["serverid"], "Web FTP" => "serverftp.php?id=" . $rows["serverid"], "Activity Logs" => "serverlog.php?id=" . $rows["serverid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 4);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab">
	<?php if(empty($rows["ipid"])): ?>
	<div id="infobox2"><strong>Server Not Installed</strong><br />Please install the server first.</div>
	<?php else: ?>
	<?= renderMessageBox() ?>
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td align="left"><a href="serverftp.php?id=<?= $serverid ?>"><img src="templates/<?= TEMPLATE ?>/images/home_24.png" align="middle" alt="" /></a><?= buildFtpBreadcrumb($path) ?>
		  <?php if(!empty($file)): ?> &gt; <a href='serverftp.php?id=<?= $serverid ?>&path=<?= urlencode($path) ?>&file=<?= $file ?>'><?= $file ?></a><?php endif; ?></td>
		  <td align="right">IP: <b><?= $rows1["ip"] ?? "" ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;Port: <b><?= $rows2["ftpport"] ?? "" ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;User: <b><?= $rows["user"] ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;Password: <b><?= $rows["password"] ?></b></td>
		</tr>
	  </table>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="5" alt="" /><br />
	  <?php if(empty($file)): ?>
	  <table width="100%" cellpadding="2" cellspacing="1" class="data">
		<tr>
		  <th>File</th>
		  <th>Size</th>
		  <th>User</th>
		  <th>Group</th>
		  <th>Perms</th>
		  <th width="30"></th>
		</tr>
		<?php foreach ($folders as $x): ?>
		<?php $x_path = (substr($path, 0, 1) == "/") ? $path . "/" . $x["name"] : $path . $x["name"] . "/"; ?>
		<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
		  <td style="text-align:left;"><img src="templates/<?= TEMPLATE ?>/images/folder_24.png" align="absmiddle" alt="" /> <a href="serverftp.php?id=<?= $serverid ?>&path=<?= urlencode($x_path) ?>"><?= $x["name"] ?></a></td>
		  <td><?= $x["size"] ?></td>
		  <td><?= $x["owner"] ?></td>
		  <td><?= $x["group"] ?></td>
		  <td><?= $x["permsn"] ?></td>
		  <td><a href="#" onclick="doDeleteDir('<?= $x["name"] ?>', '<?= $serverid ?>', '<?= urlencode($path) ?>')"><img src="templates/<?= TEMPLATE ?>/images/status/red.png" width="25" height="25" alt="Delete"></a></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ($files as $x): ?>
		<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
		  <td style="text-align:left;"><img src="templates/<?= TEMPLATE ?>/images/preview_24.png" align="absmiddle" alt="" /> <?= makeFtpFileLink($x["name"]) ?></td>
		  <td><?= $x["size"] ?></td>
		  <td><?= $x["owner"] ?></td>
		  <td><?= $x["group"] ?></td>
		  <td><?= $x["permsn"] ?></td>
		  <td><a href="#" onclick="doDeleteFile('<?= $x["name"] ?>', '<?= $serverid ?>', '<?= urlencode($path) ?>')"><img src="templates/<?= TEMPLATE ?>/images/status/red.png" width="25" height="25" alt="Delete"></a></td>
		</tr>
		<?php endforeach; ?>
	  </table>
	  <?php if(!empty($rows["ipid"])): ?>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="10" alt="" /><br />
	  <table cellpadding="2" cellspacing="0">
	  <tr>
	  <td>
	  <form method="post" action="serverftpprocess.php" enctype="multipart/form-data">
		<input type="hidden" name="task" value="fileupload" />
		<input type="hidden" name="id" value="<?= $serverid ?>" />
		<input type="hidden" name="path" value="<?= $path ?>" />
	  <table cellpadding="2" cellspacing="1" class="data">
		<tr>
		  <th>File Upload (Max: <?= ini_get("upload_max_filesize") ?>)</th>
		</tr>
		<tr>
		  <td><input type="file" name="file" class="file" size="40" /></td>
		</tr>
		<tr>
		  <td align="center"><input type="submit" value="Upload" class="button" /></td>
		</tr>
	  </table>
	  </form>
	  </td>
	  <td>
	  <form method="post" action="serverftpprocess.php">
		<input type="hidden" name="task" value="makedir" />
		<input type="hidden" name="id" value="<?= $serverid ?>" />
		<input type="hidden" name="path" value="<?= $path ?>" />
	  <table cellpadding="2" cellspacing="1" class="data">
		<tr>
		  <th>Make New Directory</th>
		</tr>
		<tr>
		  <td><input type="text" name="dir" class="text" size="40" /></td>
		</tr>
		<tr>
		  <td align="center"><input type="submit" value="Create" class="button" /></td>
		</tr>
	  </table>
	  </form>
	  </td>
	  </tr>
	  </table>
	  <script language="javascript" type="text/javascript">
	  <!--
		function doDeleteFile(file, id, path) { if (confirm("Are you sure you want to delete file: "+file+"?")) { window.location='serverftpprocess.php?task=filedelete&id='+id+'&path='+path+'&file='+file; } }
		function doDeleteDir(dir, id, path) { if (confirm("Are you sure you want to delete directory: "+dir+"?")) { window.location='serverftpprocess.php?task=dirdelete&id='+id+'&path='+path+'&dir='+dir; } }
		-->
	  </script>
	  <?php endif; ?>
	  <?php else: ?>
	  <div align="center">
	  <form method="post" action="serverftpprocess.php">
		  <input type="hidden" name="task" value="filesave" />
		  <input type="hidden" name="id" value="<?= $serverid ?>" />
		  <input type="hidden" name="path" value="<?= $path ?>" />
		  <input type="hidden" name="file" value="<?= $file ?>" />
		  <textarea name="filecontents" rows="30" cols="150" class="textarea"><?= $filecontents ?></textarea>
		  <br /><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1"><br />
		  <input type="submit" value="Save" class="button green" />
	  </form>
	  </div>
	  <?php endif; ?>
	<?php endif; ?>
	  </td>
  </tr>
</table>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
