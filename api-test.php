<?php
declare(strict_types=1);

$title = "API Tester";
$page  = "apikeys";

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$clientId = (int) ($_SESSION['clientid'] ?? 0);
if (!$clientId) {
	header('Location: login.php');
	exit;
}

// Same-origin scheme+host the browser is actually using, so the copy-paste
// curl/fetch examples work whether this is reached via IP or a real domain.
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$apiBase = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'your-panel-host') . '/api.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($title . ' - ' . SITENAME, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
	:root {
		--bg: #0f1115;
		--panel: #171a21;
		--panel-2: #1e222b;
		--line: #2a2f3a;
		--text: #e8eaed;
		--muted: #9399a8;
		--accent: #4f8cff;
		--accent-2: #7c5cff;
		--ok: #35c46f;
		--warn: #e0a72e;
		--bad: #e2544a;
		--mono: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
		--sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
	}
	* { box-sizing: border-box; }
	body {
		margin: 0;
		background: radial-gradient(1200px 600px at 10% -10%, #1a1f2b 0%, var(--bg) 55%);
		color: var(--text);
		font-family: var(--sans);
		line-height: 1.5;
	}
	a { color: var(--accent); }
	.wrap { max-width: 980px; margin: 0 auto; padding: 32px 20px 80px; }
	.top { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 6px; }
	.top h1 { font-size: 22px; margin: 0; }
	.top .back { font-size: 13px; color: var(--muted); text-decoration: none; }
	.top .back:hover { color: var(--text); }
	.sub { color: var(--muted); margin: 0 0 28px; font-size: 14px; }
	.sub code { color: var(--text); }

	.card {
		background: linear-gradient(180deg, var(--panel-2), var(--panel));
		border: 1px solid var(--line);
		border-radius: 14px;
		padding: 20px 22px;
		margin-bottom: 20px;
	}
	.card h2 { font-size: 15px; margin: 0 0 4px; display: flex; align-items: center; gap: 10px; }
	.card .desc { color: var(--muted); font-size: 13px; margin: 0 0 16px; }

	.method {
		font-family: var(--mono);
		font-size: 11px;
		font-weight: 700;
		padding: 3px 8px;
		border-radius: 6px;
		letter-spacing: .04em;
	}
	.method.get { background: rgba(79,140,255,.15); color: #7fb0ff; }
	.method.post { background: rgba(53,196,111,.15); color: #5fe294; }

	code, .mono { font-family: var(--mono); font-size: 12.5px; }
	.path { color: var(--text); }

	label { display: block; font-size: 12px; color: var(--muted); margin: 0 0 5px; }
	input[type=text], input[type=password], select {
		width: 100%;
		background: #11141b;
		border: 1px solid var(--line);
		color: var(--text);
		border-radius: 8px;
		padding: 9px 11px;
		font-family: var(--mono);
		font-size: 13px;
	}
	input:focus, select:focus { outline: none; border-color: var(--accent); }
	.row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
	.row > div { flex: 1; min-width: 140px; }
	.row.tight > div { flex: 0 0 auto; min-width: 0; }

	.key-card input { font-size: 14px; padding: 11px 12px; }
	.key-card .row { align-items: flex-end; }
	.key-card .row > div:first-child { flex: 1; }
	.key-hint { color: var(--muted); font-size: 12px; margin-top: 8px; }
	.key-hint a { color: inherit; text-decoration: underline; }

	button {
		background: var(--accent);
		color: #fff;
		border: none;
		border-radius: 8px;
		padding: 9px 16px;
		font-size: 13px;
		font-weight: 600;
		cursor: pointer;
		white-space: nowrap;
	}
	button:hover { filter: brightness(1.08); }
	button:disabled { opacity: .5; cursor: not-allowed; }
	button.danger { background: var(--bad); }
	button.ghost { background: transparent; border: 1px solid var(--line); color: var(--text); }

	.result {
		margin-top: 14px;
		background: #0b0d12;
		border: 1px solid var(--line);
		border-radius: 10px;
		padding: 12px 14px;
		font-family: var(--mono);
		font-size: 12.5px;
		white-space: pre-wrap;
		word-break: break-all;
		max-height: 340px;
		overflow: auto;
		display: none;
	}
	.result.show { display: block; }
	.status-line { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 12px; }
	.status-pill { padding: 2px 8px; border-radius: 999px; font-weight: 700; }
	.status-pill.s2 { background: rgba(53,196,111,.15); color: #5fe294; }
	.status-pill.s4 { background: rgba(224,167,46,.15); color: #f0bc55; }
	.status-pill.s5 { background: rgba(226,84,74,.15); color: #ff8c82; }
	.status-pill.s0 { background: rgba(147,153,168,.15); color: var(--muted); }

	.curl-box {
		position: relative;
		background: #0b0d12;
		border: 1px solid var(--line);
		border-radius: 10px;
		padding: 12px 44px 12px 14px;
		margin-top: 12px;
		font-family: var(--mono);
		font-size: 12px;
		color: #b9c4d6;
		white-space: pre-wrap;
		word-break: break-all;
	}
	.copy-btn {
		position: absolute;
		top: 8px;
		right: 8px;
		background: var(--panel);
		border: 1px solid var(--line);
		color: var(--muted);
		border-radius: 6px;
		font-size: 11px;
		padding: 4px 8px;
	}
	.copy-btn:hover { color: var(--text); }

	table.fields { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-top: 4px; }
	table.fields th, table.fields td { text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--line); vertical-align: top; }
	table.fields th { color: var(--muted); font-weight: 600; width: 110px; }
	table.fields code { color: #a8c7ff; }

	.errors { margin-top: 14px; }
	.errors table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
	.errors td { padding: 5px 8px; border-bottom: 1px solid var(--line); }
	.errors td:first-child { width: 60px; font-family: var(--mono); font-weight: 700; }
	.errors .c401, .errors .c403 { color: #f0bc55; }
	.errors .c404 { color: var(--muted); }
	.errors .c422, .errors .c502 { color: #ff8c82; }

	.badge-ro { font-size: 11px; color: var(--warn); border: 1px solid rgba(224,167,46,.4); padding: 1px 7px; border-radius: 999px; }
	.foot-note { color: var(--muted); font-size: 12px; margin-top: 30px; text-align: center; }
</style>
</head>
<body>
<div class="wrap">

	<div class="top">
		<h1>API Tester</h1>
		<a class="back" href="apikeys.php">&larr; Back to API Keys</a>
	</div>
	<p class="sub">Every request below runs straight from your browser to
		<code><?= htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') ?></code> using the key you paste in —
		nothing is sent to or stored by this page itself. Manage/revoke keys on the
		<a href="apikeys.php">API Keys</a> page.</p>

	<div class="card key-card">
		<h2>Your API key</h2>
		<p class="desc">A Bearer token from the API Keys page. Kept only in this browser tab (not saved, not sent anywhere but the URL above).</p>
		<div class="row">
			<div>
				<label for="apikey">Authorization: Bearer &hellip;</label>
				<input type="password" id="apikey" placeholder="paste your API key" autocomplete="off">
			</div>
			<div style="flex:0 0 auto;">
				<button type="button" class="ghost" id="toggleKey">Show</button>
			</div>
		</div>
		<div class="key-hint">Don't have one? Create a read-only or read+write key on the <a href="apikeys.php">API Keys</a> page first.</div>
	</div>

	<!-- GET /servers -->
	<div class="card">
		<h2><span class="method get">GET</span> <span class="path">/servers</span></h2>
		<p class="desc">List every server on your account.</p>
		<div class="row tight">
			<button type="button" data-endpoint="list-servers">Send request</button>
		</div>
		<div class="curl-box" data-curl="list-servers"></div>
		<div class="result" data-result="list-servers"></div>
	</div>

	<!-- GET /servers/{id} -->
	<div class="card">
		<h2><span class="method get">GET</span> <span class="path">/servers/{id}</span></h2>
		<p class="desc">One server's details. Includes a live <code>query</code> block (name/map/players) when the game server answers.</p>
		<div class="row">
			<div>
				<label for="get-id">Server ID</label>
				<input type="text" id="get-id" placeholder="e.g. 1" inputmode="numeric">
			</div>
		</div>
		<div class="row tight">
			<button type="button" data-endpoint="get-server">Send request</button>
		</div>
		<div class="curl-box" data-curl="get-server"></div>
		<div class="result" data-result="get-server"></div>
	</div>

	<!-- POST /servers/{id}/power -->
	<div class="card">
		<h2><span class="method post">POST</span> <span class="path">/servers/{id}/power</span> <span class="badge-ro">blocked for read-only keys</span></h2>
		<p class="desc">Start, stop, or restart a server. This is a real action against your server — same as clicking the buttons on its Server Details page.</p>
		<div class="row">
			<div>
				<label for="power-id">Server ID</label>
				<input type="text" id="power-id" placeholder="e.g. 1" inputmode="numeric">
			</div>
			<div>
				<label for="power-action">Action</label>
				<select id="power-action">
					<option value="start">start</option>
					<option value="stop">stop</option>
					<option value="restart">restart</option>
				</select>
			</div>
		</div>
		<div class="row tight">
			<button type="button" class="danger" data-endpoint="power">Send request</button>
		</div>
		<div class="curl-box" data-curl="power"></div>
		<div class="result" data-result="power"></div>
	</div>

	<div class="card">
		<h2>Reference</h2>
		<p class="desc">Every response is JSON. Authenticate with an <code>Authorization: Bearer &lt;token&gt;</code> header — tokens come from the <a href="apikeys.php">API Keys</a> page and are shown in full only once, at creation.</p>

		<table class="fields">
			<tr><th>Server object</th><td>
				<code>id</code>, <code>name</code>, <code>game</code>, <code>status</code>, <code>online</code>,
				<code>slots</code>, <code>address</code> ("ip:port" or <code>null</code>).
				<code>GET /servers/{id}</code> additionally includes <code>query</code>: <code>{name, map, players}</code>
				(only present when the game server responds to a live query).
			</td></tr>
		</table>

		<div class="errors">
			<table>
				<tr><td class="c401">401</td><td>Missing/malformed <code>Authorization</code> header, or the token doesn't match any key.</td></tr>
				<tr><td class="c403">403</td><td>A read-only key was used for <code>POST /servers/{id}/power</code>.</td></tr>
				<tr><td class="c404">404</td><td>Server not found on your account (or an unknown endpoint).</td></tr>
				<tr><td class="c422">422</td><td><code>action</code> in the power request wasn't one of <code>start</code>/<code>stop</code>/<code>restart</code>.</td></tr>
				<tr><td class="c502">502</td><td>The panel couldn't reach the game server's box over SSH.</td></tr>
			</table>
		</div>
	</div>

	<p class="foot-note">This page never stores or transmits your key anywhere except directly to your own panel above.</p>
</div>

<script>
(function () {
	'use strict';

	var API_BASE = <?= json_encode($apiBase, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
	var keyInput = document.getElementById('apikey');

	document.getElementById('toggleKey').addEventListener('click', function () {
		var showing = keyInput.type === 'text';
		keyInput.type = showing ? 'password' : 'text';
		this.textContent = showing ? 'Show' : 'Hide';
	});

	function shellQuote(s) {
		return "'" + String(s).replace(/'/g, "'\\''") + "'";
	}

	function buildCurl(method, url, body) {
		var key = keyInput.value || 'YOUR_API_KEY';
		var parts = ['curl -s', '-X ' + method, '-H ' + shellQuote('Authorization: Bearer ' + key)];
		if (body !== undefined) {
			parts.push("-H 'Content-Type: application/json'");
			parts.push('-d ' + shellQuote(JSON.stringify(body)));
		}
		parts.push(shellQuote(url));
		return parts.join(' \\\n     ');
	}

	function renderCurl(name, method, url, body) {
		var el = document.querySelector('[data-curl="' + name + '"]');
		if (!el) return;
		var btn = el.querySelector('.copy-btn');
		var text = buildCurl(method, url, body);
		el.textContent = text;
		el.dataset.raw = text;
		if (btn) { el.appendChild(btn); }
	}

	function statusClass(status) {
		if (status === 0) return 's0';
		if (status < 300) return 's2';
		if (status < 500) return 's4';
		return 's5';
	}

	function showResult(name, status, bodyText) {
		var el = document.querySelector('[data-result="' + name + '"]');
		if (!el) return;
		var pillClass = statusClass(status);
		var label = status === 0 ? 'no response' : status;
		el.innerHTML = '<div class="status-line"><span class="status-pill ' + pillClass + '">' + label + '</span></div>';
		var pre = document.createElement('div');
		pre.textContent = bodyText;
		el.appendChild(pre);
		el.classList.add('show');
	}

	function send(name, method, url, body) {
		renderCurl(name, method, url, body);
		var key = keyInput.value.trim();
		if (!key) {
			showResult(name, 0, 'Paste your API key above first.');
			return;
		}
		var opts = {
			method: method,
			headers: { 'Authorization': 'Bearer ' + key }
		};
		if (body !== undefined) {
			opts.headers['Content-Type'] = 'application/json';
			opts.body = JSON.stringify(body);
		}
		fetch(url, opts).then(function (res) {
			return res.text().then(function (text) {
				return { status: res.status, text: text };
			});
		}).then(function (r) {
			var pretty = r.text;
			try { pretty = JSON.stringify(JSON.parse(r.text), null, 2); } catch (e) {}
			showResult(name, r.status, pretty);
		}).catch(function (err) {
			showResult(name, 0, 'Request failed: ' + err.message + '\n\nIf the panel is served over a different origin/HTTPS, your browser may be blocking a mixed-content or cross-origin request.');
		});
	}

	function refreshCurlPreviews() {
		renderCurl('list-servers', 'GET', API_BASE + '/servers');
		var getId = document.getElementById('get-id').value.trim() || '{id}';
		renderCurl('get-server', 'GET', API_BASE + '/servers/' + getId);
		var powerId = document.getElementById('power-id').value.trim() || '{id}';
		var action = document.getElementById('power-action').value;
		renderCurl('power', 'POST', API_BASE + '/servers/' + powerId + '/power', { action: action });
	}

	document.querySelectorAll('.curl-box').forEach(function (el) {
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'copy-btn';
		btn.textContent = 'Copy';
		btn.addEventListener('click', function () {
			navigator.clipboard.writeText(el.dataset.raw || '').then(function () {
				btn.textContent = 'Copied';
				setTimeout(function () { btn.textContent = 'Copy'; }, 1200);
			});
		});
		el.appendChild(btn);
	});

	document.querySelector('[data-endpoint="list-servers"]').addEventListener('click', function () {
		send('list-servers', 'GET', API_BASE + '/servers');
	});

	document.querySelector('[data-endpoint="get-server"]').addEventListener('click', function () {
		var id = document.getElementById('get-id').value.trim();
		if (!id) { showResult('get-server', 0, 'Enter a server ID first.'); return; }
		send('get-server', 'GET', API_BASE + '/servers/' + encodeURIComponent(id));
	});

	document.querySelector('[data-endpoint="power"]').addEventListener('click', function () {
		var id = document.getElementById('power-id').value.trim();
		var action = document.getElementById('power-action').value;
		if (!id) { showResult('power', 0, 'Enter a server ID first.'); return; }
		if (!confirm('Send "' + action + '" to server #' + id + '? This performs a real action on your server.')) { return; }
		send('power', 'POST', API_BASE + '/servers/' + encodeURIComponent(id) + '/power', { action: action });
	});

	['get-id'].forEach(function (id) { document.getElementById(id).addEventListener('input', refreshCurlPreviews); });
	['power-id', 'power-action'].forEach(function (id) { document.getElementById(id).addEventListener('input', refreshCurlPreviews); });
	keyInput.addEventListener('input', refreshCurlPreviews);
	refreshCurlPreviews();
})();
</script>
</body>
</html>
