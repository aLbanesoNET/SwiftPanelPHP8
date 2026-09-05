<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<section class="graphite-view graphite-view-server" data-view="server">
<?php
// expects: $servers[] (serverid,name,game,status,online,ip,port,servername?,map?,players?)
$servers = $servers ?? [];
$flash1  = $FLASH_MSG1 ?? ($msg1 ?? ($e_msg1 ?? null));
$flash2  = $FLASH_MSG2 ?? ($msg2 ?? ($e_msg2 ?? null));
?>
<?php if (!empty($flash1)): ?>
	<div class="graphite-note graphite-note-ok">
		<strong><?= htmlspecialchars($flash1) ?></strong>
		<span><?= htmlspecialchars((string)$flash2) ?></span>
	</div>
<?php endif; ?>

<div class="graphite-section-head">
	<h2>Servers</h2>
	<span class="graphite-count"><?= count($servers) ?></span>
	<span class="graphite-legend">
		<span><i class="graphite-dot graphite-dot-ok"></i> Running</span>
		<span><i class="graphite-dot graphite-dot-warn"></i> Pending</span>
		<span><i class="graphite-dot graphite-dot-off"></i> Stopped</span>
	</span>
</div>

<?php if (empty($servers)): ?>
	<div class="graphite-empty">No servers found.</div>
<?php else: ?>
	<div class="graphite-table">
		<div class="graphite-tr graphite-th">
			<span class="graphite-c-dot"></span>
			<span class="graphite-c-name">Name &amp; game</span>
			<span class="graphite-c-query">Live query</span>
			<span class="graphite-c-addr">Address</span>
			<span class="graphite-c-act"></span>
		</div>

		<?php foreach ($servers as $srv): ?>
			<?php
			$online = $srv['online'] ?? '';
			$status = $srv['status'] ?? '';
			$dotCls = $online === 'Started' ? 'ok' : ($online === 'Pending' ? 'warn' : 'off');
			$sid    = (int)($srv['serverid'] ?? 0);
			$addr   = !empty($srv['ip']) ? htmlspecialchars($srv['ip']) . ':' . (int)($srv['port'] ?? 0) : '&mdash;';
			?>
			<div class="graphite-tr">
				<span class="graphite-c-dot"><i class="graphite-dot graphite-dot-<?= $dotCls ?>" title="<?= htmlspecialchars($online) ?>"></i></span>

				<span class="graphite-c-name">
					<a class="graphite-srv-name" href="serversummary.php?id=<?= $sid ?>"><?= htmlspecialchars($srv['name'] ?? '') ?></a>
					<span class="graphite-srv-meta"><?= htmlspecialchars($srv['game'] ?? '') ?></span>
				</span>

				<span class="graphite-c-query">
					<?php if (!empty($srv['servername'])): ?>
						<strong><?= htmlspecialchars($srv['servername']) ?></strong>
						<span class="graphite-srv-meta"><?= htmlspecialchars($srv['map'] ?? '') ?><?= (($srv['players'] ?? '') !== '') ? ' &middot; ' . htmlspecialchars((string)$srv['players']) . ' players' : '' ?></span>
					<?php else: ?>
						<span class="graphite-srv-meta">&mdash;</span>
					<?php endif; ?>
				</span>

				<span class="graphite-c-addr"><code><?= $addr ?></code></span>

				<span class="graphite-c-act">
					<?php if ($online === 'Stopped' && $status !== 'Suspended'): ?>
						<a class="graphite-ibtn graphite-ibtn-go" href="servermanage.php?task=start&amp;return=server.php&amp;serverid=<?= $sid ?>" title="Start">&#9658;</a>
					<?php elseif ($online === 'Started' && $status !== 'Suspended'): ?>
						<a class="graphite-ibtn" href="servermanage.php?task=restart&amp;return=server.php&amp;serverid=<?= $sid ?>" title="Restart">&#8635;</a>
						<a class="graphite-ibtn graphite-ibtn-stop" href="servermanage.php?task=stop&amp;return=server.php&amp;serverid=<?= $sid ?>" title="Stop">&#9632;</a>
					<?php endif; ?>
					<a class="graphite-ibtn" href="serversummary.php?id=<?= $sid ?>" title="Manage">&#8250;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

</section>
