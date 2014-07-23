<?php require_once('../library.inc.php'); ?>
<?php
	$cid = (isset ($_GET['cid']) ? $_GET['cid'] : NULL);
	$rec = (isset ($_GET['rec']) ? $_GET['rec'] : NULL);
	$exist = ($cid != NULL);
	$detail = (isset ($_GET['detail']) ? $_GET['detail'] : '../empty.html');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../stylesheet.css">
</head>
	<frameset rows="36px,*" border="0">
		<frame name="header" src="header.php?<?php echo ($exist ? "cid=$cid" : "rec=$rec"); ?>">
		<frameset cols="40%,*" border="0">
			<frame name="contact" src="contact.php?<?php echo ($exist ? "cid=$cid" : "rec=$rec"); ?>">
			<frame name="contact_detail" src="<?php echo ($exist ? "$detail?cid=$cid" : '../empty.html'); ?>">
		</frameset>
</frameset>
</html>
<?php
mysql_free_result($contact);
?>