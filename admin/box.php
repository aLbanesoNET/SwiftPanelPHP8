<?php
$title = "Boxes";
$page = "box";
$tab = "4";
$return = "box.php";
require "../configuration.php";
require "./include.php";
$linkend = "";
$dirImage = "";
$status = "";
$orderby = sanitizeInput($_GET["orderby"] ?? "");
if(empty($orderby)) {
	$orderby = "boxid";
}
$dir = sanitizeInput($_GET["dir"] ?? "");
if(empty($dir)) {
	$dirImage = " <img src='templates/" . TEMPLATE . "/images/asc.png' align='bottom' alt='' />";
} elseif($dir = "desc") {
	$dirImage = " <img src='templates/" . TEMPLATE . "/images/desc.png' align='bottom' alt='' />";
}
$search = sanitizeInput($_GET["search"] ?? "");
$q = sanitizeInput($_GET["q"] ?? "");
if(!empty($q)) {
	$linkend .= "&amp;search=" . $search . "&amp;q=" . $q;
}
if(!empty($_GET["rows"]) && is_numeric($_GET["rows"])) {
	$rowsperpage = sanitizeInput($_GET["rows"] ?? "");
	$linkend .= "&amp;rows=" . sanitizeInput($_GET["rows"] ?? "");
} elseif(!empty($_COOKIE["boxrows"]) && is_numeric($_COOKIE["boxrows"])) {
	$rowsperpage = sanitizeInput($_COOKIE["boxrows"] ?? "");
} elseif(($_GET["rows"] ?? "") == "All" || ($_COOKIE["boxrows"] ?? "") == "All") {
	$rowsperpage = 999999;
} else {
	$rowsperpage = 15;
}
$pagenum = sanitizeInput($_GET["page"] ?? "");
if(empty($pagenum)) {
	$limit = 0;
	$pagenum = 1;
} else {
	$limit = --$pagenum * $rowsperpage;
	$pagenum++;
	$linkend .= "&amp;page=" . $pagenum;
}
$query = "SELECT * FROM `box` ";
if(!empty($q)) {
	$query .= "WHERE `" . $search . "` LIKE '%" . $q . "%' ";
}
$query .= "ORDER BY `" . $orderby . "` ";
if(!empty($dir)) {
	$query .= $dir . " ";
}
$numrecords = dbCount($query);
$numpages = ceil($numrecords / $rowsperpage);
$result = dbQuery($query . "LIMIT " . $limit . ", " . $rowsperpage);
$rows1 = dbRow("SELECT * FROM `config` WHERE `setting` = 'lastcronrun' LIMIT 1");
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<table width="100%" border="0" cellspacing="0">
  <tr>
	<td width="5" class="tabspacer"><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="5" height="1" alt="" /></td>
	<td id="tabs1" class="tabs" onclick="toggleTab(1)">Search/Filter</td>
	<td width="100%" class="tabspacer">&nbsp;</td>
  </tr>
  <tr id="tab1" style="display:none;">
	<td colspan="3" class="tab"><form action="box.php" method="get">
		<p align="center">Search in
		  <select name="search" class="select">
			<option value="boxid"<?= $search == "boxid" ? ' selected="selected"' : '' ?>>Box ID</option>
			<option value="name"<?= $search == "name" ? ' selected="selected"' : '' ?>>Name</option>
			<option value="location"<?= $search == "location" ? ' selected="selected"' : '' ?>>Location</option>
			<option value="ip"<?= $search == "ip" ? ' selected="selected"' : '' ?>>IP Address</option>
		  </select>
		  for
		  <input type="text" name="q" class="text" size="40" value="<?= !empty($q) ? $q : '' ?>" />
		  <input type="submit" value="Search" class="button" />
		</p>
	  </form></td>
  </tr>
</table>
<script language="javascript" type="text/javascript">var numtabs = 1;</script>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td><b><?= $numrecords ?> Records Found, Page <?= $pagenum ?> of <?= $numpages ?></b> (<a href="boxadd.php">Add New Box</a>)</td>
	<?php if(1 <= $numpages): ?>
	<td align="right"><form method="get" action="box.php">
		<?php if(!empty($orderby) && $orderby != "clientid"): ?><input type="hidden" name="orderby" value="<?= $orderby ?>" /><?php endif; ?>
		<?php if(!empty($dir)): ?><input type="hidden" name="dir" value="<?= $dir ?>" /><?php endif; ?>
		<?php if(!empty($search)): ?><input type="hidden" name="search" value="<?= $search ?>" /><?php endif; ?>
		<?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= $q ?>" /><?php endif; ?>
		<?php if(!empty($_GET["rows"])): ?><input type="hidden" name="rows" value="<?= sanitizeInput($_GET["rows"] ?? "") ?>" /><?php endif; ?>
		Jump to Page:
		<select name="page" class="select" onchange="submit();">
		  <?php for ($n = 1; $n <= $numpages; $n++): ?>
		  <option value="<?= $n ?>"<?= $pagenum == $n ? ' selected="selected"' : '' ?>><?= $n ?></option>
		  <?php endfor; ?>
		</select>
	  </form></td>
	<?php endif; ?>
  </tr>
</table>
<form name="boxes" action="">
  <table width="100%" cellpadding="1" cellspacing="1" class="data">
	<tr>
	  <th width="20">#</th>
	  <th><a href="box.php?orderby=boxid<?= $orderby == "boxid" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">ID</a>
		<?= $orderby == "boxid" ? $dirImage : "" ?> / <a href="box.php?orderby=name<?= $orderby == "name" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Name</a>
		<?= $orderby == "name" ? $dirImage : "" ?></th>
	  <th><a href="box.php?orderby=location<?= $orderby == "location" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Location</a>
		<?= $orderby == "location" ? $dirImage : "" ?></th>
	  <th><a href="box.php?orderby=ip<?= $orderby == "ip" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">IP Address</a>
		<?= $orderby == "ip" ? $dirImage : "" ?></th>
	  <th width="55">IPs</th>
	  <th width="55">Servers</th>
	  <th>SSH</th>
	  <th>FTP</th>
	  <th>CPU</th>
	</tr>
	<?php if(dbNumRows($result) == 0 && empty($q) && empty($status)): ?>
	<tr>
	  <td colspan="11"><div id="infobox2"><strong>No Boxes Found</strong><br />
		  No boxes found. <a href="boxadd.php">Click here</a> to add a new box.</div></td>
	</tr>
	<?php elseif(dbNumRows($result) == 0 && (!empty($q) || !empty($status))): ?>
	<tr>
	  <td colspan="11"><div id="infobox2"><strong>No Boxes Found</strong><br />
		  Modify your search.</div></td>
	</tr>
	<?php endif; ?>
	<?php for ($n = 1; $rows = dbFetch($result); $n++): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td style="color:#666666;"><?= $n ?></td>
	  <td><a href="boxsummary.php?id=<?= $rows["boxid"] ?>">#<?= $rows["boxid"] ?> - <?= $rows["name"] ?></a></td>
	  <td><?= $rows["location"] ?></td>
	  <td><?= $rows["ip"] ?></td>
	  <td><?= dbCount("SELECT `ipid` FROM `ip` WHERE `boxid` = '" . $rows["boxid"] . "'") ?></td>
	  <td><?= dbCount("SELECT `serverid` FROM `server` WHERE `boxid` = '" . $rows["boxid"] . "'") ?></td>
	  <td><?= formatStatusIcon($rows["ssh"]) ?></td>
	  <td><?= formatStatusIcon($rows["ftp"]) ?></td>
	  <td><table width="100%" border="0" cellpadding="0" cellspacing="2">
		<tr>
		  <td align="center"><b><u>Load</u></b></td>
		  <td align="center"><b><u>Idle</u></b></td>
		</tr>
		<tr>
		  <td align="center"><?= $rows["load"] ?></td>
		  <td align="center"><?= $rows["idle"] ?></td>
		</tr>
	  </table></td>
	</tr>
	<?php endfor; ?>
  </table>
  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="5" width="1" alt="" /><br />
  <div align="right" style="color:#666666;font-size:11px;">Last Update: <?= formatDate($rows1["value"]) ?><?php if(formatDate($rows1["value"]) == "Never"): ?><br />Setup the cron job to enable box monitoring!<?php endif; ?></div>
</form>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td align="right"><form method="get" action="box.php">
		<?php if(!empty($orderby) && $orderby != "boxid"): ?><input type="hidden" name="orderby" value="<?= $orderby ?>" /><?php endif; ?>
		<?php if(!empty($dir)): ?><input type="hidden" name="dir" value="<?= $dir ?>" /><?php endif; ?>
		<?php if(!empty($search)): ?><input type="hidden" name="search" value="<?= $search ?>" /><?php endif; ?>
		<?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= $q ?>" /><?php endif; ?>
		Rows Per Page:
		<select name="rows" class="select" onchange="setCookie('boxrows',this.value,30);submit();">
		  <option value="15" <?= $rowsperpage == 15 ? ' selected="selected"' : '' ?>>15</option>
		  <option value="25" <?= $rowsperpage == 25 ? ' selected="selected"' : '' ?>>25</option>
		  <option value="50" <?= $rowsperpage == 50 ? ' selected="selected"' : '' ?>>50</option>
		  <option value="100" <?= $rowsperpage == 100 ? ' selected="selected"' : '' ?>>100</option>
		  <option value="All" <?= $rowsperpage == 999999 ? ' selected="selected"' : '' ?>>All</option>
		</select>
	  </form></td>
  </tr>
</table>
<?php
dbFreeResult($result);
include "./templates/" . TEMPLATE . "/footer.php";
