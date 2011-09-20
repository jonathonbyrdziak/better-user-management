<?php 
/**
 * @Author	Jonathon byrd
 * @link http://www.5twentystudios.com
 * @Package Wordpress
 * @SubPackage Better User Management
 * @Since 1.0.0
 * @copyright  Copyright (C) 2011 5Twenty Studios
 * 
 *  
 * Plugin Name: Better User Management
 * Plugin URI: http://www.5twentystudios.com
 * Description: This plugin offers better user management then what wordpress currently allows for. Right off the bat we theme the login and the registration pages and offer a few new widgets.
 * Version: 1.0.0
 * Author: 5Twenty Studios
 * Author URI: http://www.5twentystudios.com
 * 
 */

defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Access Control Level
 * 
 * Allows the developer to hook into this system and set the access level for this plugin.
 * If the user does not have the capability to view this plugin, they may still be
 * able to view the configurations area.
 * 
 */
defined("BUM_CAPABILITY") or define("BUM_CAPABILITY", "administrator");

/**
 * Job Post Type
 * 
 * I don't expect to have a lot of conflicts with the simple post type slug.
 * However, I never can know for sure, so here's the post type slug, you are welcome
 * to change this to whatever you would like.
 */
//defined("BUM_POST_TYPE") or define("BUM_POST_TYPE", "positions");

/**
 * Job Category Taxonomy Type
 * 
 * This is the taxonomy slug for the job categories. Same understandings apply as
 * above.
 */
//defined("BUM_CATEGORY_TAX") or define("BUM_CATEGORY_TAX", "job-categories");

/**
 * Initializing 
 * 
 * The directory separator is different between linux and microsoft servers.
 * Thankfully php sets the DIRECTORY_SEPARATOR constant so that we know what
 * to use.
 */
defined("DS") or define("DS", DIRECTORY_SEPARATOR);

/**
 * Initializing 
 * 
 * The directory separator is different between linux and microsoft servers.
 * Thankfully php sets the DIRECTORY_SEPARATOR constant so that we know what
 * to use.
 */
defined("BUM_VERSION") or define("BUM_VERSION", '1.0.0');

/**
 * Initialize Localization
 * 
 * @tutorial http://codex.wordpress.org/I18n_for_WordPress_Developers
 * function call loads the localization files from the current folder
 */
if (function_exists('load_theme_textdomain')) load_theme_textdomain('bum');

/**
 * Startup
 * 
 * This block of functions is only preloading a set of functions that I've prebuilt
 * and that I use throughout my websites.
 * 
 */
require_once dirname(__file__).DS."bootstrap.php";
require_once dirname(__file__).DS."register.php";
require_once dirname(__file__).DS."better-user-management.php";
/**
 * Initialize the Framework
 * 
 */
set_controller_path( dirname( __FILE__ ) );

//register assets
	wp_register_script( 'bum_js', plugin_dir_url(__file__).'js/bum.js', array('jquery'), BUM_VERSION, true);
	wp_register_style( 'bum_css', plugin_dir_url(__file__).'css/bum.css', array(), BUM_VERSION, 'all');
	
	wp_enqueue_script('bum_js');
	wp_enqueue_style('bum_css');
	
	//shortcodes
	add_shortcode('better_user_management', 'bum_pages_shortcode');
	
	if (plugin_basename(dirname(dirname(__file__))) != DS.WP_PLUGIN_DIR)
	{
		//parent plugin activation
		add_action('activate_'.plugin_basename(dirname(dirname(__file__))).DS.'index.php', 'bum_activate_plugin');
		add_action('deactivate_'.plugin_basename(dirname(dirname(__file__))).DS.'index.php', 'bum_deactivate_plugin');
	}
	else
	{
		//better user management activation
		add_action('activate_'.plugin_basename(dirname(__file__)).DS.'index.php', 'bum_activate_plugin');
		add_action('deactivate_'.plugin_basename(dirname(__file__)).DS.'index.php', 'bum_deactivate_plugin');
	}
	
	//administration area
	add_action('init', 'check_for_page_save');
	
	//initialization
	add_action('plugins_loaded', 'bum_acl');
	add_action('init', 'bum_show_ajax', 100);
	add_action('init', 'bum_user_roles');
	add_action('init', 'bum_login_redirect_original');
	add_action('wp', 'bum_init_page_login');
	add_action('wp', 'bum_init_page_profile');
	add_action('wp', 'bum_init_page_registration');
	add_action('show_user_profile', 'bum_display_custom_user_fields');
	add_action('bum_register_form', 'bum_do_registration_form');
	
	add_filter('bum_edit_user', 'bum_edit_user', 10);
	add_filter('bum_edit_user', 'bum_save_user_meta_data', 20);
	add_filter('site_url', 'bum_update_login_url', 20, 2);
	add_filter('wp_nav_menu', 'bum_wp_page_menu', 20, 2);
	add_filter('wp_page_menu', 'bum_wp_page_menu', 20, 2);
	add_filter('bum_menu_href', 'bum_check_menu_hrefs', 20, 2);
	add_filter('bum_menu_text', 'bum_check_menu_text', 20, 2);

	add_filter('bum-page-shortcode', 'bum_page_login', 20, 2);
	add_filter('bum-page-shortcode', 'bum_page_profile', 20, 2);
	add_filter('bum-page-shortcode', 'bum_page_registration', 20, 2);
	
	//widgets;
	add_action('widgets_init', 'init_registered_widgets', 1);
	
	//520 notifications
	add_action('admin_notices', 'bum_read_520_rss', 1);
	
	//////////////////////////////////////////////////
	//   WIDGETS
	register_multiwidget(array(
        'id' => 'bum-widget-login',
        'title' => 'job Login',
        'classname' => 'bum-widget-login',
        'show_view' => 'bum-widget-login',
        'fields' => array(
        array(
            'name' => 'Title',
            'id' => 'title',
            'type' => 'text',
            'std' => 'Login'
        ),
        array(
            'name' => 'Redirect To',
            'id' => 'redirect',
            'type' => 'text',
            'std' => get_bloginfo('url')
        ),
        array(
            'name' => 'Form ID',
            'id' => 'form_id',
            'type' => 'text',
            'std' => 'loginform'
        ),
        array(
            'name' => 'Username Label',
            'id' => 'label_username',
            'type' => 'text',
            'std' => __( 'Username' )
        ),
        array(
            'name' => 'Password Label',
            'id' => 'label_password',
            'type' => 'text',
            'std' => __( 'Password' )
        ),
        array(
            'name' => 'Remember Me Label',
            'id' => 'label_remember',
            'type' => 'text',
            'std' => __( 'Remember Me' )
        ),
        array(
            'name' => 'Submit Label',
            'id' => 'label_log_in',
            'type' => 'text',
            'std' => __( 'Log In' )
        ),
        array(
            'name' => 'Username ID',
            'id' => 'id_username',
            'type' => 'text',
            'std' => 'user_login'
        ),
        array(
            'name' => 'Password ID',
            'id' => 'id_password',
            'type' => 'text',
            'std' => 'user_pass'
        ),
        array(
            'name' => 'Remember Me ID',
            'id' => 'id_remember',
            'type' => 'text',
            'std' => 'rememberme'
        ),
        array(
            'name' => 'Submit ID',
            'id' => 'id_submit',
            'type' => 'text',
            'std' => 'wp-submit'
        ),
        array(
            'name' => 'Username Value',
            'id' => 'value_username',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Username Value',
            'id' => 'value_username',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Show Remember Me<br/>',
            'id' => 'remember',
            'type' => 'radio',
            'options' => array(
                array('name' => 'Visible', 'value' => '1'),
                array('name' => 'Hidden', 'value' => '0')
            )
        ),
        array(
            'name' => 'Default Remember Me<br/>',
            'id' => 'value_remember',
            'type' => 'radio',
            'options' => array(
                array('name' => 'Checked', 'value' => '1'),
                array('name' => 'Unchecked', 'value' => '0')
            )
        ),
        )
    ));