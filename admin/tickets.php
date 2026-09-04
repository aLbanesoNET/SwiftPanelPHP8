<?php
$title  = "Support Tickets";
$page   = "tickets";
$tab    = "5";
$return = "tickets.php";
require "../configuration.php";
require "./include.php";

$filter = sanitizeInput($_GET["status"] ?? "");
$where  = in_array($filter, ["open", "answered", "closed"], true) ? " WHERE t.`status` = '" . $filter . "'" : "";

$list = dbQuery(
	"SELECT t.*, c.`firstname`, c.`lastname`,
	        (SELECT COUNT(*) FROM `ticketpost` p WHERE p.`ticketid` = t.`ticketid`) AS posts
	 FROM `ticket` t LEFT JOIN `client` c ON c.`clientid` = t.`clientid`" . $where . "
	 ORDER BY (t.`status` = 'closed'), t.`updated` DESC, t.`ticketid` DESC"
);

include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<p>
  <a href="tickets.php">All</a> |
  <a href="tickets.php?status=open">Open</a> |
  <a href="tickets.php?status=answered">Answered</a> |
  <a href="tickets.php?status=closed">Closed</a>
</p>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th width="50">ID</th><th>Subject</th><th>Client</th><th width="70">Priority</th><th width="90">Status</th><th width="140">Updated</th></tr>
  <?php if (dbNumRows($list) == 0): ?>
	<tr><td colspan="6"><b>No tickets.</b></td></tr>
  <?php endif; ?>
  <?php while ($t = dbFetch($list)): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td>#<?= (int) $t["ticketid"] ?></td>
	  <td><a href="ticket.php?id=<?= (int) $t["ticketid"] ?>"><?= htmlspecialchars($t["subject"]) ?></a> <span style="color:#888;">(<?= (int) $t["posts"] ?>)</span></td>
	  <td><?= htmlspecialchars(trim(($t["firstname"] ?? "") . " " . ($t["lastname"] ?? ""))) ?: "&mdash;" ?></td>
	  <td<?= $t["priority"] === "high" ? ' style="color:#DD0000;font-weight:bold;"' : '' ?>><?= htmlspecialchars($t["priority"]) ?></td>
	  <td><?= $t["status"] === "open" ? '<b style="color:#DD0000;">open</b>' : htmlspecialchars($t["status"]) ?></td>
	  <td><?= htmlspecialchars(date("M j, H:i", strtotime((string) $t["updated"]))) ?></td>
	</tr>
  <?php endwhile; ?>
</table>
<?php
dbFreeResult($list);
include "./templates/" . TEMPLATE . "/footer.php";
