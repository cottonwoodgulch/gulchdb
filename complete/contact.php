<?php

require_once('../library.inc.php');

$exist = RecordUpdate ('contact.php', 'contacts', 'contact_id', 'cid');

$cid = (isset ($_GET['cid']) ? $_GET['cid'] : NULL);

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contact = "SELECT * FROM contacts WHERE contact_id = $cid";
	$contact = mysql_query($query_contact, $GLOBALS['db']['link']) or ($exist = false);
	if ($exist)
	{
		$row_contact = mysql_fetch_assoc($contact);
		$totalRows_contact = mysql_num_rows($contact);
	}
}

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_contact_types = "SELECT * FROM contact_types ORDER BY rank ASC";
$contact_types = mysql_query($query_contact_types, $GLOBALS['db']['link']) or exit (mysql_error());
$row_contact_types = mysql_fetch_assoc($contact_types);
$totalRows_contact_types = mysql_num_rows($contact_types);

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_titles = "SELECT * FROM titles ORDER BY title ASC";
$titles = mysql_query($query_titles, $GLOBALS['db']['link']) or exit (mysql_error());
$row_titles = mysql_fetch_assoc($titles);
$totalRows_titles = mysql_num_rows($titles);

if ($exist)
{
	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contact_type = sprintf("SELECT * FROM contact_types WHERE contact_type_id = %s", $row_contact['contact_type_id']);
	$contact_type = mysql_query($query_contact_type, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_contact_type = mysql_fetch_assoc($contact_type);
	$totalRows_contact_type = mysql_num_rows($contact_type);
}
else
{
	mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_contact_type = "SELECT * FROM contact_types WHERE 1 ORDER BY rank ASC LIMIT 1";
	$contact_type = mysql_query($query_contact_type, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_contact_type = mysql_fetch_assoc($contact_type);
	$totalRows_contact_type = mysql_num_rows($contact_type);
}

mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query_degrees = "SELECT * FROM degrees ORDER BY degree ASC";
$degrees = mysql_query($query_degrees, $GLOBALS['db']['link']) or exit (mysql_error());
$row_degrees = mysql_fetch_assoc($degrees);
$totalRows_degrees = mysql_num_rows($degrees);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php
if ($exist)
	echo str_replace (" \"\"", "", Name ($row_contact['contact_id'], '%T %F %M "%N" %L %D'));
else
	echo 'Contact'; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../stylesheet.css" rel="stylesheet" type="text/css">
<?php if ($exist) { ?>
<script><!--
	if (parent.header.href != 'header.php?cid=<?php echo $cid; ?>')
		parent.header.location.replace('header.php?cid=<?php echo $cid; ?>');
--></script>
<?php } else { ?>
<script><!--
	if (parent.header.href != 'header.php?rec=create')
		parent.header.location.replace('header.php?rec=create');
	parent.contact_detail.location.replace('../empty.html');
--></script>
<?php } ?>
</head>

<body>
<script src="../library.inc.js"></script>
<form name="contact" method="post" action="contact.php?<?php if ($exist) echo "cid=$cid&"; ?>rec=<?php echo ($exist ? 'update' : 'insert'); ?>">
  <table width="100%" >
<?php if ($exist) { ?>
    <tr>
      <th scope="row">contact id </th>
      <td><?php echo $row_contact['contact_id']; ?> <input type="hidden" name="contact_id" value="<?php echo $row_contact['contact_id']; ?>"></td>
    </tr>
<?php } ?>
    <tr>
      <th scope="row">contact type </th>
      <td><select name="contact_type_id" onchange="document.contact.submit();">
        <?php
do {
?>
        <option value="<?php echo $row_contact_types['contact_type_id']?>"<?php if ($exist) { if (!(strcmp($row_contact_types['contact_type_id'], $row_contact['contact_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_contact_types['contact_type']?></option>
        <?php
} while ($row_contact_types = mysql_fetch_assoc($contact_types));
  $rows = mysql_num_rows($contact_types);
  if($rows > 0) {
      mysql_data_seek($contact_types, 0);
	  $row_contact_types = mysql_fetch_assoc($contact_types);
  }
?>
      </select></td>
    </tr>
<?php if ($row_contact_type["show_title"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['title_caption']; ?></th>
      <td><select name="title_id" id="title">
        <?php
OptionNull ($row_contact['title_id']);
do {
?>
        <option value="<?php echo $row_titles['title_id']?>"<?php if ($exist) { if (!(strcmp($row_titles['title_id'], $row_contact['title_id']))) {echo "SELECTED";}} ?>><?php echo $row_titles['title']?></option>
        <?php
} while ($row_titles = mysql_fetch_assoc($titles));
  $rows = mysql_num_rows($titles);
  if($rows > 0) {
      mysql_data_seek($titles, 0);
	  $row_titles = mysql_fetch_assoc($titles);
  }
?>
      </select></td>
    </tr>
<?php } if ($row_contact_type["show_primary_name"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['primary_name_caption']; ?></th>
      <td><input name="primary_name" type="text" value="<?php if ($exist) echo $row_contact['primary_name']; ?>"></td>
    </tr>
<?php } if ($row_contact_type["show_first_name"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['first_name_caption']; ?></th>
      <td><input name="first_name" type="text" value="<?php if ($exist) echo $row_contact['first_name']; ?>"></td>
    </tr>
<?php } if ($row_contact_type["show_middle_name"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['middle_name_caption']; ?></th>
      <td><input name="middle_name" type="text" value="<?php if ($exist) echo $row_contact['middle_name']; ?>"></td>
    </tr>
<?php } if ($row_contact_type["show_degree"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['degree_caption']; ?></th>
      <td><select name="degree_id" id="degree">
        <?php
OptionNull ($row_contact['degree_id']);
do {
?>
        <option value="<?php echo $row_degrees['degree_id']?>"<?php if ($exist) {if (!(strcmp($row_degrees['degree_id'], $row_contact['degree_id']))) {echo "SELECTED";}} ?>><?php echo $row_degrees['degree']?></option>
        <?php
} while ($row_degrees = mysql_fetch_assoc($degrees));
  $rows = mysql_num_rows($degrees);
  if($rows > 0) {
      mysql_data_seek($degrees, 0);
	  $row_degrees = mysql_fetch_assoc($degrees);
  }
?>
      </select></td>
    </tr>
<?php } if ($row_contact_type["show_nickname"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['nickname_caption']; ?></th>
      <td><input name="nickname" type="text" value="<?php if ($exist) echo $row_contact['nickname']; ?>"></td>
    </tr>
<?php } if ($row_contact_type["show_birth_date"]) { ?>
   <tr>
      <th scope="row"><?php echo $row_contact_type['birth_date_caption']; ?></th>
      <td><input name="birth_date" type="text" value="<?php if (($exist) && ($row_contact["birth_date"] <> "0000-00-00")) echo $row_contact['birth_date']; ?>"><?php if ($exist && $row_contact['birth_date'] && $row_contact['birth_date'][0] != '0') echo '<br>Age ' . Age ($row_contact['birth_date'], true, true); ?></td>
    </tr>
<?php } if ($row_contact_type['show_gender']) { ?>
	<tr>
		<th><?php echo $row_contact_type['gender_caption']; ?></th>
		<td><input type="radio" name="gender" value="Male"<?php if ($exist) { if ($row_contact['gender'] == 'Male') echo ' checked'; } ?>>Male <input type="radio" name="gender" value="Female" <?php if ($exist) { if ($row_contact['gender'] == 'Female') echo ' checked'; } ?>>Female</td>
	</tr>
<?php } ?>
	<tr>
		<th scope="row">Mailing Preference</th>
		<td><select name="mailing_preference">
			<?php
				$mpQuery = "select * from mailing_preferences order by rank asc";
				$mpResult = mysql_query($mpQuery);
				while($mpRow=mysql_fetch_assoc($mpResult))
				{
					echo "<option value=\"{$mpRow["mailing_preference_id"]}\"";
					if ($row_contact["mailing_preference"] == $mpRow["mailing_preference_id"])
					{
						echo " selected";
					}
					echo ">{$mpRow["mailing_preference"]}</option>";
				}
			?>
		</select></td>
	</tr>
<?php if ($row_contact_type["show_deceased"]) { ?>
    <tr>
      <th scope="row"><?php echo $row_contact_type['deceased_caption']; ?></th>
      <td><input name="deceased" type="radio"<?php if ($exist) { if ($row_contact['deceased'] == 1) echo ' checked'; } ?> value="1">Yes <input type="radio" name="deceased" value="0" <?php if ($exist) { if ($row_contact['deceased'] == 0) echo ' checked'; } ?>>No</td>
    </tr>
<?php } if ($exist) { ?>
    <tr>
      <th scope="row">modified</th>
      <td><?php echo TimeStamp($row_contact['modified']); ?></td>
    </tr>
<?php } ?>
  </table>
<table>
<tr>
<td><input name="button" type="submit"  value="<?php if ($exist) echo 'Update'; else echo 'Add'; ?>">
</form></td>
<?php if ($exist) { ?>
<td><form action="contact.php?cid=<?php echo $cid; ?>&rec=delete" method="post" name="contact-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } ?>
<td><form name="revert" method="post" action="index.php" target="_top">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
</body>
</html>
<?php
if ($exist) mysql_free_result($contact);

mysql_free_result($contact_types);

mysql_free_result($titles);

mysql_free_result($contact_type);

mysql_free_result($degrees);
?>