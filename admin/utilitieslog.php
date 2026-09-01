<?php
$title = "Activity Logs";
$page = "utilitieslog";
$tab = "5";
$return = "utilitieslog.php";
require "../configuration.php";
require "./include.php";
$rowsperpage = 25;
$page = sanitizeInput($_GET["page"] ?? "");
if(empty($page)) {
	$limit = 0;
} else {
	$limit = --$page * $rowsperpage;
	$page++;
}
$query = "SELECT * FROM `log` ORDER BY `logid` DESC";
$numpages = ceil(dbCount($query) / $rowsperpage) + 1;
$result = dbQuery($query . " LIMIT " . $limit . ", " . $rowsperpage);
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td align="right"><form method="get" action="utilitieslog.php">
		Jump to Page:
		<select name="page" class="select" onchange="submit();">
		  <?php for ($n = 1; $n < $numpages; $n++): ?>
		  <option value="<?= $n ?>"<?= $page == $n ? ' selected="selected"' : '' ?>><?= $n ?></option>
		  <?php endfor; ?>
		</select>
	  </form></td>
  </tr>
</table>
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
		<?php if(dbNumRows($result) == 0): ?>
		<tr>
		  <td colspan="5" align="center">No Logs Found</td>
		</tr>
		<?php endif; ?>
		<?php while ($rows = dbFetch($result)): ?>
		<tr onmouseover="this.className='admindatatablehighlight'" onmouseout="this.className=''">
		  <td>#<?= $rows["logid"] ?></td>
		  <td><?= $rows["message"] ?></td>
		  <td><?= $rows["name"] ?></td>
		  <td><?= $rows["ip"] ?></td>
		  <td><?= formatDate($rows["timestamp"]) ?></td>
		</tr>
		<?php endwhile; ?>
	  </table></td>
  </tr>
</table>
</fieldset>
<img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1" alt="" /><br />
<div align="center">
  <input type="button" value="Purge All Logs" onclick="window.location='utilitieslogprocess.php?task=deletelog'" class="button red" />
</div>
<?php
dbFreeResult($result);
include "./templates/" . TEMPLATE . "/footer.php";
