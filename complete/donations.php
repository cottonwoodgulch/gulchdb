<?php
	require_once('../library.inc.php');
	
	$rbac->enforce('view_donation', $_SESSION['user']);

	$exist = RecordUpdate ('donations.php', 'donations', 'donation_id', 'did', 'donation_associations');

	$cid = (isset ($_GET['cid']) ?
		$_GET['cid'] :
		exit ('<strong>Unspecified Contact:</strong> A contact must be specified to load this form.'));
	$did = (isset ($_GET['did']) ?
		$_GET['did'] :
		NULL);

	$exist = ($did != NULL);

	if ($exist)
	{
		mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
		$query_donation = "SELECT * FROM donations WHERE donation_id = $did LIMIT 1";
		$donation = mysql_query ($query_donation, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_donation = mysql_fetch_assoc ($donation);
	}

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contacts = "SELECT * FROM donation_associations AS assoc JOIN contacts ON assoc.contact_id = contacts.contact_id WHERE assoc.donation_id = $did ORDER BY primary_name ASC";
	$contacts = mysql_query($query_contacts, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_contacts = mysql_fetch_assoc($contacts);
	$totalRows_contacts = mysql_num_rows($contacts);
}
else
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contacts = "SELECT * FROM contacts WHERE contact_id =  $cid LIMIT 1";
	$contacts = mysql_query($query_contacts, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_contacts = mysql_fetch_assoc($contacts);
	$totalRows_contacts = mysql_num_rows($contacts);
}

	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_donations = "SELECT * FROM donation_associations AS assoc JOIN donations AS d ON assoc.donation_id = d.donation_id WHERE assoc.contact_id = $cid ORDER BY date DESC";
	$donations = mysql_query($query_donations, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_donations = mysql_fetch_assoc($donations);
	$totalRows_donations = mysql_num_rows($donations);

	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_funds = 'SELECT * FROM funds ORDER BY fund ASC';
	$funds = mysql_query($query_funds, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_funds = mysql_fetch_assoc($funds);
	$totalRows_funds = mysql_num_rows($funds);

	$rc = 0; // row counter for stripe
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Donations</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../stylesheet.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<script src="../library.inc.js"></script>
<?php
	if ((($totalRows_donations > 0) && (! $exist)) ||
		(($totalRows_donations > 1) && ($exist)))
	{
?>
		<table width="100%">
			<tr class="horizontal">
				<th>date</th>
				<th>amount</th>
				<th>fund</th>
				<th>purpose</th>
				<th>shared</th>
				<td><form name="donations-all" method="post" action="donations.php?cid=<?php echo $cid; ?>"><input type="submit" name="button" value="All"></form></td>
			</tr>
<?php
		do {
			if ($row_donations['donation_id'] != $did)
			{
?>
			<tr<?php Stripe($rc); ?>>
				<form name="donations" action="donations.php?cid=<?php echo $cid; ?>&did=<?php echo $row_donations['donation_id']; ?>&rec=view" method="post">
					<td><?php echo $row_donations['date']; ?></td>
					<td><?php
						if ($row_donations['amount'] > 0)
							echo '$' . number_format ($row_donations['amount'], 2);
						$paren = ($row_donations['share_count'] > 0);
						if ($paren)
						{
							echo ' (' . $row_donations['share_count'] . ' shares';
							//if (strlen($row_donations['share_company'] > 0))
								echo '  of ' . $row_donations['share_company'];
							if ($row_donations['share_value'] > 0)
								echo ' at $' . number_format ($row_donations['share_value'], 2);
							echo ')';
						} ?></td>
					<td><?php
						mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
						$query_fund = 'SELECT * FROM funds WHERE fund_id = ' . $row_donations['fund_id'];
						$fund = mysql_query ($query_fund, $GLOBALS['db']['link']) or exit (mysql_error());
						$row_fund = mysql_fetch_assoc ($fund);
						echo $row_fund['fund'];
						mysql_free_result ($fund);
					?></td>
					<td><?php echo $row_donations['purpose']; ?></td>
					<td><?php
						$query_shared = 'SELECT * FROM donation_associations AS d JOIN contacts AS c ON d.contact_id = c.contact_id LEFT JOIN titles AS t ON t.title_id = c.title_id LEFT JOIN degrees AS g ON g.degree_id = c.degree_id WHERE d.donation_id = ' . $row_donations['donation_id'] . " AND d.contact_id <> $cid ORDER BY c.primary_name ASC, c.first_name ASC";
						$shared = mysql_query ($query_shared, $GLOBALS['db']['link']) or exit (mysql_error());
						if (mysql_num_rows($shared) > 0)
						{
							while ($other_donor = mysql_fetch_assoc ($shared))
							{
								echo '<a href="contacts.php?cid=' . $other_donor['contact_id'] . '&detail=donations.php" target="content">' . Name ($other_donor['contact_id'], '%L, %n', $other_donor) . '</a> ';
							}
						}
						else echo 'n/a';
						mysql_free_result ($shared);
					?></td>
					<td><input type="submit" name="button" value="Edit"></td>
				</form>
			</tr>
<?php
			}
		} while ($row_donations = mysql_fetch_assoc($donations));
?>
		</table>
<?php
	}
?>
<form name="donation-update" method="post" action="donations.php?cid=<?php echo $cid . ($did ? "&did=$did" : ''); ?>&rec=&rec=<?php echo ($exist ? 'update' : 'insert'); ?>">
	<input type="hidden" name="donor_id" value="<?php echo ($exist ? $row_donation['contact_id'] : $cid); ?>">
	<table>
<?php if ($exist) { ?>
		<tr>
			<th>donation id</th>
			<td><input type="hidden" name="donation_id" value="<?php echo $row_donation['donation_id']; ?>"><?php echo $row_donation['donation_id']; ?></td>
		</tr>
<?php } ?>
  <tr>
    <th scope="row">donor</th>
    <td><?php if ($exist && $totalRows_contacts > 1) { ?><select name="donor_id">
<?php do { ?>
      <option value="<?php echo $row_contacts['contact_id']?>"<?php if (!(strcmp($row_contacts['contact_id'], $row_donation['donor_id']))) {echo "SELECTED";} ?>><?php echo $row_contacts['primary_name'] . ', ' . $row_contacts['first_name']?></option>
<?php } while ($row_contacts = mysql_fetch_assoc($contacts));
$rows = mysql_num_rows($contacts);
if($rows > 0)
{

	mysql_data_seek($contacts, 0);
	$row_contacts = mysql_fetch_assoc($contacts);
} ?>
    </select>
<?php }
else
{
	echo $row_contacts['primary_name'] . ', ' . $row_contacts['first_name']; ?>
	<input type="hidden" name="donor_id" value="<?php if ($exist) echo  $row_donor['donor_id'] ;
else echo $cid; ?>"> <?php } ?>
	</td>
  </tr>
		<tr>
			<th>date</th>
			<td><input type="text" name="date" value="<?php if ($exist) echo $row_donation['date']; ?>"></td>
		</tr>
		<tr>
			<th>amount</th>
			<td><input type="text" name="amount" value="<?php if ($exist) echo number_format ($row_donation['amount'], 2, '.', ''); ?>"></td>
		</tr>
		<tr>
    		<th>fund</th>
    		<td><select name="fund_id">
<?php do { ?>
				<option value="<?php echo $row_funds['fund_id']?>"<?php if($exist) { if (!(strcmp($row_funds['fund_id'], $row_donation['fund_id']))) {echo "SELECTED";}} ?>><?php echo $row_funds['fund']?></option>
<?php } while ($row_funds = mysql_fetch_assoc($funds));
$rows = mysql_num_rows($funds);
if($rows > 0)
{
	mysql_data_seek($funds, 0);
	$row_funds = mysql_fetch_assoc($funds);
} ?>
			</select></td>
		</tr>
		<tr>
			<th>purpose</th>
			<td><input type="text" name="purpose" value="<?php if ($exist) echo $row_donation['purpose']; ?>"></td>
		</tr>
		<tr>
			<th>anonymous</th>
			<td><input type="checkbox" name="anonymous" value="1"<?php if ($exist && $row_donation['anonymous']) echo ' checked'; ?>></td>
		</tr>
		<tr>
			<th>check number</th>
			<td><input type="text" name="check_number" value="<?php if ($exist) echo $row_donation['check_number']; ?>"></td>
		</tr>
		<tr>
			<th>share count</th>
			<td><input type="text" name="share_count" value="<?php if ($exist) echo $row_donation['share_count']; ?>"></td>
		</tr>
		<tr>
			<th>share value</th>
			<td><input type="text" name="share_value" value="<?php if ($exist) echo number_format ($row_donation['share_value'], 2, '.', ''); ?>"></td>
		</tr>
		<tr>
			<th>share company</th>
			<td><input type-"text" name="share_company" value="<?php if ($exist) echo $row_donation['share_company']; ?>"></td>
		</tr>
<?php if ($exist) { ?>
		<tr>
			<th>modified</th>
			<td><?php echo $row_donation['modified']; ?></td>
		</tr>
<?php } ?>
	</table>
	<table><tr><td>
<input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="donations.php?cid=<?php echo $cid; ?>&did=<?php echo $did; ?>&rec=delete" method="post" name="donation-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } else { ?>
<td><form action="associate.php?cid=<?php echo $cid; ?>" method="post" name="donation-associate">
<input type="hidden" name="val" value="d">
<input type="submit" name="button" value="Attach someone's else's donation">
</form></td>
<?php } ?>
<td><form name="revert" method="post" action="donations.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
	</body>
</html>
<?php
	mysql_free_result($donations);
	mysql_free_result($funds);
	if ($exist) mysql_free_result ($donation);
?>