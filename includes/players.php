<?php
/**
 * Parse a Source / GoldSrc `status` console dump into a player list.
 * Returns [['name'=>..., 'uid'=>..., 'time'=>..., 'ping'=>...], ...].
 */

function parsePlayerStatus(string $out): array
{
	$players = [];

	foreach (preg_split('/\r\n|\r|\n/', $out) as $line) {
		$line = trim($line);
		// Both engines put player rows on lines that start with "#" then a number
		// and contain a quoted name.
		if (!preg_match('/^#\s*\d+\s+"([^"]*)"\s+(.*)$/', $line, $m)) {
			continue;
		}

		$name = $m[1];
		$rest = preg_split('/\s+/', trim($m[2]));

		// GoldSrc:  # slot "name" userid uniqueid frag time ping loss adr
		// Source :  # userid "name" uniqueid  time ping loss state rate adr
		$uid  = $rest[0] ?? '';
		$time = '';
		$ping = '';
		foreach ($rest as $tok) {
			if ($time === '' && preg_match('/^\d?\d:\d\d$/', $tok)) { $time = $tok; continue; }
			if ($time !== '' && $ping === '' && ctype_digit($tok)) { $ping = $tok; break; }
		}

		$players[$name] = [
			'name' => $name,
			'uid'  => $uid,
			'time' => $time,
			'ping' => $ping,
		];
	}

	// `screen -X hardcopy -h` includes scrollback, so the same "status" may
	// appear more than once — keep the last (most recent) line per name.
	return array_values($players);
}

/** A console-safe kick command for a player name (both engines accept kick "name"). */
function playerKickCommand(string $name): string
{
	$name = str_replace(['"', "\r", "\n", ';'], '', $name);
	return 'kick "' . $name . '"';
}
