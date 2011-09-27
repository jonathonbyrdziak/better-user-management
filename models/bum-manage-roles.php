<?php
global $wp_roles;

if( isset( $_GET['role-name'] ) )
{
	$edit = $_GET['role-name'];
	$action = 'edit-user-role';
	$verb = 'Edit';
}
else
{
	$edit = '';
	$action = 'add-user-role';
	$verb = 'Add New';
}

include $view;
?>