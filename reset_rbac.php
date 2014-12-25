<?php

require_once('library.inc.php');

$rbac->enforce('modify_permission', $_SESSION['user']);

function tree($treeName, $children, $depth = 0) {
	global $rbac;
	if ($depth == 0) {
		echo "<tr><th>$treeName</th><td><div class=\"tree\">";
	}
	if (count($children) > 0) {
		echo '<ul>';
		foreach ($children as $child) {
			echo "<li><a href=\"#\" title=\"{$child['Description']}\">{$child['Title']}</a>";
			tree($treeName, $rbac->$treeName->children($child['ID']), $depth + 1);
			echo "</li>";
		}
		echo '</ul>';
	}
	if ($depth == 0) {
		echo '</div></td></tr>';
	}
}

?>
<html>
<head>
	<title>Reset RBAC</title>
	<link href="tree.css" rel="stylesheet" type="text/css" />
	<link href="stylesheet.css" rel="stylesheet" type="text/css" />
</head>
<body>
<script src="library.inc.js"></script>
<table>
<?php

/* back up currently set roles to be reapplied after the reset */
mysql_select_db ($GLOBALS['db']['db'], $GLOBALS['db']['link']);
$query = "SELECT * FROM phprbac_userroles";
$result = mysql_query ($query, $GLOBALS['db']['link']) or exit (mysql_error());
$user_roles = array();
while ($row = mysql_fetch_assoc($result)) {
	$user_roles[] = array($row['UserID'] => $rbac->Roles->getTitle($row['RoleID']));
}
mysql_free_result($result);

$rbac->reset(true);

/* user management */
$rbac->Permissions->addPath(
	'/promote_admin/assign_role/edit_user/edit_password/edit_self',
	array(
		'Promote other users to admin roles',
		'Add or remove other database user roles',
		'Create, edit, delete other database users',
		'Change other database user passwords',
		'Edit own username and password'
	)
);

$rbac->Permissions->add('modify_permission', 'Modify database permissions structure');

/* database visibility */
$rbac->Permissions->addPath(
	'/edit_contact/view_contact',
	array(
		'Create, edit, delete all contact information',
		'View all contact information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/view_address',
	array(
		null, null,
		'View contact address information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/view_phone',
	array(
		null, null,
		'View contact phone information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/view_email',
	array(
		null, null,
		'View contact email information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/view_url',
	array(
		null, null,
		'View contact URL information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/view_relationship',
	array(
		null, null,
		'View contact relationship and family information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/view_roster',
	array(
		null, null,
		'View contact roster information'
	)
);
$rbac->Permissions->addPath(
	'/edit_contact/view_contact/search_zip_code',
	array(
		null, null,
		'Search contacts by zip code'
	)
);


$rbac->Permissions->addPath(
	'/edit_finance/edit_payment/view_payment',
	array(
		'Create, edit, delete all financial information',
		'Create, edit, delete tuition payment information',
		'View payment information'
	)
);
$rbac->Permissions->addPath(
	'/edit_finance/edit_donation/view_donation',
	array(
		null,
		'Create, edit, delete donation information',
		'View donation information'
	)
);

$rbac->Permissions->addPath(
	'/edit_note/view_note',
	array(
		'Create, edit, delete contact notes',
		'View contact notes'
	)
);

tree('Permissions', array(array('ID'=>1,'Title' => $rbac->Permissions->getTitle(1), 'Description'=>$rbac->Permissions->getDescription(1))));

$role = $rbac->Roles->add('Admin', 'Full permissions to the entire database');
$rbac->assign($role, $rbac->Permissions->returnId('promote_admin'));
$rbac->assign($role, $rbac->Permissions->returnId('modify_permission'));

	$role = $rbac->Roles->add('Contact Manager', 'Update all contact information', $rbac->Roles->returnId('Admin'));
	$rbac->assign($role, $rbac->Permissions->returnId('edit_contact'));

		$role = $rbac->Roles->add('Group Leader', 'View all contact information', $rbac->Roles->returnId('Contact Manager'));
		$rbac->assign($role, $rbac->Permissions->returnId('view_contact'));

	$role = $rbac->Roles->add('Treasurer', 'Update all financial information', $rbac->Roles->returnId('Admin'));
	$rbac->assign($role, $rbac->Permissions->returnId('edit_finance'));

		$role = $rbac->Roles->add('Bursar', 'Update tuition payments information', $rbac->Roles->returnId('Treasurer'));
		$rbac->assign($role, $rbac->Permissions->returnId('edit_payment'));

		$role = $rbac->Roles->add('Advancement', 'Update donation information and contact notes', $rbac->Roles->returnId('Treasurer'));
		$rbac->assign($role, $rbac->Permissions->returnId('edit_donation'));
		$rbac->assign($role, $rbac->Permissions->returnId('edit_note'));
	
	
	$role = $rbac->Roles->add('Trustee', 'View all information, make contact notes', $rbac->Roles->returnId('Admin'));
	$rbac->assign($role, $rbac->Permissions->returnId('view_contact'));
	$rbac->assign($role, $rbac->Permissions->returnId('view_payment'));
	$rbac->assign($role, $rbac->Permissions->returnId('view_donation'));
	$rbac->assign($role, $rbac->Permissions->returnId('edit_note'));

	$role = $rbac->Roles->add('Database Admin', 'Full permissions to manage all permissions, roles and users in the database', $rbac->Roles->returnId('Admin'));
	$rbac->assign($role, $rbac->Permissions->returnId('promote_admin'));
	
		$role = $rbac->Roles->add('Database Manager', 'Add, edit, remove users and assign their roles in the database', $rbac->Roles->returnId('Database Admin');
		$role = $rbac->assign($role, $rbac->Permissions->returnId('assign_role'));

tree('Roles', array(array('ID'=>1, 'Title'=> $rbac->Roles->getTitle(1), 'Description' => $rbac->Roles->getDescription(1))));

echo '<tr><th>Users</th><td>';
/* restore previous user role assignments */
foreach ($user_roles as $user_role) {
	list($userId, $role) = each($user_role);
	echo "<p>User $userId is $role.</p>";
	$rbac->Users->assign($role, $userId);
}
echo '</td></tr>';

?>
</table>
<form action="index.php">
	<input type="submit" value="Return to Database" />
</form>
</body>
</html>