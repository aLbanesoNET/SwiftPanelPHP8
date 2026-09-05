<?php
$title = "Clients";
$page = "client";
$tab = "2";
$return = "client.php";
require "../configuration.php";
require "./include.php";
$linkend = "";
$dirImage = "";
$orderby = sqlSortColumn(sanitizeInput($_GET["orderby"] ?? ""), ["clientid", "firstname", "lastname", "email", "lastlogin"], "clientid");
$dir = sanitizeInput($_GET["dir"] ?? "");
if(empty($dir)) {
	$dirImage = " <img src='templates/" . TEMPLATE . "/images/asc.png' align='bottom' alt='' />";
} elseif($dir === "desc") {
	$dirImage = " <img src='templates/" . TEMPLATE . "/images/desc.png' align='bottom' alt='' />";
}
$search = sanitizeInput($_GET["search"] ?? "");
if ($search !== "" && !in_array($search, ["clientid", "firstname", "lastname", "email", "lastip", "lasthost"], true)) {
	$search = "firstname";
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
} elseif(!empty($_COOKIE["clientrows"]) && is_numeric($_COOKIE["clientrows"])) {
	$rowsperpage = sanitizeInput($_COOKIE["clientrows"] ?? "");
} elseif(($_GET["rows"] ?? "") == "All" || ($_COOKIE["clientrows"] ?? "") == "All") {
	$rowsperpage = 999999;
} else {
	$rowsperpage = 25;
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
$query = "SELECT * FROM `client` ";
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
	<td class="tab" colspan="3"><form action="client.php" method="get">
		<?php if(!empty($_GET["rows"])): ?><input type="hidden" name="rows" value="<?= sanitizeInput($_GET["rows"] ?? "") ?>" /><?php endif; ?>
		<p align="center">Search in
		  <select name="search" class="select">
			<option value="clientid"<?= $search == "clientid" ? ' selected="selected"' : '' ?>>Client ID</option>
			<option value="firstname"<?= $search == "firstname" ? ' selected="selected"' : '' ?>>First Name</option>
			<option value="lastname"<?= $search == "lastname" ? ' selected="selected"' : '' ?>>Last Name</option>
			<option value="email"<?= $search == "email" ? ' selected="selected"' : '' ?>>Email Address</option>
			<option value="lastip"<?= $search == "lastip" ? ' selected="selected"' : '' ?>>Last IP</option>
			<option value="lasthost"<?= $search == "lasthost" ? ' selected="selected"' : '' ?>>Last Hostname</option>
		  </select>
		  for
		  <input type="text" name="q" class="text" size="40" value="<?= !empty($q) ? $q : '' ?>" />
		  with status
		  <select name="status" class="select">
			<option value=""<?= $status == "" ? ' selected="selected"' : '' ?>>Any</option>
			<option value="Active"<?= $status == "Active" ? ' selected="selected"' : '' ?>>Active</option>
			<option value="Inactive"<?= $status == "Inactive" ? ' selected="selected"' : '' ?>>Inactive</option>
		  </select>
		  <input type="submit" value="Search" class="button" />
		</p>
	  </form></td>
  </tr>
</table>
<script language="javascript" type="text/javascript">var numtabs = 1;</script>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td><b><?= $numrecords ?> Records Found, Page <?= $pagenum ?> of <?= $numpages ?></b> (<a href="clientadd.php">Add New Client</a>)</td>
	<?php if(1 <= $numpages): ?>
	<td align="right"><form id="pageForm" method="get" action="client.php">
		<?php if(!empty($orderby) && $orderby != "clientid"): ?><input type="hidden" name="orderby" value="<?= $orderby ?>" /><?php endif; ?>
		<?php if(!empty($dir)): ?><input type="hidden" name="dir" value="<?= $dir ?>" /><?php endif; ?>
		<?php if(!empty($search)): ?><input type="hidden" name="search" value="<?= $search ?>" /><?php endif; ?>
		<?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= $q ?>" /><?php endif; ?>
		<?php if(!empty($status)): ?><input type="hidden" name="status" value="<?= $status ?>" /><?php endif; ?>
		<?php if(!empty($_GET["rows"])): ?><input type="hidden" name="rows" value="<?= sanitizeInput($_GET["rows"] ?? "") ?>" /><?php endif; ?>
		Jump to Page:
		<select id="pageSelect" name="page" class="select" onchange="submit();">
		  <?php for ($n = 1; $n <= $numpages; $n++): ?>
		  <option value="<?= $n ?>"<?= $pagenum == $n ? ' selected="selected"' : '' ?>><?= $n ?></option>
		  <?php endfor; ?>
		</select>
	  </form></td>
	<?php endif; ?>
  </tr>
</table>
<form name="clientForm" action="emailsend.php">
  <table width="100%" cellpadding="1" cellspacing="1" class="data">
	<tr>
	  <th width="26">#</th>
	  <th width="26"><input type="checkbox" onclick="toggleCheckbox(clientForm)" /></th>
	  <th width="35"><a href="client.php?orderby=clientid<?= $orderby == "clientid" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">ID</a>
		<?= $orderby == "clientid" ? $dirImage : "" ?></th>
	  <th><a href="client.php?orderby=firstname<?= $orderby == "firstname" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">First Name</a>
		<?= $orderby == "firstname" ? $dirImage : "" ?></th>
	  <th><a href="client.php?orderby=lastname<?= $orderby == "lastname" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Last Name</a>
		<?= $orderby == "lastname" ? $dirImage : "" ?></th>
	  <th><a href="client.php?orderby=email<?= $orderby == "email" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Email</a>
		<?= $orderby == "email" ? $dirImage : "" ?></th>
	  <th>Servers</th>
	  <th><a href="client.php?orderby=lastlogin<?= $orderby == "lastlogin" && empty($dir) ? "&amp;dir=desc" : "" ?><?= $linkend ?>">Last Login</a>
		<?= $orderby == "lastlogin" ? $dirImage : "" ?></th>
	</tr>
	<?php if(dbNumRows($result) == 0 && empty($q) && empty($status)): ?>
	<tr>
	  <td colspan="9"><div id="infobox2"><strong>No Clients Found</strong><br />
		  No clients found. <a href="clientadd.php">Click here</a> to add a new client.</div></td>
	</tr>
	<?php elseif(dbNumRows($result) == 0 && (!empty($q) || !empty($status))): ?>
	<tr>
	  <td colspan="9"><div id="infobox2"><strong>No Clients Found</strong><br />
		  Modify your search.</div></td>
	</tr>
	<?php endif; ?>
	<?php for ($n = 1; $rows = dbFetch($result); $n++): ?>
	<tr onmouseover="this.className='mouseover';" class="<?= $rows["status"] ?>" onmouseout="this.className='<?= $rows["status"] ?>';">
	  <td style="color:#666666;height:22px;"><?= $n ?></td>
	  <td><input type="checkbox" name="client[]" /></td>
	  <td><a href="clientsummary.php?id=<?= $rows["clientid"] ?>"><?= $rows["clientid"] ?></a></td>
	  <td><a href="clientsummary.php?id=<?= $rows["clientid"] ?>"><?= $rows["firstname"] ?></a></td>
	  <td><a href="clientsummary.php?id=<?= $rows["clientid"] ?>"><?= $rows["lastname"] ?></a></td>
	  <td><?= $rows["email"] ?></td>
	  <td><?= dbCount("SELECT `serverid` FROM `server` WHERE `clientid` = '" . $rows["clientid"] . "'") ?></td>
	  <td><?= formatDate($rows["lastlogin"]) ?></td>
	</tr>
	<?php endfor; ?>
  </table>
</form>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
	<td align="right"><form method="get" action="client.php">
		<?php if(!empty($orderby) && $orderby != "clientid"): ?><input type="hidden" name="orderby" value="<?= $orderby ?>" /><?php endif; ?>
		<?php if(!empty($dir)): ?><input type="hidden" name="dir" value="<?= $dir ?>" /><?php endif; ?>
		<?php if(!empty($search)): ?><input type="hidden" name="search" value="<?= $search ?>" /><?php endif; ?>
		<?php if(!empty($q)): ?><input type="hidden" name="q" value="<?= $q ?>" /><?php endif; ?>
		<?php if(!empty($status)): ?><input type="hidden" name="status" value="<?= $status ?>" /><?php endif; ?>
		Rows Per Page:
		<select name="rows" class="select" onchange="setCookie('clientrows',this.value,30);submit();">
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
		<tr class="Active">
		  <td></td>
		</tr>
	  </table></td>
	<td>Active</td>
	<td width="5"></td>
	<td width="12" align="right"><table style="width:12px;height:12px;" cellspacing="1" class="data">
		<tr class="Inactive">
		  <td></td>
		</tr>
	  </table></td>
	<td>Inactive</td>
  </tr>
</table>
<?php
dbFreeResult($result);
include "./templates/" . TEMPLATE . "/footer.php";
