<?php
	require_once('../library.inc.php');

	$exist = RecordUpdate ('payments.php', 'payments', 'payment_id', 'pid');

	$cid = (isset ($_GET['cid']) ?
		$_GET['cid'] :
		exit ('<strong>Unspecified Contact:</strong> A contact must be specified to load this form.'));
	$pid = (isset ($_GET['pid']) ?
		$_GET['pid'] :
		NULL);

	$exist = ($pid != NULL);

	if ($exist)
	{
		mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
		$query_payment = "SELECT * FROM payments WHERE payment_id = $pid LIMIT 1";
		$payment = mysql_query ($query_payment, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_payment = mysql_fetch_assoc ($payment);
	}

	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_tuitions = "SELECT * FROM tuitions AS t JOIN rosters AS r on t.roster_id = r.roster_id JOIN roster_memberships AS rm ON r.roster_id = rm.roster_id JOIN groups AS g ON r.group_id = g.group_id WHERE rm.contact_id = $cid";
	$tuitions = mysql_query ($query_tuitions, $GLOBALS['db']['link']);
	$row_tuitions = mysql_fetch_assoc ($tuitions);
	$totalRows_tuitions = mysql_num_rows ($tuitions);

	if ($totalRows_tuitions > 0)
	{
		mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
		$query_payments = "SELECT p.*, t.amount AS tuition, r.*, g.*, pt.*
						   FROM payments AS p JOIN tuitions AS t ON p.tuition_id = t.tuition_id
						   JOIN rosters AS r ON t.roster_id = r.roster_id
						   JOIN groups AS g ON g.group_id = r.group_id
						   JOIN payment_types AS pt ON p.payment_type_id = pt.payment_type_id
						   WHERE p.contact_id = $cid AND r.year = '2005'
						   ORDER BY p.tuition_id DESC, date DESC";
		$payments = mysql_query($query_payments, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_payments = mysql_fetch_assoc($payments);
		$totalRows_payments = mysql_num_rows($payments);

		mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
		$query_payment_types = 'SELECT * FROM payment_types ORDER BY payment_type ASC';
		$payment_types = mysql_query ($query_payment_types, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_payment_types = mysql_fetch_assoc ($payment_types);
		$totalRows_payment_types = mysql_num_rows ($payment_types);

		$rc = 0; // row counter for stripe

		$current_tuition = NULL;
		$current_balance = 0;
		$current_group = NULL;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Payments and Credits for <?php echo Name ($cid, "%L, %F %M"); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../output.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<table>
			<tr valign="middle">
				<td><img src="../graphics/logo.gif" height="44" width="44" border="0"></td>
				<td><h1>Payment Statement</h1>
					<p><?php
						$today = getdate();
						echo $today['month'] . ' ' . $today['mday'] . ', ' . $today['year'];
					?></p></td>
			</tr>
		</table>
		<script src="../library.inc.js"></script>
		<p>Our records show that the following payments and credits have been applied towards the tuition of this trekker:</p>
<?php
	if ((($totalRows_payments > 0) && (! $exist)) ||
		(($totalRows_payments > 1) && ($exist)))
	{
?>
		<table>
			<tr class="horizontal">
				<th>Date</th>
				<th>Amount</th>
				<th>Type of payment</th>
				<th>Group</th>
			</tr>
<?php
		do {
			if ($current_tuition != $row_payments['tuition_id'])
			{
				if ($current_tuition)
				{
					echo '<tr><td></td>';
					echo '<td class="' . ($current_balance ? ($current_balance > 0 ? ' balance_due' : 'overpayment') : 'paid_in_full') . '">$' . number_format ($current_balance, 2) . '</td>';
					echo '<td>' . ($current_balance ? ($current_balance > 0 ? ' Balance Due' : 'Overpayment') : 'Paid in Full') . '</td>';
					echo '<td>' . $current_group . '</td>';
					echo '</tr>';
				}
				$current_tuition = $row_payments['tuition_id'];
				$current_balance = $row_payments['tuition'];
				$current_group = $row_payments['year'] . ' '. $row_payments['short_name'];
			}

			$current_balance -= $row_payments['amount'];

			if ($row_payments['payment_id'] != $pid)
			{
?>
			<tr<?php Stripe($rc); ?>>
					<td><?php echo $row_payments['date']; ?></td>
					<td><?php echo '$' . number_format ($row_payments['amount'], 2); ?></td>
					<td><?php echo $row_payments['payment_type']; ?></td>
					<td><?php echo $row_payments['year'] . ' ' . $row_payments['short_name']; ?></td>
			</tr>
<?php
			}
		} while ($row_payments = mysql_fetch_assoc($payments));

		echo '<tr><td></td>';
		echo '<td class="' . ($current_balance ? ($current_balance > 0 ? ' balance_due' : 'overpayment') : 'paid_in_full') . '">$' . number_format ($current_balance,
2) . '</td>';
		echo '<td>' . ($current_balance ? ($current_balance > 0 ? ' Balance Due (May 15)' : 'Overpayment (will be applied to spending money unless otherwise
instructed)') : 'Paid in Full') . '</td>';
		echo '<td>' . $current_group . '</td>';
		echo '</tr>';
?>
		</table>
<?php
	}
?>
	</body>
</html>
<?php
		mysql_free_result($payments);
		mysql_free_result($payment_types);
	}
	else
	{
?>
<html>
	<head>
		<link rel="stylesheet" href="../stylesheet.css">
		<title>Payments</title>
	</head>

	<body>
		<script src="../library.inc.js"></script>
		<p>There are no tuitions requiring payment associated with this contact.</p>
	</body>
</html>
<?php
	}
	mysql_free_result($tuitions);
	if ($exist) mysql_free_result ($payment);
?>
