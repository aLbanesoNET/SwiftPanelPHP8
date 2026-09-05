<?php
$return = TRUE;
require "../configuration.php";
require "./include.php";
requireSameOrigin('index.php');
$task = sanitizeInput($_POST["task"] ?? "");
if(empty($task)) {
	$task = sanitizeInput($_GET["task"] ?? "");
}
switch ($task) {
	case "clientadd":
		$firstname = sanitizeInput($_POST["firstname"] ?? "");
		$firstname = ucfirst($firstname);
		$lastname = sanitizeInput($_POST["lastname"] ?? "");
		$lastname = ucfirst($lastname);
		$email = sanitizeInput($_POST["email"] ?? "");
		$email = strtolower($email);
		$password = sanitizeInput($_POST["password"] ?? "");
		$company = sanitizeInput($_POST["company"] ?? "");
		$address1 = sanitizeInput($_POST["address1"] ?? "");
		$address2 = sanitizeInput($_POST["address2"] ?? "");
		$city = sanitizeInput($_POST["city"] ?? "");
		$state = sanitizeInput($_POST["state"] ?? "");
		$postcode = sanitizeInput($_POST["postcode"] ?? "");
		$country = sanitizeInput($_POST["country"] ?? "");
		$phone = sanitizeInput($_POST["phone"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		$sendemail = sanitizeInput($_POST["sendemail"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["firstname"] = $firstname;
		$_SESSION["lastname"] = $lastname;
		$_SESSION["email"] = $email;
		$_SESSION["password"] = $password;
		$_SESSION["company"] = $company;
		$_SESSION["address1"] = $address1;
		$_SESSION["address2"] = $address2;
		$_SESSION["city"] = $city;
		$_SESSION["state"] = $state;
		$_SESSION["postcode"] = $postcode;
		$_SESSION["country"] = $country;
		$_SESSION["phone"] = $phone;
		$_SESSION["notes"] = $notes;
		$_SESSION["sendemail"] = $sendemail;
		$len = strlen($firstname);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>First Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($lastname);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Last Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($email);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Entered</b> ]</li>";
		} elseif($len <= "2") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Long Enough</b> ]</li>";
		}
		if(dbCount("SELECT * FROM `client` WHERE `email` = '" . $email . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "</li>Email [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($password);
		if("1" <= $len && $len <= "3") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["formerror"] = 1;
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: clientadd.php");
			exit;
		}
		unset($_SESSION["firstname"]);
		unset($_SESSION["lastname"]);
		unset($_SESSION["email"]);
		unset($_SESSION["password"]);
		unset($_SESSION["company"]);
		unset($_SESSION["address1"]);
		unset($_SESSION["address2"]);
		unset($_SESSION["city"]);
		unset($_SESSION["state"]);
		unset($_SESSION["postcode"]);
		unset($_SESSION["country"]);
		unset($_SESSION["phone"]);
		unset($_SESSION["notes"]);
		unset($_SESSION["sendemail"]);
		if(empty($password)) {
			$password = generateRandomString(7);
		}
		dbExec("INSERT INTO `client` SET `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "', `password` = '" . hashPassword($password) . "', `company` = '" . $company . "', `address1` = '" . $address1 . "', `address2` = '" . $address2 . "', `city` = '" . $city . "', `state` = '" . $state . "', `postcode` = '" . $postcode . "', `country` = '" . $country . "', `phone` = '" . $phone . "', `notes` = '" . $notes . "', `status` = 'Active', `lastip` = '~', `lasthost` = '~', `created` = NOW()");
		$clientid = dbInsertId();
		$message = "Client Added: <a href=\"clientsummary.php?id=" . $clientid . "\">" . $firstname . " " . $lastname . "</a>";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $clientid . "', `message` = '" . $message . "', `name` = '" . dbEscape($_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"]) . "', `ip` = '" . dbEscape($_SERVER["REMOTE_ADDR"] ?? "") . "'");
		if($sendemail == "on") {
			$rows = dbRow("SELECT * FROM `emailtemp` WHERE `emailtempid` = '1'");
			$systemurl = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'systemurl' LIMIT 1");
			$patterns[0] = "/{firstname}/";
			$patterns[1] = "/{lastname}/";
			$patterns[2] = "/{email}/";
			$patterns[3] = "/{password}/";
			$patterns[4] = "/{clientarealink}/";
			$replacements[0] = $firstname;
			$replacements[1] = $lastname;
			$replacements[2] = $email;
			$replacements[3] = $password;
			$replacements[4] = $systemurl["value"];
			if (is_file(__DIR__ . "/../includes/class.phpmailer.php")) {
				include_once __DIR__ . "/../includes/class.phpmailer.php";
							$mail = new PHPMailer();
							$mail->IsMail();
							$mail->AddAddress($email, $firstname . " " . $lastname);
							$mail->AddBCC($rows["bcc"]);
							$mail->From = $rows["email"];
							$mail->FromName = $rows["name"];
							$mail->Subject = $rows["subject"];
							$mail->Body = preg_replace($patterns, $replacements, $rows["template"]);
							$mail->Send();
			}
		}
		$_SESSION["msg1"] = "Client Added Successfully!";
		$_SESSION["msg2"] = "The new client account has been added and is ready for use.";
		header("Location: clientsummary.php?id=" . urlencode($clientid));
		exit;
	case "clientprofile":
		$clientid = sanitizeInput($_POST["clientid"] ?? "");
		$firstname = sanitizeInput($_POST["firstname"] ?? "");
		$firstname = ucfirst($firstname);
		$lastname = sanitizeInput($_POST["lastname"] ?? "");
		$lastname = ucfirst($lastname);
		$email = sanitizeInput($_POST["email"] ?? "");
		$email = strtolower($email);
		$password = sanitizeInput($_POST["password"] ?? "");
		$status = sanitizeInput($_POST["status"] ?? "");
		$company = sanitizeInput($_POST["company"] ?? "");
		$address1 = sanitizeInput($_POST["address1"] ?? "");
		$address2 = sanitizeInput($_POST["address2"] ?? "");
		$city = sanitizeInput($_POST["city"] ?? "");
		$state = sanitizeInput($_POST["state"] ?? "");
		$postcode = sanitizeInput($_POST["postcode"] ?? "");
		$country = sanitizeInput($_POST["country"] ?? "");
		$phone = sanitizeInput($_POST["phone"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		$sendemail = sanitizeInput($_POST["sendemail"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$_SESSION["firstname"] = $firstname;
		$_SESSION["lastname"] = $lastname;
		$_SESSION["email"] = $email;
		$_SESSION["password"] = $password;
		$_SESSION["status"] = $status;
		$_SESSION["company"] = $company;
		$_SESSION["address1"] = $address1;
		$_SESSION["address2"] = $address2;
		$_SESSION["city"] = $city;
		$_SESSION["state"] = $state;
		$_SESSION["postcode"] = $postcode;
		$_SESSION["country"] = $country;
		$_SESSION["phone"] = $phone;
		$_SESSION["notes"] = $notes;
		$_SESSION["sendemail"] = $sendemail;
		$len = strlen($firstname);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>First Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($lastname);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Last Name [ <b>Not Entered</b> ]</li>";
		}
		$len = strlen($email);
		if($len <= "0") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Entered</b> ]</li>";
		} elseif($len <= "2") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Long Enough</b> ]</li>";
		}
		if(dbCount("SELECT * FROM `client` WHERE `email` = '" . $email . "' && `clientid` != '" . $clientid . "'") != 0) {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Already Used</b> ]</li>";
		}
		$len = strlen($password);
		if("1" <= $len && $len <= "3") {
			$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
		}
		if(isset($_SESSION["msg2"])) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "<ul>" . $_SESSION["msg2"] . "</ul>";
			header("Location: clientprofile.php?id=" . urlencode($clientid));
			exit;
		}
		unset($_SESSION["firstname"]);
		unset($_SESSION["lastname"]);
		unset($_SESSION["email"]);
		unset($_SESSION["password"]);
		unset($_SESSION["status"]);
		unset($_SESSION["company"]);
		unset($_SESSION["address1"]);
		unset($_SESSION["address2"]);
		unset($_SESSION["city"]);
		unset($_SESSION["state"]);
		unset($_SESSION["postcode"]);
		unset($_SESSION["country"]);
		unset($_SESSION["phone"]);
		unset($_SESSION["notes"]);
		unset($_SESSION["sendemail"]);
		// Blank password + "resend email" -> generate a fresh one; blank on its own
		// leaves the existing password untouched.
		$setPassword = "";
		if($password !== "") {
			$setPassword = ", `password` = '" . hashPassword($password) . "'";
		} elseif($sendemail == "on") {
			$password = generateRandomString(7);
			$setPassword = ", `password` = '" . hashPassword($password) . "'";
		}
		dbExec("UPDATE `client` SET `firstname` = '" . $firstname . "', `lastname` = '" . $lastname . "', `email` = '" . $email . "'" . $setPassword . ", `company` = '" . $company . "', `address1` = '" . $address1 . "', `address2` = '" . $address2 . "', `city` = '" . $city . "', `state` = '" . $state . "', `postcode` = '" . $postcode . "', `country` = '" . $country . "', `phone` = '" . $phone . "', `notes` = '" . $notes . "', `status` = '" . $status . "' WHERE `clientid` = '" . $clientid . "'");
		$message = "Client Edited: <a href=\"clientsummary.php?id=" . $clientid . "\">" . $firstname . " " . $lastname . "</a> (Admin)";
		dbExec("INSERT INTO `log` SET `clientid` = '" . $clientid . "', `message` = '" . $message . "', `name` = '" . dbEscape($_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"]) . "', `ip` = '" . dbEscape($_SERVER["REMOTE_ADDR"] ?? "") . "'");
		if($sendemail == "on") {
			$rows = dbRow("SELECT * FROM `emailtemp` WHERE `emailtempid` = '1'");
			$systemurl = dbRow("SELECT `value` FROM `config` WHERE `setting` = 'systemurl' LIMIT 1");
			$patterns[0] = "/{firstname}/";
			$patterns[1] = "/{lastname}/";
			$patterns[2] = "/{email}/";
			$patterns[3] = "/{password}/";
			$patterns[4] = "/{clientarealink}/";
			$replacements[0] = $firstname;
			$replacements[1] = $lastname;
			$replacements[2] = $email;
			$replacements[3] = $password;
			$replacements[4] = $systemurl["value"];
			if (is_file(__DIR__ . "/../includes/class.phpmailer.php")) {
				include_once __DIR__ . "/../includes/class.phpmailer.php";
							$mail = new PHPMailer();
							$mail->IsMail();
							$mail->AddAddress($email, $firstname . " " . $lastname);
							$mail->AddBCC($rows["bcc"]);
							$mail->From = $rows["email"];
							$mail->FromName = $rows["name"];
							$mail->Subject = $rows["subject"];
							$mail->Body = preg_replace($patterns, $replacements, $rows["template"]);
							$mail->Send();
			}
		}
		$_SESSION["msg1"] = "Client Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the client have been saved.";
		header("Location: clientsummary.php?id=" . urlencode($clientid));
		exit;
	case "clientnotes":
		$clientid = sanitizeInput($_POST["clientid"] ?? "");
		$notes = sanitizeInput($_POST["notes"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		dbExec("UPDATE `client` SET `notes` = '" . $notes . "' WHERE `clientid` = '" . $clientid . "'");
		$_SESSION["msg1"] = "Admin Notes Updated Successfully!";
		$_SESSION["msg2"] = "Your changes to the admin notes have been saved.";
		header("Location: clientsummary.php?id=" . urlencode($clientid));
		exit;
	case "clientlogin":
		$clientid = sanitizeInput($_GET["id"] ?? "");
		$return = sanitizeInput($_GET["return"] ?? "");
		if (str_contains($return, '://') || str_starts_with($return, '//')) {
			$return = "";
		}
		$numrows = dbCount("SELECT `clientid` FROM `client` WHERE `clientid` = '" . $clientid . "' LIMIT 1");
		if($numrows == 1) {
			$rows = dbRow("SELECT `clientid`, `email`, `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $clientid . "' LIMIT 1");
			$_SESSION["clientid"] = $rows["clientid"];
			$_SESSION["clientemail"] = $rows["email"];
			$_SESSION["clientfirstname"] = $rows["firstname"];
			$_SESSION["clientlastname"] = $rows["lastname"];
			if(!empty($return)) {
				header("Location: ../" . $return);
				exit;
			}
			header("Location: ../index.php");
			exit;
		}
		header("Location: ../login.php");
		exit;
	case "clientdelete":
		$clientid = sanitizeInput($_GET["id"] ?? "");
		unset($_SESSION["msg1"]);
		unset($_SESSION["msg2"]);
		$result = dbQuery("SELECT * FROM `server` WHERE `clientid` = '" . $clientid . "'");
		if(dbNumRows($result) != 0) {
			$_SESSION["msg1"] = "Validation Error!";
			$_SESSION["msg2"] = "Servers must be deleted.";
			header("Location: clientserver.php?id=" . urlencode($clientid));
			exit;
		}
		dbFreeResult($result);
		$rows = dbRow("SELECT `firstname`, `lastname` FROM `client` WHERE `clientid` = '" . $clientid . "' LIMIT 1");
		dbExec("DELETE FROM `client` WHERE `clientid` = '" . $clientid . "' LIMIT 1");
		$message = "Client Deleted: " . dbEscape($rows["firstname"] . " " . $rows["lastname"]);
		dbExec("INSERT INTO `log` SET `clientid` = '" . $clientid . "', `message` = '" . $message . "', `name` = '" . dbEscape($_SESSION["adminfirstname"] . " " . $_SESSION["adminlastname"]) . "', `ip` = '" . dbEscape($_SERVER["REMOTE_ADDR"] ?? "") . "'");
		$_SESSION["msg1"] = "Client Deleted Successfully!";
		$_SESSION["msg2"] = "The selected client has been removed.";
		header("Location: client.php");
		exit;
	default:
		header("Location: index.php");
		exit;
}

?>