<?php
$title = "PHP Info";
$page = "utilitiesphpinfo";
$tab = "5";
$return = "utilitiesphpinfo.php";
require "../configuration.php";
require "./include.php";
include "./templates/" . TEMPLATE . "/header.php";
?>
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
	<td><iframe src="includes/phpinfo.php" frameborder="0" width="100%" height="600"></iframe></td>
  </tr>
</table>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
