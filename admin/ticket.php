<?php
$title  = "Support Ticket";
$page   = "tickets";
$tab    = "5";
$return = "tickets.php";
require "../configuration.php";
require "./include.php";

$ticketid = (int) ($_GET["id"] ?? 0);
$ticket = dbRow(
	"SELECT t.*, c.`firstname`, c.`lastname`, c.`email`
	 FROM `ticket` t LEFT JOIN `client` c ON c.`clientid` = t.`clientid`
	 WHERE t.`ticketid` = '" . $ticketid . "' LIMIT 1",
	TRUE
);

if (!is_array($ticket) || empty($ticket)) {
	header("Location: tickets.php");
	exit;
}

$posts = dbQuery("SELECT * FROM `ticketpost` WHERE `ticketid` = '" . $ticketid . "' ORDER BY `postid`");

include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1><?= htmlspecialchars($ticket["subject"]) ?>
	<span style="font-size:13px;color:#888;">#<?= $ticketid ?> &mdash; <?= htmlspecialchars(trim(($ticket["firstname"] ?? "") . " " . ($ticket["lastname"] ?? ""))) ?> &mdash; <?= htmlspecialchars((string) $ticket["status"]) ?></span></h1></td>
	<td align="right">
	  <select onchange="if(this.value){window.location='ticketprocess.php?task=status&amp;ticketid=<?= $ticketid ?>&amp;status='+this.value;}">
		<option value="">Set status&hellip;</option>
		<option value="open">Open</option>
		<option value="answered">Answered</option>
		<option value="closed">Closed</option>
	  </select>
	</td></tr>
</table>

<?php while ($p = dbFetch($posts)): ?>
  <fieldset style="margin-bottom:10px;<?= $p["author"] === "staff" ? "background:#eef5ff;" : "" ?>">
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td class="fieldheader"><?= $p["author"] === "staff" ? "Support (" . htmlspecialchars((string) $p["name"]) . ")" : htmlspecialchars((string) $p["name"]) ?> &mdash; <?= htmlspecialchars(date("M j, Y H:i", strtotime((string) $p["created"]))) ?></td></tr>
	  <tr><td style="padding:8px 10px;"><?= nl2br(htmlspecialchars(trim((string) $p["body"]))) ?></td></tr>
	</table>
  </fieldset>
<?php endwhile; ?>

<form method="post" action="ticketprocess.php">
  <input type="hidden" name="task" value="reply" />
  <input type="hidden" name="ticketid" value="<?= $ticketid ?>" />
  <fieldset>
	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	  <tr><td class="fieldheader">Staff Reply</td></tr>
	  <tr><td><textarea name="body" class="textarea" rows="6" cols="90"></textarea></td></tr>
	  <tr><td><label><input type="checkbox" name="close" value="1" /> Also mark closed</label></td></tr>
	</table>
  </fieldset>
  <div align="center"><input type="submit" value="Send Reply" class="button green" /></div>
</form>
<?php
dbFreeResult($posts);
include "./templates/" . TEMPLATE . "/footer.php";
