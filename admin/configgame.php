<?php
$title = "Manage Games";
$page = "configgame";
$tab = "6";
$return = "configgame.php";
require "../configuration.php";
require "./include.php";
include "../includes/games.php";
$result = dbQuery("SELECT `gameid`, `name`, `game`, `slots`, `type`, `query`, `gamedir` FROM `game` WHERE `status` = 'Active' ORDER BY `game`");
$result1 = dbQuery("SELECT `gameid`, `name`, `game`, `slots`, `type`, `query`, `gamedir` FROM `game` WHERE `status` = 'Inactive' ORDER BY `game`");
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<table width="100%" border="0" cellspacing="0">
  <tr>
	<td width="5" class="tabspacer"><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="5" height="1" alt="" /></td>
	<td id="tabs1" class="tabsactive" onclick="toggleTab(1)">Active</td>
	<td width="2" class="tabspacer"><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="2" height="1" alt="" /></td>
	<td id="tabs2" class="tabs" onclick="toggleTab(2)">Inactive</td>
	<td width="100%" class="tabspacer">&nbsp;</td>
  </tr>
  <tr id="tab1">
	<td class="tab" colspan="5"><p><b><?= dbNumRows($result) ?> Records Found</b> (<a href="configgameadd.php">Add New Game</a>)</p>
		<table width="100%" cellpadding="1" cellspacing="1" class="data">
		  <tr>
			<th>Name</th>
			<th>Game</th>
			<th>Description</th>
			<th>Query</th>
			<th>Directory</th>
			<th width="30"></th>
		  </tr>
		  <?php while ($rows = dbFetch($result)): ?>
		  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
			<td><?= $rows["name"] ?></td>
			<td><?= $rows["game"] ?></td>
			<td><?= $rows["slots"] ?> Slots <?= $rows["type"] ?></td>
			<td><?= $gamequery[$rows["query"]] ?? "" ?></td>
			<td><?= $rows["gamedir"] ?></td>
			<td><a href="configgameedit.php?id=<?= $rows["gameid"] ?>"><img src="templates/<?= TEMPLATE ?>/images/buttons/edit24.png" width="24" height="24" border="0" alt="Edit" /></a></td>
		  </tr>
		  <?php endwhile; ?>
		</table></td>
  </tr>
  <tr id="tab2" style="display:none;">
	<td class="tab" colspan="5"><p><b><?= dbNumRows($result1) ?> Records Found</b> (<a href="configgameadd.php">Add New Game</a>)</p>
		<table width="100%" cellpadding="1" cellspacing="1" class="data">
		  <tr>
			<th>Name</th>
			<th>Game</th>
			<th>Description</th>
			<th>Query</th>
			<th>Directory</th>
			<th width="30"></th>
		  </tr>
		  <?php while ($rows1 = dbFetch($result1)): ?>
		  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
			<td><?= $rows1["name"] ?></td>
			<td><?= $rows1["game"] ?></td>
			<td><?= $rows1["slots"] ?> Slots <?= $rows1["type"] ?></td>
			<td><?= $rows1["query"] ?></td>
			<td><?= $rows1["gamedir"] ?></td>
			<td><a href="configgameedit.php?id=<?= $rows1["gameid"] ?>"><img src="templates/<?= TEMPLATE ?>/images/buttons/edit24.png" width="24" height="24" border="0" alt="Edit" /></a></td>
		  </tr>
		  <?php endwhile; ?>
		</table></td>
  </tr>
</table>
<script language="javascript" type="text/javascript">var numtabs = 2;</script>
<?php
dbFreeResult($result);
dbFreeResult($result1);
include "./templates/" . TEMPLATE . "/footer.php";
