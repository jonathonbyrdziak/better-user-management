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
bum_initialize();

