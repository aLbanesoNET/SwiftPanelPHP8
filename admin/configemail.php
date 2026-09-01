<?php
$title = "Email Templates";
$page = "configemail";
$tab = "6";
$return = "configemail.php";
$image = "mail_24";
require "../configuration.php";
require "./include.php";
$rows = dbRow("SELECT * FROM `emailtemp` WHERE `emailtempid` = '1'");
if(empty($_SESSION["e1name"])) {
	$_SESSION["e1name"] = $rows["name"];
}
if(empty($_SESSION["e1email"])) {
	$_SESSION["e1email"] = $rows["email"];
}
if(empty($_SESSION["e1bcc"])) {
	$_SESSION["e1bcc"] = $rows["bcc"];
}
if(empty($_SESSION["e1subject"])) {
	$_SESSION["e1subject"] = $rows["subject"];
}
if(empty($_SESSION["e1template"])) {
	$_SESSION["e1template"] = $rows["template"];
}
$e1name     = $_SESSION["e1name"] ?? "";     unset($_SESSION["e1name"]);
$e1email    = $_SESSION["e1email"] ?? "";    unset($_SESSION["e1email"]);
$e1bcc      = $_SESSION["e1bcc"] ?? "";      unset($_SESSION["e1bcc"]);
$e1subject  = $_SESSION["e1subject"] ?? "";  unset($_SESSION["e1subject"]);
$e1template = $_SESSION["e1template"] ?? ""; unset($_SESSION["e1template"]);
include "./templates/" . TEMPLATE . "/header.php";
echo renderMessageBox();
?>
<form method="post" action="configemailprocess.php">
  <input type="hidden" name="task" value="emailedit" />
  <fieldset>
  <table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr>
	  <td colspan="2" class="fieldheader">New Client Account Email</td>
	</tr>
	<tr>
	  <td class="fieldname" style="width:140px;">From</td>
	  <td class="fieldarea"><input type="text" name="e1name" class="text" size="25" value="<?= $e1name ?>" />
		<input type="text" name="e1email" class="text" size="35" value="<?= $e1email ?>" /></td>
	</tr>
	<tr>
	  <td class="fieldname">Bcc</td>
	  <td class="fieldarea"><input type="text" name="e1bcc" class="text" size="45" value="<?= $e1bcc ?>" />
		<font color="#666666" size="-2">(Seperate email addresses by a comma)</font></td>
	</tr>
	<tr>
	  <td class="fieldname">Subject</td>
	  <td class="fieldarea"><input type="text" name="e1subject" class="text" size="60" value="<?= $e1subject ?>" /></td>
	</tr>
	<tr>
	  <td class="fieldname">Message</td>
	  <td class="fieldarea"><textarea name="e1template" class="textarea" cols="100" rows="11"><?= $e1template ?></textarea></td>
	</tr>
	<tr>
	  <td class="fieldname">Available Fields</td>
	  <td class="fieldarea"><font color="#666666"> &nbsp; {firstname} &nbsp; &nbsp; {lastname} &nbsp; &nbsp; {email} &nbsp; &nbsp; {password} &nbsp; &nbsp; {clientarealink}</font></td>
	</tr>
  </table>
  </fieldset>
  <img src="templates/<?= TEMPLATE ?>/images/spacer.gif" height="10" width="1" alt="" /><br />
  <div align="center">
	<input type="submit" value="Save Changes" class="button green" />
  </div>
</form>
<?php
include "./templates/" . TEMPLATE . "/footer.php";
