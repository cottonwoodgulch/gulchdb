<?php
	require_once('../library.inc.php');
	
	$rbac->enforce('assign_role', $_SESSION['user']);
	
	$cid = (isset ($_GET['cid']) ?
		$_GET['cid'] :
		exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));

	use PhpRbac\Rbac;
	$rbac = new Rbac();
		
	if (isset($_REQUEST['RoleID']) && isset($_REQUEST['toggle'])) {
		if ($_REQUEST['toggle'] == 'Add') {
			$rbac->Users->assign($_POST['RoleID'], $cid);
		} else if ($_REQUEST['toggle'] == 'Remove') {
			$rbac->Users->unassign($_POST['RoleID'], $cid);
		}
	}
	
	$roles = $rbac->Roles->descendants(1);
		
	mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
	$query_user_roles = "SELECT * FROM phprbac_userroles WHERE UserID = $cid";
	$user_roles_result = mysql_query ($query_user_roles, $GLOBALS['db']['link']) or exit (mysql_error());
	$user_roles = array();
	while ($row = mysql_fetch_assoc($user_roles_result)) {
		$user_roles[] = $row['RoleID'];
	}
	mysql_free_result($user_roles_result);
	
	$rc = 0; // row counter for stripes
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Database Roles</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../stylesheet.css" rel="stylesheet" type="text/css">
</head>

<body>
<script src="../library.inc.js"></script>

<table>
	<tr class="horizontal">
		<th>role</th>
		<th>description</th>
		<td></td>
	</tr>

<?php
foreach ($roles as $role) {
	$buttonName = 'Add';
	if (in_array($role['ID'], $user_roles)) {
		$buttonName = 'Remove';
	}
?>
	<tr<?php Stripe($rc); ?>>
		<td><?php echo $role['Title']; ?></td>
		<td><?php echo $role['Description']; ?></td>
		<td><form name="role-edit" method="post" action="database_roles.php?cid=<?php echo $cid; ?>"><input type="hidden" name="RoleID" value="<?php echo $role['ID']; ?>"/><input type="submit" name="toggle" value="<?php echo $buttonName; ?>"></form></td>
	</tr>
<?php }?>
</table>
</body>
</html>