<?php

require_once('library.inc.php');

unset($_SESSION['user']);

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
		if (password_verify($_REQUEST['password'], $user['password'])) {
			$_SESSION['user'] = $user['contact_id'];
			$_SESSION['name'] = Name($user['contact_id'], '%F');
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
	</head>
	<body>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input name="redirect" type="hidden" value="<?php echo $redirect; ?>" />
			<input name="username" placeholder="mr_howie" type="text" />
			<input name="password" type="password" />
			<input type="submit" value="Log In" />
		</form>
	</body>
</html>