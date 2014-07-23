<?php
require_once('../library.inc.php');
$exist = RecordUpdate ('urls.php', 'urls', 'url_id', 'uid', 'url_associations', array ('url' => array ('function' => 'StripURL')));

$cid = (isset ($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));
$uid = (isset ($_GET['uid']) ? $_GET['uid'] : NULL);

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_urls = "SELECT * FROM url_associations AS assoc JOIN urls AS u ON assoc.url_id = u.url_id LEFT JOIN url_types AS types ON u.url_type_id = types.url_type_id WHERE assoc.contact_id = $cid ORDER BY rank ASC";
$urls = mysql_query($query_urls, $GLOBALS['db']['link']) or exit (mysql_error());
$row_urls = mysql_fetch_assoc($urls);
$totalRows_urls = mysql_num_rows($urls);

if (!$uid) $uid = $row_urls['url_id'];

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_url = "SELECT * FROM urls WHERE url_id = $uid LIMIT 1";
	$url = mysql_query($query_url, $GLOBALS['db']['link']) or ($exist = false);
	if ($exist)
	{
		$row_url = mysql_fetch_assoc($url);
		$totalRows_url = mysql_num_rows($url);
	}
}

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contacts = "SELECT * FROM url_associations AS assoc JOIN contacts ON assoc.contact_id = contacts.contact_id WHERE assoc.url_id = $uid ORDER BY primary_name ASC";
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
$query_url_types = "SELECT * FROM url_types ORDER BY rank ASC";
$url_types = mysql_query($query_url_types, $GLOBALS['db']['link']) or exit (mysql_error());
$row_url_types = mysql_fetch_assoc($url_types);
$totalRows_url_types = mysql_num_rows($url_types);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>URLs</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../stylesheet.css" rel="stylesheet" type="text/css" />
</head>

<body>
<script src="../library.inc.js"></script>
<table class="menu">
  <tr>
<?php if ($totalRows_urls > 0) do { ?>
    <td><a href="urls.php?cid=<?php echo $cid; ?>&uid=<?php echo $row_urls['url_id']; ?>&rec=view"><?php echo $row_urls['url_type']; ?></a>&nbsp;<a href="<?php echo URL($row_urls['url']); ?>" target="<?php echo $row_contacts['primary_name']; ?>"><img src="../graphics/url.gif" alt="Open"></a></td>
<?php } while ($row_urls = mysql_fetch_assoc($urls)); ?>
	<td><a href="urls.php?cid=<?php echo $cid; ?>&rec=create">New URL</a></td>
  </tr>
</table>

<form action="urls.php?cid=<?php echo $cid; ?><?php if ($exist) echo '&uid=' . $uid; ?>&rec=<?php echo ($exist ? 'update' : 'insert'); ?>" method="post" name="phone">
<table width="100%" >
<?php if ($exist) { ?>
  <tr>
    <th scope="row">url id</th>
    <td><?php echo $row_url['url_id']; ?><input type="hidden" name="url_id" value="<?php echo $row_url['url_id']; ?>"></td>
  </tr>
<?php } ?>
  <tr>
    <th scope="row">url type</th>
    <td><select name="url_type_id">
      <?php
do {
?>
      <option value="<?php echo $row_url_types['url_type_id']?>"<?php if ($exist) {if (!(strcmp($row_url_types['url_type_id'], $row_url['url_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_url_types['url_type']?></option>
      <?php
} while ($row_url_types = mysql_fetch_assoc($url_types));
  $rows = mysql_num_rows($url_types);
  if($rows > 0) {
      mysql_data_seek($url_types, 0);
	  $row_url_types = mysql_fetch_assoc($url_types);
  }
?>
    </select></td>
  </tr>
  <tr>
    <th scope="row">owner</th>
    <td><?php if ($exist && $totalRows_contacts > 1) { ?><select name="owner_id">
<?php do { ?>
      <option value="<?php echo $row_contacts['contact_id']?>"<?php if (!(strcmp($row_contacts['contact_id'], $row_url['owner_id']))) {echo "SELECTED";} ?>><?php echo $row_contacts['primary_name'] . ', ' . $row_contacts['first_name']?></option>
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
    <th scope="row"><?php if ($exist) echo '<a href="' . URL($row_url['url']) . '" target="' . $row_contacts['primary_name'] . '">'; ?>url<?php if ($exist) echo '</a>'; ?></th>
    <td><input name="url" type="text" value="<?php if ($exist) echo StripURL($row_url['url']); ?>" /><?php if ($exist) echo '<a href="' .  URL($row_url['url']) . '" target="' . $row_contacts['primary_name'] . '"><img src="../graphics/url.gif" alt="Open"></a>'; ?></td>
  </tr>
<?php if ($exist) { ?>
  <tr>
    <th scope="row"> modified </th>
    <td><?php echo TimeStamp($row_url['modified']); ?></td>
  </tr>
<?php } ?>
</table>
<table>
<tr>
<td>
<input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="urls.php?cid=<?php echo $cid; ?>&uid=<?php echo $uid; ?>&rec=delete" method="post" name="url-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } else { ?>
<td><form action="associate.php?cid=<?php echo $cid; ?>" method="post" name="url-associate">
<input type="hidden" name="val" value="u">
<input type="submit" name="button" value="Attach someone's else's URL">
</form></td>
<?php } ?>
<td><form name="revert" method="post" action="urls.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
</body>
</html>
<?php
if ($exist) mysql_free_result($url);

mysql_free_result($url_types);
mysql_free_result($urls);
mysql_free_result($contacts);
?>