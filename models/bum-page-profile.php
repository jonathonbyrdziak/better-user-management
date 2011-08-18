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
global $wp_http_referer, $errors, $user_id, $user_can_edit, 
$_wp_admin_css_colors, $super_admins;

$title = IS_PROFILE_PAGE ? __('Profile') : __('Edit User');
$profileuser = bum_get_user_to_edit($user_id);

require_once $view;