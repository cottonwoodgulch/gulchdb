<?php

require_once ('../library.inc.php');

$cid = (isset($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));
$rid = (isset($_GET['rid']) ? $_GET['rid'] : NULL);

$pattern = (isset ($_POST['pattern']) ? $_POST['pattern'] : NULL);
$search = isset ($_POST['search']);

$sql = "SELECT * FROM contacts WHERE contact_id = $cid LIMIT 1";
$result = mysql_query ($sql) or exit_sql_error ($sql);
$contact = mysql_fetch_assoc ($result);
mysql_free_result ($result);

// *** The following is a modified version of RecordUpdate() in library.inc.php ***
// for various reasons, relationships are sufficiently involved that the regular
// code cannot be used -- however this means that modifications to either this
// or the library.inc.php code should result in a carefully considered update to
// the other... I hope

$rec = (isset ($_GET['rec']) ? $_GET['rec'] : 'create');

switch ($rec)
{
	case 'insert':
		// first, do a regular insert
		$rid = Insert ($_POST, 'relationships', 'relationship_id', NULL);

		// now, find out what the inverse is of the relationship that we just inserted
		$query = 'SELECT * FROM relationship_types WHERE relationship_type_id = ' . $_POST['relationship_type_id'] . ' LIMIT 1';
		$relationship = mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_relationship = mysql_fetch_assoc ($relationship);
		mysql_free_result ($relationship);

		// insert the inverse relationship as well
		$inverse = array ('contact_id' => $_POST['relative_id'],
						  'relative_id' => $cid,
						  'relationship_type_id' => $row_relationship['inverse_relationship_id']);
		$id = Insert ($inverse, 'relationships', 'relationship_id', NULL);

		// redirect to this page again with a regular view
		header('Location: relationships.php?cid=' . $_GET['cid'] . "&rid=$rid&rec=view");
		exit;

	case 'update':
		// first, save a copy of the old relationship, pre-update
		$query = "SELECT * FROM relationships AS r JOIN relationship_types AS t ON r.relationship_type_id = t.relationship_type_id WHERE relationship_id = $rid LIMIT 1";
		$old = mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_old = mysql_fetch_assoc ($old);
		mysql_free_result ($old);

		// next, delete the old inverse relationship
		$query = "DELETE FROM relationships WHERE contact_id = " . $row_old['relative_id'] . " AND relative_id = " . $cid . " AND relationship_type_id = " . $row_old['inverse_relationship_id'] . " LIMIT 1";
		mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());

		// next, do a regular record update
		Update ($_POST, 'relationships', 'relationship_id', NULL);

		// find out what the inverse is of the relationship that we just updated
		$query = 'SELECT * FROM relationship_types WHERE relationship_type_id = ' . $_POST['relationship_type_id'] . ' LIMIT 1';
		$relationship = mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_relationship = mysql_fetch_assoc ($relationship);
		mysql_free_result ($relationship);

		//  then we need to insert an inverse relationship with the new relative
		$inverse = array ('contact_id' => $_POST['relative_id'],
						  'relative_id' => $cid,
						  'relationship_type_id' => $row_relationship['inverse_relationship_id']);
		$id = Insert ($inverse, 'relationships', 'relationship_id', NULL);

		// redirect to this page in the regular view
		header("Location: relationships.php?cid=$cid&rid=$rid&rec=view");
		exit;

	case 'delete':
		// first, save a copy of the old relationship, pre-update
		$query = "SELECT * FROM relationships AS r JOIN relationship_types AS t ON r.relationship_type_id = t.relationship_type_id WHERE relationship_id = $rid LIMIT 1";
		$old = mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());
		$row_old = mysql_fetch_assoc ($old);
		mysql_free_result ($old);

		// next, do a regular delete
		Delete ('relationships', "`relationship_id` = '$rid'");

		// finally, delete the old matching inverse relationship
		$query = 'DELETE FROM relationships WHERE contact_id = ' . $row_old['relative_id'] . '
				  AND relative_id = ' . $cid . ' AND relationship_type_id = ' .
				  $row_old['inverse_relationship_id'];
		mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());

		// redirect back to the general relationships view
		header ("Location: relationships.php?cid=$cid");
		exit;

	case 'create':
		$exist = false;
		break;

	case 'view':
	case 'revert':
	case 'edit':
	default:
		$exist = true;
		break;
}

// *** end of butchered RecordUpdate() code ***

$contacts = $totalRows_contacts = NULL;

if ($exist)
{
	$query_relationship = "SELECT * FROM relationships WHERE relationship_id = $rid LIMIT 1";
	$relationship = mysql_query ($query_relationship, $GLOBALS['db']['link']) or exit (mysql_error());
	$row_relationship = mysql_fetch_assoc ($relationship);
	if (! $search) $pattern = Name ($row_relationship["relative_id"], "%F %N %M %L");
}

$query_relationships = "SELECT *
						FROM relationships AS r
						JOIN relationship_types AS t
							ON r.relationship_type_id = t.relationship_type_id
						JOIN contacts AS c
							ON c.contact_id = r.relative_id
						WHERE r.contact_id = $cid
						ORDER BY t.rank ASC, c.primary_name ASC, c.first_name ASC";
$relationships = mysql_query ($query_relationships, $GLOBALS['db']['link']) or exit (mysql_error());
$totalRows_relationships = mysql_num_rows ($relationships);

$query_relationship_types = "SELECT * FROM relationship_types ORDER BY rank ASC";
$relationship_types = mysql_query ($query_relationship_types, $GLOBALS['db']['link']) or exit (mysql_error());
$totalRows_relationship_types = mysql_num_rows ($relationship_types);

if ($pattern)
{
	$contacts = NameSearch ($pattern);
	$totalRows_contacts = mysql_num_rows ($contacts);
}

$rc = 0;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
	<head>
		<title>Relationships</title>
		<link href="../stylesheet.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<script src="../library.inc.js"></script>

		<p>Please do not add redundant relationships to the database! For example, if we know that the parents of two people are the same, DO NOT add the sibling relationship to the database (this can be extrapolated by the system, and just adds work down the road). Thanks!</p>

		<?php if (((! $exist) && ($totalRows_relationships > 0)) || ($exist && ($totalRows_relationships > 1))) { ?>
		<table>
			<tr class="horizontal">
				<th>relative</th>
				<th>relationship</th>
			</tr>
			<?php
				while ($row_relationships = mysql_fetch_assoc ($relationships))
				{ if ($row_relationships['relationship_id'] != $rid) {?>
				<tr<?php Stripe($rc); ?>>
					<td><a href="contacts.php?cid=<?php echo $row_relationships['relative_id']; ?>&detail=relationships.php" target="content"><?php echo str_replace (" \"\"", "", Name($row_relationships['relative_id'], '%F %M "%N" %L %D')); ?></a></td>
					<td><?php echo ucfirst ((strlen ($row_relationships["gender"]) ? $row_relationships[$row_relationships["gender"]] : $row_relationships["relationship_type"])); ?></td>
					<td><form name="edit" method="post" action="relationships.php?cid=<?php echo $cid; ?>&rid=<?php echo $row_relationships['relationship_id']; ?>&rec=edit"><input type="submit" name="button" value="Edit"></form></td>
				</tr>
				<?php }}
			?>
		</table>
		<?php }
		if ($pattern) {
		?>
		<form name="edit" method="post" action="relationships.php?cid=<?php echo $cid; ?><?php echo ($exist ? "&rid=$rid&rec=update" : '&rec=insert'); ?>">
		<input type="hidden" name="contact_id" value="<?php echo $cid; ?>">
		<table>
			<?php if ($exist) { ?>
			<tr>
				<th>relationship id</th>
				<td><?php echo $row_relationship['relationship_id']; ?><input type="hidden" name="relationship_id" value="<?php echo $row_relationship['relationship_id']; ?>"></td>
			</tr>
			<?php } ?>
			<tr>
				<th>relationship type</th>
				<td><select name="relationship_type_id">
					<?php
						while ($row_relationship_types = mysql_fetch_assoc ($relationship_types))
						{ ?>
						<option value="<?php echo $row_relationship_types['relationship_type_id']; ?>"<?php if ($exist) { if ($row_relationship_types['relationship_type_id'] == $row_relationship['relationship_type_id']) echo ' selected'; } ?>><?php echo ucwords ($row_relationship_types['relationship_type']); ?></option>
						<?php }
					?>
					</select></td>
				</td>
			</tr>
			<tr>
				<th>relative</th>
				<td><?php if ($totalRows_contacts > 1) { ?>
					<select name="relative_id">
					<?php
						while ($row_contacts = mysql_fetch_assoc ($contacts))
						{ ?>
							<option value="<?php echo $row_contacts['contact_id']; ?>"<?php if ($exist) { if ($row_contacts['contact_id'] == $row_relationship['relative_id']) echo ' selected'; } ?>><?php echo Name ($row_contacts["contact_id"]); ?></option>
						<?php } ?>
				</select><?php }
				else
				{
					$row_contacts = mysql_fetch_assoc ($contacts);
					echo Name ($row_contacts["contact_id"]);
					echo '<input type="hidden" name="relative_id" value="' . $row_contacts['contact_id'] . '">';
				} ?></td>
			</tr>
			<?php if ($exist) { ?>
			<tr>
				<th>modified</th>
				<td><echo TimeStamp ($row_relationship['modified']); ?></td>
			</tr>
			<?php } ?>
		</table>
		<table>
			<tr>
				<td><input type="submit" name="button" value="<?php echo ($exist ? 'Update' : 'Add'); ?>"></form></td>
				<?php if ($exist) { ?>
				<td><form name="delete" method="post" action="relationships.php?cid=<?php echo $cid; ?>&rid=<?php echo $rid; ?>&rec=delete"><input type="submit" name="button" value="Delete"></form></td>
				<?php } ?>
				<td><form name="revert" method="post" action="relationships.php?cid=<?php echo $cid . ($exist ? "&rid=$rid&rec=revert" : '&rec=cancel'); ?>"><input type="submit" name="button" value="<?php echo ($exist ? 'Revert' : 'Cancel'); ?>"></form></td>
				<td><form name="new-pattern" method="post" value="relationships.php?cid=<?php echo $cid . ($exist ? "&rid=$rid&rec=edit" : '&rec=create'); ?>"><input type="hidden" name="search" value="yes"><input type="submit" name="button" value="Find More"></form></td>
			</tr>
		<?php } // pattern
		else
		{ ?>
			<form name="match" method="post" action="relationships.php?cid=<?php echo $cid; ?>&rid=<?php echo $rid; ?>&rec=<?php echo ($exist ? 'edit' : 'create'); ?>">
			<input type="hidden" name="search" value="yes">
			<table>
				<tr>
					<th>find by name</th>
				</tr>
				<tr>
					<td><input type="text" name="pattern"></td>
					<td><input type="submit" name="button" value="Find"></td>
				</tr>
			</table>
			</form>
		<?php } ?>
	</body>

</html>