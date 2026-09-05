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

$cdbEnabled = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'clientdb_enabled' LIMIT 1", true);
$cdbMax     = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'clientdb_max' LIMIT 1", true);
$cdbMaxsize = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'clientdb_maxsize' LIMIT 1", true);
$cdbHost    = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'clientdb_host' LIMIT 1", true);
$cdbPma     = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'clientdb_pma' LIMIT 1", true);
$vCdbEnabled = ($cdbEnabled["value"] ?? "0") === "1";
$vCdbMax     = (string) (int) ($cdbMax["value"] ?? "2");
$vCdbMaxsize = (string) (int) ($cdbMaxsize["value"] ?? "200");
$vCdbHost    = (string) ($cdbHost["value"] ?? "%");
$vCdbPma     = (string) ($cdbPma["value"] ?? "");
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

// Templates the panel can actually use need a folder in BOTH templates/ and
// admin/templates/ — offer the intersection as a dropdown.
$frontendTemplates = array_map("basename", array_filter((array) glob(__DIR__ . "/../templates/*"), "is_dir"));
$adminTemplates    = array_map("basename", array_filter((array) glob(__DIR__ . "/templates/*"), "is_dir"));
$templateChoices   = array_values(array_intersect($frontendTemplates, $adminTemplates));
sort($templateChoices);
if ($vTemplate !== "" && !in_array($vTemplate, $templateChoices, true)) {
	$templateChoices[] = $vTemplate;
}
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
			<td class="fieldarea"><select name="template" class="select">
			  <?php foreach ($templateChoices as $choice): ?>
			  <option value="<?= htmlspecialchars($choice) ?>"<?= $choice === $vTemplate ? ' selected="selected"' : '' ?>><?= htmlspecialchars(ucfirst($choice)) ?></option>
			  <?php endforeach; ?>
			  </select><br />
			  <font color="#222222" size="-2">Theme folder under <code>templates/</code> — applies to the client area and admin</font></td>
		  </tr>
		  </table>
		</fieldset>
		<fieldset>
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		  <tr><td colspan="2" class="fieldheader">Client MySQL Databases</td></tr>
		  <tr>
			<td class="fieldname" style="width:140px;">Enable Feature</td>
			<td class="fieldarea"><select name="clientdb_enabled" class="select">
			  <option value="0"<?= $vCdbEnabled ? '' : ' selected="selected"' ?>>No</option>
			  <option value="1"<?= $vCdbEnabled ? ' selected="selected"' : '' ?>>Yes</option>
			  </select><br />
			  <font color="#222222" size="-2">Let clients create MySQL databases on this panel's database server. Requires the panel DB user to hold <code>CREATE</code>, <code>CREATE USER</code> and <code>GRANT OPTION</code> globally.</font></td>
		  </tr>
		  <tr>
			<td class="fieldname">Max Per Client</td>
			<td class="fieldarea"><input type="text" name="clientdb_max" class="text" size="6" value="<?= htmlspecialchars($vCdbMax) ?>" />
			  <font color="#222222" size="-2">&nbsp;0 = unlimited</font></td>
		  </tr>
		  <tr>
			<td class="fieldname">Size Limit (MB)</td>
			<td class="fieldarea"><input type="text" name="clientdb_maxsize" class="text" size="6" value="<?= htmlspecialchars($vCdbMaxsize) ?>" />
			  <font color="#222222" size="-2">&nbsp;Shown to clients as a guideline. Size is measured and displayed, not enforced.</font></td>
		  </tr>
		  <tr>
			<td class="fieldname">Allowed Host</td>
			<td class="fieldarea"><input type="text" name="clientdb_host" class="text" size="20" value="<?= htmlspecialchars($vCdbHost) ?>" />
			  <font color="#222222" size="-2">&nbsp;MySQL "connect from" host for the created user. <code>%</code> = anywhere, <code>localhost</code> = same machine only.</font></td>
		  </tr>
		  <tr>
			<td class="fieldname">phpMyAdmin URL</td>
			<td class="fieldarea"><input type="text" name="clientdb_pma" class="text" size="45" value="<?= htmlspecialchars($vCdbPma) ?>" />
			  <font color="#222222" size="-2">&nbsp;Optional. Shown to clients as a management link.</font></td>
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
  </table>
  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1" alt="" /><br />
  <div align="center">
	<input type="submit" value="Save Changes" class="button green" />
  </div>
</form>
<script language="javascript" type="text/javascript">var numtabs = 2;</script>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
