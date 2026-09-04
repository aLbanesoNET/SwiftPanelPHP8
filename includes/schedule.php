<?php
/**
 * Server schedules — simple recurring automation (restart / stop / start /
 * console command) with an hourly / daily / weekly cadence.
 */

/** Allowed values. */
function scheduleFreqs(): array   { return ['hourly', 'daily', 'weekly']; }
function scheduleActions(): array { return ['restart', 'stop', 'start', 'command']; }

/**
 * Next fire time (unix ts) strictly after $fromTs, matching the schedule's
 * cadence. Brute-scans minute by minute (bounded to ~8 days) — correct and
 * trivial to reason about; cron only runs this a handful of times.
 */
function scheduleNextRun(array $s, ?int $fromTs = null): int
{
	$from = $fromTs ?? time();
	$min  = max(0, min(59, (int) ($s['at_minute'] ?? 0)));
	$hour = max(0, min(23, (int) ($s['at_hour'] ?? 0)));
	$dow  = max(0, min(6,  (int) ($s['dow'] ?? 0)));
	$freq = in_array($s['freq'] ?? '', scheduleFreqs(), true) ? $s['freq'] : 'daily';

	$t = strtotime(date('Y-m-d H:i:00', $from)) + 60;

	for ($i = 0; $i < 8 * 24 * 60; $i++) {
		if ((int) date('i', $t) === $min) {
			if ($freq === 'hourly') {
				return $t;
			}
			if ((int) date('G', $t) === $hour) {
				if ($freq === 'daily') {
					return $t;
				}
				if ($freq === 'weekly' && (int) date('w', $t) === $dow) {
					return $t;
				}
			}
		}
		$t += 60;
	}

	return $from + 3600;
}

function scheduleDayName(int $dow): string
{
	return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$dow % 7];
}

/** Human summary, e.g. "Restart every day at 05:00". */
function scheduleDescribe(array $s): string
{
	$action = ucfirst((string) ($s['action'] ?? ''));
	if (($s['action'] ?? '') === 'command') {
		$action = 'Run "' . trim((string) ($s['command'] ?? '')) . '"';
	}

	$at = sprintf('%02d:%02d', (int) ($s['at_hour'] ?? 0), (int) ($s['at_minute'] ?? 0));

	switch ($s['freq'] ?? '') {
		case 'hourly': return $action . ' every hour at :' . sprintf('%02d', (int) ($s['at_minute'] ?? 0));
		case 'weekly': return $action . ' every ' . scheduleDayName((int) ($s['dow'] ?? 0)) . ' at ' . $at;
		default:       return $action . ' every day at ' . $at;
	}
}
