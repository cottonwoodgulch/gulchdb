<?php
require_once('../library.inc.php');

$rbac->enforce('view_email', $_SESSION['user']);

$exist = RecordUpdate ('emails.php', 'emails', 'email_id', 'eid', 'email_associations');

$cid = (isset ($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));
$eid = (isset ($_GET['eid']) ? $_GET['eid'] : NULL);

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_emails = "SELECT * FROM email_associations AS assoc JOIN emails AS e ON assoc.email_id = e.email_id JOIN email_types AS types ON e.email_type_id = types.email_type_id WHERE assoc.contact_id = $cid ORDER BY rank ASC";
$emails = mysql_query($query_emails, $GLOBALS['db']['link']) or exit (mysql_error());
$row_emails = mysql_fetch_assoc($emails);
$totalRows_emails = mysql_num_rows($emails);

if (!$eid) $eid = $row_emails['email_id'];

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_email = "SELECT * FROM emails WHERE email_id = $eid LIMIT 1";
	$email = mysql_query($query_email, $GLOBALS['db']['link']) or ($exist = false);
	if ($exist)
	{
		$row_email = mysql_fetch_assoc($email);
		$totalRows_email = mysql_num_rows($email);
	}
}

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contacts = "SELECT * FROM email_associations AS assoc JOIN contacts ON assoc.contact_id = contacts.contact_id WHERE assoc.email_id = $eid ORDER BY primary_name ASC";
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
$query_email_types = "SELECT * FROM email_types ORDER BY rank ASC";
$email_types = mysql_query($query_email_types, $GLOBALS['db']['link']) or exit (mysql_error());
$row_email_types = mysql_fetch_assoc($email_types);
$totalRows_email_types = mysql_num_rows($email_types);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Emails</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../stylesheet.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script src="../library.inc.js"></script>
<table class="menu">
  <tr>
<?php if ($totalRows_emails > 0) do { ?>
    <td><a href="emails.php?cid=<?php echo $cid; ?>&eid=<?php echo $row_emails['email_id']; ?>&rec=view"><?php echo $row_emails["email_type"]; ?></a></td>
<?php } while ($row_emails = mysql_fetch_assoc($emails)); ?>
	<td><a href="emails.php?cid=<?php echo $cid; ?>&rec=create">New Email</a></td>
  </tr>
</table>

<form action="emails.php?cid=<?php echo $cid; ?><?php if ($exist) echo '&eid=' . $eid; ?>&rec=<?php echo ($exist ? 'update' : 'insert'); ?>" method="post" name="phone">
<table width="100%" >
<?php if ($exist) { ?>
  <tr>
    <th scope="row">email id</th>
    <td><?php echo $row_email['email_id']; ?><input type="hidden" name="email_id" value="<?php echo $row_email['email_id']; ?>"></td>
  </tr>
<?php } ?>
  <tr>
    <th scope="row">email type</th>
    <td><select name="email_type_id">
      <?php
do {
?>
      <option value="<?php echo $row_email_types['email_type_id']?>"<?php if ($exist) {if (!(strcmp($row_email_types['email_type_id'], $row_email['email_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_email_types['email_type']?></option>
      <?php
} while ($row_email_types = mysql_fetch_assoc($email_types));
  $rows = mysql_num_rows($email_types);
  if($rows > 0) {
      mysql_data_seek($email_types, 0);
	  $row_email_types = mysql_fetch_assoc($email_types);
  }
?>
    </select></td>
  </tr>
  <tr>
    <th scope="row">owner</th>
    <td><?php if ($exist && $totalRows_contacts > 1) { ?><select name="owner_id">
<?php do { ?>
      <option value="<?php echo $row_contacts['contact_id']?>"<?php if (!(strcmp($row_contacts['contact_id'], $row_email['owner_id']))) {echo "SELECTED";} ?>><?php echo $row_contacts['primary_name'] . ', ' . $row_contacts['first_name']?></option>
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
    <th scope="row"><?php if ($exist) echo '<a href="mailto:' . Name ($cid, '%n %L') . '&lt;' . $row_email['email'] . '&gt;">'; ?>email<?php if ($exist) echo '</a>'; ?></th>
    <td><input name="email" type="text" value="<?php if ($exist) echo $row_email['email']; ?>" /></td>
  </tr>
<?php if ($exist) { ?>
  <tr>
    <th scope="row"> modified </th>
    <td><?php echo TimeStamp($row_email['modified']); ?></td>
  </tr>
<?php } ?>
</table>
<table>
<tr>
<td>
<input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="emails.php?cid=<?php echo $cid; ?>&eid=<?php echo $eid; ?>&rec=delete" method="post" name="email-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } else { ?>
<td><form action="associate.php?cid=<?php echo $cid; ?>" method="post" name="email-associate">
<input type="hidden" name="val" value="e">
<input type="submit" name="button" value="Attach someone's else's email">
</form></td>
<?php } ?>
<td><form name="revert" method="post" action="emails.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
</body>
</html>
<?php
if ($exist) mysql_free_result($email);

mysql_free_result($email_types);
mysql_free_result($emails);
mysql_free_result($contacts);
?>