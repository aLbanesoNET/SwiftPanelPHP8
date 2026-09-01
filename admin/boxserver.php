<?php
$title = "Box Servers";
$page = "boxserver";
$tab = "4";
$return = "boxserver.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
$boxid = sanitizeInput($_GET["id"] ?? "");
$rows2 = array();
$rows = dbRow("SELECT `boxid`, `name` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
$result1 = dbQuery("SELECT * FROM `server` WHERE `boxid` = '" . $rows["boxid"] . "' ORDER BY `serverid`");
$tabs = array("Summary" => "boxsummary.php?id=" . $rows["boxid"], "Profile" => "boxprofile.php?id=" . $rows["boxid"], "Servers" => "boxserver.php?id=" . $rows["boxid"], "Game Files" => "boxgamefile.php?id=" . $rows["boxid"], "Activity Logs" => "boxlog.php?id=" . $rows["boxid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 3);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab"><?= renderMessageBox() ?>
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td align="left"><div style="font-size:18px;">#<?= $rows["boxid"] ?> - <?= $rows["name"] ?></div></td>
		  <td align="right"></td>
		</tr>
	  </table>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />
	  <fieldset>
	  <table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr>
		  <td class="fieldheader"><?= dbNumRows($result1) ?> Assigned Servers</td>
		</tr>
		<tr>
		  <td align="center"><table width="100%" cellpadding="2" cellspacing="1" class="data">
			  <tr>
				<th width="30">ID</th>
				<th width="35"></th>
				<th>Name</th>
				<th>Game</th>
				<th>Description</th>
				<th>User</th>
				<th>IP Address</th>
				<th>Status</th>
			  </tr>
			  <?php if(dbNumRows($result1) == 0): ?>
			  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
				<td colspan="8"><div id="infobox2"><strong>No Servers Found</strong><br />
					No servers found. <a href="serveradd.php">Click here</a> to add a new server.</div></td>
			  </tr>
			  <?php endif; ?>
			  <?php while ($rows1 = dbFetch($result1)): ?>
			  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
				<td rowspan="2"><a href="serversummary.php?id=<?= $rows1["serverid"] ?>">#<?= $rows1["serverid"] ?></a></td>
				<td rowspan="2"><?= formatStatusIcon($rows1["online"]) ?></td>
				<td><a href="serversummary.php?id=<?= $rows1["serverid"] ?>"><?= $rows1["name"] ?></a></td>
				<td><?= $rows1["game"] ?></td>
				<td><?= $rows1["slots"] ?> Slots <?= $rows1["type"] ?></td>
				<?php if(!empty($rows1["ipid"])): ?>
				<td><?= $rows1["user"] ?></td>
				<?php $rows2 = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . $rows1["ipid"] . "' LIMIT 1"); ?>
				<td><?= $rows2["ip"] ?> <b>:</b> <?= $rows1["port"] ?></td>
				<?php else: ?>
				<td colspan="2">Pending</td>
				<?php endif; ?>
				<td><?= formatStatusText($rows1["status"]) ?></td>
			  </tr>
			  <tr>
				<td colspan="6" style="background-color:#F5F5F5;color:#333333;text-align:left;"><?= buildStartCommand($rows1, (string)($rows2["ip"] ?? ""), TRUE) ?></td>
			  </tr>
			  <?php endwhile; ?>
			</table></td>
		</tr>
	  </table>
	  </fieldset></td>
  </tr>
</table>
<?php
dbFreeResult($result1);
include "./templates/" . TEMPLATE . "/footer.php";
