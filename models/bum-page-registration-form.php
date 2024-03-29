<?php 
/**
 * @Author	Jonathon byrd
 * @link http://www.5twentystudios.com
 * @Package Wordpress
 * @SubPackage Better User Management
 * @Since 1.0.0
 * @copyright  Copyright (C) 2011 5Twenty Studios
 * 
 */

defined('ABSPATH') or die("Cannot access pages directly.");

if ( !get_option('users_can_register') ) {
	wp_redirect( bum_get_permalink_registration() );
	exit();
}

//initializing
$type = bum_get_registration_type();

if( $type )
{
	//get extra fields
	$fields = get_term_by( 'slug', $type, BUM_HIDDEN_FIELDS );
	
	require $view;
}
else
	echo '<h2>Registration of this user role is not allowed.</h2>';