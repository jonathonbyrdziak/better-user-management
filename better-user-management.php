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


/**
 * Constructor.
 * 
 * This function contains all of the pre-registration and hooks required
 * to get this plugin moving in the right direction.
 * 
 * @return null
 */
function bum_initialize()
{
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
	add_filter('site_url', 'bum_update_login_url', 20, 2);
	add_filter('wp_nav_menu', 'bum_wp_page_menu', 20, 2);
	add_filter('wp_page_menu', 'bum_wp_page_menu', 20, 2);
	add_filter('bum_menu_href', 'bum_check_menu_hrefs', 20, 2);
	add_filter('bum_menu_text', 'bum_check_menu_text', 20, 2);
	
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
    
    //Registration
    bum_register_user('player', 'Player', array( 'read', 'level_0' ));
    bum_register_user('cheerleader', 'Cheerleader', array( 'read', 'level_0' ));
    bum_register_user('fan', 'Fan', array( 'read', 'level_0' ));
    
}

/**
 * Function is responsible for remembering the user roles that
 * will be used for registration
 * 
 * @param unknown_type $id
 * @param unknown_type $name
 * @param unknown_type $capabilities
 */
function bum_register_user($id = null, $name = null, $capabilities =array( 'read', 'level_0' ) )
{
	//initializing
	static $users;
	
	if (!isset($users))
	{
		$users = array();
	}
	
	if (!is_null($id) && !isset($users[$id]))
	{
		$users[$id] = array(
			'name' => $name,
			'capabilities' => $capabilities,
		);
	}
	unset($users[0]);
	
	return $users;
}

/**
 * Function is responsible for adding in the new roles that
 * we'll be using for this job board
 * 
 */
function bum_user_roles()
{
	//initializing
	$users = bum_register_user();
	
	foreach ((array)$users as $id => $user)
	{
		add_role($id, $user['name'], $user['capabilities']);
	}
}

/**
 * Function is responsible for displaying the login page.
 *
 * @param unknown_type $config
 * @return unknown
 */
function bum_pages_shortcode( $config )
{
	//initializing
	global $bum_public_user;
	$defaults = array(
		'page' => 'Login'
	);
	$config = shortcode_atts($defaults, $config);
	$page = strtolower($config['page']);
	
	//calling an audible view
	if (isset($_GET['bump']))
	{
		$page .= '-form';
	}
	elseif (!is_null($bum_public_user))
	{
		$page .= '-public';
	}
	elseif ($page == 'profile' && isset($_GET['action']) && $_GET['action'] == 'edit')
	{
		$page .= '-edit';
	}
	
	return bum_show_view("bum-page-{$page}");
}

/**
 * Function is responsible for redirecting the user from the original 
 * login page, to our login page.
 */
function bum_login_redirect_original()
{
	//initializing
	$url = parse_url( bum_get_page_url() );
	
	//reasons to return
	if (substr($url['path'],-12) != 'wp-login.php') return false;
	
	wp_redirect( bum_get_permalink_login() );
	exit();
}

/**
 * Function is responsible for adjusting the login/out links
 * 
 * @param unknown_type $menu
 * @param unknown_type $args
 */
function bum_wp_page_menu( $menu, $args )
{
	//initializing
	$html = str_get_html($menu);
	
	if ($html->find('a', 0))
	{
		foreach ($html->find('a') as $a)
		{
			$a->href = apply_filters('bum_menu_href', $ahref = $a->href, $a->innertext);
			$a->innertext = apply_filters('bum_menu_text', $a->innertext, $ahref);
			
			//remove the registration page if logged in
			if (is_user_logged_in() && (string)$a->href == bum_get_permalink_registration())
			{
				$parent = $a->parent();
				$parent->outertext = '';
			}
			
			//remove the profile page if not logged in
			elseif (!is_user_logged_in() && (string)$a->href == bum_get_permalink_profile())
			{
				$parent = $a->parent();
				$parent->outertext = '';
			}
		}
	}
	
	return (string)$html;
}

/**
 * Function is responsible for changing the hrefs
 * 
 * @param unknown_type $href
 * @param unknown_type $text
 */
function bum_check_menu_hrefs( $href, $text )
{
	//initializing
	$parts = parse_url($href);
	
	if (is_user_logged_in())
	{
		// this is the login page
		if (bum_get_permalink_login() == $href)
		{
			$href = bum_get_permalink_login('action=logout');
		}
	}
	
	return $href;
}

/**
 * Function is responsible for changing the menun item texts
 * 
 * @param unknown_type $text
 * @param unknown_type $href
 */
function bum_check_menu_text( $text, $href )
{
	if (is_user_logged_in())
	{
		//this is the login page
		if (bum_get_permalink_login() == $href)
		{
			$text = 'Logout';
		}
	}
		
	return $text;
}

/**
 * Since we're using this value to register users, we don't want
 * the possibility for users to change it to something other then
 * our specific user types
 * 
 * @return string|string
 */
function bum_get_registration_type()
{
	//initializing
	static $type;
	$users = (array)bum_register_user();
	$users = array_keys($users);
	
	//reasons to return
	if (!isset($_REQUEST['bump'])) return false;
	
	if (!isset($type) && in_array($_REQUEST['bump'], $users))
	{
		$type = $_REQUEST['bump'];
	}
	
	return $type;
}

/**
 * Function is responsible for returning the necessary separator
 */
function bum_get_query_separator()
{
	//initializing
	global $wp_rewrite;
	$s = ($wp_rewrite->using_permalinks())? "?" :"&" ;
	return $s;
}

/**
 * Function is responsible for updating the login url
 *
 * @param unknown_type $login_url
 * @return unknown
 */
function bum_update_login_url( $url, $path )
{
	if (substr($path,0,12) == 'wp-login.php')
	{
		$parts = parse_url($url);
		return bum_get_permalink_login( $parts['query'] );
	}
	/*
	//@FIX ME this is not equaling
	if ($url == bum_get_permalink_login('action=register'))
	{
		$url = bum_get_permalink_registration();
	}
	*/
	return $url;
}

/**
 * function is responsible for loading the ACL constants
 *
 */
function bum_acl()
{
	defined("BUM_CURRENT_USER_CAN") or define("BUM_CURRENT_USER_CAN", (current_user_can(BUM_CAPABILITY)));
	defined("BUM_CURRENT_USER_CANNOT") or define("BUM_CURRENT_USER_CANNOT", (!BUM_CURRENT_USER_CAN) );
}

/**
 * Function is called upon activation
 *
 * @return null
 */
function bum_activate_plugin()
{
	//creating the pages
	bum_get_page_login();
	bum_get_page_profile();
	bum_get_page_registration();
}

/**
 * Function is called upon de-activation
 *
 * @return null
 */
function bum_deactivate_plugin()
{
	
}

/**
 * Function is responsible for making sure that the
 * @param unknown_type $query
 */
function bum_clean_querystring( $query )
{
	//reasons to fail
	if (!$query) return '';
	
	//cleaning off the starter
	if (substr($query,0,1) == '?')
	{
		$query = substr($query,1);
	}
	if (substr($query,0,1) == '&')
	{
		$query = substr($query,1);
	}
	
	//initializing
	$querystring = bum_get_query_separator();
	$parts = explode('&', $query);
	
	foreach((array)$parts as $keyvalue)
	{
		//reasons to continue;
		if (empty($keyvalue)) continue;
		
		list($key, $value) = explode('=', $keyvalue);
		
		//reasons to continue;
		if (empty($key) || empty($value)) continue;
		
		$querystring .="{$key}={$value}";
	}
	
	return $querystring;
}

/**
 * Function is responsible for locating the Login page and returning its object
 *
 * @return unknown
 */
function bum_get_page_login()
{
	return bum_get_page('Login');
}

/**
 * Function is responsible for returning the permalink for the given page
 *
 * @return unknown
 */
function bum_get_permalink_login( $query = '' )
{
	//initializing
	$query = bum_clean_querystring($query);
		
	return get_permalink( bum_get_page_login() ).$query;
}

/**
 * Function is responsible for locating the Registration page and returning its object
 *
 * @return unknown
 */
function bum_get_page_registration()
{
	return bum_get_page('Registration');
}

/**
 * Function is responsible for returning the permalink for the given page
 *
 * @return unknown
 */
function bum_get_permalink_registration( $query = '' )
{
	//initializing
	$query = bum_clean_querystring($query);
		
	return get_permalink( bum_get_page_registration() ).$query;
}

/**
 * Function is responsible for locating the Login page and returning its object
 *
 * @return unknown
 */
function bum_get_page_profile()
{
	return bum_get_page('Profile');
}

/**
 * Function is responsible for returning the permalink for the given page
 *
 * @return unknown
 */
function bum_get_permalink_profile( $query = '' )
{
	//initializing
	$query = bum_clean_querystring($query);
		
	return get_permalink( bum_get_page_profile() ).$query;
}

/**
 * Function is responsible for locating the requested page and returning its ID
 *
 * @param unknown_type $title
 * @return unknown
 */
function bum_get_page( $title = 'Login' )
{
	//save some time
	static $pages;
	
	if (!isset($pages))
	{
		$pages = array();
	}
	
	if (!isset($pages[$title]))
	{
		//initializing
		$slug = bum_slug_it( $title );
		$query = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => "bum_page_$slug",
					'value' => '',
					'compare' => '!='
				),
			)
		);
		
		$dum_loop = new WP_Query;
		$dum_loop->query( $query );
		
		if (!$dum_loop->post_count)
		{
			$post = array();
			$post['post_type'] = 'page';
			$post['post_title'] = $title;
			$post['post_name'] = $slug;
			$post['post_status'] = 'publish';
			$post['post_content'] = "[better_user_management page=\"$title\"]";
			
			$pages[$title] = wp_insert_post($post);
			add_post_meta( $pages[$title], "bum_page_$slug", 'true', true );
		}
		else
		{
			$pages[$title] = $dum_loop->posts[0]->ID;
		}
	}
	
	return $pages[$title];
}

/**
 * Function is responsible for determining if we are on a specific page
 *
 * @param unknown_type $page
 */
function bum_is_page( $page = 'Login' )
{
	//initializing
	global $wp_query;
	
	//reasons to fail
	if ( is_admin() ) return false;
	if ( !$wp_query->is_page ) return false;
	
	//initializing
	$page_id = bum_get_page($page);
	$current_id = $wp_query->get_queried_object_id();
	
	//reasons to fail
	if ( $page_id != $current_id ) return false;
	
	return true;
}

/**
 * Function is responsible for turning something into a slug
 *
 * @param unknown_type $string
 * @return unknown
 */
function bum_slug_it( $string )
{
	$slug = strtolower(str_replace(" ", "-", preg_replace("/[^a-zA-Z0-9 ]/", "", $string)));
	return $slug;
}

/**
 * Function is responsible for preparing the profile page.
 */
function bum_init_page_profile()
{
	//reasons to return
	if (!bum_is_page('Profile')) return false;
	
	//initializing
	define('IS_PROFILE_PAGE', true);
	wp_enqueue_script('user-profile');
	
	global $wp_http_referer, $errors, $user_can_edit, $bum_public_user, $user_id, $_wp_admin_css_colors, $super_admins;
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	
	$action = (isset($_REQUEST['action']))? $_REQUEST['action'] :'view';
	$wp_http_referer = remove_query_arg(array('update', 'delete_count'), stripslashes($wp_http_referer));
		
	$all_post_caps = array('posts', 'pages');
	$user_can_edit = false;
	foreach ( $all_post_caps as $post_cap )
		$user_can_edit |= current_user_can("edit_$post_cap");
		
	//if the user is not logged in, does not have rights
	if (isset($_REQUEST['bumu']) && !empty($_REQUEST['bumu']))
	{
		$bum_public_user = get_userdata( $_REQUEST['bumu'] );
		$user_id = $bum_public_user->ID;
	}
	
	elseif ($action == 'view'){ }
	elseif ($action == 'edit'){ }
	
	//this area allows users to update profile information
	//so naturally the user needs to be logged in first
	//the user should also have editing capabilities
	elseif ($action == 'update' && current_user_can('edit_user', $user_id))
	{
		
		if ( IS_PROFILE_PAGE )
			do_action('personal_options_update', $user_id);
		else
			do_action('edit_user_profile_update', $user_id);
		
		if ( !is_multisite() ) {
			$errors = bum_edit_user($user_id);
		} else {
			$user = get_userdata( $user_id );
			
			// Update the email address in signups, if present.
			if ( $user->user_login && isset( $_POST[ 'email' ] ) && is_email( $_POST[ 'email' ] ) && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $user->user_login ) ) )
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST[ 'email' ], $user_login ) );
			
			// WPMU must delete the user from the current blog if WP added him after editing.
			$delete_role = false;
			$blog_prefix = $wpdb->get_blog_prefix();
			if ( $user_id != $current_user->ID ) {
				$cap = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = '{$user_id}' AND meta_key = '{$blog_prefix}capabilities' AND meta_value = 'a:0:{}'" );
				if ( !is_network_admin() && null == $cap && $_POST[ 'role' ] == '' ) {
					$_POST[ 'role' ] = 'contributor';
					$delete_role = true;
				}
			}
			if ( !isset( $errors ) || ( isset( $errors ) && is_object( $errors ) && false == $errors->get_error_codes() ) )
				$errors = bum_edit_user($user_id);
			if ( $delete_role ) // stops users being added to current blog when they are edited
				delete_user_meta( $user_id, $blog_prefix . 'capabilities' );
		
			if ( is_multisite() && is_network_admin() && !IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && !isset($super_admins) && empty( $_POST['super_admin'] ) == is_super_admin( $user_id ) )
				empty( $_POST['super_admin'] ) ? revoke_super_admin( $user_id ) : grant_super_admin( $user_id );
		}
		
		if ( !is_wp_error( $errors ) ) {
			$redirect = (IS_PROFILE_PAGE ? bum_get_permalink_profile().'?' : "user-edit.php?user_id=$user_id&"). "updated=true";
			if ( $wp_http_referer )
				$redirect = add_query_arg('wp_http_referer', urlencode($wp_http_referer), $redirect);
			wp_redirect($redirect);
			exit;
		}
	}
	
	//if the user is not logged in, does not have rights
	//and is not looking at another users profile, then
	//they need to leave.
	else
	{
		wp_redirect( bum_get_permalink_login() );
		exit();
	}
	
}

/**
 * Edit user settings based on contents of $_POST
 *
 * Used on user-edit.php and bum_get_permalink_profile() to manage and process user options, passwords etc.
 *
 * @since 2.0
 *
 * @param int $user_id Optional. User ID.
 * @return int user id of the updated user
 */
function bum_edit_user( $user_id = 0 ) 
{
	global $wp_roles, $wpdb;
	$user = new stdClass;
	if ( $user_id ) {
		$update = true;
		$user->ID = (int) $user_id;
		$userdata = get_userdata( $user_id );
		$user->user_login = $wpdb->escape( $userdata->user_login );
	} else {
		$update = false;
	}

	if ( !$update && isset( $_POST['user_login'] ) )
		$user->user_login = sanitize_user($_POST['user_login'], true);

	$pass1 = $pass2 = '';
	if ( isset( $_POST['pass1'] ))
		$pass1 = $_POST['pass1'];
	if ( isset( $_POST['pass2'] ))
		$pass2 = $_POST['pass2'];

	if ( isset( $_POST['role'] ) && current_user_can( 'edit_users' ) ) {
		$new_role = sanitize_text_field( $_POST['role'] );
		$potential_role = isset($wp_roles->role_objects[$new_role]) ? $wp_roles->role_objects[$new_role] : false;
		// Don't let anyone with 'edit_users' (admins) edit their own role to something without it.
		// Multisite super admins can freely edit their blog roles -- they possess all caps.
		if ( ( is_multisite() && current_user_can( 'manage_sites' ) ) || $user_id != get_current_user_id() || ($potential_role && $potential_role->has_cap( 'edit_users' ) ) )
			$user->role = $new_role;

		// If the new role isn't editable by the logged-in user die with error
		$editable_roles = get_editable_roles();
		if ( ! empty( $new_role ) && empty( $editable_roles[$new_role] ) )
			wp_die(__('You can&#8217;t give users that role.'));
	}

	if ( isset( $_POST['email'] ))
		$user->user_email = sanitize_text_field( $_POST['email'] );
	if ( isset( $_POST['url'] ) ) {
		if ( empty ( $_POST['url'] ) || $_POST['url'] == 'http://' ) {
			$user->user_url = '';
		} else {
			$user->user_url = esc_url_raw( $_POST['url'] );
			$user->user_url = preg_match('/^(https?|ftps?|mailto|news|irc|gopher|nntp|feed|telnet):/is', $user->user_url) ? $user->user_url : 'http://'.$user->user_url;
		}
	}
	if ( isset( $_POST['first_name'] ) )
		$user->first_name = sanitize_text_field( $_POST['first_name'] );
	if ( isset( $_POST['last_name'] ) )
		$user->last_name = sanitize_text_field( $_POST['last_name'] );
	if ( isset( $_POST['nickname'] ) )
		$user->nickname = sanitize_text_field( $_POST['nickname'] );
	if ( isset( $_POST['display_name'] ) )
		$user->display_name = sanitize_text_field( $_POST['display_name'] );

	if ( isset( $_POST['description'] ) )
		$user->description = trim( $_POST['description'] );

	foreach ( _wp_get_user_contactmethods( $user ) as $method => $name ) {
		if ( isset( $_POST[$method] ))
			$user->$method = sanitize_text_field( $_POST[$method] );
	}

	if ( $update ) {
		$user->rich_editing = isset( $_POST['rich_editing'] ) && 'false' == $_POST['rich_editing'] ? 'false' : 'true';
		$user->admin_color = isset( $_POST['admin_color'] ) ? sanitize_text_field( $_POST['admin_color'] ) : 'fresh';
		$user->show_admin_bar_front = isset( $_POST['admin_bar_front'] ) ? 'true' : 'false';
		$user->show_admin_bar_admin = isset( $_POST['admin_bar_admin'] ) ? 'true' : 'false';
	}

	$user->comment_shortcuts = isset( $_POST['comment_shortcuts'] ) && 'true' == $_POST['comment_shortcuts'] ? 'true' : '';

	$user->use_ssl = 0;
	if ( !empty($_POST['use_ssl']) )
		$user->use_ssl = 1;

	$errors = new WP_Error();

	/* checking that username has been typed */
	if ( $user->user_login == '' )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.' ));

	/* checking the password has been typed twice */
	do_action_ref_array( 'check_passwords', array ( $user->user_login, & $pass1, & $pass2 ));

	if ( $update ) {
		if ( empty($pass1) && !empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.' ), array( 'form-field' => 'pass1' ) );
		elseif ( !empty($pass1) && empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.' ), array( 'form-field' => 'pass2' ) );
	} else {
		if ( empty($pass1) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter your password.' ), array( 'form-field' => 'pass1' ) );
		elseif ( empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter your password twice.' ), array( 'form-field' => 'pass2' ) );
	}

	/* Check for "\" in password */
	if ( false !== strpos( stripslashes($pass1), "\\" ) )
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".' ), array( 'form-field' => 'pass1' ) );

	/* checking the password has been typed twice the same */
	if ( $pass1 != $pass2 )
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in the two password fields.' ), array( 'form-field' => 'pass1' ) );

	if ( !empty( $pass1 ) )
		$user->user_pass = $pass1;

	if ( !$update && isset( $_POST['user_login'] ) && !validate_username( $_POST['user_login'] ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ));

	if ( !$update && username_exists( $user->user_login ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ));

	/* checking e-mail address */
	if ( empty( $user->user_email ) ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an e-mail address.' ), array( 'form-field' => 'email' ) );
	} elseif ( !is_email( $user->user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The e-mail address isn&#8217;t correct.' ), array( 'form-field' => 'email' ) );
	} elseif ( ( $owner_id = email_exists($user->user_email) ) && ( !$update || ( $owner_id != $user->ID ) ) ) {
		$errors->add( 'email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'), array( 'form-field' => 'email' ) );
	}

	// Allow plugins to return their own errors.
	do_action_ref_array('user_profile_update_errors', array ( &$errors, $update, &$user ) );

	if ( $errors->get_error_codes() )
		return $errors;

	if ( $update ) {
		$user_id = wp_update_user( get_object_vars( $user ) );
	} else {
		$user_id = wp_insert_user( get_object_vars( $user ) );
		wp_new_user_notification( $user_id, isset($_POST['send_password']) ? $pass1 : '' );
	}
	return $user_id;
}

/**
 * Retrieve user data and filter it.
 *
 * @since 2.0.5
 *
 * @param int $user_id User ID.
 * @return object WP_User object with user data.
 */
function bum_get_user_to_edit( $user_id )
{
	$user = new WP_User( $user_id );
	
	$user_contactmethods = _wp_get_user_contactmethods( $user );
	foreach ($user_contactmethods as $method => $name) {
		if ( empty( $user->{$method} ) )
			$user->{$method} = '';
	}
	
	if ( empty($user->description) )
		$user->description = '';
	
	$user = sanitize_user_object($user, 'edit');
	
	return $user;
}

/**
 * Echos a submit button, with provided text and appropriate class
 *
 * @since 3.1.0
 *
 * @param string $text The text of the button (defaults to 'Save Changes')
 * @param string $type The type of button. One of: primary, secondary, delete
 * @param string $name The HTML name of the submit button. Defaults to "submit". If no id attribute
 *               is given in $other_attributes below, $name will be used as the button's id.
 * @param bool $wrap True if the output button should be wrapped in a paragraph tag,
 * 			   false otherwise. Defaults to true
 * @param array|string $other_attributes Other attributes that should be output with the button,
 *                     mapping attributes to their values, such as array( 'tabindex' => '1' ).
 *                     These attributes will be ouput as attribute="value", such as tabindex="1".
 *                     Defaults to no other attributes. Other attributes can also be provided as a
 *                     string such as 'tabindex="1"', though the array format is typically cleaner.
 */
function bum_submit_button( $text = NULL, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = NULL ) {
	echo bum_get_submit_button( $text, $type, $name, $wrap, $other_attributes );
}


/**
 * Returns a submit button, with provided text and appropriate class
 *
 * @since 3.1.0
 *
 * @param string $text The text of the button (defaults to 'Save Changes')
 * @param string $type The type of button. One of: primary, secondary, delete
 * @param string $name The HTML name of the submit button. Defaults to "submit". If no id attribute
 *               is given in $other_attributes below, $name will be used as the button's id.
 * @param bool $wrap True if the output button should be wrapped in a paragraph tag,
 * 			   false otherwise. Defaults to true
 * @param array|string $other_attributes Other attributes that should be output with the button,
 *                     mapping attributes to their values, such as array( 'tabindex' => '1' ).
 *                     These attributes will be ouput as attribute="value", such as tabindex="1".
 *                     Defaults to no other attributes. Other attributes can also be provided as a
 *                     string such as 'tabindex="1"', though the array format is typically cleaner.
 */
function bum_get_submit_button( $text = NULL, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = NULL ) {
	switch ( $type ) :
		case 'primary' :
		case 'secondary' :
			$class = 'button-' . $type;
			break;
		case 'delete' :
			$class = 'button-secondary delete';
			break;
		default :
			$class = $type; // Custom cases can just pass in the classes they want to be used
	endswitch;
	$text = ( NULL == $text ) ? __( 'Save Changes' ) : $text;

	// Default the id attribute to $name unless an id was specifically provided in $other_attributes
	$id = $name;
	if ( is_array( $other_attributes ) && isset( $other_attributes['id'] ) ) {
		$id = $other_attributes['id'];
		unset( $other_attributes['id'] );
	}

	$attributes = '';
	if ( is_array( $other_attributes ) ) {
		foreach ( $other_attributes as $attribute => $value ) {
			$attributes .= $attribute . '="' . esc_attr( $value ) . '" '; // Trailing space is important
		}
	} else if ( !empty( $other_attributes ) ) { // Attributes provided as a string
		$attributes = $other_attributes;
	}

	$button = '<input type="submit" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="' . esc_attr( $class );
	$button	.= '" value="' . esc_attr( $text ) . '" ' . $attributes . ' />';

	if ( $wrap ) {
		$button = '<p class="submit">' . $button . '</p>';
	}

	return $button;
}

/**
 * Resets global variables based on $_GET and $_POST
 *
 * This function resets global variables based on the names passed
 * in the $vars array to the value of $_POST[$var] or $_GET[$var] or ''
 * if neither is defined.
 *
 * @since 2.0.0
 *
 * @param array $vars An array of globals to reset.
 */
function bum_reset_vars( $vars ) {
	for ( $i=0; $i<count( $vars ); $i += 1 ) {
		$var = $vars[$i];
		global $$var;

		if ( empty( $_POST[$var] ) ) {
			if ( empty( $_GET[$var] ) )
				$$var = '';
			else
				$$var = $_GET[$var];
		} else {
			$$var = $_POST[$var];
		}
	}
}


/**
 * Function is responsible for managing the user registrations
 * 
 * @return string
 */
function bum_init_page_registration()
{
	//reasons to return
	if (!bum_is_page('Registration')) return false;
	
	if ( is_multisite() ) {
		// Multisite uses wp-signup.php
		wp_redirect( apply_filters( 'wp_signup_location', site_url('wp-signup.php') ) );
		exit;
	}
	
	if (is_user_logged_in())
	{
		wp_redirect( bum_get_permalink_profile() );
		exit();
	}
	
	//initializing
	global $bum_errors;
	
	if ( !empty($_POST) )
	{
		$role = bum_get_registration_type();
		$bum_errors = bum_register_new_user($_POST['user_login'], $_POST['user_email'], $role);
	}
	
}

/**
 * Function is responsible for initializing the login page
 *
 */
function bum_init_page_login()
{
	//reasons to return
	if (!bum_is_page('Login')) return false;
	
	// Redirect to https login if forced to use SSL
	if ( force_ssl_admin() && !is_ssl() ) {
		if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
			wp_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
			exit();
		} else {
			wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit();
		}
	}
	
	// Don't index any of these forms
	add_filter( 'pre_option_blog_public', '__return_zero' );
	add_action( 'login_head', 'noindex' );
	
	
	//initializing
	global $bum_action, $bum_errors, $bum_redirect_to, $bum_user, $bum_http_post, 
	$bum_secure_cookie, $bum_interim_login, $bum_reauth, $bum_rememberme, 
	$bum_messages_txt, $bum_errors_txt;
	
	$bum_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
	$bum_errors = new WP_Error();
	
	if ( isset($_GET['key']) )
		$bum_action = 'resetpass';
	
	// validate action so as to default to the login screen
	if ( !in_array($bum_action, array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login'), true) && false === has_filter('login_form_' . $bum_action) )
		$bum_action = 'login';
	
	nocache_headers();
	
	header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));
	
	if ( defined('RELOCATE') ) { // Move flag is set
		if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
			$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );
	
		$schema = is_ssl() ? 'https://' : 'http://';
		if ( dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) != get_option('siteurl') )
			update_option('siteurl', dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) );
	}
	
	//Set a cookie now to see if they are supported by the browser.
	setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
	if ( SITECOOKIEPATH != COOKIEPATH )
		setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
	
	// allow plugins to override the default actions, and to add extra actions if they want
	do_action( 'login_init' );
	do_action( 'login_form_' . $bum_action );
	
	$bum_http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	switch ($bum_action) {
	
	case 'logout' :
		
		//check_admin_referer('log-out');
		wp_logout();
	
		$bum_redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : bum_get_permalink_login().'?loggedout=true';
		wp_safe_redirect( $bum_redirect_to );
		exit();
		
	break;
	
	case 'lostpassword' :
	case 'retrievepassword' :
	
		if ( $bum_http_post ) {
			$bum_errors = bum_retrieve_password();
			if ( !is_wp_error($bum_errors) ) {
				$bum_redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : bum_get_permalink_login().'?checkemail=confirm';
				wp_safe_redirect( $bum_redirect_to );
				exit();
			}
		}
		
		if ( isset($_GET['error']) && 'invalidkey' == $_GET['error'] ) $bum_errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
		$bum_redirect_to = apply_filters( 'lostpassword_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
		
		do_action('lost_password');
		
	break;
	
	case 'resetpass' :
	case 'rp' :
		
		$bum_user = bum_check_password_reset_key($_GET['key'], $_GET['login']);
	
		if ( is_wp_error($bum_user) ) {
			wp_redirect( bum_get_permalink_login().'?action=lostpassword&error=invalidkey' );
			exit;
		}
	
		$bum_errors = '';
	
		if ( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] ) {
			$bum_errors = new WP_Error('password_reset_mismatch', __('The passwords do not match.'));
		} elseif ( isset($_POST['pass1']) && !empty($_POST['pass1']) ) {
			bum_reset_password($bum_user, $_POST['pass1']);
			exit;
		}
	
		wp_enqueue_script('utils');
		wp_enqueue_script('user-profile');
		
	break;
	
	case 'register' :
		wp_redirect( bum_get_permalink_registration() );
		exit;
		
	break;
	
	case 'login' :
	default:
		
		//redirect if logged in
		if (is_user_logged_in())
		{
			wp_redirect( get_bloginfo('url') );
			exit();
		}
	
		$bum_secure_cookie = '';
		$bum_interim_login = isset($_REQUEST['interim-login']);
	
		// If the user wants ssl but the session is not ssl, force a secure cookie.
		if ( !empty($_POST['log']) && !force_ssl_admin() ) {
			$bum_user_name = sanitize_user($_POST['log']);
			if ( $bum_user = get_userdatabylogin($bum_user_name) ) {
				if ( get_user_option('use_ssl', $bum_user->ID) ) {
					$bum_secure_cookie = true;
					force_ssl_admin(true);
				}
			}
		}
	
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$bum_redirect_to = $_REQUEST['redirect_to'];
			// Redirect to https if user wants ssl
			if ( $bum_secure_cookie && false !== strpos($bum_redirect_to, 'wp-admin') )
				$bum_redirect_to = preg_replace('|^http://|', 'https://', $bum_redirect_to);
		} else {
			$bum_redirect_to = admin_url();
		}
	
		$bum_reauth = empty($_REQUEST['reauth']) ? false : true;
	
		// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
		// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
		// the admin via http or https.
		if ( !$bum_secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($bum_redirect_to, 'https') ) && ( 0 === strpos($bum_redirect_to, 'http') ) )
			$bum_secure_cookie = false;
		
		$bum_user = wp_signon('', $bum_secure_cookie);
		
		$bum_redirect_to = apply_filters('login_redirect', $bum_redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $bum_user);
		
		if ( !is_wp_error($bum_user) && !$bum_reauth ) {
			
			if ( ( empty( $bum_redirect_to ) || $bum_redirect_to == 'wp-admin/' || $bum_redirect_to == admin_url() ) ) {
				// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
				if ( is_multisite() && !get_active_blog_for_user($bum_user->id) )
					$bum_redirect_to = user_admin_url();
				elseif ( is_multisite() && !$bum_user->has_cap('read') )
					$bum_redirect_to = get_dashboard_url( $bum_user->id );
				elseif ( !$bum_user->has_cap('edit_posts') )
					$bum_redirect_to = bum_get_permalink_profile();
			}
			wp_safe_redirect($bum_redirect_to);
			exit();
		}
	
		$bum_errors = $bum_user;
		// Clear errors if loggedout is set.
		if ( !empty($_GET['loggedout']) || $bum_reauth )
			$bum_errors = new WP_Error();
	
		// If cookies are disabled we can't log in even with a valid user+pass
		if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
			$bum_errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
		
		// Some parts of this script use the main login form to display a message
		if		( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )
			$bum_errors->add('loggedout', __('You are now logged out.'), 'message');
		elseif	( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )
			$bum_errors->add('registerdisabled', __('User registration is currently not allowed.'));
		elseif	( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )
			$bum_errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');
		elseif	( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )
			$bum_errors->add('newpass', __('Check your e-mail for your new password.'), 'message');
		elseif	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )
			$bum_errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');
		elseif	( $bum_interim_login )
			$bum_errors->add('expired', __('Your session has expired. Please log-in again.'), 'message');
		
		// Clear any stale cookies.
		if ( $bum_reauth )
			wp_clear_auth_cookie();
		
		if ( isset($_POST['log']) )
			$bum_user_login = ( 'incorrect_password' == $bum_errors->get_error_code() || 'empty_password' == $bum_errors->get_error_code() ) ? esc_attr(stripslashes($_POST['log'])) : '';
		$bum_rememberme = ! empty( $_POST['rememberme'] );
		
	break;
	}
		
	
	if ( $bum_errors->get_error_code() )
	{
		$bum_errors_txt = '';
		$bum_messages_txt = '';
		foreach ( $bum_errors->get_error_codes() as $code ) {
			$bum_severity = $bum_errors->get_error_data($code);
			foreach ( $bum_errors->get_error_messages($code) as $error ) {
				if ( 'message' == $bum_severity )
					$bum_messages_txt .= '	' . $error . "<br />\n";
				else
					$bum_errors_txt .= '	' . $error . "<br />\n";
			}
		}
	}
	
}

/**
 * Handles sending password retrieval email to user.
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function bum_retrieve_password() {
	global $wpdb, $current_site;

	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.'));

	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by_email(trim($_POST['user_login']));
		if ( empty($user_data) )
			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_userdatabylogin($login);
	}

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
		return $errors;
	}

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )
		return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
	else if ( is_wp_error($allow) )
		return $allow;

	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
	if ( empty($key) ) {
		// Generate something random for a key...
		$key = wp_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);
		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
	}
	$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
	$message .= network_site_url() . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Password Reset'), $blogname );

	$title = apply_filters('retrieve_password_title', $title);
	$message = apply_filters('retrieve_password_message', $message, $key);

	if ( $message && !wp_mail($user_email, $title, $message) )
		wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

	return true;
}

/**
 * Retrieves a user row based on password reset key and login
 * 
 * @uses $wpdb WordPress Database object
 * 
 * @param string $key Hash to validate sending user's password
 * @param string $login The user login
 * 
 * @return object|WP_Error
 */
function bum_check_password_reset_key($key, $login) {
	global $wpdb;

	$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new WP_Error('invalid_key', __('Invalid key'));

	if ( empty($login) || !is_string($login) )
		return new WP_Error('invalid_key', __('Invalid key'));

	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

	if ( empty( $user ) )
		return new WP_Error('invalid_key', __('Invalid key'));

	return $user;
}

/**
 * Handles resetting the user's password.
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 */
function bum_reset_password($user, $new_pass) {
	do_action('password_reset', $user, $new_pass);

	wp_set_password($new_pass, $user->ID);

	wp_password_change_notification($user);
}

/**
 * Handles registering a new user.
 *
 * @param string $user_login User's username for logging in
 * @param string $user_email User's email address to send password and add
 * @return int|WP_Error Either user's ID or error on failure.
 */
function bum_register_new_user( $user_login, $user_email, $role = null ) {
	$errors = new WP_Error();

	$sanitized_user_login = sanitize_user( $user_login );
	$user_email = apply_filters( 'user_registration_email', $user_email );

	// Check the username
	if ( $sanitized_user_login == '' ) {
		$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.' ) );
	} elseif ( ! validate_username( $user_login ) ) {
		$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
		$sanitized_user_login = '';
	} elseif ( username_exists( $sanitized_user_login ) ) {
		$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.' ) );
	}

	// Check the e-mail address
	if ( $user_email == '' ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.' ) );
	} elseif ( ! is_email( $user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.' ) );
		$user_email = '';
	} elseif ( email_exists( $user_email ) ) {
		$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ) );
	}

	do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

	$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

	if ( $errors->get_error_code() )
		return $errors;
	
	if (is_null($role))
		$role = get_option('default_role');

	$user_pass = wp_generate_password( 12, false);
	$userdata = array(
		'user_login' => $sanitized_user_login, 
		'user_email' => $user_email, 
		'user_pass' => $user_pass,
		'role' => $role,
	);
	$user_id = wp_insert_user($userdata);
	
	if ( !$user_id ) {
		$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
		return $errors;
	}
	
	update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.
	
	wp_new_user_notification( $user_id, $user_pass );
	
	return $user_id;
}

if ( !function_exists('wp_password_change_notification') ) :
/**
 *  PLUGGABLE OVERRIDE
 * 
 * Notify the blog admin of a user changing password, normally via email.
 *
 * @since 2.7
 *
 * @param object $user User Object
 */
function wp_password_change_notification(&$user) {
	// send a copy of password change notification to the admin
	// but check to see if it's the admin whose password we're changing, and skip this
	if ( $user->user_email != get_option('admin_email') ) {
		$message = sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "\r\n";
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		wp_mail(get_option('admin_email'), sprintf(__('[%s] Password Lost/Changed'), $blogname), $message);
	}
}
endif;

if ( ! function_exists('wp_notify_postauthor') ) :
/**
 * Notify an author of a comment/trackback/pingback to one of their posts.
 *
 * @since 1.0.0
 *
 * @param int $comment_id Comment ID
 * @param string $comment_type Optional. The comment type either 'comment' (default), 'trackback', or 'pingback'
 * @return bool False if user email does not exist. True on completion.
 */
function wp_notify_postauthor( $comment_id, $comment_type = '' ) {
	$comment = get_comment( $comment_id );
	$post    = get_post( $comment->comment_post_ID );
	$author  = get_userdata( $post->post_author );

	// The comment was left by the author
	if ( $comment->user_id == $post->post_author )
		return false;

	// The author moderated a comment on his own post
	if ( $post->post_author == get_current_user_id() )
		return false;

	// If there's no email to send the comment to
	if ( '' == $author->user_email )
		return false;

	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	if ( empty( $comment_type ) ) $comment_type = 'comment';

	if ('comment' == $comment_type) {
		$notify_message  = sprintf( __( 'New comment on your post "%s"' ), $post->post_title ) . "\r\n";
		/* translators: 1: comment author, 2: author IP, 3: author domain */
		$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
		$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= sprintf( __('Whois  : http://whois.arin.net/rest/ip/%s'), $comment->comment_author_IP ) . "\r\n";
		$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		$notify_message .= __('You can see all comments on this post here: ') . "\r\n";
		/* translators: 1: blog name, 2: post title */
		$subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
	} elseif ('trackback' == $comment_type) {
		$notify_message  = sprintf( __( 'New trackback on your post "%s"' ), $post->post_title ) . "\r\n";
		/* translators: 1: website name, 2: author IP, 3: author domain */
		$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= __('Excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		$notify_message .= __('You can see all trackbacks on this post here: ') . "\r\n";
		/* translators: 1: blog name, 2: post title */
		$subject = sprintf( __('[%1$s] Trackback: "%2$s"'), $blogname, $post->post_title );
	} elseif ('pingback' == $comment_type) {
		$notify_message  = sprintf( __( 'New pingback on your post "%s"' ), $post->post_title ) . "\r\n";
		/* translators: 1: comment author, 2: author IP, 3: author domain */
		$notify_message .= sprintf( __('Website: %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
		$notify_message .= __('Excerpt: ') . "\r\n" . sprintf('[...] %s [...]', $comment->comment_content ) . "\r\n\r\n";
		$notify_message .= __('You can see all pingbacks on this post here: ') . "\r\n";
		/* translators: 1: blog name, 2: post title */
		$subject = sprintf( __('[%1$s] Pingback: "%2$s"'), $blogname, $post->post_title );
	}
	$notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
	$notify_message .= sprintf( __('Permalink: %s'), get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment_id ) . "\r\n";
	if ( EMPTY_TRASH_DAYS )
		$notify_message .= sprintf( __('Trash it: %s'), admin_url("comment.php?action=trash&c=$comment_id") ) . "\r\n";
	else
		$notify_message .= sprintf( __('Delete it: %s'), admin_url("comment.php?action=delete&c=$comment_id") ) . "\r\n";
	$notify_message .= sprintf( __('Spam it: %s'), admin_url("comment.php?action=spam&c=$comment_id") ) . "\r\n";

	$wp_email = 'wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));

	if ( '' == $comment->comment_author ) {
		$from = "From: \"$blogname\" <$wp_email>";
		if ( '' != $comment->comment_author_email )
			$reply_to = "Reply-To: $comment->comment_author_email";
	} else {
		$from = "From: \"$comment->comment_author\" <$wp_email>";
		if ( '' != $comment->comment_author_email )
			$reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
	}

	$message_headers = "$from\n"
		. "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

	if ( isset($reply_to) )
		$message_headers .= $reply_to . "\n";

	$notify_message = apply_filters('comment_notification_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_notification_subject', $subject, $comment_id);
	$message_headers = apply_filters('comment_notification_headers', $message_headers, $comment_id);

	@wp_mail( $author->user_email, $subject, $notify_message, $message_headers );

	return true;
}
endif;

if ( !function_exists('wp_notify_moderator') ) :
/**
 * Notifies the moderator of the blog about a new comment that is awaiting approval.
 *
 * @since 1.0
 * @uses $wpdb
 *
 * @param int $comment_id Comment ID
 * @return bool Always returns true
 */
function wp_notify_moderator($comment_id) {
	global $wpdb;

	if ( 0 == get_option( 'moderation_notify' ) )
		return true;

	$comment = get_comment($comment_id);
	$post = get_post($comment->comment_post_ID);
	$user = get_userdata( $post->post_author );
	// Send to the administation and to the post author if the author can modify the comment.
	$email_to = array( get_option('admin_email') );
	if ( user_can($user->ID, 'edit_comment', $comment_id) && !empty($user->user_email) && ( get_option('admin_email') != $user->user_email) )
		$email_to[] = $user->user_email;

	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	switch ($comment->comment_type)
	{
		case 'trackback':
			$notify_message  = sprintf( __('A new trackback on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
			$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
			$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
			$notify_message .= __('Trackback excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
			break;
		case 'pingback':
			$notify_message  = sprintf( __('A new pingback on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
			$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
			$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
			$notify_message .= __('Pingback excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
			break;
		default: //Comments
			$notify_message  = sprintf( __('A new comment on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
			$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
			$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
			$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
			$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
			$notify_message .= sprintf( __('Whois  : http://whois.arin.net/rest/ip/%s'), $comment->comment_author_IP ) . "\r\n";
			$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
			break;
	}

	$notify_message .= sprintf( __('Approve it: %s'),  admin_url("comment.php?action=approve&c=$comment_id") ) . "\r\n";
	if ( EMPTY_TRASH_DAYS )
		$notify_message .= sprintf( __('Trash it: %s'), admin_url("comment.php?action=trash&c=$comment_id") ) . "\r\n";
	else
		$notify_message .= sprintf( __('Delete it: %s'), admin_url("comment.php?action=delete&c=$comment_id") ) . "\r\n";
	$notify_message .= sprintf( __('Spam it: %s'), admin_url("comment.php?action=spam&c=$comment_id") ) . "\r\n";

	$notify_message .= sprintf( _n('Currently %s comment is waiting for approval. Please visit the moderation panel:',
 		'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comments_waiting), number_format_i18n($comments_waiting) ) . "\r\n";
	$notify_message .= admin_url("edit-comments.php?comment_status=moderated") . "\r\n";

	$subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), $blogname, $post->post_title );
	$message_headers = '';

	$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_moderation_subject', $subject, $comment_id);
	$message_headers = apply_filters('comment_moderation_headers', $message_headers);

	foreach ( $email_to as $email )
		@wp_mail($email, $subject, $notify_message, $message_headers);

	return true;
}
endif;

if ( !function_exists('wp_new_user_notification') ) : 
/**
 * PLUGGABLE OVERRIDE
 * 
 * Notify the blog admin of a new user, normally via email.
 *
 * @since 2.0
 *
 * @param int $user_id User ID
 * @param string $plaintext_pass Optional. The user's plaintext password
 */
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = new WP_User($user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
	$message .= wp_login_url() . "\r\n";

	wp_mail($user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);

}
endif;

