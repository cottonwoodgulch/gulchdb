<?php
	$cid = (isset ($_GET['cid']) ? $_GET['cid'] : NULL);
	$rec = (isset ($_GET['rec']) ? $_GET['rec'] : NULL);
	$exist = ($cid!= NULL);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Menu</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../stylesheet.css" rel="stylesheet" type="text/css">
<base target="contact_detail">
</head>

<body class="menu">
<?php
	if ($exist) { ?>
<table class="menu">
	<tr>
		<td>
			<a href="summary.php?cid=<?php echo $cid; ?>">Summary</a>
		</td>
		<td>
			<a href="addresses.php?cid=<?php echo $cid; ?>">Addresses</a>
		</td>
		<td>
			<a href="phones.php?cid=<?php echo $cid; ?>">Phones</a>
		</td>
		<td>
			<a href="emails.php?cid=<?php echo $cid; ?>">Emails</a>
		</td>
		<td>
			<a href="urls.php?cid=<?php echo $cid; ?>">URLs</a>
		</td>
		<td>
			<a href="relationships.php?cid=<?php echo $cid; ?>">Relationships</a>
		</td>
		<td>
			<a href="group_memberships.php?cid=<?php echo $cid; ?>">Groups</a>
		</td>
		<td>
			<a href="donations.php?cid=<?php echo $cid; ?>">Donations</a>
		</td>
		<td>
			<a href="payments.php?cid=<?php echo $cid; ?>">Payments</a>
		</td>
		<td>
			<a href="database_roles.php?cid=<?php echo $cid; ?>">Database Roles</a>
		</td>
		<td>
			<a href="notes.php?cid=<?php echo $cid; ?>">Notes</a>
		</td>
		<td>
			<a target="_top" href="../login.php">Log Out</a>
		</td>
		<td width="100%" align="right">Complete</td>
	</tr>
</table>
<?php } else { ?>
<table class="menu">
	<tr>
		<td>Contact details will be accessible when you complete the contact information below and click 'Add'.</td>
	</tr>
</table>
<?php } ?>
</body>
</html>