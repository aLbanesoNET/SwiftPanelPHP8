<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
// expects: $databases[], $cfg, $canCreate, $msg1, $msg2
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="title">
  <tr><td align="left"><h1>Databases</h1></td></tr>
</table>

<?php if (!empty($msg1)): ?>
  <div id="infobox"><strong><?= htmlspecialchars($msg1) ?></strong><br /><?= htmlspecialchars($msg2 ?? '') ?></div>
<?php endif; ?>

<p>MySQL databases for your game servers &mdash; use these credentials in server plugins or web tools.
<?php if ((int) $cfg['max'] > 0): ?>Your account may hold up to <strong><?= (int) $cfg['max'] ?></strong> database(s).<?php endif; ?>
Each has a <strong><?= (int) $cfg['maxsize'] ?> MB</strong> guideline limit.</p>

<table width="100%" cellpadding="2" cellspacing="1" class="data">
  <tr>
    <th>Database</th>
    <th>Username</th>
    <th>Password</th>
    <th>Host</th>
    <th>Size</th>
    <th width="150">&nbsp;</th>
  </tr>

  <?php if (empty($databases)): ?>
    <tr><td colspan="6"><b>No databases yet.</b></td></tr>
  <?php else: ?>
    <?php foreach ($databases as $d): ?>
      <?php $over = $d['limit_mb'] > 0 && $d['used_mb'] > $d['limit_mb']; ?>
      <tr onmouseover="this.className='mouseover'" onmouseout="this.className=''">
        <td><code><?= htmlspecialchars($d['dbname']) ?></code></td>
        <td><code><?= htmlspecialchars($d['dbuser']) ?></code></td>
        <td><code><?= htmlspecialchars($d['plainpass']) ?></code></td>
        <td><code><?= htmlspecialchars($d['dbhost']) ?></code></td>
        <td<?= $over ? ' style="color:#DD0000;font-weight:bold;"' : '' ?>>
          <?= number_format((float) $d['used_mb'], 1) ?> / <?= (int) $d['limit_mb'] ?> MB
        </td>
        <td>
          <a href="clientdatabasesprocess.php?task=resetpw&amp;dbid=<?= (int) $d['dbid'] ?>"
             onclick="return confirm('Reset the password for <?= htmlspecialchars($d['dbname'], ENT_QUOTES) ?>?');">Reset PW</a>
          &nbsp;|&nbsp;
          <a href="clientdatabasesprocess.php?task=delete&amp;dbid=<?= (int) $d['dbid'] ?>"
             onclick="return confirm('Delete <?= htmlspecialchars($d['dbname'], ENT_QUOTES) ?> and all its data? This cannot be undone.');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</table>

<br />

<?php if ($canCreate): ?>
  <fieldset>
    <table width="100%" border="0" cellpadding="2" cellspacing="2">
      <tr><td colspan="2" class="fieldheader">Create Database</td></tr>
      <tr>
        <td class="fieldname" style="width:140px;">Name</td>
        <td class="fieldarea">
          <form method="post" action="clientdatabasesprocess.php" style="margin:0;">
            <input type="hidden" name="task" value="create" />
            <code>c<?= (int) ($_SESSION['clientid'] ?? 0) ?>_</code>
            <input type="text" name="name" class="text" size="20" maxlength="24" />
            <input type="submit" value="Create" class="button green" />
            <br /><font color="#666666" size="-2">Lowercase letters, digits and underscore, up to 24 characters.</font>
          </form>
        </td>
      </tr>
    </table>
  </fieldset>
<?php else: ?>
  <div id="infobox2"><strong>Database limit reached</strong><br />Delete an existing database to create another.</div>
<?php endif; ?>
