<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
require "../includes/notify.php";

$task     = sanitizeInput($_POST["task"] ?? ($_GET["task"] ?? ""));
$ticketid = (int) ($_POST["ticketid"] ?? ($_GET["ticketid"] ?? 0));

$ticket = dbRow("SELECT * FROM `ticket` WHERE `ticketid` = '" . $ticketid . "' LIMIT 1", TRUE);
if (!is_array($ticket) || empty($ticket)) {
	header("Location: tickets.php");
	exit;
}

$staff = trim(($_SESSION["adminfirstname"] ?? "") . " " . ($_SESSION["adminlastname"] ?? "")) ?: "Staff";

if ($task === "reply") {
	$body = trim((string) ($_POST["body"] ?? ""));
	if ($body !== "") {
		dbExec(
			"INSERT INTO `ticketpost` SET `ticketid` = '" . $ticketid . "', `author` = 'staff', " .
			"`name` = '" . dbEscape($staff) . "', `body` = '" . dbEscape($body) . "', `created` = NOW()"
		);
		$newStatus = !empty($_POST["close"]) ? "closed" : "answered";
		dbExec("UPDATE `ticket` SET `status` = '" . $newStatus . "', `updated` = NOW() WHERE `ticketid` = '" . $ticketid . "'");
		notifyClient((int) $ticket["clientid"], 'ticket', 'Support replied: ' . (string) $ticket["subject"], '', 'ticket.php?id=' . $ticketid);
		$_SESSION["msg1"] = "Reply Sent";
		$_SESSION["msg2"] = "The client can see your reply now.";
	}
	header("Location: ticket.php?id=" . $ticketid);
	exit;
}

if ($task === "status") {
	$status = sanitizeInput($_GET["status"] ?? "");
	if (in_array($status, ["open", "answered", "closed"], true)) {
		dbExec("UPDATE `ticket` SET `status` = '" . $status . "', `updated` = NOW() WHERE `ticketid` = '" . $ticketid . "'");
		$_SESSION["msg1"] = "Status Updated";
		$_SESSION["msg2"] = "Ticket is now " . $status . ".";
	}
	header("Location: ticket.php?id=" . $ticketid);
	exit;
}

header("Location: tickets.php");
