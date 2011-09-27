<?php
if( isset( $_GET['id'] ) )
{
	$id = $_GET['id'];
	$action = 'editrole';
	$verb = 'Save';
	
	$xml = new xmlDataManagement( 'roles' );
	$data = $xml->load_file();
	$data = $data->$id;
}
else
{
	$action = 'createrole';
	$verb = 'Add New';
}

include $view;
?>