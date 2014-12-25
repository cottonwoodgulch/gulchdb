<?php

require_once('library.inc.php');

$rbac->enforce('modify_permission', $_SESSION['user']);
	
?>
<html>
	<head>
		<style>
			body {
				font-family:arial,verdana,tahoma;
				font-size:11px
			}
			
			h1 {
				padding-top:2em;
				padding-bottom:0;
				margin:0
			}
			
			/* http://thecodeplayer.com/walkthrough/css3-family-tree */
			* {
				margin:0;
				padding:0
			}
			
			.tree ul {
				padding-top:20px;
				position:relative;
				transition:all .5s;
				-webkit-transition:all .5s;
				-moz-transition:all .5s
			}
			
			.tree li {
				float:left;
				text-align:center;
				list-style-type:none;
				position:relative;
				padding:20px 5px 0;
				transition:all .5s;
				-webkit-transition:all .5s;
				-moz-transition:all .5s
			}
			
			/*We will use ::before and ::after to draw the connectors*/
			.tree li::before,.tree li::after {
				content:'';
				position:absolute;
				top:0;
				right:50%;
				border-top:1px solid #ccc;
				width:50%;
				height:20px
			}
			
			.tree li::after {
				right:auto;
				left:50%;
				border-left:1px solid #ccc
			}
			
			/*We need to remove left-right connectors from elements without 
			any siblings*/
			.tree li:only-child::after,.tree li:only-child::before {
				display:none
			}
			
			/*Remove space from the top of single children*/
			.tree li:only-child {
				padding-top:0
			}
			
			/*Remove left connector from first child and 
			right connector from last child*/
			.tree li:first-child::before,.tree li:last-child::after {
				border:0 none
			}
			
			/*Adding back the vertical connector to the last nodes*/
			.tree li:last-child::before {
				border-right:1px solid #ccc;
				border-radius:0 5px 0 0;
				-webkit-border-radius:0 5px 0 0;
				-moz-border-radius:0 5px 0 0
			}
			
			.tree li:first-child::after {
				border-radius:5px 0 0 0;
				-webkit-border-radius:5px 0 0 0;
				-moz-border-radius:5px 0 0
			}
			
			/*Time to add downward connectors from parents*/
			.tree ul ul::before {
				content:'';
				position:absolute;
				top:0;
				left:50%;
				border-left:1px solid #ccc;
				width:0;
				height:20px
			}
			
			.tree li a {
				border:1px solid #ccc;
				padding:5px 10px;
				text-decoration:none;
				color:#666;
				font-family:arial,verdana,tahoma;
				font-size:11px;
				display:inline-block;
				border-radius:5px;
				-webkit-border-radius:5px;
				-moz-border-radius:5px;
				transition:all .5s;
				-webkit-transition:all .5s;
				-moz-transition:all .5s
			}
			
			/*Time for some hover effects*/
			/*We will apply the hover effect the the lineage of the element also*/
			.tree li a:hover,.tree li a:hover+ul li a {
				background:#c8e4f8;
				color:#000;
				border:1px solid #94a0b4
			}
			
			/*Connector styles on hover*/
			.tree li a:hover+ul li::after,.tree li a:hover+ul li::before,.tree li a:hover+ul::before,.tree li a:hover+ul ul::before {
				border-color:#94a0b4
			}
			
			/*Thats all. I hope you enjoyed it.
			Thanks :)*/
		</style>
	<body>
<?php

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

function tree($treeName, $children, $depth = 0) {
	global $rbac;
	if ($depth == 0) {
		echo '<div class="tree">';
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
		echo '</div>';
	}
}
echo "<h1>Permissions</h1>";
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

echo '<br clear="all" /><h1>Roles</h1>';
tree('Roles', array(array('ID'=>1, 'Title'=> $rbac->Roles->getTitle(1), 'Description' => $rbac->Roles->getDescription(1))));
	
$rbac->Users->assign('Admin', 683);



?>
	</body>
</html>