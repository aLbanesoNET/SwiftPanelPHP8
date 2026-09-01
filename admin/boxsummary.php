<?php
$title = "Box Summary";
$page = "boxsummary";
$tab = "4";
$return = "boxsummary.php?id=" . ($_GET["id"] ?? "");
require "../configuration.php";
require "./include.php";
require "../includes/boxctl.php";
$boxid = sanitizeInput($_GET["id"] ?? "");
$rows = dbRow("SELECT * FROM `box` WHERE `boxid` = '" . $boxid . "' LIMIT 1");
$stats = getBoxStats($rows);
$result1 = dbQuery("SELECT * FROM `ip` WHERE `boxid` = '" . $boxid . "' ORDER BY `ip`");
$result3 = dbQuery("SELECT * FROM `log` WHERE `boxid` = '" . $boxid . "' ORDER BY `logid` DESC LIMIT 5");
$tabs = array("Summary" => "boxsummary.php?id=" . $rows["boxid"], "Profile" => "boxprofile.php?id=" . $rows["boxid"], "Servers" => "boxserver.php?id=" . $rows["boxid"], "Game Files" => "boxgamefile.php?id=" . $rows["boxid"], "Activity Logs" => "boxlog.php?id=" . $rows["boxid"]);
include "./templates/" . TEMPLATE . "/header.php";
renderTabs($tabs, 1);
?>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="tab">
	  <?= renderMessageBox() ?>
	  <div style="font-size:18px;">#<?= $rows["boxid"] ?> - <?= $rows["name"] ?></div>
	  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="1" height="6" alt="" /><br />
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td width="50%" valign="top"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Box Information</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Name</td>
				<td class="fieldarea"><?= $rows["name"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Location</td>
				<td class="fieldarea"><?= $rows["location"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">IP Address</td>
				<td class="fieldarea"><?= $rows["ip"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">OS Type</td>
				<td class="fieldarea"><?= $rows["ostype"] ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Monthly Cost</td>
				<td class="fieldarea"><?= $rows["cost"] ?></td>
			  </tr>
			</table>
			</fieldset>
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">Box Monitoring</td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">FTP</td>
				<td class="fieldarea"><?= formatStatusText($rows["ftp"]) ?>
				  <font color="#666666" size="-2">(Port: <?= $rows["ftpport"] ?>)</font></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">SSH</td>
				<td class="fieldarea"><?= formatStatusText($rows["ssh"]) ?>
				  <font color="#666666" size="-2">(Port: <?= $rows["sshport"] ?>)</font></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">CPU Load</td>
				<td class="fieldarea"><?= ($rows["load"] === "" || $rows["load"] === "~") ? "&mdash;" : htmlspecialchars($rows["load"]) ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">CPU Idle</td>
				<td class="fieldarea"><?= ($rows["idle"] === "" || $rows["idle"] === "~") ? "&mdash;" : htmlspecialchars($rows["idle"]) ?></td>
			  </tr>
			</table>
			</fieldset>
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td colspan="2" class="fieldheader">System Information</td>
			  </tr>
			  <?php if (empty($stats["ok"])): ?>
			  <tr>
				<td colspan="2" align="center"><font color="#DD0000">Live stats unavailable &mdash; box unreachable over SSH or the root login failed.</font></td>
			  </tr>
			  <?php else: ?>
			  <tr>
				<td class="fieldname" style="height:20px;width:110px;">Linux</td>
				<td class="fieldarea"><?= htmlspecialchars($stats["os"] ?? "") ?: "&mdash;" ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Kernel</td>
				<td class="fieldarea"><?= htmlspecialchars(trim(($stats["kernel"] ?? "") . " " . ($stats["arch"] ?? ""))) ?: "&mdash;" ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">CPU</td>
				<td class="fieldarea"><?= htmlspecialchars($stats["cpu"] ?? "") ?: "&mdash;" ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">CPU MHz</td>
				<td class="fieldarea"><?= ($stats["mhz"] ?? "") !== "" ? htmlspecialchars(number_format((float) $stats["mhz"], 0)) . " MHz" : "&mdash;" ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Cores / Threads</td>
				<td class="fieldarea"><?= htmlspecialchars(($stats["cores"] ?? "?") . " cores / " . ($stats["threads"] ?? "?") . " threads") ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Load Average</td>
				<td class="fieldarea"><?= htmlspecialchars($stats["load"] ?? "") ?: "&mdash;" ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">CPU Idle (now)</td>
				<td class="fieldarea"><?= ($stats["idle"] ?? "") !== "" ? htmlspecialchars($stats["idle"]) . "%" : "&mdash;" ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Memory</td>
				<td class="fieldarea"><?= formatBoxBytes(($stats["memtotal"] ?? 0) - ($stats["memavail"] ?? 0)) ?> used / <?= formatBoxBytes($stats["memtotal"] ?? 0) ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Storage (/)</td>
				<td class="fieldarea"><?= formatBoxBytes($stats["diskused"] ?? 0) ?> used, <?= formatBoxBytes($stats["diskfree"] ?? 0) ?> free / <?= formatBoxBytes($stats["disktotal"] ?? 0) ?></td>
			  </tr>
			  <tr>
				<td class="fieldname" style="height:20px;">Uptime</td>
				<td class="fieldarea"><?= formatBoxUptime($stats["uptime"] ?? 0) ?></td>
			  </tr>
			  <?php endif; ?>
			</table>
			</fieldset></td>
		  <td width="50%" valign="top"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td class="fieldheader">Last 5 Actions</td>
			  </tr>
			  <?php if(dbNumRows($result3) == 0): ?>
			  <tr>
				<td align="center">No Logs Found</td>
			  </tr>
			  <?php endif; ?>
			  <?php while ($rows3 = dbFetch($result3)): ?>
			  <tr>
				<td style="font-size:11px;"><?= formatDate($rows3["timestamp"]) ?> - <?= $rows3["message"] ?></td>
			  </tr>
			  <?php endwhile; ?>
			</table>
			</fieldset>
			<fieldset>
			<form method="post" action="boxprocess.php">
			  <input type="hidden" name="task" value="boxnotes" />
			  <input type="hidden" name="boxid" value="<?= $rows["boxid"] ?>" />
			  <table width="100%" border="0" cellpadding="2" cellspacing="2">
				<tr>
				  <td class="fieldheader" colspan="2">Admin Notes</td>
				</tr>
				<tr>
				  <td width="350" align="center"><textarea name="notes" class="textarea" rows="4" cols="60"><?= $rows["notes"] ?></textarea></td>
				  <td align="center"><input type="submit" value="Save" class="button green" /></td>
				</tr>
			  </table>
			</form>
			</fieldset>
			<fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr><td class="fieldheader">File Browser <font color="#666666" size="-2">(read only)</font></td></tr>
			  <tr><td><div id="boxFbArea" style="font-size:11px;">Loading&hellip;</div></td></tr>
			</table>
			</fieldset></td>
		</tr>
		<tr>
		  <td colspan="3"><fieldset>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
			  <tr>
				<td class="fieldheader"><?= dbNumRows($result1) ?> Assigned IPs (<a href="boxipadd.php?id=<?= $rows["boxid"] ?>" style="font-weight:normal;">Add IP Address</a>)</td>
			  </tr>
			  <tr>
				<td align="center"><table width="100%" cellpadding="2" cellspacing="1" class="data">
					<tr>
					  <th>IP Address</th>
					  <th>Servers</th>
					  <th>Slots</th>
					  <th>Used Ports</th>
					  <th width="30"></th>
					</tr>
					<?php if(dbNumRows($result1) == 0): ?>
					<tr>
					  <td colspan="7"><div id="infobox2"><strong>No IPs Found</strong><br />
					No IPs found. <a href="boxipadd.php?id=<?= $rows["boxid"] ?>">Click here</a> to add a new IP Address.</div></td>
					</tr>
					<?php endif; ?>
					<?php while ($rows1 = dbFetch($result1)):
						$servers = 0;
						$slots = 0;
						$ports = array();
						$result2 = dbQuery("SELECT `slots`, `port` FROM `server` WHERE `ipid` = '" . $rows1["ipid"] . "'");
						while ($rows2 = dbFetch($result2)) {
							$servers++;
							$slots = $slots + $rows2["slots"];
							$ports[] = $rows2["port"];
						}
						dbFreeResult($result2);
						sort($ports);
					?>
					<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
					  <td><?= $rows1["ip"] ?></td>
					  <td><?= $servers ?></td>
					  <td><?= $slots ?></td>
					  <?php if(!empty($ports)): ?>
					  <td><?= implode(" / ", $ports) ?></td>
					  <?php else: ?>
					  <td>None</td>
					  <?php endif; ?>
					  <td><a href="#" onclick="doDelete('<?= $rows1["ip"] ?>', '<?= $rows1["ipid"] ?>')"><img src="templates/<?= TEMPLATE ?>/images/status/red.png" width="25" height="25" alt="Delete" /></a></td>
					</tr>
					<?php endwhile; ?>
				  </table></td>
			  </tr>
			</table>
			</fieldset></td>
		</tr>
	  </table>
	  <fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr><td class="fieldheader">Console <font color="#666666" size="-2">(runs as <?= htmlspecialchars($rows["login"] ?: "root") ?> on the box)</font></td></tr>
		  <tr><td>
			<pre id="swConsoleOut" class="swconsole">Connecting&hellip;</pre>
			<form onsubmit="return swConsoleSend();" style="margin:6px 0 0;">
			  <input type="text" id="swConsoleCmd" class="text" style="width:74%;" autocomplete="off" placeholder="shell command, e.g. df -h" />
			  <input type="submit" value="Send" class="button" />
			  <input type="button" value="Refresh" class="button" onclick="swConsoleRefresh()" />
			  <label style="font-size:11px;"><input type="checkbox" id="swConsoleAuto" /> Auto</label>
			</form>
		  </td></tr>
		</table>
	  </fieldset>
	  <style type="text/css">
	  .swconsole{background:#0b0b0b;color:#3ad33a;font:11px/1.45 "DejaVu Sans Mono",Consolas,"Courier New",monospace;height:280px;overflow:auto;padding:8px;margin:0;white-space:pre-wrap;word-break:break-all;border:1px solid #333;}
	  </style>
	  <script type="text/javascript">
	  (function(){
		var BID=<?= (int)$rows["boxid"] ?>, EP="boxconsole.php";
		var out=document.getElementById('swConsoleOut'),
			inp=document.getElementById('swConsoleCmd'),
			auto=document.getElementById('swConsoleAuto'),
			busy=false;
		function parseJSON(s){
		  if(window.JSON&&typeof JSON.parse==='function')return JSON.parse(s);
		  if(window.JSON&&typeof JSON.decode==='function')return JSON.decode(s);
		  return eval('('+s+')');
		}
		function send(cmd){
		  if(busy){return;} busy=true;
		  var x=new XMLHttpRequest();
		  x.open('POST',EP,true);
		  x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		  x.onreadystatechange=function(){
			if(x.readyState!==4){return;} busy=false;
			var r; try{r=parseJSON(x.responseText);}catch(e){out.textContent='Console error: unexpected response.';return;}
			if(!r.ok){out.textContent=r.error||'Console error.';return;}
			out.textContent=r.output; out.scrollTop=out.scrollHeight;
		  };
		  x.send('id='+BID+'&command='+encodeURIComponent(cmd||''));
		}
		window.swConsoleSend=function(){ var c=inp.value; inp.value=''; send(c); if(c){ setTimeout(function(){ if(!busy){ send(''); } },2500); } return false; };
		window.swConsoleRefresh=function(){ send(''); };
		setInterval(function(){ if(auto&&auto.checked&&!busy){ send(''); } },5000);
		send('');
	  })();
	  </script>
	  <script type="text/javascript">
	  (function(){
		var BID=<?= (int)$rows["boxid"] ?>, area=document.getElementById('boxFbArea'), fbBusy=false;
		function fbLoad(url){
		  if(fbBusy){return;} fbBusy=true; area.innerHTML='Loading&hellip;';
		  var x=new XMLHttpRequest();
		  x.open('GET',url,true);
		  x.onreadystatechange=function(){
			if(x.readyState!==4){return;} fbBusy=false;
			area.innerHTML=(x.status===200 && x.responseText) ? x.responseText : '<div id="infobox2">File browser unavailable.</div>';
		  };
		  x.send();
		}
		window.boxFbNav=function(p){ fbLoad('boxfiles.php?id='+BID+'&path='+encodeURIComponent(p)); return false; };
		window.boxFbView=function(p,f){ fbLoad('boxfiles.php?id='+BID+'&path='+encodeURIComponent(p)+'&view='+encodeURIComponent(f)); return false; };
		boxFbNav('/home');
	  })();
	  </script>
	  <script language="javascript" type="text/javascript">
		<!--
		function doDelete(ip, id) { if (confirm("Are you sure you want to delete IP address: "+ip+"?")) { window.location='boxprocess.php?task=boxipdelete&ipid='+id; } }
		function deleteBox() { if (confirm("Are you sure you want to delete box: <?= $rows["name"] ?>?")) { window.location="boxprocess.php?task=boxdelete&id=<?= $rows["boxid"] ?>"; } }
		-->
	  </script>
	  <p align="center">
		<input type="button" onclick="deleteBox();return false;" class="button red" value="Delete Box" />
	  </p></td>
  </tr>
</table>
<?php
dbFreeResult($result3);
dbFreeResult($result1);
include "./templates/" . TEMPLATE . "/footer.php";
