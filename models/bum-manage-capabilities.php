<?php
$roles = new WP_Roles();

$capabilities = array();
foreach( $roles->roles as $key => $value )
{
	foreach( $value['capabilities'] as $key_2 => $value_2 )
	{
		$role_tmp = array();
		foreach( $roles->roles as $key_3 => $value_3 )
			if( isset( $value_3['capabilities'][$key_2] ) )
				$role_tmp[$key_3] = $value_3['name'];
		
		$capabilities[$key_2] = $role_tmp;
	}
}

if( isset( $_GET['edit-cap'] ) )
{
	$edit = $_GET['edit-cap'];
	$action = 'edit-user-cap';
	$verb = 'Edit';
}
else
{
	$edit = '';
	$action = 'add-user-cap';
	$verb = 'Add New';
}
include $view;
?>