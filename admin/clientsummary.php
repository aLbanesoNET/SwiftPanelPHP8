<?php
$title = "Client Summary";
$page = "clientsummary";
$tab = "2";
$return = "clientsummary.php?id=" . ($_GET["id"] ?? "");
$image = "client_48";
require "../configuration.php";
require "./include.php";
include "../includes/countries.php";
$clientid = sanitizeInput($_GET["id"] ?? "");
$rows2 = array();
$rows = dbRow("SELECT * FROM `client` WHERE `clientid` = '" . $clientid . "' LIMIT 1");
$result1 = dbQuery("SELECT * FROM `server` WHERE `clientid` = '" . $clientid . "' ORDER BY `serverid`");
$result3 = dbQuery("SELECT * FROM `log` WHERE `clientid` = '" . $clientid . "' ORDER BY `logid` DESC LIMIT 5");
$tabs = array("Summary" => "clientsummary.php?id=" . $rows["clientid"], "Profile" => "clientprofile.php?id=" . $rows["clientid"], "Servers" => "clientserver.php?id=" . $rows["clientid"], "Activity Logs" => "clientlog.php?id=" . $rows["clientid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 1);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab"><?= renderMessageBox() ?>
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td align="left"><div style="font-size:18px;">#<?= $rows["clientid"] ?> - <?= $rows["firstname"] ?> <?= $rows["lastname"] ?> [ <?= formatStatusText($rows["status"]) ?> ]</div></td>
		  <td align="right"></td>
		</tr>
	  </table>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td width="50%" valign="top"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Client Information</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Full Name</td>
				<td class="fieldarea"><?= $rows["firstname"] ?> <?= $rows["lastname"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Email Address</td>
				<td class="fieldarea"><?= $rows["email"] ?></td>
			  </tr>
			  <?php if($rows["company"]): ?>
			  <tr>
				<td class="fieldname" style="height:20px;">Company Name</td>
				<td class="fieldarea"><?= $rows["company"] ?></td>
			  </tr>
			  <?php endif; ?>
			  <?php if($rows["address1"] || $rows["city"] || $rows["state"]): ?>
			  <tr>
				<td class="fieldname" style="height:20px;">Address</td>
				<td class="fieldarea"><?= $rows["address1"] ?><br />
				<?= $rows["address2"] == "" ? "" : $rows["address2"] . "<br />" ?>
				<?= $rows["city"] ?>, <?= $rows["state"] ?> <?= $rows["postcode"] ?><br />
				<?= $countries[$rows["country"]] ?? "" ?></td>
			  </tr>
			  <?php endif; ?>
			  <?php if($rows["phone"]): ?>
			  <tr>
				<td class="fieldname" style="height:20px;">Phone Number</td>
				<td class="fieldarea"><?= $rows["phone"] ?></td>
			  </tr>
			  <?php endif; ?>
			</table>
			</fieldset>
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Other Information</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Status</td>
				<td class="fieldarea"><?= formatStatusText($rows["status"]) ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Client Since</td>
				<td class="fieldarea"><?= formatDate($rows["created"]) ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Last Login</td>
				<td class="fieldarea"><?= formatDate($rows["lastlogin"]) ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Last IP</td>
				<td class="fieldarea"><?= $rows["lastip"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Last Hostname</td>
				<td class="fieldarea"><?= $rows["lasthost"] ?></td>
			  </tr>
			</table>
			</fieldset></td>
		  <td width="50%" valign="top"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td class="fieldheader">Last 5 Actions</td>
			  </tr>
			  <?php if(dbNumRows($result3) == 0): ?>
			  <tr>
				<td align="center">No Logs Found</td>
			  </tr>
			  <?php endif; ?>
			  <?php while ($rows3 = dbFetch($result3)): ?>
			  <tr>
				<td style="font-size:11px;"><?= formatDate($rows3["timestamp"]) ?> - <?= $rows3["message"] ?></td>
			  </tr>
			  <?php endwhile; ?>
			</table>
			</fieldset>
			<fieldset>
			<form method="post" action="clientprocess.php">
			  <input type="hidden" name="task" value="clientnotes" />
			  <input type="hidden" name="clientid" value="<?= $rows["clientid"] ?>" />
			  <table width="100%" border="0" cellpadding="2" cellspacing="2">
				<tr>
				  <td class="fieldheader" colspan="2">Admin Notes</td>
				</tr>
				<tr>
				  <td width="350" align="center"><textarea name="notes" class="textarea" rows="4" cols="60"><?= $rows["notes"] ?></textarea></td>
				  <td align="center"><input type="submit" value="Save" class="button green" /></td>
				</tr>
			  </table>
			</form>
			</fieldset></td>
		</tr>
		<tr>
		  <td colspan="3"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td class="fieldheader"><?= dbNumRows($result1) ?> Assigned Servers (<a href="serveradd.php?clientid=<?= $rows["clientid"] ?>" style="font-weight:normal;">Add New Server</a>)</td>
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
					<tr>
					  <td colspan="8"><div id="infobox2"><strong>No Servers Found</strong><br />
					No servers found. <a href="serveradd.php?clientid=<?= $rows["clientid"] ?>">Click here</a> to add a new server.</div></td>
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
	  <script language="javascript" type="text/javascript">
	  <!--
		function deleteClient() { if (confirm("Are you sure you want to delete client: <?= $rows["firstname"] ?> <?= $rows["lastname"] ?>?")) { window.location.href='clientprocess.php?task=clientdelete&id=<?= $rows["clientid"] ?>'; } }
		-->
	  </script>
	  <p align="center">
		<input type="button" onclick="window.open('clientprocess.php?task=clientlogin&amp;id=<?= $rows["clientid"] ?>')" class="button blue" value="Login as Client" />
		<input type="button" onclick="deleteClient();return false;" class="button red" value="Delete Client" />
	  </p></td>
  </tr>
</table>
<?php
dbFreeResult($result3);
dbFreeResult($result1);
include "./templates/" . TEMPLATE . "/footer.php";
