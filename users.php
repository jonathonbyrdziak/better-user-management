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


if ( !function_exists( 'get_user' ) ):

	/**
	 * Action Hooks
	 * 
	 */
	add_action( 'init', 'do_redirect_from_admin' );
	add_action( 'init', 'save_user_profile', 20 );
	
	//actions for user profile page
	add_action( 'show_user_profile', 'display_custom_user_fields' );
	add_action( 'edit_user_profile', 'display_custom_user_fields' );
	
	//actions for registration page
	add_action( 'register_form', 'do_registration_form' );
	add_action( 'edit_profile_fields', 'display_profile_fields' );
	
	//actions for updating fields
	add_action( 'personal_options_update', 'save_user_meta_data' );
	add_action( 'edit_user_profile_update', 'save_user_meta_data' );

	
	/**
	 * Do registration form
	 * 
	 * @return boolean
	 * @since 1.2
	 */
	function do_registration_form()
	{
		//initializing variables
		$user_type = is_user_type();
		$pages = get_registration_pages();
		$status = true;
		
		//reasons to fail
		if (!$user_type) $status = false;
		if ($status && !isset($pages[$user_type])) $status = false;
		if (!$status) do_redirect( get_bloginfo('url').'/registration/' );
		
		display_custom_user_fields( null, get_registration_fields( $user_type ));
		return true;
	}
	
	/**
	 * Redirects to the proper page
	 */
	function do_redirect_from_admin()
	{
		//initializing variables
		$capability = 'activate_plugins';
		$user =& get_user();
		
		//reasons to fail
		if ( strpos($_SERVER["REQUEST_URI"], '/wp-admin') === false ) return false;
		if ( current_user_can($capability) ) return false;
		if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) return false;
		if ( defined('DOING_AJAX') && DOING_AJAX ) return false;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;
		if ( defined('DOING_CRON') && DOING_CRON ) return false;
		if ( defined('WP_FIRST_INSTALL') && WP_FIRST_INSTALL ) return false;
		if ( defined('WP_IMPORTING') && WP_IMPORTING ) return false;
		if ( defined('WP_INSTALLING') && WP_INSTALLING ) return false;
		if ( defined('WP_REPAIRING') && WP_REPAIRING ) return false;
		if ( defined('WP_UNINSTALL_PLUGIN') && WP_UNINSTALL_PLUGIN ) return false;
		if ( is_ajaxrequest() ) return false;
		
		//if this is an ajax post
		if (!$user->ID && isset($_POST['logged_in_cookie']))
		{
			$parts = explode('|', $_POST['logged_in_cookie']);
			$user =& get_user( $parts[0] );
		}
		
		//if we can get the user ourself
		if ( $user->has_cap($capability) ) return false;
		
		if (is_user_logged_in())
		{
			do_redirect(get_bloginfo('url').'/profile/');
		}
		else
		{
			do_redirect(get_bloginfo('url').'/login/');
		}
	}
	
	/**
	 * Save the User Profile
	 * 
	 * This function is responsible for saving the user fields upon post. SO
	 * LONG AS, the user is already logged in. This does not create a new user.
	 * 
	 * @return boolean
	 * @since 1.2
	 */
	function save_user_profile()
	{
		//initializing variables
		$user =& get_user( BRequest::getVar('user_id') );
		
		//reasons to fail
		//handling any required actions
		if ( !is_user_logged_in() ) return false;
		if ( BRequest::getVar('action',false) != 'edit' ) return false;
		if ( !wp_verify_nonce(BRequest::getVar("user_meta_box_nonce"), basename(__FILE__)) ) 
			return false;
		
		//initializing variables
		$data = BRequest::get('post');
		$data['ID'] = $user->ID;
		
		//loading libraries
		require_once( ABSPATH.WPINC.DS.'registration.php' );
				
		
		//doing all the saves
		if (!save_useremail()) $data['user_email'] = $user->user_email;
		
		if (wp_insert_user($data) //update the user
		&& save_userpw( $data['pass1'], $data['pass2'] ) //update the users pw
		&& save_user_meta_data( $data['ID'] )) //update the users email
		{
			set_notification('Profile has been updated');
		}
		return true;
	}
	
	/**
	 * Save the password
	 * 
	 * @param $pass1
	 * @param $pass2
	 * @since 1.0
	 */
	function save_userpw( $pass1 = null, $pass2 = null )
	{
		//reasons to fail
		if ( !is_user_logged_in() ) return false;
		if ( is_null($pass1) ) return true;
		if ( is_null($pass2) ) return true;
		if ( trim($pass1) == "" ) return true;
		
		//checking for harmful injections
		$temp = strip_tags($pass1);
		if ($temp != $pass1) return false;
		if ($pass2 != $pass1) return false;
		
		//initializing variables
		$data = array();
		$data['user_pass'] = wp_hash_password($pass1);
		
		//loading resources
		global $wpdb;
		$user =& get_user();
		
		if ($wpdb->update( $wpdb->users, $data, array('ID' => $user->ID) ))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Saves the users email
	 * 
	 * @since 1.0
	 */
	function save_useremail()
	{	
		//initializing variables
		if ( !is_user_logged_in() ) return false;
		if ( !BRequest::getVar("user_email", false) )
		{
			set_warning('An email is required.');
			return false;
		}
		
		require_once dirname(__file__).DS."includes".DS.'mail.php';
		if (!check_email_address(BRequest::getVar("user_email")))
		{
			set_warning('The given email must be valid.');
			return false;
		}
		
		//loading resources
		require_once(ABSPATH . WPINC  . '/pluggable.php');
		
		//initializing variables
		global $wpdb;
		$user =& get_user();
		$data = array();
		$data["user_email"] = BRequest::getVar("user_email");
		
		if ($wpdb->update( $wpdb->users, $data, array('ID' => $user->ID) ))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Save user meta data
	 * 
	 * @param $user_id
	 */
	function save_user_meta_data( $user_id ) 
	{
		//initializing variables
		$user = new WP_User( $user_id );
		$fields = array();
		
		$fields = wp_parse_args($fields, get_custom_user_fields( $user->roles[0] ));
		
		//reasons to fail
		if (empty($fields)) return false;
		
		//load library
		require_once ABSPATH.WPINC."/pluggable.php";
		
		// verify nonce
		if (!wp_verify_nonce(BRequest::getVar('user_meta_box_nonce'), basename(__FILE__))) {
			return $user_id;
		}
		
		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $user_id;
		}
		
		if (is_array($fields))
		{
			foreach ($fields as $field)
			{
				if (!isset($_POST[$field['id']])) continue;

				$old = get_user_meta($user_id, $field['id'], true);
	    		$new = BRequest::getVar($field['id'],"");

	    		if ($new && $new != $old)
	    		{
	    			//if ($field['type'] == "address") save_latitude_and_longitude($post_id,$new);
	    			update_user_meta($user_id, $field['id'], $new);
	    		}
	    		elseif ('' == $new && $old)
	    		{
	    			delete_user_meta($user_id, $field['id'], $old);
	    		}
	    		
	    	}
	    	return true;
	    }
	    
	}
	
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
		
		if (!isset($pages))
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
			BRequest::getVar('user_login', $user->user_login), 
			trim(BRequest::getVar('first_name', $user->first_name).' '.
				BRequest::getVar('last_name', $user->last_name)), 
			BRequest::getVar('nickname', $user->nickname)
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
	 * Get the users profile link
	 * 
	 * @param unknown_type $user_id
	 * @return string
	 * @since 1.2
	 */
	function get_profile_url( $user_id = null )
	{
		//initializing variables
		$user =& get_user($user_id);
		$link = get_bloginfo('url').'/profile/?user_id='.$user->ID;
		
		return $link;
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
		//initializing variables
		$fields = add_custom_user_field();
		
		if (!isset($fields[$user_type])) return false;
		return $fields[$user_type];
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
		
		$defaults = get_default_profile_fields();
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
		$role = BRequest::getVar('user_type');
		$page = $pages[$role];
		
		return $page;
	}
	
	/**
	 * Contains all of the default user fields
	 * 
	 * @return array
	 */
	function get_default_profile_fields()
	{
		return array(
			'rich_editing' => array(
				'name' => 'Visual Editor',
				'desc' => 'Disable the visual editor when writing',
				'id' => 'rich_editing',
				'type' => 'checkbox',
				'std' => ''
			),
			'comment_shortcuts' => array(
				'name' => 'Keyboard Shortcuts',
				'desc' => 'Enable keyboard shortcuts for comment moderation. <a href="http://codex.wordpress.org/Keyboard_Shortcuts">More information</a>',
				'id' => 'comment_shortcuts',
				'type' => 'checkbox',
				'std' => ''
			),
			'user_login' => array(
				'name' => 'Username',
				'desc' => 'Usernames cannot be changed.',
				'id' => 'user_login',
				'type' => 'text',
				'std' => ''
			),
			'role' => array(
				'name' => 'Role',
				'desc' => 'Disable the visual editor when writing',
				'id' => 'role',
				'type' => 'select',
				'options' => create_function('', "return get_roles_array();")
			),
			'first_name' => array(
				'name' => 'First Name',
				'desc' => '',
				'id' => 'first_name',
				'type' => 'text',
				'std' => ''
			),
			'last_name' => array(
				'name' => 'Last Name',
				'desc' => '',
				'id' => 'last_name',
				'type' => 'text',
				'std' => ''
			),
			'nickname' => array(
				'name' => 'Nickname',
				'desc' => '',
				'id' => 'nickname',
				'type' => 'text',
				'std' => ''
			),
			'display_name' => array(
				'name' => 'Display name publicly as',
				'desc' => '',
				'id' => 'display_name',
				'type' => 'select',
				'options' => create_function('', "return get_display_names( BRequest::getVar( 'user_id' ));")
			),
			'user_email' => array(
				'name' => 'E-mail',
				'desc' => '',
				'id' => 'user_email',
				'type' => 'email',
				'std' => ''
			),
			'url' => array(
				'name' => 'Website',
				'desc' => '',
				'id' => 'url',
				'type' => 'text',
				'std' => ''
			),
			'aim' => array(
				'name' => 'AIM',
				'desc' => '',
				'id' => 'aim',
				'type' => 'text',
				'std' => ''
			),
			'yim' => array(
				'name' => 'Yahoo IM',
				'desc' => '',
				'id' => 'yim',
				'type' => 'text',
				'std' => ''
			),
			'jabber' => array(
				'name' => 'Jabber / Google Talk',
				'desc' => '',
				'id' => 'jabber',
				'type' => 'text',
				'std' => ''
			),
			'description' => array(
				'name' => 'Biographical Info',
				'desc' => 'Share a little biographical information to fill out your profile. This may be shown publicly.',
				'id' => 'description',
				'type' => 'textarea',
				'std' => ''
			),
			'password' => array(
				'name' => 'New Password',
				'desc' => '',
				'id' => 'password',
				'type' => 'password',
				'std' => ''
			),
		);
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
	 * Display the user type links
	 * 
	 * @return boolean
	 * @since 1.2
	 */
	function display_user_type_links()
	{
		if (is_user_type()) return false;
		
		$pages = get_registration_pages();
		
		echo '<ul class="registration_types">';
		foreach ($pages as $page)
		{
			echo "<li><a href='?user_type={$page['role']}'><span>Register as a </span>{$page['name']}</a></li>";
		}
		echo '</ul>';
		
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
		$defaults = get_default_profile_fields();
		
		$fields = wp_parse_args( $fields, $defaults );
		display_custom_user_fields($user, $fields);
	}
	
	/**
	 * Display the user edit fields
	 * 
	 * @param unknown_type $user
	 */
	function display_custom_user_fields($user = null, $fields = null) 
	{
		//initializing variables
		if (!is_null($user)) $user = get_userdata($user->ID);
		$is_administration = false;
		if (is_null($fields)) $is_administration = true;
		
		if ($is_administration)
		{
			echo "<style>",
			".field_wrapper label {display:block;position:relative;float:left;width:220px;}",
			".typetext input {width: 25em;}",
			".typecheckbox input {margin-right:200px;position:relative;float:left;}",
			".field_wrapper span {display:block;padding-left:220px;}",
			".field_wrapper {padding: 10px;}",
			".typetextarea textarea {width: 500px;}",
			".field_wrapper .profile_description{font-family: 'Lucida Grande', Verdana, Arial, 'Bitstream Vera Sans', sans-serif;font-size: 12px;font-style: italic;color: #666;}",
			"</style>",
			"<h3>Additional Details</h3>";
			
			//initializing variables
			$currentUser = new WP_User( $user->ID );
			$fields = array();
			
			foreach ($currentUser->roles as $role)
			{
				$fields = wp_parse_args($fields, get_custom_user_fields( $role ));
			}
		}
		
		//reasons to fail
		if (empty($fields)) return false;
		
		// Use nonce for verification
		echo '<div class="nonce_wrapper"><input type="hidden" name="user_meta_box_nonce" value="',
			wp_create_nonce(basename(__FILE__)), '" /></div>',
			'<input type="hidden" name="user_type" value="',BRequest::getVar('user_type'),'" />';
		
	    foreach ($fields as $field) 
	    {
	    	if (!current_user_can('edit_users') && $field['id'] == 'role')
	    	{
	    		continue;
	    	}
	    	
	        // get current post meta data
	        $unique = md5(microtime());
	        if (!is_null($user) && isset($user->{$field['id']}))
	        {
	        	$meta = $user->{$field['id']};
	        }
	        elseif(!is_null($user))
	        {
	        	$meta = get_user_meta($user->ID, $field['id'], true);
	        }
	        else
	        {
	        	$meta = BRequest::getVar($field['id'], '');
	        }
	        
			echo '<div class="field_wrapper div', $field['id'], ' type',$field['type'],'">';
			if ($field['type'] != 'password') echo '<label for="', $field['id'], '">', $field['name'], '</label>';
	        
	        switch ($field['type'])
	        {
	            case 'password':
	                echo 
	                '<label for="', $field['id'], '">', $field['name'], '</label>',
	                '<input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off">',
	                '<span class="description">If you would like to change the password type a new one. Otherwise leave this blank.</span><br>',
	                '<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off">',
	                '<span class="description">Type your new password again.</span><br>',
	                '<div id="pass-strength-result">Strength indicator</div>',
	                '<p class="description indicator-hint">Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).</p>',
	                '<script type="text/javascript"> /* <![CDATA[ */
					var pwsL10n = {
						empty: "Strength indicator",
						short: "Very weak",
						bad: "Weak",
						good: "Medium",
						strong: "Strong",
						mismatch: "Mismatch"
					};
					try{convertEntities(pwsL10n);}catch(e){};
					/* ]]> */
					</script>',
					'<script type="text/javascript" src="',get_bloginfo('url'),'/wp-admin/load-scripts.php?c=1&load=jquery,hoverIntent,common,jquery-color,user-profile,password-strength-meter"></script>';
	                break;
	            case 'address':
	                echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', "\n", 
	                '<span class="profile_description">',$field['desc'],'</span>';
	                break;
	                
				case 'email':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" class="regular-text" />', "\n";
					echo '<input type="text" name="', $field['id'], '1" id="', $field['id'], '" value="', ($default = "Please confirm your email"), 
					'" class="regular-text" onBlur="if (this.value == \'\') this.value = \'',$default,'\';"  onFocus="if (this.value == \'',$default,'\') this.value = \'\';" />', "\n", 
					'<span class="profile_description">',$field['desc'],'</span>';
					break;
					
	            case 'text':
	            	$disabled = '';
	            	if (is_user_logged_in() && $field['id'] == 'user_login') $disabled = 'readonly="true"';
	                echo '<input ',$disabled,' type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" class="regular-text" />', "\n", 
	                '<span class="profile_description">', $field['desc'], '</span>';
	                break;
	                
	            case 'textarea':
	                echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="30" rows="5">', $meta ? $meta : $field['std'], '</textarea>', "\n", 
	                '<span class="profile_description">', $field['desc'], '</span>';
	                break;
	                
	            case 'select':
	                echo '<select name="', $field['id'], '" id="', $field['id'], '">';
	                if (!is_array($field['options']))
	                {
	                	$field['options'] = $field['options']();
	                }
	        		foreach ($field['options'] as $key => $option)
	        		{
	        			if (is_int($key)) $key = $option;
	                    echo '<option ', $meta == $option ? ' selected="selected"' : '', 
	        			' value="',$key,'">', $option, '</option>';
	        		}
	                echo '</select>';
	                break;
	                
	            case 'radio':
	                foreach ($field['options'] as $option)
	                {
	                    echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
	                }
	                echo '<br/>',$field['desc'];
	                break;
	                
	            case 'checkbox':
	                echo '<input type="hidden" name="', $field['id'], '" value="" /> ';
	                echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', ($meta && $meta != 'false') ? ' checked="checked"' : '', ' />',
	                '<span class="profile_description">', $field['desc'], '</span>';
	                break;
	                
	            case 'editor':
	            	echo 
	                '<div style="border:1px solid #DFDFDF;border-collapse: separate;border-top-left-radius: 6px 6px;border-top-right-radius: 6px 6px;">',
	                	'<textarea rows="10" class="theEditor" cols="40" name="', $field['id'], '" id="'.$unique.'"></textarea>',
	                '</div>', 
	                '<script type="text/javascript">edCanvas = document.getElementById(\''.$unique.'\');</script>', "\n", $field['desc'];
	                break;
	        }
	        
	        echo '</div>';
	    }
	}
	
	/**
	 * Prints the users profile link
	 * 
	 * @param unknown_type $user_id
	 * @return null
	 * @since 1.2
	 */
	function profile_url( $user_id = null )
	{
		echo get_profile_url($user_id);
	}
	
	/**
	 * Checks to see if there's a user type
	 * 
	 */
	function is_user_type()
	{
		if ($type = BRequest::getVar('user_type',false)) 
			return $type;
		return false;
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
endif;

