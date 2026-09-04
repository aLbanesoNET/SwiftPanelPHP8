<?php
/**
 * Inline SVG sparkline of a server's recent player count, from the `serverstat`
 * history that admin/cron.php collects. Returns '' when there isn't enough data.
 * Colours come from CSS custom properties so it fits any theme.
 */

function serverSparkline(int $serverid, int $hours = 24): string
{
	$res = dbQuery(
		"SELECT `players`, `maxplayers`, UNIX_TIMESTAMP(`ts`) AS t
		 FROM `serverstat`
		 WHERE `serverid` = '" . $serverid . "' AND `ts` >= DATE_SUB(NOW(), INTERVAL " . (int) $hours . " HOUR)
		 ORDER BY `ts`"
	);

	$pts = [];
	$max = 1;
	while ($res && ($r = dbFetch($res))) {
		$pts[] = [(int) $r['t'], (int) $r['players']];
		$max = max($max, (int) $r['players'], (int) $r['maxplayers']);
	}

	if (count($pts) < 2) {
		return '';
	}

	$w = 520;
	$h = 90;
	$pad = 4;
	$t0 = $pts[0][0];
	$span = max(1, $pts[count($pts) - 1][0] - $t0);

	$line = [];
	foreach ($pts as [$t, $p]) {
		$x = round(($t - $t0) / $span * $w, 1);
		$y = round($h - ($p / $max) * ($h - 2 * $pad) - $pad, 1);
		$line[] = $x . ',' . $y;
	}
	$poly = implode(' ', $line);
	$area = '0,' . $h . ' ' . $poly . ' ' . $w . ',' . $h;

	return '<svg class="fp-spark" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none" role="img" aria-label="Players over the last ' . (int) $hours . ' hours">'
		. '<polygon points="' . $area . '" fill="rgba(74,222,128,0.12)" />'
		. '<polyline points="' . $poly . '" fill="none" stroke="#4ade80" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />'
		. '</svg>';
}
