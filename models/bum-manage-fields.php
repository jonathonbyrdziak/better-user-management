<?php
$roles = new WP_Roles();

if( isset( $_GET['edit_id'] ) )
{
	$id = $_GET['edit_id'];
	$action = 'edit-user-field';
	$verb = 'Save';
}
else
{
	$action = 'add-user-field';
	$verb = 'Add New';
}

global $wp_user_fields;

include $view;
?>