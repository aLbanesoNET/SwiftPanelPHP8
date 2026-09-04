<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td width="33%" valign="top"><fieldset>
	  <table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr>
		  <td colspan="3" class="fieldheader">Clients</td>
		</tr>
		<tr>
		  <td width="75" align="right"><a href="client.php?status=Active">Active</a></td>
		  <td width="120"><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent ?>%" class="greenbar" style="background-position: -<?= 120 - $numpercent * 1.2 ?>px 0pt;" title="<?= $numpercent ?>%" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows ?></td>
		</tr>
		<tr>
		  <td align="right"><a href="client.php?status=Inactive">Inactive</a></td>
		  <td><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent1 ?>%" title="<?= $numpercent1 ?>%" class="goldbar" style="background-position: -<?= 120 - $numpercent1 * 1.2 ?>px 0pt;" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows1 ?></td>
		</tr>
	  </table>
	  </fieldset></td>
	<td width="34%" valign="top"><fieldset>
	  <table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr>
		  <td colspan="3" class="fieldheader">Servers</td>
		</tr>
		<tr>
		  <td width="75" align="right"><a href="server.php?status=Pending">Pending</a></td>
		  <td width="120"><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent2 ?>%" title="<?= $numpercent2 ?>%" class="goldbar" style="background-position: -<?= 120 - $numpercent2 * 1.2 ?>px 0pt;" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows2 ?></td>
		</tr>
		<tr>
		  <td align="right"><a href="server.php?status=Active">Active</a></td>
		  <td><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent3 ?>%" title="<?= $numpercent3 ?>%" class="greenbar" style="background-position: -<?= 120 - $numpercent3 * 1.2 ?>px 0pt;" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows3 ?></td>
		</tr>
		<tr>
		  <td align="right"><a href="server.php?status=Suspended">Suspended</a></td>
		  <td><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent4 ?>%" title="<?= $numpercent4 ?>%" class="redbar" style="background-position: -<?= 120 - $numpercent4 * 1.2 ?>px 0pt;" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows4 ?></td>
		</tr>
	  </table>
	  </fieldset></td>
	<td width="33%" valign="top"><fieldset>
	  <table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr>
		  <td colspan="3" class="fieldheader">Boxes</td>
		</tr>
		<tr>
		  <td width="75" align="right"><a href="box.php">Online</a></td>
		  <td width="120"><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent5 ?>%" title="<?= $numpercent5 ?>%" class="greenbar" style="background-position: -<?= 120 - $numpercent5 * 1.2 ?>px 0pt;" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows5 ?></td>
		</tr>
		<tr>
		  <td align="right"><a href="box.php">Offline</a></td>
		  <td><img src="templates/<?= TEMPLATE ?>/images/percent/bar.png" alt="<?= $numpercent6 ?>%" title="<?= $numpercent6 ?>%" class="redbar" style="background-position: -<?= 120 - $numpercent6 * 1.2 ?>px 0pt;" /></td>
		  <td class="fieldname" style="height:20px;text-align:center;"><?= $numrows6 ?></td>
		</tr>
	  </table>
	  </fieldset></td>
  </tr>
  <tr>
	<td colspan="3"><form method="post" action="process.php">
		<input type="hidden" name="task" value="personalnotes" />
		<input type="hidden" name="adminid" value="<?= $rows["adminid"] ?>" />
		<fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr>
			<td class="fieldheader" colspan="2">Personal Notes</td>
		  </tr>
		  <tr>
			<td align="center" width="800"><textarea name="notes" class="textarea" rows="4" cols="150" style="width:98%"><?= $rows["notes"] ?></textarea></td>
			<td align="center"><input type="submit" value="Save" class="button green" /></td>
		  </tr>
		</table>
		</fieldset>
	  </form>
	</td>
  </tr>
</table>
<p><b>Last 15 Actions</b> (<a href="utilitieslog.php">View All</a>)</p>
<table width="100%" cellpadding="2" cellspacing="1" class="data">
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
</table>
