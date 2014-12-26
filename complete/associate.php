<?php

	require_once('../library.inc.php');

	if (!$rbac->check('edit_contact_information', $_SESSION['user']) && !$rbac->check('edit_financial_information', $_SESSION['user'])) {
		$rbac->enforce('edit_contact_information', $_SESSION['user']);
	}

	$rec = array ('a' => array ('page' => 'addresses.php',
								'title' => 'Address',
								'table' => 'addresses',
								'identifier' => 'address_id',
								'id' => 'aid',
								'assoc_table' => 'address_associations',
								'type_table' => 'address_types',
								'type_identifier' => 'address_type_id',
								'function' => 'DisplayAddress'),
				  'd' => array ('page' => 'donations.php',
				  				'title' => 'Donation',
				  				'table' => 'donations',
				  				'identifier' => 'donation_id',
				  				'id' => 'did',
				  				'assoc_table' => 'donation_associations',
				  				'type_table' => 'funds',
				  				'type_identifier' => 'fund_id',
								'function' => 'DisplayDonation'),
				  'e' => array ('page' => 'emails.php',
				  				'title' => 'Email Address',
				  				'table' => 'emails',
				  				'identifier' => 'email_id',
				  				'id' => 'eid',
				  				'assoc_table' => 'email_associations',
				  				'type_table' => 'email_types',
				  				'type_identifier' => 'email_type_id',
								'function' => 'DisplayEmail'),
				  'p' => array ('page' => 'phones.php',
				  				'title' => 'Phone Number',
				  				'table' => 'phones',
				  				'identifier' => 'phone_id',
				  				'id' => 'pid',
				  				'assoc_table' => 'phone_associations',
				  				'type_table' => 'phone_types',
				  				'type_identifier' => 'phone_type_id',
								'function' => 'DisplayPhone'),
				  'u' => array ('page' => 'urls.php',
				  				'title' => 'URL',
				  				'table' => 'urls',
				  				'identifier' => 'url_id',
				  				'id' => 'uid',
				  				'assoc_table' => 'url_associations',
				  				'type_table' => 'url_types',
				  				'type_identifier' => 'url_type_id',
								'function' => 'DisplayURL'));

	$cid = (isset ($_GET['cid']) ?
		$_GET['cid'] :
		exit ('<strong>Unknown contact:</strong> a contact must be specified to load this page.'));
	$val = (isset ($_POST['val']) ?
		$_POST['val'] :
		exit ('<strong>Unknown value:</strong> a value must be specified to load this page.'));


	$assoc_id = (isset ($_GET[$rec[$val]['id']]) ?
		$_GET[$rec[$val]['id']] :
		NULL);

	if (! $assoc_id)
	{
		$pattern = (isset ($_POST['pattern']) ? $_POST['pattern'] : NULL);
		$exist = (strlen($pattern) > 0);

		if ($exist)
		{
			$contacts = NameSearch ($pattern);
			$row_contacts = mysql_fetch_assoc($contacts);
			$totalRows_contacts = mysql_num_rows($contacts);
		}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Associate an Existing <?php echo $rec[$val]['title']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" href="../stylesheet.css">
	</head>

	<body>
		<script src="../library.inc.js"></script>
		<form name="find_contact" action="associate.php?cid=<?php echo $cid; ?>" method="post">
		<input type="hidden" name="val" value="<?php echo $val; ?>">
			<table>
				<tr>
					<th>find by name</th>
				</tr>
				<tr>
					<td>
						<input type="text" name="pattern" value="<?php if ($exist) echo $pattern; ?>">
						<input type="submit" name="button" value="Find">
					</td>
				</tr>
			</table>
		</form>
<?php
		if ($exist)
		{
			echo "<ol>\n";
			do {
				$query_assoc = 'SELECT * FROM `' . $rec[$val]['assoc_table'] . '` AS assoc JOIN `' . $rec[$val]['table'] . '` AS t ON `assoc`.`' . $rec[$val]['identifier'] . '` = t.`' . $rec[$val]['identifier'] . '` JOIN `' . $rec[$val]['type_table'] . '` AS types ON t.`' . $rec[$val]['type_identifier'] . '` = types.`' . $rec[$val]['type_identifier'] . '` WHERE assoc.contact_id = ' . $row_contacts['contact_id'] . ' ORDER BY types.rank ASC';
				$assoc = mysql_query ($query_assoc, $GLOBALS['db']['link']) or exit (mysql_error());

				if (mysql_num_rows ($assoc) > 0)
				{
					echo '<li><dl><dt>' . str_replace (" \"\"", "", Name ($row_contacts['contact_id'], '%F %M "%N" %L %D')) . '</dt>';

					echo '<dd><table>';
					while ($row_assoc = mysql_fetch_assoc ($assoc))
					{
						echo '<form name="associate" method="post" action="associate.php?cid=' . $cid . '&' . $rec[$val]['id'] . '=' . $row_assoc[$rec[$val]['identifier']] . '">';
						echo '<tr>';
						echo "<th>" . $row_assoc[substr ($rec[$val]['type_table'], 0, strlen ($rec[$val]['type_table']) - 1)] . "</th>";
						echo '<td>';
						$rec[$val]['function'] ($row_assoc);
						echo '</td>';
						echo '<td>';
						echo '<input type="hidden" name="val" value="' . $val . '">';
						echo '<input type="submit" name="button" value="Use this ' . $rec[$val]['title'] . '"></form>';
						echo '</dd>';
						echo '</tr>';
					}
					mysql_free_result ($assoc);
					echo '</table></dd>';
					echo '</dl>';
				}
			} while ($row_contacts = mysql_fetch_assoc ($contacts));
			echo '</ol>';
		}
?>
	</body>
</html>
<?php
		if ($exist) mysql_free_result($contacts);
	}
	else
	{
		mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
		$query_assoc = 'INSERT INTO `' . $rec[$val]['assoc_table'] . '` (`contact_id`,`' . $rec[$val]['identifier'] . "`) VALUES ('$cid','$assoc_id')";
		mysql_query ($query_assoc, $GLOBALS['db']['link']) or exit (mysql_error());

		header ('Location: ' . $rec[$val]['page'] . "?cid=$cid&" . $rec[$val]['id'] . "=$assoc_id");
	}
?>