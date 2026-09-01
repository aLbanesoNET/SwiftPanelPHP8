<?php

/*
 * Deterministic GNU screen session management for game servers.
 *
 * Every server runs in a screen session named "<serverid>-<user>". These
 * helpers guarantee there is at most one live session with that name, so the
 * web console (includes/console.php) always knows which one to attach to.
 */

function screenSessionName($serverid, string $user): string
{
	return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $serverid . '-' . $user);
}

/* Run one command over an existing ssh2 connection, return stdout+stderr. */
function sshExec($ssh, string $command, int $timeout = 20): string
{
	$stream = @ssh2_exec($ssh, $command);
	if ($stream === false) {
		return '';
	}
	stream_set_blocking($stream, true);
	stream_set_timeout($stream, $timeout);
	$out = (string) @stream_get_contents($stream);

	$err = @ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
	if ($err) {
		stream_set_blocking($err, true);
		$out .= (string) @stream_get_contents($err);
		@fclose($err);
	}
	@fclose($stream);

	return $out;
}

/*
 * Shell one-liner that terminates EVERY screen session whose name matches
 * exactly, wipes dead sockets, then force-kills anything the game server left
 * behind for this (dedicated per-server) user. Idempotent.
 */
function screenKillCommand(string $session, string $user = ''): string
{
	$s = "screen -wipe >/dev/null 2>&1; "
	   . "screen -ls 2>/dev/null | grep -oE '[0-9]+\\." . $session . "\\b' | while IFS= read -r S; do screen -S \"\$S\" -X quit >/dev/null 2>&1; done; "
	   . "screen -wipe >/dev/null 2>&1; ";

	if ($user !== '') {
		// Detached so it can't kill the shell that is running this command
		// (that shell is owned by the same user). Belt for daemonised servers
		// that ignore screen's SIGHUP.
		$s .= "setsid sh -c 'sleep 2; pkill -9 -u " . escapeshellarg($user) . "' </dev/null >/dev/null 2>&1 & ";
	}

	return $s . "true";
}

/*
 * Shell one-liner that clears any existing session with this name (quit + wipe,
 * NOT the user-wide kill — a restart must not nuke the server it's about to
 * bring back up), then starts the server fresh in a new detached session.
 * Prints "1" if the session came up, "0" otherwise. $startline is the fully
 * built command from buildStartCommand().
 */
function screenStartCommand(string $session, string $startline): string
{
	$run = 'cd "$HOME" 2>/dev/null; ' . $startline;

	return screenKillCommand($session)   // no $user -> quit + wipe only
		. "; sleep 1; "
		. "screen -dmS " . escapeshellarg($session) . " /bin/sh -c " . escapeshellarg($run) . "; "
		. "sleep 1; "
		. "screen -ls 2>/dev/null | grep -cE '[0-9]+\\." . $session . "\\b'";
}

/*
 * Resolve "<serverid>-<user>" to the single live "PID.name" token screen wants
 * when a plain name would be ambiguous. Empty string = no such session.
 */
function screenResolve($ssh, string $session): string
{
	$out = sshExec($ssh, "screen -ls 2>/dev/null | grep -oE '[0-9]+\\." . $session . "\\b' | tail -n1", 10);
	return trim($out);
}
