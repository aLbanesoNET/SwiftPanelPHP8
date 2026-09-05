<?php
$title = "Servers";
$page = "server";
$tab = "3";
$return = "server.php";
require "../configuration.php";
require "./include.php";
$linkend = "";
$dirImage = "";
$orderby = sqlSortColumn(sanitizeInput($_GET["orderby"] ?? ""), ["online", "serverid", "name", "game"], "serverid");
$dir = sanitizeInput($_GET["dir"] ?? "");
if(empty($dir)) {
	$dirImage = " <img src='templates/" . TEMPLATE . "/images/asc.png' align='bottom' alt='' />";
} elseif($dir === "desc") {
	$dirImage = " <img src='templates/" . TEMPLATE . "/images/desc.png' align='bottom' alt='' />";
}
$search = sanitizeInput($_GET["search"] ?? "");
if ($search !== "" && !in_array($search, ["serverid", "clientid", "boxid", "name", "game"], true)) {
	$search = "name";
}
$q = sanitizeInput($_GET["q"] ?? "");
if(!empty($q)) {
	$linkend .= "&amp;search=" . $search . "&amp;q=" . $q;
}
$status = sanitizeInput($_GET["status"] ?? "");
if(!empty($status)) {
	$linkend .= "&amp;status=" . $status;
}
if(!empty($_GET["rows"]) && is_numeric($_GET["rows"])) {
	$rowsperpage = sanitizeInput($_GET["rows"] ?? "");
	$linkend .= "&amp;rows=" . sanitizeInput($_GET["rows"] ?? "");
} elseif(!empty($_COOKIE["serverrows"]) && is_numeric($_COOKIE["serverrows"])) {
	$rowsperpage = sanitizeInput($_COOKIE["serverrows"] ?? "");
} elseif(($_GET["rows"] ?? "") == "All" || ($_COOKIE["serverrows"] ?? "") == "All") {
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
$query = "SELECT * FROM `server` ";
if(!empty($q) || !empty($status)) {
	$query .= "WHERE ";
}
if(!empty($q)) {
	$query .= "`" . $search . "` LIKE '%" . $q . "%' ";
}
if(!empty($q) && !empty($status)) {
	$query .= "AND ";
}
if(!empty($status)) {
	$query .= "`status` = '" . $status . "' ";
}
$query .= "ORDER BY `" . $orderby . "` ";
if(!empty($dir)) {
	$query .= sqlSortDir($dir) . " ";
}
$numrecords = dbCount($query);
$numpages = ceil($numrecords / $rowsperpage);
$result = dbQuery($query . "LIMIT " . $limit . ", " . $rowsperpage);
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
	<td colspan="3" class="tab"><form action="server.php" method="get">
		<?php if(!empty($_GET["rows"])): ?><input type="hidden" name="rows" value="<?= sanitizeInput($_GET["rows"] ?? "") ?>" /><?php endif; ?>
		<p align="center">Search in
		  <select name="search" class="select">
			<option value="serverid"<?= $search == "serverid" ? ' selected="selected"' : '' ?>>Server ID</option>
			<option value="clientid"<?= $search == "clientid" ? ' selected="selected"' : '' ?>>Client ID</option>
			<option value="boxid"<?= $search == "boxid" ? ' selected="selected"' : '' ?>>Box ID</option>
			<option value="name"<?= $search == "name" ? ' selected="selected"' : '' ?>>Name</option>
			<option value="game"<?= $search == "game" ? ' selected="selected"' : '' ?>>Game</option>
		  </select>
		  for
		  <input type="text" name="q" class="text" size="40" value="<?= !empty($q) ? $q : '' ?>" />
		  with status
		  <select name="status" class="select">
			<option value=""<?= $status == "" ? ' selected="selected"' : '' ?>>Any</option>
			<option value="Pending"<?= $status == "Pending" ? ' selected="selected"' : '' ?>>Pending</option>
			<option value="Active"<?= $status == "Active" ? ' selected="selected"' : '' ?>>Active</option>
			<option value="Suspended"<?= $status == "Suspended" ? ' selected="selected"' : '' ?>>Suspended</option>
		  </select>
		  <input type="submit" value="Search" class="button" />
		</p>
	  </form></td>
  </tr>
</table>
<script language="javascript" type="text/javascript">var numtabs = 1;</script>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td><b><?= $numrecords ?> Records Found, Page <?= $pagenum ?> of <?= $numpages ?></b> (<a href="serveradd.php">Add New Server</a>)</td>
	<?php if(1 <= $numpages): ?>
	<td align="right"><form method="get" action="server.php">
		<?php if(!empty($orderby) && $orderby != "serverid"): ?><input type="hidden" name="orderby" value="<?= $orderby ?>" /><?php endif; ?>
		<?php if(!empty($dir)): ?><input type="hidden" name="dir" value="<?= $dir ?>" /><?php endif; ?>
		<?php if(!empty($search)): ?><input type="hidden" name="search" value="<?= $search ?>" /><?php endif; ?>
		<?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= $q ?>" /><?php endif; ?>
		<?php if(!empty($status)): ?><input type="hidden" name="status" value="<?= $status ?>" /><?php endif; ?>
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
<form name="servers" action="#">
  <table width="100%" cellpadding="1" cellspacing="1" class="data">
	<tr>
	  <th width="26">#</th>
	  <th width="50"><a href="server.php?orderby=online<?= $orderby == "online" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Status</a>
		<?= $orderby == "online" ? $dirImage : "" ?></th>
	  <th><a href="server.php?orderby=serverid<?= $orderby == "serverid" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">ID</a>
		<?= $orderby == "serverid" ? $dirImage : "" ?> / <a href="server.php?orderby=name<?= $orderby == "name" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Name</a>
		<?= $orderby == "name" ? $dirImage : "" ?> / <a href="server.php?orderby=game<?= $orderby == "game" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Game</a>
		<?= $orderby == "game" ? $dirImage : "" ?></th>
	  <th width="40">Type</th>
	  <th>Real Time Query (<a href="#" onclick="window.location.reload();">Refresh</a>)</th>
	  <th width="60"></th>
	</tr>
	<?php if(dbNumRows($result) == 0 && empty($q) && empty($status)): ?>
	<tr>
	  <td colspan="8"><div id="infobox2"><strong>No Servers Found</strong><br />
		  No servers found. <a href="serveradd.php">Click here</a> to add a new server.</div></td>
	</tr>
	<?php elseif(dbNumRows($result) == 0 && (!empty($q) || !empty($status))): ?>
	<tr>
	  <td colspan="8"><div id="infobox2"><strong>No Servers Found</strong><br />
		  Modify your search.</div></td>
	</tr>
	<?php endif; ?>
	<?php for ($n = 1; $rows = dbFetch($result); $n++):
		$rows2 = dbRow("SELECT `clientid`, `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $rows["clientid"] . "' LIMIT 1");
	?>
	<tr onmouseover="this.className='mouseover';" class="<?= $rows["status"] ?>" onmouseout="this.className='<?= $rows["status"] ?>';">
	  <td style="color:#666666;"><?= $n ?></td>
	  <td><?= formatStatusIcon($rows["online"]) ?></td>
	  <td><a href="serversummary.php?id=<?= $rows["serverid"] ?>">#<?= $rows["serverid"] ?> - <?= $rows["name"] ?></a><br />
	  <i><?= $rows["game"] ?></i><br />
	  <span style="font-size:10px;color:#555555;"><?= $rows2["firstname"] ?> <?= $rows2["lastname"] ?></span></td>
	  <td><img src="templates/<?= TEMPLATE ?>/images/linux.png" width="20" height="25" align="middle" alt="" /></td>
	  <?php if(!empty($rows["ipid"])):
		$rows1 = dbRow("SELECT `ip` FROM `ip` WHERE `ipid` = '" . $rows["ipid"] . "' LIMIT 1");
		if(!empty($rows1["ip"]) && !empty($rows["port"]) && $rows["query"] != "none"):
			$qryport = empty($rows["qryport"]) ? $rows["port"] : $rows["qryport"];
			$serverinfo = querySingleServer(array($rows["query"], $rows1["ip"], $qryport));
	  ?>
	  <td style="line-height:13px;"><?php if(!empty($serverinfo["Server Name"]) || !empty($serverinfo["Current Map"])): ?><b><?= htmlspecialchars((string) ($serverinfo["Server Name"] ?? ""), ENT_QUOTES, "UTF-8") ?></b><br /><?= htmlspecialchars((string) ($serverinfo["Current Map"] ?? ""), ENT_QUOTES, "UTF-8") ?> ( <font color="#0000FF"><b><?= htmlspecialchars((string) ($serverinfo["Players"] ?? ""), ENT_QUOTES, "UTF-8") ?></b></font> Players <?php if(!empty($serverinfo["Bot Players"])): ?>/ <b><?= htmlspecialchars((string) $serverinfo["Bot Players"], ENT_QUOTES, "UTF-8") ?></b> Bots<?php endif; ?> <?php if(!empty($serverinfo["Max Players"])): ?>/ <font color="#DD0000"><b><?= htmlspecialchars((string) $serverinfo["Max Players"], ENT_QUOTES, "UTF-8") ?></b></font> Slots<?php endif; ?> )<br /><?php endif; ?><i><?= htmlspecialchars((string) $rows1["ip"], ENT_QUOTES, "UTF-8") ?><b>:</b><?= (int) $rows["port"] ?></i></td>
	  <?php else: ?>
	  <td><i><?= htmlspecialchars((string) $rows1["ip"], ENT_QUOTES, "UTF-8") ?><b>:</b><?= (int) $rows["port"] ?></i></td>
	  <?php endif; ?>
	  <td><?php if($rows["online"] == "Stopped"): ?>&nbsp;<a href="servermanage.php?task=start&amp;return=<?= urlencode($return) ?>&amp;serverid=<?= $rows["serverid"] ?>"><img src="templates/<?= TEMPLATE ?>/images/buttons/play.png" width="25" height="25" align="middle" alt="" /></a>&nbsp;<?php elseif($rows["online"] == "Started"): ?><a href="servermanage.php?task=restart&amp;return=<?= urlencode($return) ?>&amp;serverid=<?= $rows["serverid"] ?>"><img src="templates/<?= TEMPLATE ?>/images/buttons/refresh.png" width="25" height="25" align="middle" alt="" /></a> <a href="servermanage.php?task=stop&amp;return=<?= urlencode($return) ?>&amp;serverid=<?= $rows["serverid"] ?>"><img src="templates/<?= TEMPLATE ?>/images/buttons/stop.png" width="25" height="25" align="middle" alt="" /></a><?php endif; ?></td>
	  <?php else: ?>
	  <td><input type="button" onclick="window.location='serverinstall.php?id=<?= $rows["serverid"] ?>'" class="button" value="Install Wizard" /></td>
	  <td></td>
	  <?php endif; ?>
	</tr>
	<?php endfor; ?>
  </table>
</form>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td align="right"><form method="get" action="server.php">
		<?php if(!empty($orderby) && $orderby != "serverid"): ?><input type="hidden" name="orderby" value="<?= $orderby ?>" /><?php endif; ?>
		<?php if(!empty($dir)): ?><input type="hidden" name="dir" value="<?= $dir ?>" /><?php endif; ?>
		<?php if(!empty($search)): ?><input type="hidden" name="search" value="<?= $search ?>" /><?php endif; ?>
		<?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= $q ?>" /><?php endif; ?>
		<?php if(!empty($status)): ?><input type="hidden" name="status" value="<?= $status ?>" /><?php endif; ?>
		Rows Per Page:
		<select name="rows" class="select" onchange="setCookie('serverrows',this.value,30);submit();">
		  <option value="15" <?= $rowsperpage == 15 ? ' selected="selected"' : '' ?>>15</option>
		  <option value="25" <?= $rowsperpage == 25 ? ' selected="selected"' : '' ?>>25</option>
		  <option value="50" <?= $rowsperpage == 50 ? ' selected="selected"' : '' ?>>50</option>
		  <option value="100" <?= $rowsperpage == 100 ? ' selected="selected"' : '' ?>>100</option>
		  <option value="All" <?= $rowsperpage == 999999 ? ' selected="selected"' : '' ?>>All</option>
		</select>
	  </form></td>
  </tr>
</table>
<table align="center">
  <tr>
	<td width="12" align="right"><table style="width:12px;height:12px;" cellspacing="1" class="data">
		<tr class="Pending">
		  <td></td>
		</tr>
	  </table></td>
	<td>Pending</td>
	<td width="5"></td>
	<td width="12" align="right"><table style="width:12px;height:12px;" cellspacing="1" class="data">
		<tr class="Active">
		  <td></td>
		</tr>
	  </table></td>
	<td>Active</td>
	<td width="5"></td>
	<td width="12" align="right"><table style="width:12px;height:12px;" cellspacing="1" class="data">
		<tr class="Suspended">
		  <td></td>
		</tr>
	  </table></td>
	<td>Suspended</td>
  </tr>
</table>
<?php
dbFreeResult($result);
include "./templates/" . TEMPLATE . "/footer.php";
