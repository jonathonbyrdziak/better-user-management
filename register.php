<?php 
/**
 * @Author	Jonathon byrd
 * @link http://www.jonathonbyrd.com
 * @Package Wordpress
 * @SubPackage Byrd Plugin Framework
 * @copyright Proprietary Software, Copyright Byrd Incorporated. All Rights Reserved
 * @Since 1.0
 * 
 * users.php
 * 
 */

defined('ABSPATH') or die("Cannot access pages directly.");
	
	/**  
	 * Sets a new custom user field
	 * 
	 * @param array $args
	 */
	function add_custom_user_field( $user_type = 'subscriber', $args = null )
	{
		static $fields;
		if (!isset($fields))
		{
			$fields = array();
		}
		
		if (is_null($args)) return $fields;
		
		$defaults = array(
			'name' => 'example',
			'desc' => '',
			'id' => 'example',
			'type' => 'text',
			'std' => ''
		);
			
		$args = wp_parse_args( $args, $defaults );
		
		$fields[$user_type][$args['id']] = $args;
		return true;
	}
	
	/**
	 * Register all of the user types
	 * 
	 * @param array $user_types
	 */
	function register_user_types( $user_types )
	{
		//reasons to fail
		if (!is_array($user_types)) return false;
		
		foreach ($user_types as $user_type)
		{
			register_user_type( $user_type );
		}
		return true;
	}
	
	/**
	 * Register the user Type
	 * 
	 * @param array $user_type
	 */
	function register_user_type( $user_type = null )
	{
		//initializing variables
		static $user_types;
		$default = array(
			'role' => get_option('default_role'),
			'name' => ucfirst(get_option('default_role')),
			'registration' => false,
			'user_meta' => false,
		);
		
		if (!isset($user_types))
		{
			$user_types = array();
		}
		
		if (is_null($user_type)) return $user_types;
		
		$user_type = wp_parse_args($user_type, $default);
		
		//set the registration page if we have one
		if ($user_type['registration'])
		{
			$user_type['registration']['role'] = $user_type['role'];
			$user_type['registration']['name'] = $user_type['name'];
			registration_page( $user_type['registration'] );
		}
		
		if ($user_type['user_meta'])
		{
			register_user_metas($user_type['role'], $user_type['user_meta']);
		}
		
		$user_types[$user_type['role']] = $user_type;
		return true;
	}
	
	/**
	 * Get and return all of the custom user fields
	 * 
	 * @return array
	 */
	function register_user_metas( $user_type = 'subscriber', $fields = null )
	{
		if (is_null($fields)) return false;
		
		foreach ($fields as $field)
		{
			add_custom_user_field( $user_type, $field );
		}
		return true;
	}
	
	/**
	 * Register a bunch of pages
	 * 
	 * @param unknown_type $pages
	 */
	function registration_pages( $pages )
	{
		//reasons to fail
		if (!is_array($pages)) return false;
		
		foreach ($pages as $page)
		{
			registration_page( $page );
		}
		return true;
	}
	
	/**
	 * Register a single page
	 * 
	 * @param unknown_type $page
	 * @return array
	 * @since 1.2
	 */
	function registration_page( $page = null )
	{
		//initializing variables
		static $pages;
		$default = array(
			'role' => get_option('default_role'),
			'name' => ucfirst(get_option('default_role')),
			'redirect_to' => get_bloginfo('url').'/profile/',
			'fields' => array('user_login','user_email'),
			'force_login' => false
		);
		
		if (empty($pages) && empty($page))
		{
			$pages = array();
			$pages[$default['role']] = $default;
		}
		
		if (is_null($page)) return $pages;
		
		$page = wp_parse_args($page, $default);
		
		$pages[$page['role']] = $page;
		return true;
	}
	
	/**
	 * Get's the display names for the given user
	 * 
	 * @return array
	 */
	function get_display_names( $user_id = null )
	{
		$user =& get_user( $user_id );
		
		$displays = array(
			getVar('user_login', $user->user_login), 
			trim(getVar('first_name', $user->first_name).' '.
				getVar('last_name', $user->last_name)), 
			getVar('nickname', $user->nickname)
		);
		
		return $displays;
	}
	
	/**
	 * Get the Roles Array
	 * 
	 * This will return an array of user roles
	 * 
	 * @param $author_id
	 * @param $post_type
	 * @return array
	 */
	function get_roles_array()
	{
		global $wpdb, $wp_roles;
		$user =& get_user();
		$roles = array();
		$continue = true;
		
		$capabilities = $user->{$wpdb->prefix . 'capabilities'};
		if (!is_array($capabilities) && !is_object($capabilities)) return false;
		
		if ( !isset( $wp_roles ) )
			$wp_roles = new WP_Roles();
	
		foreach ( $wp_roles->role_names as $role => $name ) :
			
			if ( array_key_exists( $role, $capabilities ) )
			{
				$continue = false;
			}
			if ($continue) continue;
			$roles[$role] = $name;
		endforeach;
		
		return $roles;
	}
	
	/**
	 * Count the posts
	 * 
	 * @param $author_id
	 * @param $post_type
	 * @return array
	 */
	function get_user_role( $user_id = null )
	{
		global $wpdb, $wp_roles;
		$user =& get_user();
		if (is_null($user_id))
		{
			$user_id = $user->ID;
		}
		$user = get_userdata( $user_id );

		$capabilities = $user->{$wpdb->prefix . 'capabilities'};
		if (!is_array($capabilities) && !is_object($capabilities)) return false;
		
		if ( !isset( $wp_roles ) )
			$wp_roles = new WP_Roles();
	
		foreach ( $wp_roles->role_names as $role => $name ) :
			
			if ( array_key_exists( $role, $capabilities ) )
				break;
	
		endforeach;
		
		return $role;
	}
	
	/**
	 * Get the current user
	 * 
	 * Function is responsible for creating and returning the user object
	 * 
	 * @since 1.0
	 * @param $userid
	 * @return global object reference
	 */
	function &get_user( $userid = null )
	{
		//initializing variables
		static $users;
		if (is_null($users))
		{
			$users = array();
		}
				
		//loading library
		require_once ABSPATH . WPINC . DS . 'pluggable.php';
		
		//if we want the logged in user
		if (is_null($userid))
		{
			if ( !$user = wp_validate_auth_cookie() )
			{
				if ( is_admin() 
				|| empty($_COOKIE[LOGGED_IN_COOKIE]) 
				|| !$user = wp_validate_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in') )
				{
					$userid = 0;
				}
			}
			$userid = $user;
		}
		
		//if we're wanting to standardize the userid
		if (is_object($userid) && isset($userid->ID))
		{
			$userid = $userid->ID;
		}
		
		if (!isset($users[$userid]))
		{
			$user = new WP_User( $userid );
			$users[$userid] =& $user;
		}
		
		return $users[$userid];
	}
	
	/**
	 * Count the posts
	 * 
	 * @param $author_id
	 * @param $post_type
	 * @return array
	 */
	function get_users_by_role( $search_term = '', $page = '', $role = "Author" )
	{
		require_once ABSPATH."/wp-admin/includes/user.php";
		$wp_user_search = new WP_User_Search($search_term, $page, $role);
		return $wp_user_search->get_results();
	}
	
	/**
	 * Get and return all of the custom user fields
	 * 
	 * @return array
	 */
	function get_custom_user_fields( $user_type = 'subscriber' )
	{
		$fields = get_term_by( 'slug', $user_type, BUM_HIDDEN_FIELDS );
		$fields = $fields->description ? json_decode( $fields->description ) : array();
		return $fields;
		
		//initializing variables
		/* OLD CODE
		 * $fields = add_custom_user_field();
		
		if (!isset($fields[$user_type])) return false;
		return $fields[$user_type];*/
	}
	
	/**
	 * Get the registered pages
	 * 
	 * @return array
	 * @since 1.2
	 */
	function get_registration_pages()
	{
		return registration_page();
	}
	
	/**
	 * Get the registration fields
	 * 
	 * @return array
	 */
	function get_registration_fields( $user_type )
	{
		//initializing variables
		$pages = get_registration_pages();
		$page = $pages[$user_type];
		
		$defaults = bum_get_default_profile_fields();
		$field_ids = $page['fields'];
		$fields = get_custom_user_fields( $user_type );
		$fields = wp_parse_args( $fields, $defaults );
		
		
		//verifying that we have what we need.
		if (!in_array('user_login', $field_ids)) $field_ids[] = 'user_login';
		if (!in_array('user_email', $field_ids)) $field_ids[] = 'user_email';
		
		$registration_fields = array();
		foreach ($field_ids as $id)
		{
			$registration_fields[] = $fields[$id];
		}
		return $registration_fields;
	}
	
	/**
	 * Get the registration details
	 * 
	 * @return array
	 * @since 1.2
	 */
	function get_registration_page()
	{
		$pages = get_registration_pages();
		$role = getVar('user_type');
		$page = $pages[$role];
		
		return $page;
	}
	
	/**
	 * Checks to see if we have any custom user meta fields
	 * 
	 * @return boolean
	 */
	function has_custom_user_fields()
	{
		$fields = get_custom_user_fields();
		if (empty($fields)) return false;
		return true;
	}
	
	/**
	 * Displays all of the user profile fields.
	 * 
	 * @param unknown_type $userid
	 */
	function display_profile_fields()
	{
		//initializing variables
		$user =& get_user();
		$fields = get_custom_user_fields( $user->roles[0] );
		$defaults = bum_get_default_profile_fields();
		
		$fields = wp_parse_args( $fields, $defaults );
		bum_display_custom_user_fields($user, $fields);
	}
	
	/**
	 * Checks to see if the logged in user is the post owner
	 *
	 * @return unknown
	 */
	function is_post_owner()
	{
		//initializing variables
		global $authordata;
		$user =& get_user();
		
		if (!is_object($user)) return false;
		if (!is_object($authordata)) return false;
		if ($authordata->ID != $user->ID) return false;
		return true;
	}
	
	/**
	 * Count the posts
	 * 
	 * @param $author_id
	 * @param $post_type
	 * @return array
	 */
	function user_is( $role = null )
	{
		if (strtolower($role) != strtolower(get_user_role(null, true))) return false;
		return true;
	}