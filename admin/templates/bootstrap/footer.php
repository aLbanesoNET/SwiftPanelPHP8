<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $page = $page ?? ''; ?>
</div>
</div>
<div id="footer">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td class="left"></td>
	<td class="center">Copyright &copy; 2009 <a href="http://www.swiftpanel.com" target="_blank">SWIFT Panel</a>.  All Rights Reserved.</td>
	<?php if ($page != "login"): ?>
	<td class="right"><a href="http://www.swiftpanel.com/forum" target="_blank">Community Forums</a></td>
	<?php else: ?>
	<td class="right">Version <?= VERSION ?></td>
	<?php endif; ?>
  </tr>
</table>
</div>
</div>
</body>
</html>
