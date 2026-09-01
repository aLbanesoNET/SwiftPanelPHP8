<?php
$title = "Administrators";
$page = "configadmin";
$tab = "6";
$return = "configadmin.php";
require "../configuration.php";
require "./include.php";
$result = dbQuery("SELECT * FROM `admin` ORDER BY `adminid`");
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<p><b><?= dbNumRows($result) ?> Records Found</b> (<a href="configadminadd.php">Add New Administrator</a>)</p>
<table width="100%" cellpadding="1" cellspacing="1" class="data">
	<tr>
	  <th width="40">ID</th>
	  <th>Full Name</th>
	  <th>Email</th>
	  <th>Username</th>
	  <!--<th>Access Level</th>-->
	  <th>Last Login</th>
	  <th width="30"></th>
	  <th width="30"></th>
	</tr>
	<?php while ($rows = dbFetch($result)): ?>
	<tr onmouseover="this.className='mouseover';" class="<?= $rows["status"] ?>" onmouseout="this.className='<?= $rows["status"] ?>';">
	  <td><?= $rows["adminid"] ?></td>
	  <td><?= $rows["firstname"] ?> <?= $rows["lastname"] ?></td>
	  <td><?= $rows["email"] ?></td>
	  <td><?= $rows["username"] ?></td>
	  <!--<td><?= $rows["access"] ?></td>-->
	  <td><?= formatDate($rows["lastlogin"]) ?></td>
	  <td><a href="configadminedit.php?id=<?= $rows["adminid"] ?>"><img src="templates/<?= TEMPLATE ?>/images/buttons/edit24.png" width="24" height="24" alt="Edit" /></a></td>
	  <td><a href="#" onclick="doDelete('<?= $rows["adminid"] ?>', '<?= $rows["firstname"] ?> <?= $rows["lastname"] ?>')"><img src="templates/<?= TEMPLATE ?>/images/status/red.png" width="25" height="25" alt="Delete" /></a></td>
	</tr>
	<?php endwhile; ?>
  </table>
<script language="JavaScript" type="text/javascript">
<!--
function doDelete(id, name) { if (confirm("Are you sure you want to delete administrator: "+name+"?")) { window.location='configadminprocess.php?task=configadmindelete&id='+id; } }
-->
</script>
<br />
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
