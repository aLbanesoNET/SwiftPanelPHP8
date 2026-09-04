<?php
$title  = "Announcements";
$page   = "announce";
$tab    = "5";
$return = "announce.php";
require "../configuration.php";
require "./include.php";

$edit = null;
$editId = (int) ($_GET["edit"] ?? 0);
if ($editId > 0) {
	$edit = dbRow("SELECT * FROM `announcement` WHERE `annid` = '" . $editId . "' LIMIT 1", TRUE);
	if (!is_array($edit)) { $edit = null; }
}

$list = dbQuery("SELECT * FROM `announcement` ORDER BY `annid` DESC");

include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<form method="post" action="announceprocess.php">
  <input type="hidden" name="task" value="save" />
  <input type="hidden" name="annid" value="<?= (int) ($edit["annid"] ?? 0) ?>" />
  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td colspan="2" class="fieldheader"><?= $edit ? "Edit Announcement" : "New Announcement" ?></td></tr>
	  <tr>
		<td class="fieldname" style="width:120px;">Title</td>
		<td class="fieldarea"><input type="text" name="title" class="text" size="55" value="<?= htmlspecialchars($edit["title"] ?? "") ?>" /></td>
	  </tr>
	  <tr>
		<td class="fieldname">Message</td>
		<td class="fieldarea"><textarea name="body" class="textarea" rows="5" cols="70"><?= htmlspecialchars($edit["body"] ?? "") ?></textarea><br />
		  <font color="#666666" size="-2">Shown on every client's dashboard. Plain text; blank lines become paragraphs.</font></td>
	  </tr>
	  <tr>
		<td class="fieldname">Status</td>
		<td class="fieldarea"><select name="active" class="select">
		  <option value="1"<?= ($edit["active"] ?? "1") === "1" ? ' selected="selected"' : '' ?>>Visible</option>
		  <option value="0"<?= ($edit["active"] ?? "1") === "0" ? ' selected="selected"' : '' ?>>Hidden</option>
		</select></td>
	  </tr>
	</table>
  </fieldset>
  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="8" width="1" alt="" /><br />
  <div align="center">
	<input type="submit" value="<?= $edit ? "Save Changes" : "Post Announcement" ?>" class="button green" />
	<?php if ($edit): ?><input type="button" value="Cancel" class="button" onclick="window.location='announce.php'" /><?php endif; ?>
  </div>
</form>

<img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="12" width="1" alt="" /><br />

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th width="60">ID</th><th>Title</th><th width="90">Status</th><th width="150">Posted</th><th width="120"></th></tr>
  <?php if (dbNumRows($list) == 0): ?>
	<tr><td colspan="5"><b>No announcements yet.</b></td></tr>
  <?php endif; ?>
  <?php while ($a = dbFetch($list)): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td>#<?= (int) $a["annid"] ?></td>
	  <td><?= htmlspecialchars($a["title"]) ?></td>
	  <td><?= $a["active"] === "1" ? '<span style="color:#669933;font-weight:bold;">Visible</span>' : '<span style="color:#999999;">Hidden</span>' ?></td>
	  <td><?= htmlspecialchars((string) $a["created"]) ?></td>
	  <td>
		<a href="announce.php?edit=<?= (int) $a["annid"] ?>">Edit</a> |
		<a href="announceprocess.php?task=delete&amp;annid=<?= (int) $a["annid"] ?>" onclick="return confirm('Delete this announcement?');">Delete</a>
	  </td>
	</tr>
  <?php endwhile; ?>
</table>
<?php
dbFreeResult($list);
include "./templates/" . TEMPLATE . "/footer.php";
