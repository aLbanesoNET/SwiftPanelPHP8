<?php
$return = true;

require __DIR__ . '/configuration.php';
require __DIR__ . '/include.php';

$task = sanitizeInput($_POST["task"] ?? $_GET["task"] ?? "");

if ($task !== "profile") {
	header("Location: index.php");
	exit;
}

$clientid  = $_SESSION["clientid"];
$firstname = ucfirst(sanitizeInput($_POST["firstname"]));
$lastname  = ucfirst(sanitizeInput($_POST["lastname"]));
$email	 = strtolower(sanitizeInput($_POST["email"]));
$password  = sanitizeInput($_POST["password"]);

unset($_SESSION["msg1"], $_SESSION["msg2"]);

$_SESSION["firstname"] = $firstname;
$_SESSION["lastname"]  = $lastname;
$_SESSION["email"]	 = $email;
$_SESSION["password"]  = $password;

if (strlen($firstname) === 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>First Name [ <b>Not Entered</b> ]</li>";
}

if (strlen($lastname) === 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Last Name [ <b>Not Entered</b> ]</li>";
}

if (strlen($email) === 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Entered</b> ]</li>";
} elseif (strlen($email) <= 2) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Not Long Enough</b> ]</li>";
}

if (dbCount("SELECT clientid FROM client WHERE email='$email' AND clientid!='$clientid'") > 0) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Email [ <b>Already Used</b> ]</li>";
}

if (strlen($password) > 0 && strlen($password) < 4) {
	$_SESSION["msg2"] = ($_SESSION["msg2"] ?? "") . "<li>Password [ <b>Not Long Enough</b> ]</li>";
}

if (!empty($_SESSION["msg2"])) {
	$_SESSION["msg1"] = "Validation Error!";
	header("Location: profile.php");
	exit;
}

unset(
	$_SESSION["firstname"],
	$_SESSION["lastname"],
	$_SESSION["email"],
	$_SESSION["password"]
);

if ($password === "") {
	// Leave the password untouched when the field is left blank.
	dbExec(
		"UPDATE client
		 SET firstname='$firstname',
			 lastname='$lastname',
			 email='$email'
		 WHERE clientid='$clientid'"
	);
} else {
	$hashed = hashPassword($password);
	dbExec(
		"UPDATE client
		 SET firstname='$firstname',
			 lastname='$lastname',
			 email='$email',
			 password='$hashed'
		 WHERE clientid='$clientid'"
	);
}

$message = "Client Edited: <a href=\"clientsummary.php?id=$clientid\">$firstname $lastname</a>";

dbExec(
	"INSERT INTO log 
	 SET clientid='$clientid',
		 message='$message',
		 name='{$_SESSION["clientfirstname"]} {$_SESSION["clientlastname"]}',
		 ip='{$_SERVER["REMOTE_ADDR"]}'"
);

$_SESSION["msg1"] = "Profile Updated Successfully!";
$_SESSION["msg2"] = "Your changes to your profile have been saved.";

header("Location: index.php");
exit;
