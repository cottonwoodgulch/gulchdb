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

/* database user permissions */
$rbac->Permissions->addPath(
	'/promote_admin/assign_role/edit_user/view_role/view_user/edit_self',
	array(
		'Promote other users to Administrator role',
		'Assign or unassign roles within the database to other users',
		'Create, edit, delete other database users',
		'View the roles assigned to other database users',
		'View user information of other database users (password obscured)',
		"Edit the user's own database user information"
	)
);
$rbac->Permissions->add('modify_permission', 'Modify database permissions structure');

/* contact information permissions */
$rbac->Permissions->addPath(
	'/edit_contact_information/search_zip_code/view_contact_information',
	array(
		'Create, edit, delete all contact information (individuals, addresses, phones, emails, URLs, rosters, relationships)',
		'Search for individuals in the database by zip code',
		'View all contact information (individuals, addresses, phones, emails, URLs, rosters, relationships)'
	)
);
$tables = array('contact','address','email','phone','relationship','roster','url');
foreach($tables as $table) {
	$rbac->Permissions->addPath(
		"/edit_contact_information/edit_$table/view_$table",
		array(
			null,
			"Create, edit, delete {$table}s",
			"View {$table}s"
		)
	);
}

/* financial information permissions */
$rbac->Permissions->addPath(
	'/edit_financial_information/view_financial_information',
	array(
		'Create, edit, delete all financial information (payments, donations)',
		'View all financial information (payments, donations)'
	)
);
$tables = array('payment','donation');
foreach($tables as $table) {
	$rbac->Permissions->addPath(
		"/edit_financial_information/edit_$table/view_$table",
		array(
			null,
			"Create, edit, delete {$table}s",
			"View {$table}s"
		)
	);
}

/* contact notes permissions */
$rbac->Permissions->addPath(
	'/edit_note/view_note',
	array(
		'Create, edit, delete contact notes',
		'View contact notes'
	)
);

tree('Permissions', array(array('ID'=>1,'Title' => $rbac->Permissions->getTitle(1), 'Description'=>$rbac->Permissions->getDescription(1))));

$rbac->Roles->addPath(
	'/Administrator/Database Manager',
	array(
		'Full permissions to the entire database',
		'Create, edit, delete users and assign their roles in the database'
	)
);

$role = $rbac->Roles->returnId('Administrator');
$rbac->assign($role, $rbac->Permissions->returnId('promote_admin'));
$rbac->assign($role, $rbac->Permissions->returnId('modify_permission'));

$role = $rbac->Roles->returnId('Database Manager');
$rbac->assign($role, $rbac->Permissions->returnId('assign_role'));

$rbac->Roles->addPath(
	'/Administrator/Contact Information Editor/Contact Information Viewer',
	array(
		null,
		'Create, edit, delete all contact information (individuals, addresses, emails, phones, relationships, rosters, urls)',
		'View all contact information (individuals, addresses, emails, phones, relationships, rosters, urls)'
	)
);
$role = $rbac->Roles->returnId('Contact Information Editor');
$rbac->assign($role, $rbac->Permissions->returnId('edit_contact_information'));

$role = $rbac->Roles->returnId('Contact Information Viewer');
$rbac->assign($role, $rbac->Permissions->returnId('view_contact_information'));
$rbac->assign($role, $rbac->Permissions->returnId('edit_self'));
/* assuming everything else is coded right, we can actually remove these next permissions, but -- since we can't have more than one parent of a particular permission in the hierarchy, it is safest to explicitly add these permissions */
$tables = array('contact','address','email','phone','relationship','roster','url');
foreach($tables as $table) {
	$rbac->assign($role, $rbac->Permissions->returnId("view_$table"));
}

$rbac->Roles->AddPath(
	'/Administrator/Advancement Information Editor/Financial Information Editor/Financial Information Viewer',
	array(
		null,
		'Create, edit, delete all advancement information (financial information and contact notes)',
		'Create, edit, delete all financial information (donations, payments)',
		'View all financial information (donations, payments)'
	)
);

$role = $rbac->Roles->returnId('Financial Information Editor');
$rbac->assign($role, $rbac->Permissions->returnId('edit_financial_information'));

$role = $rbac->Roles->returnId('Financial Information Viewer');
$rbac->assign($role, $rbac->Permissions->returnId('view_financial_information'));
$rbac->assign($role, $rbac->Permissions->returnId('edit_self'));
/* assuming everything else is coded right, we can actually remove these next permissions, but -- since we can't have more than one parent of a particular permission in the hierarchy, it is safest to explicitly add these permissions */
$tables = array('donation', 'payment');
foreach($tables as $table) {
	$rbac->assign($role, $rbac->Permissions->returnId("view_$table"));
}

$rbac->Roles->addPath(
	'/Administrator/Advancement Information Editor/Notes Editor/Notes Viewer',
	array(
		null, null,
		'Create, edit, delete all contact notes',
		'View all contact notes'
	)
);

$role = $rbac->Roles->returnId('Notes Editor');
$rbac->assign($role, $rbac->Permissions->returnId('edit_note'));

$role = $rbac->Roles->returnId('Notes Viewer');
$rbac->assign($role, $rbac->Permissions->returnId('view_note'));
$rbac->assign($role, $rbac->Permissions->returnId('edit_self'));

$rbac->Roles->addPath(
	'/Administrator/Advancement Information Editor/Advancement Information Viewer',
	array(
		null, null,
		'View all advancement information (financial information and contact notes)'
	)
);

$role = $rbac->Roles->returnId('Advancement Information Viewer');
$rbac->assign($role, $rbac->Permissions->returnId('view_financial_information'));
$rbac->assign($role, $rbac->Permissions->returnId('edit_self'));
/* assuming everything else is coded right, we can actually remove these next permissions, but -- since we can't have more than one parent of a particular permission in the hierarchy, it is safest to explicitly add these permissions */
$tables = array('donation', 'payment', 'note');
foreach($tables as $table) {
	$rbac->assign($role, $rbac->Permissions->returnId("view_$table"));
}

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