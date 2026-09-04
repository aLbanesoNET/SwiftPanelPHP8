<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$openTickets = dbCount("SHOW TABLES LIKE 'ticket'") > 0 ? dbCount("SELECT `ticketid` FROM `ticket` WHERE `status` != 'closed'") : 0;
$dbCount     = dbCount("SHOW TABLES LIKE 'clientdatabase'") > 0 ? dbCount("SELECT `dbid` FROM `clientdatabase`") : 0;
$cards = [
	['Clients',   $numrows + $numrows1, 'client.php',                'ok'],
	['Servers',   $numrows2 + $numrows3 + $numrows4, 'server.php',   'ok'],
	['Active',    $numrows3, 'server.php?status=Active',              'ok'],
	['Pending',   $numrows2, 'server.php?status=Pending',             $numrows2 > 0 ? 'warn' : 'muted'],
	['Boxes up',  $numrows5, 'box.php',                               $numrows6 > 0 ? 'warn' : 'ok'],
	['Open tickets', $openTickets, 'tickets.php',                     $openTickets > 0 ? 'warn' : 'muted'],
	['Databases', $dbCount, 'configgeneral.php',                      'ok'],
];
?>
<section class="fp-hero">
	<div class="fp-hero-text">
		<h1>Admin</h1>
		<p><?= (int) $numrows5 ?>/<?= (int) ($numrows5 + $numrows6) ?> boxes online &middot; <?= (int) $numrows3 ?> active server<?= $numrows3 === 1 ? '' : 's' ?></p>
	</div>
	<div style="display:flex;gap:8px;">
		<a class="fp-btn" href="clientadd.php">Add client</a>
		<a class="fp-btn fp-btn-ghost" href="serveradd.php">Add server</a>
	</div>
</section>

<div class="fp-stats">
	<?php foreach ($cards as [$label, $val, $href, $tone]): ?>
		<a class="fp-stat" href="<?= htmlspecialchars($href) ?>" style="text-decoration:none;">
			<span class="fp-stat-label"><?= htmlspecialchars($label) ?></span>
			<span class="fp-stat-value" style="<?= $tone === 'warn' ? 'color:var(--warn);' : ($tone === 'muted' ? 'color:var(--muted);' : '') ?>"><?= (int) $val ?></span>
		</a>
	<?php endforeach; ?>
</div>

<div class="fp-grid fp-grid-2" style="margin-top:20px;">
	<div class="fp-col">
		<div class="fp-section-head"><h2>Recent activity</h2><a class="fp-card-link" href="utilitieslog.php">View all</a></div>
		<div class="fp-table">
			<div class="fp-tr fp-th" style="grid-template-columns:1.7fr 1fr 1fr;"><span>Event</span><span>By</span><span>When</span></div>
			<?php if (dbNumRows($result1) == 0): ?>
				<div class="fp-tr"><span>No activity yet.</span></div>
			<?php endif; ?>
			<?php while ($r = dbFetch($result1)): ?>
				<div class="fp-tr" style="grid-template-columns:1.7fr 1fr 1fr;">
					<span><?= $r['message'] ?></span>
					<span class="fp-srv-meta"><?= htmlspecialchars($r['name']) ?></span>
					<span class="fp-srv-meta"><?= htmlspecialchars(formatDate($r['timestamp'])) ?></span>
				</div>
			<?php endwhile; ?>
		</div>
	</div>

	<div class="fp-col">
		<form method="post" action="process.php" class="fp-card fp-form">
			<input type="hidden" name="task" value="personalnotes">
			<input type="hidden" name="adminid" value="<?= (int) $rows['adminid'] ?>">
			<div class="fp-card-head"><h2>Personal notes</h2></div>
			<textarea name="notes" rows="10"><?= htmlspecialchars($rows['notes']) ?></textarea>
			<div class="fp-form-actions"><button type="submit" class="fp-btn">Save</button></div>
		</form>
	</div>
</div>
