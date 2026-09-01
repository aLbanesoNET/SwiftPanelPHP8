<?php
$title = "Server Summary";
$page = "serversummary";
$tab = "3";
$return = "serversummary.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
$serverid = sanitizeInput($_GET["id"] ?? "");
$rows2 = array();
$rows3 = array();
$serverinfo = null;
$rows = dbRow("SELECT * FROM `server` WHERE `serverid` = '" . $serverid . "' ORDER BY `serverid` LIMIT 1");
$rows1 = dbRow("SELECT `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $rows["clientid"] . "' LIMIT 1");
if(!empty($rows["ipid"])) {
	$rows2 = dbRow("SELECT * FROM `ip` WHERE `ipid` = '" . $rows["ipid"] . "' LIMIT 1");
	$rows3 = dbRow("SELECT `boxid`, `name`, `location` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "' LIMIT 1");
}
$result4 = dbQuery("SELECT `serverid`, `name` FROM `server` WHERE `clientid` = '" . $rows["clientid"] . "' ORDER BY `serverid`");
if(!empty($rows2["ip"]) && !empty($rows["port"]) && $rows["query"] != "none") {
	if(empty($rows["qryport"])) {
		$qryport = $rows["port"];
	} else {
		$qryport = $rows["qryport"];
	}
	$serverinfo = querySingleServer(array($rows["query"], $rows2["ip"], $qryport));
}
$tabs = array("Summary" => "serversummary.php?id=" . $rows["serverid"], "Settings" => "serverprofile.php?id=" . $rows["serverid"], "Advanced" => "serveradvanced.php?id=" . $rows["serverid"], "Web FTP" => "serverftp.php?id=" . $rows["serverid"], "Activity Logs" => "serverlog.php?id=" . $rows["serverid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 1);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab"><?= renderMessageBox() ?>
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td align="left"><div style="font-size:18px;">#<?= $rows["serverid"] ?> - <?= $rows["name"] ?> [ <?= formatStatusText($rows["status"]) ?> ]</div></td>
		  <td align="right"><?php if($rows["online"] == "Stopped"): ?>
			  <input type="button" value="Start Server" onclick="window.location='servermanage.php?task=start&amp;serverid=<?= $rows["serverid"] ?>'" class="button green start" />
			  <?php elseif($rows["online"] == "Started"): ?>
			  <input type="button" value="Restart Server" onclick="window.location='servermanage.php?task=restart&amp;serverid=<?= $rows["serverid"] ?>'" class="button blue restart" />
			  <input type="button" value="Stop Server" onclick="window.location='servermanage.php?task=stop&amp;serverid=<?= $rows["serverid"] ?>'" class="button red stop" />
			  <?php endif; ?></td>
		</tr>
	  </table>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td width="50%" valign="top"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Server Information</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Name</td>
				<td class="fieldarea"><?= $rows["name"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Game</td>
				<td class="fieldarea"><?= $rows["game"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Status</td>
				<td class="fieldarea"><?= formatStatusText($rows["status"]) ?></td>
			  </tr>
			</table>
			</fieldset>
			<form method="get" action="serversummary.php">
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
				<tr>
				  <td colspan="2" class="fieldheader">Client Information</td>
				</tr>
				<tr>
				  <td class="fieldname" style="height:20px;width:110px;">Name</td>
				  <td class="fieldarea"><a href="clientsummary.php?id=<?= $rows["clientid"] ?>"><?= $rows1["firstname"] ?> <?= $rows1["lastname"] ?></a></td>
				</tr>
				<tr>
				  <td class="fieldname" style="width:110px;">Servers</td>
				  <td class="fieldarea"><select name="id" class="select" onchange="submit();">
					  <?php while ($rows4 = dbFetch($result4)): ?>
					  <option value="<?= $rows4["serverid"] ?>"<?= $serverid == $rows4["serverid"] ? ' selected="selected"' : '' ?>>#<?= $rows4["serverid"] ?> - <?= $rows4["name"] ?></option>
					  <?php endwhile; ?>
					</select></td>
				</tr>
			</table>
			</fieldset>
			</form>
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Server Configuration</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Priority</td>
				<td class="fieldarea"><?= $rows["priority"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Max Slots</td>
				<td class="fieldarea"><?= $rows["slots"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Type</td>
				<td class="fieldarea"><?= $rows["type"] ?></td>
			  </tr>
			  <?php for ($n = 1; !empty($rows["cfg" . $n . "name"]); $n++): ?>
			  <tr>
				<td class="fieldname" style="height:20px;"><?= $rows["cfg" . $n . "name"] ?></td>
				<td class="fieldarea"><?= $rows["cfg" . $n] ?></td>
			  </tr>
			  <?php endfor; ?>
			  <tr>
				<td class="fieldname" style="height:20px;">Start Command</td>
				<td class="fieldarea"><?= buildStartCommand($rows, (string)($rows2["ip"] ?? ""), TRUE) ?></td>
			  </tr>
			</table>
			</fieldset></td>
		  <td width="50%" valign="top"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Box Details</td>
			  </tr>
			  <?php if(empty($rows["ipid"])): ?>
			  <tr>
				<td align="center" colspan="2"><br />
				  <input type="button" onclick="window.location='serverinstall.php?id=<?= $rows["serverid"] ?>'" class="button" value="Install Wizard" />
				  <br />
				  <br /></td>
			  </tr>
			  <?php else: ?>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Box</td>
				<td class="fieldarea"><a href="boxsummary.php?id=<?= $rows3["boxid"] ?>"><?= $rows3["name"] ?></a></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Location</td>
				<td class="fieldarea"><?= $rows3["location"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">IP Address</td>
				<td class="fieldarea"><?= $rows2["ip"] ?><b>:</b><?= $rows["port"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">User</td>
				<td class="fieldarea"><?= $rows["user"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Password</td>
				<td class="fieldarea"><?= $rows["password"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Home Directory</td>
				<td class="fieldarea"><?= $rows["homedir"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Install Directory</td>
				<td class="fieldarea"><?php if(!empty($rows["installdir"])): ?><?= $rows["installdir"] ?><?php else: ?>Rebuild Disabled (No Install Directory)<?php endif; ?></td>
			  </tr>
			  <?php endif; ?>
			</table>
			</fieldset>
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Server Status</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Status</td>
				<td class="fieldarea"><?= formatStatusText($rows["online"]) ?> (<a href="#" onclick="window.location.reload();">Refresh</a>)</td>
			  </tr>
			  <?php if($serverinfo): ?>
			  <?php foreach ($serverinfo as $name => $value): ?>
			  <tr>
				<td class="fieldname" style="height:20px;"><?= $name ?></td>
				<td class="fieldarea"><?= $value ?></td>
			  </tr>
			  <?php endforeach; ?>
			  <?php endif; ?>
			</table>
			</fieldset></td>
		</tr>
	  </table>
	  <script language="javascript" type="text/javascript">
	  <!--
	  function rebuildServer() { if (confirm("Are you sure you want to rebuild server: <?= $rows["name"] ?>?\n\nAll files will be deleted from directory: <?= $rows["homedir"] ?>")) { window.location="serverprocess.php?task=serverrebuild&serverid=<?= $rows["serverid"] ?>"; } }
	  <?php if(!empty($rows["ipid"])): ?>
	  function deleteServer() { if (confirm("Are you sure you want to delete server: <?= $rows["name"] ?>?")) { if (confirm("Do you want to remove user: <?= $rows["user"] ?>?\n\nAll files will be deleted from directory: <?= $rows["homedir"] ?>")) { window.location="serverprocess.php?task=serverdelete&delete=yes&serverid=<?= $rows["serverid"] ?>"; } else { window.location="serverprocess.php?task=serverdelete&delete=no&serverid=<?= $rows["serverid"] ?>"; } } }
	  <?php else: ?>
	  function deleteServer() { if (confirm("Are you sure you want to delete server: <?= $rows["name"] ?>?")) { window.location="serverprocess.php?task=serverdelete&delete=no&serverid=<?= $rows["serverid"] ?>"; } }
	  <?php endif; ?>
	  -->
	  </script>
	  <p align="center">
	  <?php if(!empty($rows["ipid"]) && !empty($rows["installdir"])): ?><input type="button" onclick="rebuildServer();return false;" class="button green" value="Rebuild Server" /><?php endif; ?>
		<input type="button" onclick="deleteServer();return false;" class="button red" value="Delete Server" />
	  </p></td>
  </tr>
</table>
<?php
dbFreeResult($result4);
include "./templates/" . TEMPLATE . "/footer.php";
