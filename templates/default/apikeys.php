<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $keys = $keys ?? []; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>API Keys</h1></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<p>Control your servers from scripts with an <code>Authorization: Bearer &lt;token&gt;</code> header.
Base URL: <code><?= htmlspecialchars((($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/'))) ?>/api.php</code>
&mdash; <a href="api-test.php">try it out &rsaquo;</a></p>

<?php if (!empty($newToken)): ?>
  <fieldset><table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr><td class="fieldheader">New Token &mdash; Copy It Now</td></tr>
	<tr><td style="padding:8px 10px;"><code style="word-break:break-all;"><?= htmlspecialchars($newToken) ?></code><br /><font color="#666666" size="-2">This is the only time the full token is shown.</font></td></tr>
  </table></fieldset>
<?php endif; ?>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr><th>Label</th><th>Prefix</th><th width="90">Scope</th><th width="130">Last Used</th><th width="80"></th></tr>
  <?php if (empty($keys)): ?>
	<tr><td colspan="5"><b>No API keys yet.</b></td></tr>
  <?php endif; ?>
  <?php foreach ($keys as $k): ?>
	<tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
	  <td><b><?= htmlspecialchars($k['label']) ?></b></td>
	  <td><code><?= htmlspecialchars($k['prefix']) ?>&hellip;</code></td>
	  <td><?= $k['readonly'] === '1' ? 'read' : 'read+write' ?></td>
	  <td><?= $k['lastused'] ? htmlspecialchars(date('M j, H:i', strtotime((string) $k['lastused']))) : 'never' ?></td>
	  <td><a href="apikeysprocess.php?task=revoke&amp;keyid=<?= (int) $k['keyid'] ?>" onclick="return confirm('Revoke this key?');">Revoke</a></td>
	</tr>
  <?php endforeach; ?>
</table>

<br />

<form method="post" action="apikeysprocess.php">
  <input type="hidden" name="task" value="create" />
  <fieldset><table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr><td colspan="2" class="fieldheader">New Key</td></tr>
	<tr><td class="fieldname" style="width:110px;">Label</td><td class="fieldarea"><input type="text" name="label" class="text" size="30" placeholder="deploy script" /></td></tr>
	<tr><td class="fieldname">Scope</td><td class="fieldarea"><label><input type="checkbox" name="readonly" value="1" checked="checked" /> Read-only (no power actions)</label></td></tr>
  </table></fieldset>
  <div align="center"><input type="submit" value="Create Key" class="button green" /></div>
</form>
