<?php
$title = "Server Config Builder";
$page = "serverconfig";
$tab = "3";
$return = "serverconfig.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
include "../includes/ftp.php";
$serverid = sanitizeInput($_GET["id"] ?? "");
$path = "";
$file = "";
$filecontents = "";
$rows = dbRow("SELECT `serverid`, `ipid`, `boxid`, `user`, `password`, `configdir` FROM `server` WHERE `serverid` = '" . $serverid . "' LIMIT 1");
if(!empty($rows["ipid"]) && !empty($rows["configdir"])) {
	$rows1 = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . $rows["ipid"] . "' LIMIT 1");
	$rows2 = dbRow("SELECT `ftpport`, `passive` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
	if($rows2["passive"] == "On") {
		$passive = TRUE;
	} else {
		$passive = FALSE;
	}
	if($ftpconnection = get_ftp_connection($rows1["ip"], $rows2["ftpport"], $rows["user"], $rows["password"], $passive)) {
		$tempHandle = fopen("php://temp", "r+");
		ftp_fget($ftpconnection, $tempHandle, $rows["configdir"], FTP_BINARY);
		rewind($tempHandle);
		$filecontents = stream_get_contents($tempHandle);
	}
}
$tabs = array("Summary" => "serversummary.php?id=" . $rows["serverid"], "Settings" => "serverprofile.php?id=" . $rows["serverid"], "Advanced" => "serveradvanced.php?id=" . $rows["serverid"], "Config Builder" => "serverconfig.php?id=" . $rows["serverid"], "Web FTP" => "serverftp.php?id=" . $rows["serverid"], "Activity Logs" => "serverlog.php?id=" . $rows["serverid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 4);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab"><?php if(empty($rows["ipid"])): ?>
	  <div id="infobox2"><strong>Server Not Installed</strong><br />Please install the server first.</div>
	  <?php elseif(empty($rows["configdir"])): ?>
	  <div id="infobox2"><strong>Server Config Not Selected</strong><br />Please install the server first.</div>
	  <?php else: ?>
	  <div align="center">
		<form method="post" action="serverftpprocess.php">
		  <input type="hidden" name="task" value="filesave" />
		  <input type="hidden" name="id" value="<?= $serverid ?>" />
		  <input type="hidden" name="path" value="<?= $path ?>" />
		  <input type="hidden" name="file" value="<?= $file ?>" />
		  <textarea name="filecontents" rows="30" cols="150" class="textarea"><?= $filecontents ?></textarea>
		  <br />
		  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1"><br />
		  <input type="submit" value="Save" class="button green" />
		</form>
	  </div>
	  <?php endif; ?>
	</td>
  </tr>
</table>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
