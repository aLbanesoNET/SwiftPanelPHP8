<?php
$title = "Automation Settings";
$page = "configcron";
$tab = "6";
$return = "configcron.php";
$image = "admin_48";
require "../configuration.php";
require "./include.php";
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
$cronPath = @substr($_SERVER["SCRIPT_FILENAME"], 0, @strrpos($_SERVER["SCRIPT_FILENAME"], "/")) . "/cron/cron.php";
?>
<table width="90%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td align="center" class="fieldname"><input type="text" size="100" value="php -q <?= $cronPath ?>" />
	</td>
  </tr>
</table>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
