<?php

require_once('library.inc.php');

$rbac->enforce('modify_permission', $_SESSION['user']);

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

$role = $rbac->Roles->add('Admin', 'Full permissions to the entire database');
$rbac->assign($role, $rbac->Permissions->returnId('promote_admin'));
$rbac->assign($role, $rbac->Permissions->returnId('modify_permission');

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
	
$rbac->User->assign('Admin', 683);

?>