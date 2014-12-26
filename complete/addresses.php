<?php
	
require_once('../library.inc.php');

if (!$rbac->check('view_address', $_SESSION['user']) && !$rbac->check('view_contact_information', $_SESSION['user'])) {
	$rbac->enforce('view_address', $_SESSION['user']);
}
	
?>
<?php

$exist = true;
if ($rbac->check('edit_address', $_SESSION['user'])) {
	$exist = RecordUpdate ('addresses.php', 'addresses', 'address_id', 'aid', 'address_associations');
}

$cid = (isset($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));
$aid = (isset($_GET['aid']) ? $_GET['aid'] : NULL);

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_addresses = "SELECT * FROM address_associations as assoc JOIN addresses AS addr ON assoc.address_id = addr.address_id JOIN address_types AS types ON addr.address_type_id = types.address_type_id WHERE assoc.contact_id = $cid ORDER BY rank ASC";
$addresses = mysql_query($query_addresses, $GLOBALS['db']['link']) or exit (mysql_error());
$row_addresses = mysql_fetch_assoc($addresses);
$totalRows_addresses = mysql_num_rows($addresses);

if (!$aid) $aid = $row_addresses['address_id'];

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_address = "SELECT * FROM addresses WHERE address_id = $aid LIMIT 1";
	$address = mysql_query($query_address, $GLOBALS['db']['link']) or ($exist = false);
	if ($exist)
	{
		$row_address = mysql_fetch_assoc($address);
		$totalRows_address = mysql_num_rows($address);
	}
}

if ($exist)
{
	$query_address_type = "SELECT * FROM address_types WHERE address_type_id = " . $row_address["address_type_id"];
	$address_type = mysql_query ($query_address_type, $GLOBALS["db"]["link"]) or ($exist = false);
	if ($exist)
	{
		$row_address_type = mysql_fetch_assoc ($address_type);
		$totalRows_address_type = mysql_num_rows($address_type);
	}
}

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contacts = "SELECT * FROM address_associations AS assoc JOIN contacts ON assoc.contact_id = contacts.contact_id WHERE assoc.address_id = $aid ORDER BY primary_name ASC";
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
$query_address_types = "SELECT * FROM address_types ORDER BY rank ASC";
$address_types = mysql_query($query_address_types, $GLOBALS['db']['link']) or exit (mysql_error());
$row_address_types = mysql_fetch_assoc($address_types);
$totalRows_address_types = mysql_num_rows($address_types);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Addresses</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../stylesheet.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script src="../library.inc.js"></script>
<table class="menu">
  <tr>
<?php do { ?>
    <td><a href="addresses.php?cid=<?php echo $cid; ?>&aid=<?php echo $row_addresses['address_id']; ?>&rec=view" title="<?php echo $row_addresses['street_address_1']; ?>"><?php echo $row_addresses["address_type"]; ?></a><!--&nbsp;<a href="letter.php?cid=<?php echo $cid; ?>&aid=<?php echo $row_addresses['address_id']; ?>"><img src="../graphics/letter.gif" alt="Write"></a>--></td>
<?php } while ($row_addresses = mysql_fetch_assoc($addresses)); ?>
<?php if ($rbac->check('edit_contact', $_SESSION['user'])): ?>
	<td><a href="addresses.php?cid=<?php echo $cid; ?>&rec=create">New Address</a></td>
<?php endif; ?>
  </tr>
</table>

<form action="addresses.php?cid=<?php echo $cid; ?><?php if ($exist) echo '&aid=' . $aid; ?>&rec=<?php echo ($exist ? 'update' : 'insert'); ?>" method="post" name="address" id="address">
<table width="100%" >
<?php if ($exist) { ?>
  <tr>
    <th scope="row">address id</th>
    <td><?php echo $row_address['address_id']; ?><input type="hidden" name="address_id" value="<?php echo $row_address['address_id']; ?>"></td>
  </tr>
<?php } ?>
  <tr>
    <th scope="row">address type</th>
    <td><select name="address_type_id" onchange="document.address.submit();">
<?php do { ?>
      <option value="<?php echo $row_address_types['address_type_id']?>"<?php if($exist) { if (!(strcmp($row_address_types['address_type_id'], $row_address['address_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_address_types['address_type']?></option>
<?php } while ($row_address_types = mysql_fetch_assoc($address_types));
$rows = mysql_num_rows($address_types);
if($rows > 0)
{
	mysql_data_seek($address_types, 0);
	$row_address_types = mysql_fetch_assoc($address_types);
} ?>
    </select><?php if ($exist) { ?>&nbsp;<a href="letter.php?cid=<?php echo $cid; ?>&aid=<?php echo $row_address['address_id']; ?>"><img src="../graphics/letter.gif" alt="Write"></a><?php } ?></td>
  </tr>
  <?php if ($exist) { if ($row_address_type["show_custom"]) { ?>
  <tr>
  <th scope="row"><?php echo $row_address_type["custom_caption"]; ?></th>
  <td><input name="custom" type="text" value="<?php if ($exist) echo $row_address["custom"]; ?>"></td>
  </tr>
  <?php }} ?>
  <tr>
    <th scope="row">owner</th>
    <td><?php if ($exist && $totalRows_contacts > 1) { ?><select name="owner_id">
<?php do { ?>
      <option value="<?php echo $row_contacts['contact_id']?>"<?php if (!(strcmp($row_contacts['contact_id'], $row_address['owner_id']))) {echo "SELECTED";} ?>><?php echo $row_contacts['primary_name'] . ', ' . $row_contacts['first_name']?></option>
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
	<input type="hidden" name="owner_id" value="<?php if ($exist) echo  $row_address['owner_id'] ;
else echo $_GET['cid']; ?>"> <?php } ?>
	</td>
  </tr>
  <tr>
    <th scope="row">street address 1 </th>
    <td><input name="street_address_1" type="text" value="<?php if ($exist) echo $row_address['street_address_1']; ?>"></td>
  </tr>
  <tr>
    <th scope="row">street address 2 </th>
    <td><input name="street_address_2" type="text" value="<?php if ($exist) echo $row_address['street_address_2']; ?>"></td>
  </tr>
  <tr>
    <th scope="row">city</th>
    <td><input name="city" type="text" value="<?php if ($exist) echo $row_address['city']; ?>"></td>
  </tr>
  <tr>
    <th scope="row">state</th>
    <td><input name="state" type="text" value="<?php if ($exist) echo $row_address['state']; ?>"></td>
  </tr>
  <tr>
    <th scope="row">country</th>
    <td><input name="country" type="text" value="<?php if ($exist) echo $row_address['country']; else echo 'United States'; ?>"></td>
  </tr>
  <tr>
    <th scope="row">postal code </th>
    <td><input name="postal_code" type="text" value="<?php if ($exist) echo $row_address['postal_code']; ?>"></td>
  </tr>
<?php if ($exist) { ?>
  <tr>
    <th scope="row"> modified </th>
    <td><?php echo TimeStamp($row_address['modified']); ?></td>
  </tr>
<?php } ?>
</table>
<?php if ($rbac->check('edit_address', $_SESSION['user'])): ?>
<table>
<tr>
<td>
<input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="addresses.php?cid=<?php echo $cid; ?>&aid=<?php echo $aid; ?>&rec=delete" method="post" name="address-delete" id="address">
<input name="button" type="submit" value="Delete"></form></td>
<?php } else { ?>
<td><form action="associate.php?cid=<?php echo $cid; ?>" method="post" name="address-associate">
<input type="hidden" name="val" value="a">
<input type="submit" name="button" value="Attach someone's else's address">
</form></td>
<?php } ?>
<td><form name="revert" method="post" action="addresses.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
<?php endif; ?>
</body>
</html>
<?php
if ($exist) mysql_free_result($address);

mysql_free_result($address_types);
mysql_free_result($addresses);
mysql_free_result($contacts);
?>