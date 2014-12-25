<?php

	require_once ('../library.inc.php');
	
	$rbac->enforce('view_note', $_SESSION['user']);

	$exist = RecordUpdate ('notes.php', 'notes', 'note_id', 'nid');
	
	$cid = (isset($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));
	$nid = (isset($_GET['nid']) ? $_GET['nid'] : NULL);
	
	$query_notes = "SELECT * FROM notes WHERE contact_id = $cid";
	$notes = mysql_query ($query_notes, $GLOBALS['db']['link']) or exit (mysql_error());
	
	$rc = 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Notes</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../stylesheet.css" rel="stylesheet" type="text/css">
</head>

<body>
	<script src="../library.inc.js"></script>
	
	<table>
		<?php while ($row_notes = mysql_fetch_assoc ($notes)) { ?>
			<form action="notes.php?cid=<?php echo $cid; ?>&nid=<?php echo $row_notes['note_id']; ?>&rec=update" method="post">
			<tr<?php Stripe($rc); ?>>
				<td>
					<input type="hidden" name="note_id" value="<?php echo $row_notes['note_id']; ?>">
					<textarea name="note" rows="10" cols="30"><?php echo $row_notes['note']; ?></textarea><br><?php echo $row_notes['modified']; ?></td>
				<td><input type="submit" name="button" value="Update"></td>
			</tr>
			</form>
		<?php } ?>
		<form action="notes.php?cid=<?php echo $cid; ?>&rec=insert" method="post">
		<tr<?php Stripe($rc); ?>>
			<td>
				<input type="hidden" name="contact_id" value="<?php echo $cid; ?>">
				<textarea name="note" rows="10" cols="30"></textarea></td>
			<td><input type="submit" name="button" value="Add"></td>
		</tr>
		</form>
	</table>
	
</body>
</html>