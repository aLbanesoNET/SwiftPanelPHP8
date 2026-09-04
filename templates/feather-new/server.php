<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $servers[] (serverid,name,game,status,online,ip,port,servername?,map?,players?)
$servers = $servers ?? [];
$flash1  = $FLASH_MSG1 ?? ($msg1 ?? ($e_msg1 ?? null));
$flash2  = $FLASH_MSG2 ?? ($msg2 ?? ($e_msg2 ?? null));
?>
<?php if (!empty($flash1)): ?>
	<div class="fp-note fp-note-ok">
		<strong><?= htmlspecialchars($flash1) ?></strong>
		<span><?= htmlspecialchars((string)$flash2) ?></span>
	</div>
<?php endif; ?>

<div class="fp-section-head">
	<h2>Servers</h2>
	<span class="fp-count"><?= count($servers) ?></span>
	<span class="fp-legend">
		<span><i class="fp-dot fp-dot-ok"></i> Running</span>
		<span><i class="fp-dot fp-dot-warn"></i> Pending</span>
		<span><i class="fp-dot fp-dot-off"></i> Stopped</span>
	</span>
</div>

<?php if (empty($servers)): ?>
	<div class="fp-empty">No servers found.</div>
<?php else: ?>
	<div class="fp-table">
		<div class="fp-tr fp-th">
			<span class="fp-c-dot"></span>
			<span class="fp-c-name">Name &amp; game</span>
			<span class="fp-c-query">Live query</span>
			<span class="fp-c-addr">Address</span>
			<span class="fp-c-act"></span>
		</div>

		<?php foreach ($servers as $srv): ?>
			<?php
			$online = $srv['online'] ?? '';
			$status = $srv['status'] ?? '';
			$dotCls = $online === 'Started' ? 'ok' : ($online === 'Pending' ? 'warn' : 'off');
			$sid    = (int)($srv['serverid'] ?? 0);
			$addr   = !empty($srv['ip']) ? htmlspecialchars($srv['ip']) . ':' . (int)($srv['port'] ?? 0) : '&mdash;';
			?>
			<div class="fp-tr">
				<span class="fp-c-dot"><i class="fp-dot fp-dot-<?= $dotCls ?>" title="<?= htmlspecialchars($online) ?>"></i></span>

				<span class="fp-c-name">
					<a class="fp-srv-name" href="serversummary.php?id=<?= $sid ?>"><?= htmlspecialchars($srv['name'] ?? '') ?></a>
					<span class="fp-srv-meta"><?= htmlspecialchars($srv['game'] ?? '') ?></span>
				</span>

				<span class="fp-c-query">
					<?php if (!empty($srv['servername'])): ?>
						<strong><?= htmlspecialchars($srv['servername']) ?></strong>
						<span class="fp-srv-meta"><?= htmlspecialchars($srv['map'] ?? '') ?><?= (($srv['players'] ?? '') !== '') ? ' &middot; ' . htmlspecialchars((string)$srv['players']) . ' players' : '' ?></span>
					<?php else: ?>
						<span class="fp-srv-meta">&mdash;</span>
					<?php endif; ?>
				</span>

				<span class="fp-c-addr"><code><?= $addr ?></code></span>

				<span class="fp-c-act">
					<?php if ($online === 'Stopped' && $status !== 'Suspended'): ?>
						<a class="fp-ibtn fp-ibtn-go" href="servermanage.php?task=start&amp;return=server.php&amp;serverid=<?= $sid ?>" title="Start">&#9658;</a>
					<?php elseif ($online === 'Started' && $status !== 'Suspended'): ?>
						<a class="fp-ibtn" href="servermanage.php?task=restart&amp;return=server.php&amp;serverid=<?= $sid ?>" title="Restart">&#8635;</a>
						<a class="fp-ibtn fp-ibtn-stop" href="servermanage.php?task=stop&amp;return=server.php&amp;serverid=<?= $sid ?>" title="Stop">&#9632;</a>
					<?php endif; ?>
					<a class="fp-ibtn" href="serversummary.php?id=<?= $sid ?>" title="Manage">&#8250;</a>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
