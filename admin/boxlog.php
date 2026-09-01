<?php
$title = "Box Activity Logs";
$page = "boxlog";
$tab = "4";
$return = "boxlog.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
$boxid = sanitizeInput($_GET["id"] ?? "");
$rowsperpage = 25;
$page = sanitizeInput($_GET["page"] ?? "");
if(empty($page)) {
	$limit = 0;
} else {
	$limit = --$page * $rowsperpage;
	$page++;
}
$rows = dbRow("SELECT `boxid`, `name` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
$query = "SELECT * FROM `log` WHERE `boxid` = '" . $rows["boxid"] . "' ORDER BY `logid` DESC";
$numpages = ceil(dbCount($query) / $rowsperpage) + 1;
$result1 = dbQuery($query . " LIMIT " . $limit . ", " . $rowsperpage);
$tabs = array("Summary" => "boxsummary.php?id=" . $rows["boxid"], "Profile" => "boxprofile.php?id=" . $rows["boxid"], "Servers" => "boxserver.php?id=" . $rows["boxid"], "Game Files" => "boxgamefile.php?id=" . $rows["boxid"], "Activity Logs" => "boxlog.php?id=" . $rows["boxid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 5);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab">
	  <?= renderMessageBox() ?>
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td align="left"><div style="font-size:18px;">#<?= $rows["boxid"] ?> - <?= $rows["name"] ?></div></td>
		  <td align="right"><form method="get" action="boxlog.php">
			  <input type="hidden" name="id" value="<?= $rows["boxid"] ?>" />
			  Jump to Page:
			  <select name="page" class="select" onchange="submit();">
				<?php for ($n = 1; $n < $numpages; $n++): ?>
				<option value="<?= $n ?>"<?= $page == $n ? ' selected="selected"' : '' ?>><?= $n ?></option>
				<?php endfor; ?>
			  </select>
			</form></td>
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
