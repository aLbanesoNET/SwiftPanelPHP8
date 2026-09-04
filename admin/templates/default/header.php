<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php
$title = $title ?? '';
$page  = $page  ?? '';
$tab   = $tab   ?? '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= $title === "" ? SITENAME : $title . " - " . SITENAME ?></title>
<link href="templates/<?= TEMPLATE ?>/images/favicon.ico" rel="shortcut icon" />
<link href="templates/<?= TEMPLATE ?>/style.css" rel="stylesheet" type="text/css" />
<link href="templates/<?= TEMPLATE ?>/dropdown.css" rel="stylesheet" type="text/css" />
<?php if (isInternetExplorer()): ?>
<!--[if IE 7]>
<style type="text/css">
.dropdown ul li { margin-left: -16px; }
</style>
<![endif]-->
<?php endif; ?>
<script type="text/javascript" src="javascript/mootools.js"></script>
<script type="text/javascript" src="javascript/functions.js"></script>
<script type="text/javascript" src="javascript/dropdown.js"></script>
<script type="text/javascript">
	new Dropdown('menu1');new Dropdown('menu2');
</script>
</head>
<body>
<div id="topbg"></div>
<div id="nav"> <a href="index.php" title="Home" id="navhome"></a>
  <?php if ($page != "login"): ?>
  <div id="navleft">
	<ul id="menu1" class="dropdown">
	  <li class="clients"><a href="client.php"<?= $tab == "2" ? ' class="current"' : '' ?>>Clients</a></li>
	  <li class="servers"><a href="server.php"<?= $tab == "3" ? ' class="current"' : '' ?>>Servers</a></li>
	  <li class="boxes"><a href="box.php"<?= $tab == "4" ? ' class="current"' : '' ?>>Boxes</a></li>
	  <li class="utilities"><a href="#"<?= $tab == "5" ? ' class="current"' : '' ?>>Utilities</a>
		<ul>
		  <li><a href="utilitieslog.php">Activity Logs</a></li>
		  <li><a href="announce.php">Announcements</a></li>
		</ul>
	  </li>
	  <li class="configuration"><a href="#"<?= $tab == "6" ? ' class="current"' : '' ?>>Configuration</a>
		<ul>
		  <li><a href="configgeneral.php">General Settings</a></li>
		  <li><a href="configadmin.php">Administrators</a></li>
		  <li><a href="configgame.php">Manage Games</a></li>
		  <li><a href="configemail.php">Email Templates</a></li>
		  <li><a href="configcron.php">Cron Settings</a></li>
		</ul>
	  </li>
	</ul>
  </div>
  <div id="navright">
	<ul id="menu2" class="dropdown">
	  <li class="account"><a href="myaccount.php"<?= $tab == "7" ? ' class="current"' : '' ?>>My Account</a></li>
	  <li class="logout"><a href="process.php?task=logout" title="Clients">Logout</a></li>
	</ul>
  </div>
  <div id="navtime"><?= date("l | F j, Y | g:i A") ?></div>
  <?php endif; ?>
</div>
<div id="container">
  <div id="page">
	<div id="content">
	  <div id="title"> <h1><?= $title ?></h1>
		<?php if ($page == "index"): ?>
		<div id="titleright"><input type="button" onclick="window.location='clientadd.php'" class="button green" value="Add New Client" /> <input type="button" onclick="window.location='serveradd.php'" class="button green" value="Add New Server" /></div>
		<?php elseif ($page == "client"): ?>
		<div id="titleright"><input type="button" onclick="window.location='clientadd.php'" class="button green" value="Add New Client" /></div>
		<?php elseif ($page == "server"): ?>
		<div id="titleright"><input type="button" onclick="window.location='serveradd.php'" class="button green" value="Add New Server" /></div>
		<?php endif; ?>
	  </div>
