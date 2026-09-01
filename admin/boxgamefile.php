<?php
$title = "Box Game Files";
$page = "boxgamefile";
$tab = "4";
$return = "boxgamefile.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
$boxid = sanitizeInput($_GET["id"] ?? "");
$gamefiles = array();
$rows = dbRow("SELECT `boxid`, `name` FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
$result1 = dbQuery("SELECT `gameid`, `name`, `game`, `gamedir` FROM `game` WHERE `status` = 'Active' ORDER BY `game`");
$rows2 = dbRow("SELECT `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '" . $rows["boxid"] . "'");
if(!extension_loaded("ssh2")) {
	$_SESSION["msg1"] = "SSH2 Extension Error!";
	$_SESSION["msg2"] = "SSH2 Extension not detected!";
} elseif(!($sshconnection = @ssh2_connect($rows2["ip"], $rows2["sshport"]))) {
	$_SESSION["msg1"] = "Connection Error!";
	$_SESSION["msg2"] = "Unable to connect to box with SSH.";
} elseif(!@ssh2_auth_password($sshconnection, $rows2["login"], @base64_decode($rows2["password"]))) {
	$_SESSION["msg1"] = "Authentication Error!";
	$_SESSION["msg2"] = "Unable to login to box with SSH.";
} else {
	$sshshell = ssh2_shell($sshconnection, "vt102", null, 400, 80, SSH2_TERM_UNIT_CHARS);
	while ($rows1 = dbFetch($result1)) {
		$gamefiles[$rows1["gameid"]] = "<font color=\"#669933\"><b>Found</b></font>";
		fwrite($sshshell, "cd " . $rows1["gamedir"] . "\n");
		usleep(350000);
		while ($sshline = fgets($sshshell)) {
			if(preg_match("/No such file or directory/", $sshline)) {
				$gamefiles[$rows1["gameid"]] = "<font color=\"#DD0000\"><b>Not Found</b></font>";
			}
		}
	}
}
$result1 = dbQuery("SELECT `gameid`, `name`, `game`, `gamedir` FROM `game` WHERE `status` = 'Active' ORDER BY `game`");
$tabs = array("Summary" => "boxsummary.php?id=" . $rows["boxid"], "Profile" => "boxprofile.php?id=" . $rows["boxid"], "Servers" => "boxserver.php?id=" . $rows["boxid"], "Game Files" => "boxgamefile.php?id=" . $rows["boxid"], "Activity Logs" => "boxlog.php?id=" . $rows["boxid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 4);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab"><?= renderMessageBox() ?>
	  <div style="font-size:18px;">#<?= $rows["boxid"] ?> - <?= $rows["name"] ?></div>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />
	  <table width="100%" cellpadding="4" cellspacing="1" class="data">
		<tr>
		  <th>Name</th>
		  <th>Game</th>
		  <th>Install Path</th>
		  <th></th>
		</tr>
		<?php while ($rows1 = dbFetch($result1)): ?>
		<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
		  <td><?= $rows1["name"] ?></td>
		  <td><?= $rows1["game"] ?></td>
		  <td><?= $rows1["gamedir"] ?></td>
		  <td><?= $gamefiles[$rows1["gameid"]] ?? "" ?></td>
		</tr>
		<?php endwhile; ?>
	  </table>
	</td>
  </tr>
</table>
<?php
dbFreeResult($result1);
include "./templates/" . TEMPLATE . "/footer.php";
