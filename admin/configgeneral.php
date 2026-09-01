<?php
$title = "General Settings";
$page = "configgeneral";
$tab = "6";
$return = "configgeneral.php";
require "../configuration.php";
require "./include.php";
include "../includes/countries.php";
$panelname = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'panelname' LIMIT 1");
$systemurl = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'systemurl' LIMIT 1");
$template = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'template' LIMIT 1");
$country = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'country' LIMIT 1");
if(empty($_SESSION["panelname"])) {
	$_SESSION["panelname"] = $panelname["value"];
}
if(empty($_SESSION["systemurl"])) {
	$_SESSION["systemurl"] = $systemurl["value"];
}
if(empty($_SESSION["template"])) {
	$_SESSION["template"] = $template["value"];
}
if(empty($_SESSION["country"])) {
	$_SESSION["country"] = $country["value"];
}
$vPanelname = $_SESSION["panelname"] ?? ""; unset($_SESSION["panelname"]);
$vSystemurl = $_SESSION["systemurl"] ?? ""; unset($_SESSION["systemurl"]);
$vTemplate  = $_SESSION["template"] ?? "";  unset($_SESSION["template"]);
$vCountry   = $_SESSION["country"] ?? "";
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<form method="post" action="configgeneralprocess.php">
  <input type="hidden" name="task" value="generaledit" />
  <table width="100%" border="0" cellspacing="0">
	<tr>
	  <td width="5" class="tabspacer"><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="5" height="1" alt="" /></td>
	  <td id="tabs1" class="tabsactive" onclick="toggleTab(1)">General</td>
	  <td width="2" class="tabspacer"><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="2" height="1" alt="" /></td>
	  <td id="tabs2" class="tabs" onclick="toggleTab(2)">Localize</td>
	  <td width="2" class="tabspacer"><img src="templates/<?= TEMPLATE ?>/images/spacer.gif" width="2" height="1" alt="" /></td>
	  <td id="tabs3" class="tabs" onclick="toggleTab(3)">Support</td>
	  <td width="100%" class="tabspacer">&nbsp;</td>
	</tr>
	<tr id="tab1">
	  <td class="tab" colspan="7"><fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr>
			<td class="fieldname" style="width:140px;">Panel Name</td>
			<td class="fieldarea"><input type="text" name="panelname" class="text" size="35" value="<?= $vPanelname ?>" /><br />
			  <font color="#222222" size="-2">The name of the panel for the header in the client panel</font></td>
		  </tr>
		  <tr>
			<td class="fieldname">Panel URL</td>
			<td class="fieldarea"><input type="text" name="systemurl" class="text" size="45" value="<?= $vSystemurl ?>" /><br />
			  <font color="#222222" size="-2">URL of the client panel, eg. http://www.yourdomain.com/panel/</font></td>
		  </tr>
		  <tr>
			<td class="fieldname">Panel Template</td>
			<td class="fieldarea"><input type="text" name="template" class="text" size="15" value="<?= $vTemplate ?>" /><br />
			  <font color="#222222" size="-2">Name of the folder in templates</font></td>
		  </tr>
		  </table>
		</fieldset></td>
	</tr>
	<tr id="tab2" style="display:none;">
	  <td class="tab" colspan="7"><fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr>
			<td class="fieldname" style="width:140px;">Default Country</td>
			<td class="fieldarea"><select name="country" class="select">
			<?php foreach ($countries as $abbrev => $countryName): ?>
<option value="<?= $abbrev ?>"<?= $abbrev == $vCountry ? ' selected="selected"' : '' ?>><?= $countryName ?></option>
			<?php endforeach; ?>
</select></td>
		  </tr>
		  </table>
		</fieldset></td>
	</tr>
	<tr id="tab3" style="display:none;">
	  <td class="tab" colspan="7"><p align="center"><b>Support Ticket Feature Coming Soon!</b></p></td>
	</tr>
  </table>
  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1" alt="" /><br />
  <div align="center">
	<input type="submit" value="Save Changes" class="button green" />
  </div>
</form>
<script language="javascript" type="text/javascript">var numtabs = 3;</script>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
