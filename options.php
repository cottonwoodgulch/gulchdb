<?php require_once('library.inc.php'); ?>
<html>
<head>
	<link rel="stylesheet" href="stylesheet.css">
	<base target="_top">
</head>

<body>
<script src="library.inc.js"></script>
<table class="menu">
	<tr>
		<td class="menu_tab"><a href="complete/">Complete</a></td>
	</tr>
	<tr>
		<td class="menu_tab"><a href="groups/" target="content">Groups</a> (<a href="logs.php" target="content">Logs</a>)</td>
	</tr>
	<!-- disabled until implementation is clear -- 2010-10-25 SDB
	<tr>
		<td class="menu_tab"><a href="http://www.cottonwoodgulch.org/rosters" target="_top">Online Rosters</a></td>
	</tr>
	-->
	<?php if ($rbac->Users->hasRole('Administrator', $_SESSION['user'])): ?>
	<tr>
		<td class="menu_tab"><a href="http://trek.sqldb.swcp.com/">PhpMyAdmin</a></td>
	</tr>
	<?php
		endif;
		if ($rbac->check('modify_permission', $_SESSION['user'])):
	?>
	<tr>
		<td class="menu_tab"><a href="reset_rbac.php">Reset RBAC</a></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td class="menu_tab"><a href="home.php" target="content">Home</a></td>
	</tr>
	<tr>
		<td class="menu_tab"><a href="login.php" target="_top">Log out <?php echo Name($_SESSION['user'], '%F'); ?></a></td>
	</tr>
</table>
</body>