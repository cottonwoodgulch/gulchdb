<?php

	require_once('../library.inc.php');
	
	$rbac->enforce('view_phone', $_SESSION['user']);

	$exist = RecordUpdate ('phones.php','phones', 'phone_id', 'pid', 'phone_associations', array('number' => array ('function' => 'StripPhone')));

$cid = (isset ($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));
$pid = (isset ($_GET['pid']) ? $_GET['pid'] : NULL);

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_phones = "SELECT * FROM phone_associations as assoc JOIN phones AS phone ON assoc.phone_id = phone.phone_id JOIN phone_types AS types ON phone.phone_type_id = types.phone_type_id WHERE assoc.contact_id = $cid ORDER BY rank ASC";
$phones = mysql_query($query_phones, $GLOBALS['db']['link']) or exit (mysql_error());
$row_phones = mysql_fetch_assoc($phones);
$totalRows_phones = mysql_num_rows($phones);

if (!$pid) $pid = $row_phones['phone_id'];

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_phone = "SELECT * FROM phones WHERE phone_id = $pid LIMIT 1";
	$phone = mysql_query($query_phone, $GLOBALS['db']['link']) or ($exist = false);
	if ($exist)
	{
		$row_phone = mysql_fetch_assoc($phone);
		$totalRows_phone = mysql_num_rows($phone);
	}
}

if($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contacts = "SELECT * FROM phone_associations AS assoc JOIN contacts ON assoc.contact_id = contacts.contact_id WHERE assoc.phone_id = $pid ORDER BY primary_name ASC";
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
$query_phone_types = "SELECT * FROM phone_types ORDER BY rank ASC";
$phone_types = mysql_query($query_phone_types, $GLOBALS['db']['link']) or exit (mysql_error());
$row_phone_types = mysql_fetch_assoc($phone_types);
$totalRows_phone_types = mysql_num_rows($phone_types);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Phones</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../stylesheet.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script src="../library.inc.js"></script>
<table class="menu">
  <tr>
<?php if ($totalRows_phones > 0) do { ?>
    <td><a href="phones.php?cid=<?php echo $cid; ?>&pid=<?php echo $row_phones['phone_id']; ?>&rec=view" title="<?php echo ($row_phones['formatted'] ? $row_phones['number'] : dbPhone($row_phones['number'])); ?>"><?php echo $row_phones["phone_type"]; ?></a></td>
<?php } while ($row_phones = mysql_fetch_assoc($phones)); ?>
	<td><a href="phones.php?cid=<?php echo $cid; ?>&rec=create">New Phone Number</a></td>
  </tr>
</table>

<form action="phones.php?cid=<?php echo $cid; ?><?php if ($exist) echo '&pid=' . $pid; ?>&rec=<?php echo ($exist ? 'update' : 'insert'); ?>" method="post" name="phone">
<table width="100%" >
<?php if ($exist) { ?>
  <tr>
    <th scope="row">phone id</th>
    <td><?php echo $row_phone['phone_id']; ?><input type="hidden" name="phone_id" value="<?php echo $row_phone['phone_id']; ?>"></td>
  </tr>
<?php } ?>
  <tr>
    <th scope="row">phone type</th>
    <td><select name="phone_type_id">
      <?php
do {
?>
      <option value="<?php echo $row_phone_types['phone_type_id']?>"<?php if ($exist) {if (!(strcmp($row_phone_types['phone_type_id'], $row_phone['phone_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_phone_types['phone_type']?></option>
      <?php
} while ($row_phone_types = mysql_fetch_assoc($phone_types));
  $rows = mysql_num_rows($phone_types);
  if($rows > 0) {
      mysql_data_seek($phone_types, 0);
	  $row_phone_types = mysql_fetch_assoc($phone_types);
  }
?>
    </select></td>
  </tr>
  <tr>
    <th scope="row">owner</th>
    <td><?php if ($exist && $totalRows_contacts > 1) { ?><select name="owner_id">
<?php do { ?>
      <option value="<?php echo $row_contacts['contact_id']?>"<?php if (!(strcmp($row_contacts['contact_id'], $row_phone['owner_id']))) {echo "SELECTED";} ?>><?php echo $row_contacts['primary_name'] . ', ' . $row_contacts['first_name']?></option>
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
	<input type="hidden" name="owner_id" value="<?php if ($exist) echo  $row_phone['owner_id'] ;
else echo $_GET['cid']; ?>"> <?php } ?>
	</td>
  </tr>
  <tr>
    <th scope="row">number</th>
    <td><input name="number" type="text" value="<?php if ($exist) echo ($row_phone['formatted'] ? $row_phone['number'] : dbPhone($row_phone['number'])); ?>" /></td>
  </tr>
  <tr>
    <th scope="row">formatted</th>
    <td><input name="formatted" type="checkbox" value="1" <?php if($exist) if (!(strcmp($row_phone['formatted'],1))) {echo "checked";} ?> /></td>
  </tr>
<?php if ($exist) { ?>
  <tr>
    <th scope="row"> modified </th>
    <td><?php echo TimeStamp($row_phone['modified']); ?></td>
  </tr>
<?php } ?>
</table>
<table>
<tr>
<td>
<input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="phones.php?cid=<?php echo $cid; ?>&pid=<?php echo $pid; ?>&rec=delete" method="post" name="phone-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } else { ?>
<td><form action="associate.php?cid=<?php echo $cid; ?>" method="post" name="phone-associate">
<input type="hidden" name="val" value="p">
<input type="submit" name="button" value="Attach someone's else's phone number">
</form></td>
<?php } ?>
<td><form name="revert" method="post" action="phones.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
</body>
</html>
<?php
if ($exist) mysql_free_result($phone);

mysql_free_result($phone_types);
mysql_free_result($phones);
mysql_free_result($contacts);
?>