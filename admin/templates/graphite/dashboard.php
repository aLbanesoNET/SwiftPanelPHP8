<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-dashboard" data-view="dashboard">
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
<section class="graphite-hero">
	<div class="graphite-hero-text">
		<h1>Admin</h1>
		<p><?= (int) $numrows5 ?>/<?= (int) ($numrows5 + $numrows6) ?> boxes online &middot; <?= (int) $numrows3 ?> active server<?= $numrows3 === 1 ? '' : 's' ?></p>
	</div>
	<div style="display:flex;gap:8px;">
		<a class="graphite-btn" href="clientadd.php">Add client</a>
		<a class="graphite-btn graphite-btn-ghost" href="serveradd.php">Add server</a>
	</div>
</section>

<div class="graphite-stats">
	<?php foreach ($cards as [$label, $val, $href, $tone]): ?>
		<a class="graphite-stat" href="<?= htmlspecialchars($href) ?>" style="text-decoration:none;">
			<span class="graphite-stat-label"><?= htmlspecialchars($label) ?></span>
			<span class="graphite-stat-value" style="<?= $tone === 'warn' ? 'color:var(--warn);' : ($tone === 'muted' ? 'color:var(--muted);' : '') ?>"><?= (int) $val ?></span>
		</a>
	<?php endforeach; ?>
</div>

<div class="graphite-grid graphite-grid-2" style="margin-top:20px;">
	<div class="graphite-col">
		<div class="graphite-section-head"><h2>Recent activity</h2><a class="graphite-card-link" href="utilitieslog.php">View all</a></div>
		<div class="graphite-table">
			<div class="graphite-tr graphite-th" style="grid-template-columns:1.7fr 1fr 1fr;"><span>Event</span><span>By</span><span>When</span></div>
			<?php if (dbNumRows($result1) == 0): ?>
				<div class="graphite-tr"><span>No activity yet.</span></div>
			<?php endif; ?>
			<?php while ($r = dbFetch($result1)): ?>
				<div class="graphite-tr" style="grid-template-columns:1.7fr 1fr 1fr;">
					<span><?= $r['message'] ?></span>
					<span class="graphite-srv-meta"><?= htmlspecialchars($r['name']) ?></span>
					<span class="graphite-srv-meta"><?= htmlspecialchars(formatDate($r['timestamp'])) ?></span>
				</div>
			<?php endwhile; ?>
		</div>
	</div>

	<div class="graphite-col">
		<form method="post" action="process.php" class="graphite-card graphite-form">
			<input type="hidden" name="task" value="personalnotes">
			<input type="hidden" name="adminid" value="<?= (int) $rows['adminid'] ?>">
			<div class="graphite-card-head"><h2>Personal notes</h2></div>
			<textarea name="notes" rows="10"><?= htmlspecialchars($rows['notes']) ?></textarea>
			<div class="graphite-form-actions"><button type="submit" class="graphite-btn">Save</button></div>
		</form>
	</div>
</div>

</section>
