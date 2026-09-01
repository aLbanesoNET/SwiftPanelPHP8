<?php
$title = "Client Activity Logs";
$page = "clientlog";
$tab = "2";
$return = "clientlog.php?id=" . ($_GET["id"] ?? "");
$image = "logs_48";
require "../configuration.php";
require "./include.php";
$clientid = sanitizeInput($_GET["id"] ?? "");
$rowsperpage = 25;
$page = sanitizeInput($_GET["page"] ?? "");
if(empty($page)) {
	$limit = 0;
} else {
	$limit = --$page * $rowsperpage;
	$page++;
}
$rows = dbRow("SELECT `clientid`, `firstname`, `lastname`, `status` FROM `client` WHERE `clientid` = '" . $clientid . "' LIMIT 1");
$query = "SELECT * FROM `log` WHERE `clientid` = '" . $rows["clientid"] . "' ORDER BY `logid` DESC";
$numpages = ceil(dbCount($query) / $rowsperpage) + 1;
$result1 = dbQuery($query . " LIMIT " . $limit . ", " . $rowsperpage);
$tabs = array("Summary" => "clientsummary.php?id=" . $rows["clientid"], "Profile" => "clientprofile.php?id=" . $rows["clientid"], "Servers" => "clientserver.php?id=" . $rows["clientid"], "Activity Logs" => "clientlog.php?id=" . $rows["clientid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 4);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab">
	  <?= renderMessageBox() ?>
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td align="left"><div style="font-size:18px;">#<?= $rows["clientid"] ?> - <?= $rows["firstname"] ?> <?= $rows["lastname"] ?> [ <?= formatStatusText($rows["status"]) ?> ]</div></td>
		  <td align="right"><?php if(1 < $numpages): ?><form method="get" action="clientlog.php">
			  <input type="hidden" name="id" value="<?= $rows["clientid"] ?>" />
			  Jump to Page:
			  <select name="page" class="select" onchange="submit();">
				<?php for ($n = 1; $n < $numpages; $n++): ?>
				<option value="<?= $n ?>"<?= $page == $n ? ' selected="selected"' : '' ?>><?= $n ?></option>
				<?php endfor; ?>
			  </select>
			</form><?php endif; ?></td>
		</tr>
	  </table>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />
	  <fieldset>
	  <table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr>
		  <td class="fieldheader">Activity Logs</td>
		</tr>
		<tr>
		  <td align="center"><table width="100%" cellpadding="2" cellspacing="1" class="data">
			  <tr>
				<th>ID</th>
				<th>Message</th>
				<th>Name</th>
				<th>IP</th>
				<th>Timestamp</th>
			  </tr>
			  <?php if(dbNumRows($result1) == 0): ?>
			  <tr>
				<td colspan="5" align="center">No Logs Found</td>
			  </tr>
			  <?php endif; ?>
			  <?php while ($rows1 = dbFetch($result1)): ?>
			  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
				<td>#<?= $rows1["logid"] ?></td>
				<td><?= $rows1["message"] ?></td>
				<td><?= $rows1["name"] ?></td>
				<td><?= $rows1["ip"] ?></td>
				<td><?= formatDate($rows1["timestamp"]) ?></td>
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
