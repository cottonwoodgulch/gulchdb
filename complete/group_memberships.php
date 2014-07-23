<?php
	require_once('../library.inc.php');

	$exist = RecordUpdate ('group_memberships.php', 'roster_memberships', 'roster_membership_id', 'gid');

	$cid = (isset ($_GET['cid']) ?
		$_GET['cid'] :
		exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));

	$gid = (isset ($_GET['gid']) ?
		$_GET['gid'] :
		NULL);

	$exist = ($gid != NULL);

	if ($exist)
	{
		mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
		$query_roster_membership = "SELECT * FROM roster_memberships WHERE roster_membership_id = $gid LIMIT 1";
		$roster_membership = mysql_query ($query_roster_membership, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_roster_membership = mysql_fetch_assoc ($roster_membership);
	}

	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_rosters = "SELECT * FROM rosters AS r JOIN groups AS g ON r.group_id = g.group_id ORDER BY year DESC, short_name ASC";
	$rosters = mysql_query($query_rosters, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_rosters = mysql_fetch_assoc($rosters);
	$totalRows_rosters = mysql_num_rows($rosters);

	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_roles = "SELECT * FROM roles ORDER BY `rank` ASC, `role` ASC";
	$roles = mysql_query($query_roles, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_roles = mysql_fetch_assoc($roles);
	$totalRows_roles = mysql_num_rows($roles);

	$query_tuition_types = "SELECT * FROM `tuition_types` ORDER BY `tuition_type` ASC";
	$tuition_types = mysql_query ($query_tuition_types) or exit (mysql_error());
	$row_tuition_types = mysql_fetch_assoc($tuition_types);
	$totalRows_tuition_types = mysql_num_rows ($tuition_types);

	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_roster_memberships = "SELECT * FROM roster_memberships AS rm JOIN rosters AS r ON rm.roster_id = r.roster_id WHERE contact_id = $cid ORDER BY year DESC";
	$roster_memberships = mysql_query($query_roster_memberships, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_roster_memberships = mysql_fetch_assoc($roster_memberships);
	$totalRows_roster_memberships = mysql_num_rows($roster_memberships);

	$rc = 0; // row counter for stripes
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Group Memberships</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../stylesheet.css" rel="stylesheet" type="text/css">
</head>

<body>
<script src="../library.inc.js"></script>

<table>
<?php if ((($totalRows_roster_memberships > 0) && (! $exist)) ||
		  (($totalRows_roster_memberships > 1) && ($exist))) { ?>
	<tr class="horizontal">
		<th>roster</th>
		<th>role</th>
		<td><form name="groups-all" method="post" action="group_memberships.php?cid=<?php echo $cid; ?>"><input type="submit" name="button" value="All"></form></td>
	</tr>

<?php
do {
	if ($row_roster_memberships['roster_membership_id'] != $gid) {
?>
	<tr<?php Stripe($rc); ?>>
		<td><?php
			mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
			$query_roster = 'SELECT * FROM rosters AS r JOIN groups AS g ON r.group_id = g.group_id WHERE r.roster_id = ' . $row_roster_memberships['roster_id'];
			$roster = mysql_query ($query_roster, $GLOBALS['db']['link']) or exit (mysql_error());
			$row_roster = mysql_fetch_assoc ($roster);
			echo "<a target=\"content\" href=\"../groups/roster.php?id=" . $row_roster["roster_id"] . "\">" . ($row_roster['year'] > 0 ? $row_roster['year'] : 'Recurring') . ' ' . $row_roster['group'] . "</a>";
			mysql_free_result ($roster); ?></td>
		<td><?php
			if ($row_roster_memberships['role_id'])
			{
				mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
				$query_role = 'SELECT * FROM roles WHERE role_id = ' . $row_roster_memberships['role_id'];
				$role = mysql_query ($query_role, $GLOBALS['db']['link']) or exit (mysql_error());
				$row_role = mysql_fetch_assoc ($role);
				if ($row_role) echo $row_role['role'];
				mysql_free_result ($role);
			}?></td>
		<td><form name="roster-edit" method="post" action="group_memberships.php?cid=<?php echo $cid; ?>&gid=<?php echo $row_roster_memberships['roster_membership_id']; ?>"><input type="submit" name="button" value="Edit"></form></td>
	</tr>
<?php }} while ($row_roster_memberships = mysql_fetch_assoc($roster_memberships)); }?>
</table>
<form name="roster-edit" method="post" action="group_memberships.php?cid=<?php echo $cid . ($exist ? "&gid=$gid" : '') . '&rec=' . ($exist ? 'update' : 'insert'); ?>">
<input type="hidden" name="contact_id" value="<?php echo ($exist ? $row_roster_membership['contact_id'] : $cid); ?>">
<table>
<?php if ($exist) { ?>
	<tr>
		<th>roster membership id</th>
		<td><input type="hidden" name="roster_membership_id" value="<?php echo $row_roster_membership['roster_membership_id']; ?>"><?php echo $row_roster_membership['roster_membership_id']; ?></td>
	</tr>
<?php } ?>
	<tr>
		<th>roster</th>
		<td><select name="roster_id" id="roster_id">
		  <?php
do {
?>
		  <option value="<?php echo $row_rosters['roster_id']?>"<?php if ($exist) { if (!(strcmp($row_rosters['roster_id'], $row_roster_membership['roster_id']))) {echo "SELECTED";}} ?>><?php echo ($row_rosters['year'] > 0 ? $row_rosters['year'] : 'Recurring') . ' ' . $row_rosters['short_name']; ?></option>
		  <?php
} while ($row_rosters = mysql_fetch_assoc($rosters));
  $rows = mysql_num_rows($rosters);
  if($rows > 0) {
      mysql_data_seek($rosters, 0);
	  $row_rosters = mysql_fetch_assoc($rosters);
  }
?>
		</select></td>
		<tr>
		<th>role</th>
		<td><select name="role_id" id="role_id">
		<?php
		OptionNull ($row_roster_membership['role_id']);
		do { ?>
			<option value="<?php echo $row_roles['role_id']; ?>"<?php if ($exist) { if (!(strcmp($row_roles['role_id'], $row_roster_membership['role_id']))) {echo "SELECTED";}} ?>><?php echo $row_roles['role']; ?></option>
			<?php } while ($row_roles = mysql_fetch_assoc($roles));
				$rows = mysql_num_rows($roles);
				if($rows > 0) {
					mysql_data_seek($roles, 0);
					$row_roles = mysql_fetch_assoc($roles);
				} ?>
		</select></td>
		</tr>
<!--		<tr>
		<th>tuition type</th>
		<td><select name="tuition_type_id" id="tuition_type_id">
		<?php
		OptionNull ($row_roster_membership["tuition_type_id"]);
		do { ?>
			<option value="<?php echo $row_tuition_types['tuition_type_id']; ?>"<?php if ($exist) { if (!(strcmp($row_tuition_types['tuition_type_id'], $row_roster_membership['tuition_type_id']))) {echo "SELECTED";}} ?>><?php echo $row_tuition_types['tuition_type']; ?></option>
			<?php } while ($row_tuition_types = mysql_fetch_assoc($tuition_types));
				$rows = mysql_num_rows($tuition_types);
				if($rows > 0) {
					mysql_data_seek($tuition_types, 0);
					$row_tuition_types = mysql_fetch_assoc($tuition_types);
				} ?>
		</select></td>
		</tr> -->
<?php if ($exist) { ?>
		<tr>
			<th>modified</th>
			<td><?php echo $row_roster_membership['modified']; ?></td>
		</tr>
<?php } ?>
	</table>
	<table>
		<tr>
			<td><input type="submit" name="button" value="<?php echo ($exist ? 'Update' : 'Add'); ?>"></form></td>
<?php if ($exist) { ?>
<td><form action="group_memberships.php?cid=<?php echo $cid; ?>&gid=<?php echo $gid; ?>&rec=delete" method="post" name="group-delete">
<input name="button" type="submit" value="Delete"></form></td>
<?php } ?>
<td><form name="revert" method="post" action="group_memberships.php?cid=<?php echo $cid; ?>&rec=revert">
<input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
</tr>
</table>
		</tr>
	</table>
</body>
</html>
<?php
mysql_free_result($rosters);
mysql_free_result($roster_memberships);
mysql_free_result($roles);
if ($exist) mysql_free_result ($roster_membership);
?>