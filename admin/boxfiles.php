<?php
/*
 * Read-only file browser fragment for the box summary page.
 * Returns an HTML fragment (breadcrumb + listing, or a file view).
 * Never writes anything on the box.
 */
$return = true;

require "../configuration.php";
require "./include.php";
require "../includes/boxctl.php";

header("Content-Type: text/html; charset=utf-8");

$boxid = (int) ($_POST["id"] ?? $_GET["id"] ?? 0);
$path  = (string) ($_POST["path"] ?? $_GET["path"] ?? "/home");
$view  = (string) ($_POST["view"] ?? $_GET["view"] ?? "");

function fbError(string $msg): void
{
	echo '<div id="infobox2"><strong>' . htmlspecialchars($msg) . '</strong></div>';
	exit;
}

if ($boxid <= 0) {
	fbError("Invalid request.");
}

$box = dbRow(
	"SELECT `boxid`, `ip`, `sshport`, `login`, `password` FROM `box` WHERE `boxid` = '{$boxid}' LIMIT 1",
	true
);
if (!$box) {
	fbError("Box not found.");
}

$conn = boxSshConnect($box);
if (!$conn) {
	fbError("Box unreachable over SSH — check the box is up and the root login is correct.");
}

$tpl = defined("TEMPLATE") ? TEMPLATE : "default";

/* Normalise the path: absolute, no traversal, no control chars. */
$path = str_replace(["\0", "\r", "\n"], "", $path);
$path = "/" . ltrim($path, "/");
$parts = [];
foreach (explode("/", $path) as $seg) {
	if ($seg === "" || $seg === ".") {
		continue;
	}
	if ($seg === "..") {
		array_pop($parts);
		continue;
	}
	$parts[] = $seg;
}
$path = "/" . implode("/", $parts);

/* ---------- File view ---------- */
if ($view !== "") {
	$view = str_replace(["/", "\0", "\r", "\n"], "", $view);
	$full = rtrim($path, "/") . "/" . $view;
	$q = escapeshellarg($full);

	$size = (int) trim(sshExec($conn, "if [ -f $q ]; then stat -c '%s' $q 2>/dev/null || wc -c < $q; else echo -1; fi", 8));
	if ($size < 0) {
		fbError("Not a regular file.");
	}

	$data = $size > 0 ? sshExec($conn, "head -c 262144 $q 2>&1", 15) : "";
	$binary = strpos($data, "\0") !== false;
	?>
	<div style="margin-bottom:4px;">
	  <a href="#" onclick="return boxFbNav('<?= htmlspecialchars(addslashes($path), ENT_QUOTES) ?>')">&laquo; back</a>
	  &nbsp;<b><?= htmlspecialchars(rtrim($path, "/") . "/" . $view) ?></b>
	  <?php if ($size > 262144): ?>
	  <font color="#666666" size="-2">(first 256 KB of <?= formatBoxBytes($size) ?>)</font>
	  <?php endif; ?>
	</div>
	<?php if ($binary): ?>
	<div id="infobox2"><strong>Binary file</strong><br /><?= formatBoxBytes($size) ?> &mdash; not shown.</div>
	<?php else: ?>
	<textarea readonly="readonly" class="textarea" rows="16" style="width:99%;" onclick="this.select();"><?= htmlspecialchars($data) ?></textarea>
	<?php endif; ?>
	<?php
	exit;
}

/* ---------- Directory listing ---------- */
$q = escapeshellarg($path);
$raw = sshExec($conn, "cd $q 2>/dev/null && ls -lA --time-style=long-iso 2>/dev/null", 12);

$dirs = [];
$files = [];
foreach (explode("\n", $raw) as $line) {
	$line = rtrim($line, "\r");
	if ($line === "" || strncmp($line, "total ", 6) === 0) {
		continue;
	}
	$c = preg_split('/\s+/', $line, 8);
	if (count($c) < 8) {
		continue;
	}
	$perms = $c[0];
	$type  = $perms[0];
	$name  = $c[7];
	// resolve symlink display ("name -> target")
	$arrow = strpos($name, " -> ");
	$linkTarget = "";
	if ($type === "l" && $arrow !== false) {
		$linkTarget = substr($name, $arrow + 4);
		$name = substr($name, 0, $arrow);
	}
	$entry = [
		"name"   => $name,
		"perms"  => $perms,
		"owner"  => $c[2],
		"group"  => $c[3],
		"size"   => (int) $c[4],
		"mtime"  => $c[5] . " " . $c[6],
		"link"   => $linkTarget,
	];
	if ($type === "d") {
		$dirs[] = $entry;
	} else {
		$files[] = $entry;
	}
}

$parent = ($path === "/") ? null : ("/" . implode("/", array_slice($parts, 0, -1)));
$childBase = ($path === "/") ? "/" : ($path . "/");
$jsPath = htmlspecialchars(addslashes($path), ENT_QUOTES);
?>
<div style="margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
  <img src="templates/<?= htmlspecialchars($tpl) ?>/images/home_24.png" align="absmiddle" alt="" />
  <a href="#" onclick="return boxFbNav('/')">/</a>
  <?php
	$acc = "";
	foreach ($parts as $seg) {
		$acc .= "/" . $seg;
		echo '<a href="#" onclick="return boxFbNav(\'' . htmlspecialchars(addslashes($acc), ENT_QUOTES) . '\')">' . htmlspecialchars($seg) . '</a> / ';
	}
  ?>
</div>
<div style="max-height:300px;overflow:auto;border:1px solid #ccc;">
<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr>
	<th style="text-align:left;">Name</th>
	<th width="70">Size</th>
	<th width="70">Owner</th>
	<th width="90">Perms</th>
  </tr>
  <?php if ($parent !== null): ?>
  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	<td style="text-align:left;" colspan="4"><a href="#" onclick="return boxFbNav('<?= htmlspecialchars(addslashes($parent === "" ? "/" : $parent), ENT_QUOTES) ?>')">.. <font color="#666666" size="-2">(up one level)</font></a></td>
  </tr>
  <?php endif; ?>
  <?php foreach ($dirs as $d): ?>
  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	<td style="text-align:left;"><img src="templates/<?= htmlspecialchars($tpl) ?>/images/folder_24.png" align="absmiddle" alt="" /> <a href="#" onclick="return boxFbNav('<?= htmlspecialchars(addslashes($childBase . $d["name"]), ENT_QUOTES) ?>')"><?= htmlspecialchars($d["name"]) ?></a><?php if ($d["link"] !== ""): ?> <font color="#666666" size="-2">&rarr; <?= htmlspecialchars($d["link"]) ?></font><?php endif; ?></td>
	<td>&mdash;</td>
	<td><?= htmlspecialchars($d["owner"]) ?></td>
	<td><font size="-2"><?= htmlspecialchars($d["perms"]) ?></font></td>
  </tr>
  <?php endforeach; ?>
  <?php foreach ($files as $f): ?>
  <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	<td style="text-align:left;"><img src="templates/<?= htmlspecialchars($tpl) ?>/images/preview_24.png" align="absmiddle" alt="" /> <a href="#" onclick="return boxFbView('<?= $jsPath ?>','<?= htmlspecialchars(addslashes($f["name"]), ENT_QUOTES) ?>')"><?= htmlspecialchars($f["name"]) ?></a><?php if ($f["link"] !== ""): ?> <font color="#666666" size="-2">&rarr; <?= htmlspecialchars($f["link"]) ?></font><?php endif; ?></td>
	<td><?= $f["perms"][0] === "l" ? "&mdash;" : formatBoxBytes($f["size"]) ?></td>
	<td><?= htmlspecialchars($f["owner"]) ?></td>
	<td><font size="-2"><?= htmlspecialchars($f["perms"]) ?></font></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$dirs && !$files): ?>
  <tr><td colspan="4" align="center"><font color="#666666">Empty or unreadable directory.</font></td></tr>
  <?php endif; ?>
</table>
</div>
