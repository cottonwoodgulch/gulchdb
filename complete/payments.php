<?php
	require_once('../library.inc.php');
	
	$rbac->enforce('view_payment', $_SESSION['user']);

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
	$query_tuitions = "SELECT * FROM tuitions AS t JOIN rosters AS r on t.roster_id = r.roster_id JOIN roster_memberships AS rm ON r.roster_id = rm.roster_id JOIN groups AS g ON r.group_id = g.group_id JOIN tuition_types AS tt ON tt.tuition_type_id = t.tuition_type_id WHERE rm.contact_id = '$cid' ORDER BY r.year DESC, g.short_name ASC";
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
						   WHERE p.contact_id = $cid
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
		<title>Payments</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../stylesheet.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<script src="../library.inc.js"></script>
<?php
	if ((($totalRows_payments > 0) && (! $exist)) ||
		(($totalRows_payments > 1) && ($exist)))
	{
?>
		<table width="100%">
			<tr class="horizontal">
				<th>date</th>
				<th>amount</th>
				<th>type</th>
				<th>roster</th>
				<td><form name="payments-all" method="post" action="payments.php?cid=<?php echo $cid; ?>"><input type="submit" name="button" value="All"></form></td>
			</tr>
<?php
		do {
			if ($current_tuition != $row_payments['tuition_id'])
			{
				if ($current_tuition)
				{
					echo '<tr class="' . ($current_balance ? ($current_balance > 0 ? ' balance_due' : 'overpayment') : 'paid_in_full') . '"><td></td>';
					echo '<td>$' . number_format ($current_balance, 2) . '</td>';
					echo '<td>' . ($current_balance ? ($current_balance > 0 ? ' Balance Due' : 'Overpayment') : 'Paid in Full') . '</td>';
					echo '<td>' . $current_group . '</td>';
					echo '</tr>';
				}
				$current_tuition = $row_payments['tuition_id'];
				$current_balance = $row_payments['tuition']; 
				$current_group = $row_payments['year'] . ' '. $row_payments['group'];
			}
		
			$current_balance -= $row_payments['amount'];

			if ($row_payments['payment_id'] != $pid)
			{
?>
			<tr<?php Stripe($rc); ?>>
				<form name="payments" action="payments.php?cid=<?php echo $cid; ?>&pid=<?php echo $row_payments['payment_id']; ?>&rec=view" method="post">
					<td><?php echo $row_payments['date']; ?></td>
					<td><?php echo '$' . number_format ($row_payments['amount'], 2); ?></td>
					<td><?php echo $row_payments['payment_type']; ?></td>
					<td><?php echo $row_payments['year'] . ' ' . $row_payments['group']; ?></td>
					<td><input type="submit" name="button" value="Edit"></td>
				</form>
			</tr>
<?php
			}
		} while ($row_payments = mysql_fetch_assoc($payments));

		echo '<tr class="' . ($current_balance ? ($current_balance > 0 ? ' balance_due' : 'overpayment') : 'paid_in_full') . '"><td></td>';
		echo '<td>$' . number_format ($current_balance, 2) . '</td>';
		echo '<td>' . ($current_balance ? ($current_balance > 0 ? ' Balance Due' : 'Overpayment') : 'Paid in Full') . '</td>';
		echo '<td>' . $current_group . '</td>';
		echo '</tr>';
?>
		</table>
<?php
	}
?>
<form name="payment-update" method="post" action="payments.php?cid=<?php echo $cid . ($pid ? "&pid=$pid" : ''); ?>&rec=&rec=<?php echo ($exist ? 'update' : 'insert'); ?>">
	<input type="hidden" name="contact_id" value="<?php echo ($exist ? $row_payment['contact_id'] : $cid); ?>">
	<table>
<?php if ($exist) { ?>
		<tr>
			<th>payment id</th>
			<td><input type="hidden" name="payment_id" value="<?php echo $row_payment['payment_id']; ?>"><?php echo $row_payment['payment_id']; ?></td>
		</tr>
<?php } ?>
		<tr>
			<th>date</th>
			<td><input type="text" name="date" value="<?php if ($exist) echo $row_payment['date']; ?>"></td>
		</tr>
		<tr>
			<th>amount</th>
			<td><input type="text" name="amount" value="<?php if ($exist) echo number_format ($row_payment['amount'], 2, '.', '') ; ?>"></td>
		</tr>
		<tr>
    		<th>payment type</th>
    		<td><select name="payment_type_id">
<?php do { ?>
				<option value="<?php echo $row_payment_types['payment_type_id']?>"<?php if($exist) { if (!(strcmp($row_payment_types['payment_type_id'], $row_payment['payment_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_payment_types['payment_type']; ?></optio
n>
<?php } while ($row_payment_types = mysql_fetch_assoc($payment_types));
$rows = mysql_num_rows($payment_types);
if($rows > 0)
{
	mysql_data_seek($payment_types, 0);
	$row_payment_types = mysql_fetch_assoc($payment_types);
} ?>
			</select></td>
		</tr>
		<tr>
    		<th>tuition</th>
    		<td><select name="tuition_id">
<?php do { ?>
				<option value="<?php echo $row_tuitions['tuition_id']?>"<?php if($exist) { if (!(strcmp($row_tuitions['tuition_id'], $row_payment['tuition_id']))) {echo "SELECTED";}} ?>><?php echo $row_tuitions['year'] . ' ' . $row_tuitions['short_name'] . ' (' . $row_tuitions['tuition_type'] . ', $' . number_format($row_tuitions['amount'], 2) . ')'; ?></option>

<?php } while ($row_tuitions = mysql_fetch_assoc($tuitions));
$rows = mysql_num_rows($tuitions);
if($rows > 0)
{
	mysql_data_seek($tuitions, 0);
	$row_tuitions = mysql_fetch_assoc($tuitions);
} ?>
			</select></td>
		</tr>
		<tr>
			<th>check number</th>
			<td><input type="text" name="check_number" value="<?php if ($exist) echo $row_payment['check_number']; ?>"></td>
		</tr>
<?php if ($exist) { ?>
		<tr>
			<th>modified</th>
			<td><?php echo $row_payment['modified']; ?></td>
		</tr>
<?php } ?>
	</table>
	<table><tr><td>
<input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="payments.php?cid=<?php echo $cid; ?>&pid=<?php echo $pid; ?>&rec=delete" method="post" name="payment-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } else { ?>
<!-- <td><form action="associate.php?cid=<?php echo $cid; ?>" method="post" name="donation-associate">
<input type="hidden" name="val" value="d">
<input type="submit" name="button" value="Attach someone's else's donation">
</form></td> -->
<?php } ?>
<td><form name="revert" method="post" action="payments.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
<td><form name="statement" method="post" action="payments-statement.php?cid=<?php echo $cid; ?>" target="statement-window">
<input type="submit" name="button" value="Statement"></form></td>
</tr>
</table>
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
