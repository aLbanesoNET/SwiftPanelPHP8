<?php
$title = "Cron Settings";
$page = "configcron";
$tab = "6";
$return = "configcron.php";
$image = "edit_48";
require "../configuration.php";
require "./include.php";
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
$cronPath = @substr($_SERVER["SCRIPT_FILENAME"], 0, @strrpos($_SERVER["SCRIPT_FILENAME"], "/")) . "/cron.php";
?>
<p>To enable server monitoring, set up the cron job to run every 10 minutes.</p>
<center>
<fieldset style="width:80%;">
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
	<td class="fieldname" style="padding-right:10px;text-align:center;">
	Create the following Cron Job using PHP:<br />
	<img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="3" width="1" alt="" /><br />
	<input type="text" class="text" size="100" value="php -q <?= $cronPath ?>" />
	</td>
  </tr>
</table>
</fieldset>
</center>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
