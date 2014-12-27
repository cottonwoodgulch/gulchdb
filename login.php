<?php

require_once('library.inc.php');

unset($_SESSION['user']);

// let's ignore redirect for now
unset($_REQUEST['redirect']);

if (isset($_REQUEST['redirect'])) {
	$redirect = $_REQUEST['redirect'];
} else {
	$redirect = 'index.php';
}

if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
	mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query = "SELECT * FROM contacts WHERE username = '" . strtolower($_REQUEST['username']) . "' LIMIT 1";
	$result = mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());
	if ($user = mysql_fetch_assoc($result)) {
		if ($phpass->CheckPassword($_REQUEST['password'], $user['password'])) {
			$_SESSION['user'] = $user['contact_id'];
		}
	}
}

if (isset($_SESSION['user'])) {
	header("Location: $redirect");
	exit;
}

?>
<html>
	<head>
		<title>Log In</title>
		<style>
			#login {
				width: 150px;
				display: block;
				margin-left: auto;
				margin-right: auto;
				position: relative;
				top: 50%;
				transform: translateY(-50%);
				padding: 20px;
				border: solid 1px #eeeeee;
				background: #eeeeff;
			}
			
			#login input {
				margin: 0.5em;
			}
			
			#login input[type="submit"] {
				right: 0;
			}
		</style>
		<script lang="javascript"><!--
			if (top.location != self.location) {
				top.location = self.location.href;
			}
		//--></script>
	</head>
	<body onload="document.getElementById('username').focus();">
		<div id="login">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input name="redirect" type="hidden" value="<?php echo $redirect; ?>" />
			<input id="username" name="username" placeholder="mr_howie" type="text" />
			<input name="password" placeholder="hEY_rU8E!!1!" type="password" />
			<input type="submit" value="Log In" />
		</form>
	</body>
	</div>
</html>