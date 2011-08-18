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

//initializing
extract($args[1]);

echo $sidebar['before_widget'];

if (isset($params['title']))
{
	echo $sidebar['before_title'].$params['title'].$sidebar['after_title'];
}

wp_login_form( $params );

echo $sidebar['after_widget'];
?>