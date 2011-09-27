<?php 
/**
 * @Author	Jonathon byrd
 * @link http://www.5twentystudios.com
 * @Package Wordpress
 * @SubPackage Jobity Job Board
 * @Since 1.0.0
 * @copyright  Copyright (C) 2011 5Twenty Studios
 * 
 */

defined('ABSPATH') or die("Cannot access pages directly.");

if (!function_exists("bum_get_show_view")):
	/**
	 * Controller.
	 * 
	 * This function will locate the associated element and display it in the
	 * place of this function call
	 * 
	 * @param string $name
	 */
	function bum_get_show_view( $name = null )
	{
		//initializing variables
		$paths = set_controller_path();
		$theme = get_theme_path();
		$html = '';
		
		if (!($view = bum_find(array($theme), "views".DS.$name.".php")))
		{
			$view = bum_find($paths, "views".DS.$name.".php");
		}
		if (!($model = bum_find(array($theme), "models".DS.$name.".php")))
		{
			$model = bum_find($paths, "models".DS.$name.".php");
		}
		
		if (is_null($name)) return false;
		if (!$view && !$model) return false;
		
		do_action( "byrd-controller", $model, $view );
		$path = $view;
		$html = false;
		
		if (file_exists($model))
		{
			ob_start();
				$args = func_get_args();
				require $model;
				unset($html);
			$html = ob_get_clean();
		}
		else
		{
			ob_start();
				$args = func_get_args();
				require $path;
				unset($html);
			$html = ob_get_clean();
		}
		
		$html = apply_filters( "byrd-controller-html", $html );
		
		return $html;
	}
endif;

if (!function_exists("bum_show_view")):
	/**
	 * Function prints out the bum_get_show_view()
	 * 
	 * @param string $name
	 * @see bum_get_show_view
	 */
	function bum_show_view( $name = null )
	{
		$args = func_get_args();
		unset($args[0]);
		
		echo bum_get_show_view($name, @$args[1]);
	}
endif;

if (!function_exists("bum_show_ajax")):
	
	/**
	 * Show the Ajax
	 * 
	 * Function will return the view file without the template. This makes for easy access
	 * to the view files during an ajax call
	 * 
	 * 
	 */
	function bum_show_ajax() 
	{
		if(!isset($_REQUEST['view']) || empty($_REQUEST['view'])) return false;
		
		//making sure that we load the template file
		$functions = get_theme_root()."/".get_option('template').'/functions.php';
		if (file_exists($functions)) require_once $functions;
		
		$html = bum_get_show_view( $_REQUEST['view'] );
		
		if (strlen(trim($html))>0)
		{
			echo apply_filters( 'byrd-ajax-html', $html );
			die();
		}
	}
endif;

if (!function_exists("set_controller_path")):
	/**
	 * Function prints out the bum_get_show_view()
	 * 
	 * @param string $name
	 * @see bum_get_show_view
	 */
	function set_controller_path( $name = null )
	{
		static $controller_paths;
		
		if (!isset($controller_paths))
		{
			$controller_paths = array();
		}
		
		if (!is_null($name))
		{
			$controller_paths[$name] = $name;
		}
		
		return $controller_paths;
	}
endif;

if (!function_exists("get_theme_path")):
	/**
	 * Returns the name of the theme
	 * 
	 */
	function get_theme_path()
	{
		$templateurl = ABSPATH."wp-content".DS."themes".DS.get_option('template');
		
		return $templateurl;
	}
endif;

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @access	protected
	  * @param	array|string	$path	An path or array of path to search in
	 * @param	string	$file	The file name to look for.
	 * @return	mixed	The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 * @since	1.5
	 */
	function bum_find($paths, $file)
	{
		settype($paths, 'array'); //force to array
		
		// start looping through the path set
		foreach ($paths as $path)
		{
			// get the path to the file
			$fullname = $path.DS.$file;

			// is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// the substr() check added to make sure that the realpath()
			// results in a directory registered so that
			// non-registered directores are not accessible via directory
			// traversal attempts.
			
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path) {
				return $fullname;
			}
		}

		// could not find the file in the set of paths
		return false;
	}
	
if (!function_exists('bum_chmod_directory')):
	/**
	 * function is responsible for changing the mod of the directory for registration
	 *
	 * @param unknown_type $path
	 * @param unknown_type $level
	 */
	function bum_chmod_directory( $path = '.', $chmod = 0777, $level = 0 )
	{  
		//initializing variables
		$ignore = array( 'cgi-bin', '.', '..' );
	
		//reasons to fail
		if (!$dh = @opendir( $path )) return false;
		
		while( false !== ( $file = readdir( $dh ) ) )
		{
			if( !in_array( $file, $ignore ) )
			{
				if( is_dir( "$path/$file" ) )
				{
					chmod("$path/$file",$chmod);
					bum_chmod_directory( "$path/$file", $chmod, ($level+1));
				}
				else
				{
					chmod("$path/$file",$chmod);
				}
			}
		}
		closedir( $dh ); 
	}
endif;
	
if (!class_exists("TwcPath")):
		
	/**
	 * 
	 * @author Jonathon Byrd
	 * 
	 *
	 */
	class TwcPath
	{

	/**
	 * Utility function to read the files in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for file names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the file.
	 * @param	array	Array with names of files which should not be shown in
	 * the result.
	 * @return	array	Files in the given folder.
	 * 
	 */
	function byrd_files($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = TwcPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		{
			trigger_error('BFolder::files: ' . 'Path is not a folder '.'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude)))
			{
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir)
				{
					if ($recurse)
					{
						if (is_integer($recurse))
						{
							$arr2 = TwcPath::files($dir, $filter, $recurse - 1, $fullpath);
						}
						else
						{
							$arr2 = TwcPath::files($dir, $filter, $recurse, $fullpath);
						}
						
						$arr = array_merge($arr, $arr2);
					}
				}
				else
				{
					if (preg_match("/$filter/", $file))
					{
						if ($fullpath)
						{
							$arr[] = $path . DS . $file;
						}
						else
						{
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}

	/**
	 * Function to strip additional / or \ in a path name
	 *
	 * @static
	 * @param	string	$path	The path to clean
	 * @param	string	$ds		Directory separator (optional)
	 * @return	string	The cleaned path
	 * @since	1.5
	 */
	function clean($path, $ds=DS)
	{
		$path = trim($path);

		if (empty($path))
		{
			$path = ABSPATH;
		}
		else
		{
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param string Folder name relative to installation dir
	 * @return boolean True if path is a folder
	 * 
	 */
	function exists($path)
	{
		return @is_dir(TwcPath::clean($path));
	}

	/**
	 * Create a folder -- and all necessary parent folders.
	 *
	 * @param string A path to create from the base path.
	 * @param int Directory permissions to set for folders created.
	 * @return boolean True if successful.
	 * 
	 */
	function create($path = '', $mode = 0755)
	{
		// Initialize variables
		static $nested = 0;

		// Check to make sure the path valid and clean
		$path = TwcPath::clean($path);
		
		// Check if parent dir exists
		$parent = dirname($path);
		if (!TwcPath::exists($parent))
		{
			// Prevent infinite loops!
			$nested++;
			if (($nested > 20) || ($parent == $path))
			{
				error_log(
					'BFolder::create: '.'Infinite loop detected', E_USER_WARNING
				);
				$nested--;
				return false;
			}

			// Create the parent directory
			if (TwcPath::create($parent, $mode) !== true)
			{
				// BFolder::create throws an error
				$nested--;
				return false;
			}

			// OK, parent directory has been created
			$nested--;
		}

		// Check if dir already exists
		if (TwcPath::exists($path))
		{
			return true;
		}

		// We need to get and explode the open_basedir paths
		$obd = ini_get('open_basedir');

		// If open_basedir is set we need to get the open_basedir that the path is in
		if ($obd != null)
		{
			if (defined('Path_ISWIN') && Path_ISWIN)
			{
				$obdSeparator = ";";
			}
			else
			{
				$obdSeparator = ":";
			}
			
			// Create the array of open_basedir paths
			$obdArray = explode($obdSeparator, $obd);
			$inBaseDir = false;
			
			// Iterate through open_basedir paths looking for a match
			foreach ($obdArray as $test)
			{
				$test = TwcPath::clean($test);
				if (strpos($path, $test) === 0)
				{
					$obdpath = $test;
					$inBaseDir = true;
					break;
				}
			}
			if ($inBaseDir == false)
			{
				// Return false for BFolder::create because the path to be created is not in open_basedir
				error_log(
					'TwcPath::create: '.'Path not in open_basedir paths', E_USER_WARNING
				);
				return false;
			}
		}

		// First set umask
		$origmask = @umask(0);

		// Create the path
		if (!$ret = @mkdir($path, $mode))
		{
			@umask($origmask);
			error_log(
				'Path::create: ' . 'Could not create directory '
				.'Path: ' . $path, E_USER_WARNING
			);
			return false;
		}
			
		// Reset umask
		@umask($origmask);
		
		return $ret;
	}

	/**
	 * Delete a folder.
	 *
	 * @param string The path to the folder to delete.
	 * @return boolean True on success.
	 * 
	 */
	function delete($path)
	{
		// Sanity check
		if (!$path)
		{
			// Bad programmer! Bad Bad programmer!
			error_log('Path::delete: ' . 'Attempt to delete base directory' );
			return false;
		}

		// Initialize variables
		
		// Check to make sure the path valid and clean
		$path = TwcPath::clean($path);

		// Is this really a folder?
		if (!is_dir($path))
		{
			error_log('Path::delete: ' . 'Path is not a folder '.'Path: ' . $path);
			return false;
		}

		// Remove all the files in folder if they exist
		$files = TwcPath::files($path, '.', false, true, array());
		if (!empty($files))
		{
			if (TwcPath::delete($files) !== true)
			{
				// File::delete throws an error
				return false;
			}
		}

		// Remove sub-folders of folder
		$folders = TwcPath::folders($path, '.', false, true, array());
		foreach ($folders as $folder)
		{
			if (is_link($folder))
			{
				// Don't descend into linked directories, just delete the link.
				if (TwcPath::delete($folder) !== true)
				{
					// File::delete throws an error
					return false;
				}
			}
			elseif (TwcPath::delete($folder) !== true)
			{
				// BFolder::delete throws an error
				return false;
			}
		}

		
		// In case of restricted permissions we zap it one way or the other
		// as long as the owner is either the webserver or the ftp
		if (@rmdir($path))
		{
			$ret = true;
		}
		else
		{
			error_log(
				'BFolder::delete: ' . 'Could not delete folder '
				.'Path: ' . $path, E_USER_WARNING
			);
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Utility function to read the folders in a folder.
	 *
	 * @param	string	The path of the folder to read.
	 * @param	string	A filter for folder names.
	 * @param	mixed	True to recursively search into sub-folders, or an
	 * integer to specify the maximum depth.
	 * @param	boolean	True to return the full path to the folders.
	 * @param	array	Array with names of folders which should not be shown in
	 * the result.
	 * @return	array	Folders in the given folder.
	 * 
	 */
	function folders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = TwcPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path))
		{
			error_log('BFolder::folder: ' . 'Path is not a folder '.'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude)))
			{
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir)
				{
					// Removes filtered directories
					if (preg_match("/$filter/", $file))
					{
						if ($fullpath)
						{
							$arr[] = $dir;
						}
						else
						{
							$arr[] = $file;
						}
						
					}
					if ($recurse)
					{
						if (is_integer($recurse))
						{
							$arr2 = TwcPath::folders($dir, $filter, $recurse - 1, $fullpath);
						}
						else
						{
							$arr2 = TwcPath::folders($dir, $filter, $recurse, $fullpath);
						}
						
						$arr = array_merge($arr, $arr2);
					}
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}
	
	} //ends TwcPath class
endif;

if (!function_exists("is_520")):
	/**
	 * Check if this is Jon
	 * 
	 */
	function is_520()
	{
		//initializing variables
		global $current_user;
		wp_get_current_user();
		
		if ($_SERVER['REMOTE_ADDR'] == '71.231.37.59') return true;
		//if ($current_user->ID == 1) return true;
		return false;
	}
endif;

if (!function_exists("_520")):
		
	/**
	 * Quick dump of an variables that are sent as parameters to this function.
	 * Make sure to enter your IP address so that it doens't display for anybody
	 * but yourself.
	 * 
	 * @return null
	 */
	function _520()
	{
		if (!is_520()) return;
		
		//initializing variables
		$variables = func_get_args();
		static $debug;
	
		//reasons to return
		if (empty($variables))
		{
			echo $debug;
			die();
		}
	
		foreach ($variables as $variable)
		{
			$string = "";
			if (!is_string($variable))
			{
				ob_start();
				echo  '<pre>';
				print_r($variable);
				echo  '</pre>';
				$string = ob_get_clean();
			}
			elseif (is_bool($variable))
			{
				ob_start();
				var_dump($variable);
				$string = ob_get_clean();
			}
			else
			{
				$string = $variable;
			}
	
			if (!isset($debug))
			{
				$debug = $string;
			}
			else
			{
				$debug .= '<BR>'.$string;
			}
		}
	
		return $string;
	}
endif;

if (!function_exists("create_guid")):
		
	/**
	 * Create Global Unique Identifier
	 * 
	 * Method will activate only if sugar has not already activated this
	 * same method. This method has been copied from the sugar files and
	 * is used for cakphp database saving methods.
	 * 
	 * There is no format to these unique ID's other then that they are
	 * globally unique and based on a microtime value
	 * 
	 * @return string //aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee
	 */
	function create_guid()
	{
		$microTime = microtime();
		list($a_dec, $a_sec) = explode(" ", $microTime);
		
		$dec_hex = sprintf("%x", $a_dec* 1000000);
		$sec_hex = sprintf("%x", $a_sec);
		
		ensure_length($dec_hex, 5);
		ensure_length($sec_hex, 6);
		
		$guid = "";
		$guid .= $dec_hex;
		$guid .= create_guid_section(3);
		$guid .= '-';
		$guid .= create_guid_section(4);
		$guid .= '-';
		$guid .= create_guid_section(4);
		$guid .= '-';
		$guid .= create_guid_section(4);
		$guid .= '-';
		$guid .= $sec_hex;
		$guid .= create_guid_section(6);
		
		return $guid;
	}
	function create_guid_section($characters)
	{
		$return = "";
		for($i=0; $i<$characters; $i++)
		{
			$return .= sprintf("%x", mt_rand(0,15));
		}
		return $return;
	}
	function ensure_length(&$string, $length)
	{
		$strlen = strlen($string);
		if($strlen < $length)
		{
			$string = str_pad($string,$length,"0");
		}
		else if($strlen > $length)
		{
			$string = substr($string, 0, $length);
		}
	}
endif;

if (!function_exists("register_multiwidget")):
	
	/**
	 * Register a widget
	 * 
	 * @param $widget
	 */
	function register_multiwidget( $widget = null )
	{
		static $widgets;
		if (!isset($widgets))
		{
			$widgets = array();
		}
		
		if (is_null($widget)) return $widgets;
		if (!is_array($widget)) return false;
		
		$defaults = array(
			'id' => '1',
			'title' => 'Generic Widget',
			'classname' => '',
			'description' => '',
			'width' => 200,
			'height' => 200,
			'fields' => array(),
		);
		
		$widgets[$widget['id']] = wp_parse_args($widget, $defaults);
		
		return true;
	}
endif;

if (!function_exists("get_registered_widgets")):
	
	/**
	 * Get the registered widgets
	 * 
	 * @return array
	 */
	function get_registered_widgets()
	{
		return register_multiwidget();
	}
endif;

if (!function_exists("init_registered_widgets")):
	
	/**
	 * Initialize the widgets
	 * 
	 * @return boolean
	 */
	function init_registered_widgets()
	{
		//initialziing variables
		global $wp_widget_factory;
		$widgets = get_registered_widgets();
		
		//reasons to fail
		if (empty($widgets) || !is_array($widgets)) return false;
		
		foreach ($widgets as $id => $widget)
		{
			$wp_widget_factory->widgets[$id] =& new Multiple_Widget_Master( $widget );
		}
		
		return false;
	}
endif;

if (!class_exists("Multiple_Widget_Master")):
	
	/**
	 * Multiple Widget Master Class
	 * 
	 * This class allows us to easily create qidgets without having to deal with the
	 * mass of php code.
	 * 
	 * @author byrd
	 * @since 1.3
	 */
	class Multiple_Widget_Master extends WP_Widget
	{
		
	/**
	 * Constructor.
	 * 
	 * @param $widget
	 */
	function Multiple_Widget_Master( $widget )
	{
		$this->widget = apply_filters('bum_widget_setup', $widget);
		$widget_ops = array(
			'classname' => $this->widget['classname'], 
			'description' => $this->widget['description'] 
		);
		$this->WP_Widget($this->widget['id'], $this->widget['title'], $widget_ops);
	}
	
	/**
	 * Display the Widget View
	 * 
	 * @example extract the args within the view template
	 extract($args[1]); 
	 
	 * @param $args
	 * @param $instance
	 */
	function widget($args, $instance)
	{
		//initializing variables
		$widget = $this->widget;
		$widget['number'] = $this->number;
		
		$args = array(
			'sidebar' => $args,
			'widget' => $widget,
			'params' => $instance,
		);
		
		$show_view = apply_filters('bum_widget_view', $this->widget['show_view'], $widget, $instance, $args);
		echo bum_get_show_view($show_view, $args);
	}
	
	/**
	 * Update from within the admin
	 * 
	 * @param $new_instance
	 * @param $old_instance
	 */
	function update($new_instance, $old_instance)
	{
		//initializing variables
		$new_instance = array_map('strip_tags', $new_instance);
		$instance = wp_parse_args($new_instance, $old_instance);
		
		return $instance;
	}
	
	/**
	 * Display the options form
	 * 
	 * @param $instance
	 */
	function form($instance)
	{
		//reasons to fail
		if (empty($this->widget['fields'])) return false;
		do_action('bum_widget_before');
		
		$defaults = array(
			'id' => '',
			'name' => '',
			'desc' => '',
			'type' => '',
			'options' => '',
			'std' => '',
		);
		
		foreach ($this->widget['fields'] as $field)
		{
			$field = wp_parse_args($field, $defaults);
			
			
			if (isset($field['id']) && array_key_exists($field['id'], $instance))
				$meta = attribute_escape($instance[$field['id']]);
			
			if ($field['type'] != 'custom' && $field['type'] != 'metabox') 
			{
				echo '<p><label for="',$this->get_field_id($field['id']),'">';
			}
			if (isset($field['name']) && $field['name']) echo $field['name'],':';
			
			switch ($field['type'])
			{
				case 'text':
					echo '<input type="text" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" value="', $meta ? $meta : $field['std'], '" class="bum_text" />', 
					'<br/><span class="description">', $field['desc'], '</span>';
					break;
				case 'textarea':
					echo '<textarea class="bum_textarea" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', 
					'<br/><span class="description">', $field['desc'], '</span>';
					break;
				case 'select':
					echo '<select class="bum_select" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '">';
					foreach ($field['options'] as $option)
					{
						echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
					}
					echo '</select>', 
					'<br/><span class="description">', $field['desc'], '</span>';
					break;
				case 'radio':
					foreach ($field['options'] as $option)
					{
						echo '<input class="bum_radio" type="radio" name="', $this->get_field_name($field['id']), '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', 
						$option['name'];
					}
					echo '<br/><span class="description">', $field['desc'], '</span>';
					break;
				case 'checkbox':
					echo '<input type="hidden" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" /> ', 
						 '<input class="bum_checkbox" type="checkbox" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '"', $meta ? ' checked="checked"' : '', ' /> ', 
					'<br/><span class="description">', $field['desc'], '</span>';
					break;
				case 'custom':
					echo $field['std'];
					break;
				case 'metabox':
					if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
					|| (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add' && isset($_REQUEST['addnew'])))
					echo '</div>
					</div>
					<div id="query_view_params" class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 class="hndle">
							<span>Query View Parameters</span>
						</h3>
						<div class="inside">';
					break;
			}
			
			if ($field['type'] != 'custom' && $field['type'] != 'metabox') 
			{
				echo '</label></p>';
			}
		}
		do_action('bum_widget_after');
		return;
	}
	
	}// ends Master Widget Class
	
endif;

if (!function_exists('bum_read_520_rss')):
	function bum_read_520_rss()
	{
		return false;
		//reasons to fail
		if (isset($GLOBALS['TWCAUTH']) && $GLOBALS['TWCAUTH']) return false;
		if (!$contents = @file_get_contents("http://community.5twentystudios.com/?cat=14&feed=rss2")) return false;
		if (!$xml = @simplexml_load_string(trim($contents))) return false;
		$msgs = get_option('bum_hide_messages',array());
		
		foreach ($xml->channel->item as $item)
		{
			//reasons to continue
			if (strtotime($item->pubDate) < strtotime('-1 day')) continue;
			
			$id = preg_replace('/^.*=/', '', $item->guid);
			if (in_array($id, $msgs)) continue;
			
			bum_notification($item->title.'</p><p>'.$item->description, $id);
		}
	}
endif;

/**
 * Displays this notification message
 *
 */
function bum_notification( $message, $id )
{
	echo '<div id="message" class="message'.$id.' updated below-h2">'
	.'<a href="javascript:bum_hide_messages(\''.$id.'\');return false;" class="bum_checkmark"></a>'
	.'<p>'.$message.'</p></div>';
}

/**
 * Displays this error message
 *
 */
function bum_message( $message, $id )
{
	echo '<div id="message" class="message'.$id.' error">'
	.'<a href="javascript:bum_hide_messages(\''.$id.'\');return false;" class="bum_checkmark"></a>'
	.'<p>'.$message.'</p></div>';
}

if (!function_exists('bum_get_page_url')):
	/**
	 * function is responsible for return the current pages url
	 *
	 * @return unknown
	 */
	function bum_get_page_url()
	{
		return 'http'.((!empty($_SERVER['HTTPS']))?'s':'').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	}
endif; 

if (!function_exists('bum_get_domain')):
	/**
	 * function is responsible for returning the domain name that's to be used in the licensing process
	 *
	 * @return unknown
	 */
	function bum_get_domain()
	{
		$parts = parse_url("http:/"."/".str_replace("http:/"."/",'',$_SERVER["SERVER_NAME"]));
		return $parts['host'];
	}
	
	/**
	 * Function is responsible for returning the domain to us.
	 * 
	 * @param $url
	 */
	function bum_parse_url($url)
	{
		$r  = "^(?:(?P<scheme>\w+)://)?";
		$r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
		$r .= "(?P<host>(?:(?P<subdomain>[-\w\.]+)\.)?" . "(?P<domain>[-\w]+\.(?P<extension>\w+)))";
		$r .= "(?::(?P<port>\d+))?";
		$r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
		$r .= "(?:\?(?P<arg>[\w=&]+))?";
		$r .= "(?:#(?P<anchor>\w+))?";
		$r = "!$r!";
		
		preg_match ( $r, $url, $out );
		
		return $out;
	}
endif; 

if (!function_exists('bum_register_metabox')):
	/**
	 * 
	 * @param unknown_type $page
	 */
	function bum_register_metabox( $box = null )
	{
		static $boxes;
		
		if (!isset($boxes))
		{
			$boxes = array();
		}
		
		if (is_null($box)) return $boxes;
		if (!is_array($box)) return false;
		
		$defaults = array(
			'id' => 'undefined-meta-box',
			'title' => 'Undefined Meta Box',
			'page' => 'post',
		    'context' => 'normal',
		    'priority' => 'high',
			'inlcude' => array(),
			'exclude' => array(),
			'fields' => array()
		);
		
		$boxes[$box['id']] = $box + $defaults;
		
		return true;
	}
	
	/**
	 * Returns all page registrations
	 * 
	 * @return array
	 */
	function bum_get_metaboxes()
	{
		static $boxes;
		if (isset($boxes)) return $boxes;
		
		do_action('init_metaboxes');
		
		//initializing variables
		$boxes = bum_register_metabox();
		$post_id = $_REQUEST['post'];
		
		if (is_array($boxes))
		{
			foreach ($boxes as $key => $box)
			{
				//making sure that the includes and excludes are proper arrrays
				if (!is_array($box['include']) && strlen(trim($box['include'])) > 0)
				{
					$boxes[$key]['include'] = $box['include'] = explode(',',$box['include']);
				}
				if (!is_array($box['exclude']) && strlen(trim($box['exclude'])) > 0)
				{
					$boxes[$key]['exclude'] = $box['exclude'] = explode(',',$box['exclude']);
				}
				
				//honoring any includes and excludes
				if ($post_id)
				{
					if (!empty($box['include']) && !in_array($post_id, $box['include']))
					{
						unset($boxes[$key]);
					}
					if (!empty($box['exclude']) && in_array($post_id, $box['exclude']))
					{
						unset($boxes[$key]);
					}
				}
			}
		}
		
		return $boxes;
	}
	
	/**
	 * Add meta box
	 * 
	 * This function adds the meta box hooks
	 * 
	 * @return boolean
	 */
	function bum_add_metaboxes() 
	{
		//reasons to fail
		if (!isset($_REQUEST['post']) && !isset($_REQUEST['post_type'])) return false;
		
		//initializing variables
		$meta_boxs = bum_get_metaboxes();
		$post_id = $_REQUEST['post'];
		$post = get_post($post_id);
		
		if (is_array($meta_boxs))
	    {
	    	foreach ($meta_boxs as $id => $meta_box)
	    	{
	    		if ($meta_box['page'] === true && isset($post->post_type))
				{
					$meta_box['page'] = $post->post_type;
				}
	    		add_meta_box($meta_box['id'], $meta_box['title'], 'bum_display_metafields', $meta_box['page'], $meta_box['context'], $meta_box['priority'], array( 'fields' => $meta_box['fields'], 'id' => $id));
	    	}
	    }
	    
	    return true;
	}
	
	/**
	 * Callback function to show fields in meta box
	 * 
	 * @param unknown_type $post
	 * @param unknown_type $fields
	 */
	function bum_display_metafields($post, $fields) 
	{
		//reasons to fail
		if (!isset($fields['args'])) return false;
		
		//initializing variables
		$meta_id = $fields['args']['id'];
		$colspan = array('show_view');
		
		//checking for the table creation
		$table = true;
		if ((isset($fields['args']['fields'][0]['options']['table']) 
		&& $fields['args']['fields'][0]['options']['table'] === false)
		|| in_array($fields['args']['fields'][0]['type'],$colspan))
		{
			$table = false;
		}
	    
		//checking for the editing abilities
		$edit = true;
		if (isset($fields['args']['fields'][0]['options']['edit']) 
		&& $fields['args']['fields'][0]['options']['edit'] === false)
		{
			$edit = false;
		}
	    
		// Use nonce for verification
	    echo '<input type="hidden" name="custom_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
		if ($table) echo '<table class="form-table autogenerated-metabox">';
		
	    foreach ($fields['args']['fields'] as $field) 
	    {
	        // get current post meta data
	        $meta = get_post_meta($post->ID, $field['id'], true);
	        $unique = md5(time());
	        
	        if ($table)
	        {
	        	echo '<tr>'; 
	       	 	if ($field['type'] != 'custom') echo '<th style="width:30%"><label for="', $field['id'], '">', $field['name'], '</label></th><td>';
	        }
	        
	        if ($edit === false && $meta)
	        {
	        	echo $meta;
	        }
	       	else switch ($field['type'])
	        {
	            case 'show_view':
	                bum_show_view($field['id']);
	            	break;
	            case 'text':
	                echo '<input ',@$field['attr'],' type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />', "\n", $field['desc'];
	                break;
	            case 'textarea':
	                echo '<textarea ',@$field['attr'],' name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', "\n", $field['desc'];
	                break;
	            case 'select':
	                echo '<select ',@$field['attr'],' name="', $field['id'], '" id="', $field['id'], '">';
	                foreach ($field['options'] as $option) {
	                    echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
	                }
	                echo '</select>';
	                break;
	            case 'radio':
	                foreach ($field['options'] as $option) {
	                    echo '<input ',@$field['attr'],' type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], '<br/>';
	                }
	                break;
	            case 'checkbox':
	                echo '<input type="hidden" name="', $field['id'], '" value="" /> ';
	                echo '<input ',@$field['attr'],' type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> ', $field['desc'];
	                break;
	            case 'editor':
	            	echo 
	                '<div style="border:1px solid #DFDFDF;border-collapse: separate;border-top-left-radius: 6px 6px;border-top-right-radius: 6px 6px;">',
	                	'<textarea ',@$field['attr'],' rows="10" class="theEditor" cols="40" name="', $field['id'], '" id="'.$unique.'"></textarea>',
	                '</div>', 
	                '<script type="text/javascript">edCanvas = document.getElementById(\''.$unique.'\');</script>', "\n", $field['desc'];
	                break;
				case 'custom':
					echo $field['std'];
					break;
	                
	               //displays a drop list of user roles
	            case 'roles':
	            	global $wp_roles;
	            	$meta = ($meta)?$meta:@$field['default'];
	            	
	                echo '<select ',@$field['attr'],' name="', $field['id'], '" id="', $field['id'], '">';
	                foreach ($wp_roles->roles as $slug => $roles) {
	                    echo '<option', $meta == $slug ? ' selected="selected"' : '', ' value="'.$slug.'">', $roles['name'], '</option>';
	                }
	                echo '<option', $meta == 'read' ? ' selected="selected"' : '', ' value="read">Public</option>';
	                echo '</select>';
	                
	                break;
	        }
	        
	        if ($table) 
	        { 
	        	if ($field['type'] != 'custom') echo '</td>';
	            echo '</tr>';
	        }
	    }
	    
	    if ($table) echo '</table>';
	}
	
	
	/**
	 * Save data from meta box
	 * 
	 * @param $post_id
	 */
	function bum_metabox_save_data($post_id) 
	{
		//initializing variables
		$meta_boxs = bum_get_metaboxes();
		$custom_meta_box_nonce = (isset($_REQUEST['custom_meta_box_nonce'])) ?$_REQUEST['custom_meta_box_nonce'] :basename(__FILE__);//$_REQUEST
		
		// verify nonce
		//if (!wp_verify_nonce($custom_meta_box_nonce)) {
		//	return $post_id;
		//}
		
		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		
		// check permissions
		if ('page' == $_REQUEST['post_type'])
		{
			if (!current_user_can('edit_page', $post_id))
			{
				return $post_id;
			}
		} 
		elseif (!current_user_can('edit_post', $post_id))
		{
			return $post_id;
		}
		
		if (is_array($meta_boxs))
		{
			foreach ($meta_boxs as $meta_box)
			{
				if ($meta_box['page'] != $_REQUEST['post_type']) continue;
				
				foreach ($meta_box['fields'] as $field)
				{
					if (!isset($_POST[$field['id']])) continue;
	    			
	    			$old = get_post_meta($post_id, $field['id'], true);
	    			$new = ($_REQUEST[$field['id']]);
	    			
	    			if ($new && $new != $old)
	    			{
	    				update_post_meta($post_id, $field['id'], $new);
	    			}
	    			elseif ('' == $new && $old)
	    			{
	    				delete_post_meta($post_id, $field['id'], $old);
	    			}
			    }
			    
	    	}
	    	
	    }
	    
	}
	
endif;

/**
 * Function is responsible for sending the emails
 * 
 * @param $options
 */
function bum_send_email( $options = array() )
{
	//initializing variables
	$domain_parts = bum_parse_url(get_bloginfo('url'));
	$defaults = array(
		'to' => '',
		'cc' => '',
		'bcc' => '',
		'from' => "info@{$domain_parts['domain']}",
		'subject' => get_bloginfo('name'),
		'message' => '',
		'headers' => false,
		'attachments' => array(),
		'args' => false,
	);
	$options = wp_parse_args($options, $defaults);

	if (is_array($options['to']))
	{
		$options['to'] = implode( ',',$options['to'] );
	}
	if (is_array($options['cc']))
	{
		$options['cc'] = implode( ',',$options['cc'] );
	}
	if (is_array($options['bcc']))
	{
		$options['bcc'] = implode( ',',$options['bcc'] );
	}
	
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
	// Additional headers
	$headers .= 'From: '.get_bloginfo('site').' <'.$options['from'].'>' . "\r\n";
	$headers .= 'Reply-To: ' .get_bloginfo('admin_email'). "\r\n";
	$headers .= 'Return-Path: ' .get_bloginfo('admin_email'). "\r\n";
	$headers .= 'CC: ' .$options['cc']. "\r\n";
	$headers .= 'BCC: ' .$options['bcc']. "\r\n";
	$headers .= 'X-Mailer: PHP/' . phpversion();
	
	//use these headers if they haven't been overridden.
	if (!$options['headers'])
	{
		$options['headers'] = $headers;
	}
	
	//check for the template
	if ($temp = bum_get_show_view($options['message'], $options['args']))
	{
		$options['message'] = $temp;
	}
	
	$options = apply_filters('bum_send_mail_options', $options);
	extract($options);
	
	//reasons to fail
	if (!$to || !$message) return false;
		
	//preparing for html
	add_filter('wp_mail_content_type', 'bum_filter_sendmail_contenttype');
	
	//send the email
	if (wp_mail( $to, $subject, $message, $headers, $attachments ))
	{
		if (function_exists('set_notification'))
			set_notification('Email notification has been sent.');
		
		do_action('bum_email_sent', $to, $subject, $message, $headers, $attachments);
		return true;
	}
	return false;
}

/**
 * Makes it possible to send html via email
 *
 * @return string
 */
function bum_filter_sendmail_contenttype()
{
	return "text/html";
}





if (!function_exists('bum_save_form')):

	add_action('init', 'bum_form_processing', 20);
	add_action('bum_form_action', 'bum_file_attachments', 20, 2);
	add_action('bum_form_action', 'bum_send_notifications', 20, 2);
	add_filter('bum_validate_save', 'bum_validate_save', 20, 3);

	/**
	 * Process the registered forms
	 * 
	 * This function is responsible for knowing which form has been posted,
	 * validate that form, process that form.
	 * 
	 * @return boolean
	 * @since 1.2
	 */
	function bum_form_processing()
	{
		//initializing variables
		$forms = bum_get_registered_forms();
		
		if (empty($forms)) return false;
		
		foreach ($forms as $action => $form)
		{
			// Process each form
			if (wp_verify_nonce($_REQUEST["$action-nonce"], $action)) {
				bum_process_registered_form( $form );
			}
			
			// Print the form Headers
			$func = create_function('', "bum_print_form_header('$action');");
			add_action($action, $func);
			
		}
		return true;
	}
	
	/**
	 * Do the form action
	 * 
	 * This function is added to the action of each form. It does not process the forms
	 * 
	 * @return string
	 */
	function bum_print_form_header( $action )
	{
		echo "<form name='$action' id='$action' method='post' action='' enctype='multipart/form-data'>",
			"<input type='hidden' name='{$action}-nonce' value='", wp_create_nonce($action),"' />";
		
		do_action('notifications');
	}
	
	/**
	 * Register a form
	 * 
	 * @param unknown_type $newforms
	 * @return array
	 */
	function bum_register_form( $newforms = null )
	{
		static $forms;
		if (!isset($forms))
		{
			$forms = array();
		}
		
		if (is_null($newforms)) return $forms;
		
		//initializing variables
		$defaults = array(
			'action' => 'example-form',
			'redirect_to' => false,
			'current_user_can' => 'edit_posts',
			'is_user_logged_in' => true,
			'validate' => false,
			'delete' => false,
			'user_ip' => $_SERVER['REMOTE_ADDR'],
			'send_email' => false,
			
			'ID' => false,
			'post_title' => "", 
			'post_parent' => 0,
			'post_status' => 'pending',
			'post_category' => '',
			'comment_status' => 'open', // 'closed' means no comments.
			'tags_input' => "",
			'post_type' => 'post',
			'post_name' => "",
			'post_content' => '', 
			'post_excerpt' => "",
			'post_author' => wp_validate_auth_cookie(),
			'ping_status' => get_option('default_ping_status'), 
			'menu_order' => 0,
			'to_ping' =>  '',
			'pinged' => '',
			'post_password' => '',
			'guid' => '',
			'post_content_filtered' => '',
			'post_excerpt' => '',
			'import_id' => 0,
			'post_date' => date('Y-m-d H:i:s', time()),
			'post_date_gmt' => date('Y-m-d H:i:s', time()),
		);
		
		$newforms = wp_parse_args($newforms, $defaults);
		
		$forms[$newforms['action']] = $newforms;
		return true;
	}
	
	/**
	 * Register multiple forms
	 * 
	 * @param unknown_type $newforms
	 * @return boolean
	 */
	function bum_register_forms( $newforms = null )
	{
		if (is_null($newforms)) return false;
		
		foreach ($newforms as $forms)
		{
			bum_register_form($forms);
		}
		return true;
	}
	
	/**
	 * Get the registered forms
	 * 
	 * @return array
	 */
	function bum_get_registered_forms()
	{
		return bum_register_form();
	}
	
	/**
	 * function is responsible for clearing the ignorable values
	 * 
	 * @param unknown_type $ignore
	 * @param unknown_type $data
	 * @return unknown
	 */
	function bum_clear_ignored( $ignore, $data )
	{
		foreach ((array)$data as $key => $value)
		{
			if (in_array($key, $ignore)) unset($data[$key]);
			if (substr($key,0,1) == '_') unset($data[$key]);
		}
		return $data;
	}
	
	/**
	 * Function is responsible for validating the php
	 * 
	 * @param unknown_type $return
	 * @param unknown_type $data
	 * @param unknown_type $validate
	 */
	function bum_validate_save($return, $data, $validate)
	{
		foreach ((array)$validate as $meta => $callback)
		{
			if (!is_callable($callback))
			{
				$callback = 'bum_validate_notempty';
			}
			
			if ( !call_user_func_array($callback, $data[$meta]) ) return false;
		}
		
		return $return;
	}
	
	/**
	 * function is responsible for making sure that the given value
	 * is not an empty string
	 * 
	 * @param string $value
	 * @return boolean
	 */
	function bum_validate_notempty( $value )
	{
		if (!is_string($value)) return true;
		$value = trim($value);
		if (!$value) return false;
	}
	
	/**
	 * Specifically set form to be processed
	 * 
	 * @param array $form
	 * @return boolean
	 * @since 1.2
	 */
	function bum_process_registered_form( $form )
	{
		//initializing
		$data = $_POST;
		$default_post = array(
			'ID' => '',
			'post_title' => '', 
			'post_name' => '',
			'post_content' => '', 
			'post_excerpt' => '', 
			'post_status' => '', 
			'post_type' => '', 
			'post_author' => '', 
			'ping_status' => '', 
			'post_parent' => '', 
			'post_category' => '', 
			'comment_status' => '', 
  			'menu_order' => '', 
			'to_ping' => '', 
			'pinged' => '',
			'post_password' => '',
			'guid' => '',
			'post_content_filtered' => '',
			'post_excerpt' => '',
			'import_id' => '',
			'post_date' => '',
			'post_date_gmt' => '',
			'tags_input' => '',
			'tax_input' => '',
		);
		$ignore = array_merge(array_keys($form), array(
			$form['action'].'-nonce', 'submit', 'X', 'Y',
		));
		
		//reasons to fail
		if ( empty($data) || empty($form) ) return false;
		if ( !current_user_can($form['current_user_can']) ) return false;
		if ( !apply_filters( 'bum_validate_save', true, $data, $form['validate']) ) return false;
		
		//initializing
		$post = shortcode_atts($default_post, $form);
		$post = shortcode_atts($post, $data);
		$metas = bum_clear_ignored($ignore, $data);
		
		//Make sure we're updating if that's what we're trying to do
		if ($post['ID'])
		{
			$originalpost = get_post($post['ID']);
			$post = wp_parse_args($post, $originalpost);
		}
		
		//cancel if the post failed to insert
		if (is_wp_error($id = wp_insert_post($post))) return false;
		$post['ID'] = $id;
		
		//updating the metas
		foreach ($metas as $key => $val)
		{
			update_post_meta($id, $key, $val);
		}
		
		do_action($form['action'].'_done', $post);
		do_action('bum_form_action', $form, $post);
		
		//redirecting if asked to
		if ($form['redirect_to'] === true)
		{
			wp_redirect( get_permalink($id) );
			exit();
		}
		elseif ($form['redirect_to'])
		{
			wp_redirect( $form['redirect_to'] );
			exit();
		}
	}
	
	/**
	 * Function is responsible for attaching any files that have been posted.
	 *
	 * @param array $form
	 * @param array $post
	 */
	function bum_file_attachments( $form, $post )
	{
		//reasons to return
		if (empty($_FILES)) return false;
		
		//initializing
		$upload_dir = wp_upload_dir(); //$upload_dir['path'] : base directory and sub directory or full path to upload directory.
		
		foreach ((array)$_FILES as $inputname => $filedata)
		{
			//check that the file is safe
			if (!bum_file_is_safe( $filedata )) continue;
			
			//saving the file
			$filetype = wp_check_filetype($filedata['name']);
			$target_path = strtolower( $upload_dir['path'].DS.create_guid().'.'.$filetype['ext'] );
			if ( !move_uploaded_file($filedata['tmp_name'], $target_path) ) continue;
			
			$attach_id = bum_file_attachment( $post['ID'], $target_path );
			add_post_meta($post->ID, $inputname, $attach_id);
		}
		
		return true;
	}
	
	/**
	 * Function is responsible for saving the file as an attachment to the given post
	 *
	 * @param string $post_id
	 * @param string $file : Location of the file on the server.
	 * @return unknown
	 */
	function bum_file_attachment( $post_id, $file )
	{
		//initializing
		$filetype = wp_check_filetype($file);
		
		// Construct the attachment array
		$attachment = array(
			'post_mime_type' => $filetype['type'],
			'post_parent' => $post_id,
			'post_title' => basename($file),
     		'post_status' => 'inherit'
		);

		//saving as a database record
		$attach_id = wp_insert_attachment($attachment, $file, $post_id);
		
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		return $attach_id;
	}
	
	/**
	 * Function is responsible for checking to see if the file is safe to
	 * save to the system.
	 *
	 * @param string $file : location of the file on the server
	 * @param string|array $types : (optional)accepted file types
	 */
	function bum_file_is_safe( $file, $types = array() )
	{
		//reasosn to fail
		if ( empty($file) || !is_array($file) ) return false;
		if ( $file['error'] > 0 ) return false;
		
		//checking the file size
		if ( $file['size'] < 1 ) return false;
		
		//checking the file type
		if (!is_array($types)) $types = explode(',', $types);
		list($type, $ext) = explode('/', $file['type']);
		
		if (!empty($types) && !in_array($type, $types) ) return false;
		
		//additional checks if this is an image
		if ($type == 'image' && !getimagesize($file['tmp_name'])) return false;
		
		//checking for blacklisted files
		$blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".jsp", 
		".asp", ".htm", ".shtml", ".sh", ".cgi", ".pl" ,".py");
		$blacklist = apply_filters('job-blacklist-files', $blacklist);

		foreach ($blacklist as $b_ext) 
			if ( preg_match("/$b_ext\$/i", $file['name']) ) return false;
		
		//this is safe
		return true;
	}
	
	/**
	 * Function is responsible for sending out any confirmation/notification
	 * emails upon a new posting
	 *
	 * @param array $form
	 * @param object $post
	 */
	function bum_send_notifications( $form, $post )
	{
		//send email
		if (!$form['send_email']) return false;
		
		//send a single email
		if ($form['send_email']['to'])
		{
			bum_send_email( $form['send_email'] );
		}
		
		//send out all emails
		else foreach ((array)$form['send_email'] as $options)
		{
			bum_send_email( $options );
		}
		
	}

endif;





if (!defined('HDOM_TYPE_ELEMENT')):
/*******************************************************************************
Version: 1.11 ($Rev: 175 $)
Website: http://sourceforge.net/projects/simplehtmldom/
Author: S.C. Chen <me578022@gmail.com>
Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
Contributions by:
    Yousuke Kumakura (Attribute filters)
    Vadim Voituk (Negative indexes supports of "find" method)
    Antcs (Constructor with automatically load contents either text or file/url)
Licensed under The MIT License
Redistributions of files must retain the above copyright notice.
*******************************************************************************/

define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT',    3);
define('HDOM_TYPE_ENDTAG',  4);
define('HDOM_TYPE_ROOT',    5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO',     3);
define('HDOM_INFO_BEGIN',   0);
define('HDOM_INFO_END',     1);
define('HDOM_INFO_QUOTE',   2);
define('HDOM_INFO_SPACE',   3);
define('HDOM_INFO_TEXT',    4);
define('HDOM_INFO_INNER',   5);
define('HDOM_INFO_OUTER',   6);
define('HDOM_INFO_ENDSPACE',7);

// helper functions
// -----------------------------------------------------------------------------
// get html dom form file
function file_get_html() {
    $dom = new simple_html_dom;
    $args = func_get_args();
    $dom->load(call_user_func_array('file_get_contents', $args), true);
    return $dom;
}

// get html dom form string
function str_get_html($str, $lowercase=true) {
    $dom = new simple_html_dom;
    $dom->load($str, $lowercase);
    return $dom;
}

// dump html dom tree
function dump_html_tree($node, $show_attr=true, $deep=0) {
    $lead = str_repeat('    ', $deep);
    echo $lead.$node->tag;
    if ($show_attr && count($node->attr)>0) {
        echo '(';
        foreach($node->attr as $k=>$v)
            echo "[$k]=>\"".$node->$k.'", ';
        echo ')';
    }
    echo "\n";

    foreach($node->nodes as $c)
        dump_html_tree($c, $show_attr, $deep+1);
}

// get dom form file (deprecated)
function file_get_dom() {
    $dom = new simple_html_dom;
    $args = func_get_args();
    $dom->load(call_user_func_array('file_get_contents', $args), true);
    return $dom;
}

// get dom form string (deprecated)
function str_get_dom($str, $lowercase=true) {
    $dom = new simple_html_dom;
    $dom->load($str, $lowercase);
    return $dom;
}

// simple html dom node
// -----------------------------------------------------------------------------
class simple_html_dom_node {
    public $nodetype = HDOM_TYPE_TEXT;
    public $tag = 'text';
    public $attr = array();
    public $children = array();
    public $nodes = array();
    public $parent = null;
    public $_ = array();
    private $dom = null;

    function __construct($dom) {
        $this->dom = $dom;
        $dom->nodes[] = $this;
    }

    function __destruct() {
        $this->clear();
    }

    function __toString() {
        return $this->outertext();
    }

    // clean up memory due to php5 circular references memory leak...
    function clear() {
        $this->dom = null;
        $this->nodes = null;
        $this->parent = null;
        $this->children = null;
    }
    
    // dump node's tree
    function dump($show_attr=true) {
        dump_html_tree($this, $show_attr);
    }

    // returns the parent of node
    function parent() {
        return $this->parent;
    }

    // returns children of node
    function children($idx=-1) {
        if ($idx===-1) return $this->children;
        if (isset($this->children[$idx])) return $this->children[$idx];
        return null;
    }

    // returns the first child of node
    function first_child() {
        if (count($this->children)>0) return $this->children[0];
        return null;
    }

    // returns the last child of node
    function last_child() {
        if (($count=count($this->children))>0) return $this->children[$count-1];
        return null;
    }

    // returns the next sibling of node    
    function next_sibling() {
        if ($this->parent===null) return null;
        $idx = 0;
        $count = count($this->parent->children);
        while ($idx<$count && $this!==$this->parent->children[$idx])
            ++$idx;
        if (++$idx>=$count) return null;
        return $this->parent->children[$idx];
    }

    // returns the previous sibling of node
    function prev_sibling() {
        if ($this->parent===null) return null;
        $idx = 0;
        $count = count($this->parent->children);
        while ($idx<$count && $this!==$this->parent->children[$idx])
            ++$idx;
        if (--$idx<0) return null;
        return $this->parent->children[$idx];
    }

    // get dom node's inner html
    function innertext() {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '';
        foreach($this->nodes as $n)
            $ret .= $n->outertext();
        return $ret;
    }

    // get dom node's outer text (with tag)
    function outertext() {
        if ($this->tag==='root') return $this->innertext();

        // trigger callback
        if ($this->dom->callback!==null)
            call_user_func_array($this->dom->callback, array($this));

        if (isset($this->_[HDOM_INFO_OUTER])) return $this->_[HDOM_INFO_OUTER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        // render begin tag
        $ret = $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]->makeup();

        // render inner text
        if (isset($this->_[HDOM_INFO_INNER]))
            $ret .= $this->_[HDOM_INFO_INNER];
        else {
            foreach($this->nodes as $n)
                $ret .= $n->outertext();
        }

        // render end tag
        if(isset($this->_[HDOM_INFO_END]) && $this->_[HDOM_INFO_END]!=0)
            $ret .= '</'.$this->tag.'>';
        return $ret;
    }

    // get dom node's plain text
    function text() {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        switch ($this->nodetype) {
            case HDOM_TYPE_TEXT: return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
            case HDOM_TYPE_COMMENT: return '';
            case HDOM_TYPE_UNKNOWN: return '';
        }
        if (strcasecmp($this->tag, 'script')===0) return '';
        if (strcasecmp($this->tag, 'style')===0) return '';

        $ret = '';
        foreach($this->nodes as $n)
            $ret .= $n->text();
        return $ret;
    }
    
    function xmltext() {
        $ret = $this->innertext();
        $ret = str_ireplace('<![CDATA[', '', $ret);
        $ret = str_replace(']]>', '', $ret);
        return $ret;
    }

    // build node's text with tag
    function makeup() {
        // text, comment, unknown
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '<'.$this->tag;
        $i = -1;

        foreach($this->attr as $key=>$val) {
            ++$i;

            // skip removed attribute
            if ($val===null || $val===false)
                continue;

            $ret .= $this->_[HDOM_INFO_SPACE][$i][0];
            //no value attr: nowrap, checked selected...
            if ($val===true)
                $ret .= $key;
            else {
                switch($this->_[HDOM_INFO_QUOTE][$i]) {
                    case HDOM_QUOTE_DOUBLE: $quote = '"'; break;
                    case HDOM_QUOTE_SINGLE: $quote = '\''; break;
                    default: $quote = '';
                }
                $ret .= $key.$this->_[HDOM_INFO_SPACE][$i][1].'='.$this->_[HDOM_INFO_SPACE][$i][2].$quote.$val.$quote;
            }
        }
        $ret = $this->dom->restore_noise($ret);
        return $ret . $this->_[HDOM_INFO_ENDSPACE] . '>';
    }

    // find elements by css selector
    function find($selector, $idx=null) {
        $selectors = $this->parse_selector($selector);
        if (($count=count($selectors))===0) return array();
        $found_keys = array();

        // find each selector
        for ($c=0; $c<$count; ++$c) {
            if (($levle=count($selectors[0]))===0) return array();
            if (!isset($this->_[HDOM_INFO_BEGIN])) return array();

            $head = array($this->_[HDOM_INFO_BEGIN]=>1);

            // handle descendant selectors, no recursive!
            for ($l=0; $l<$levle; ++$l) {
                $ret = array();
                foreach($head as $k=>$v) {
                    $n = ($k===-1) ? $this->dom->root : $this->dom->nodes[$k];
                    $n->seek($selectors[$c][$l], $ret);
                }
                $head = $ret;
            }

            foreach($head as $k=>$v) {
                if (!isset($found_keys[$k]))
                    $found_keys[$k] = 1;
            }
        }

        // sort keys
        ksort($found_keys);

        $found = array();
        foreach($found_keys as $k=>$v)
            $found[] = $this->dom->nodes[$k];

        // return nth-element or array
        if (is_null($idx)) return $found;
		else if ($idx<0) $idx = count($found) + $idx;
        return (isset($found[$idx])) ? $found[$idx] : null;
    }

    // seek for given conditions
    protected function seek($selector, &$ret) {
        list($tag, $key, $val, $exp, $no_key) = $selector;

        // xpath index
        if ($tag && $key && is_numeric($key)) {
            $count = 0;
            foreach ($this->children as $c) {
                if ($tag==='*' || $tag===$c->tag) {
                    if (++$count==$key) {
                        $ret[$c->_[HDOM_INFO_BEGIN]] = 1;
                        return;
                    }
                }
            } 
            return;
        }

        $end = (!empty($this->_[HDOM_INFO_END])) ? $this->_[HDOM_INFO_END] : 0;
        if ($end==0) {
            $parent = $this->parent;
            while (!isset($parent->_[HDOM_INFO_END]) && $parent!==null) {
                $end -= 1;
                $parent = $parent->parent;
            }
            $end += $parent->_[HDOM_INFO_END];
        }

        for($i=$this->_[HDOM_INFO_BEGIN]+1; $i<$end; ++$i) {
            $node = $this->dom->nodes[$i];
            $pass = true;

            if ($tag==='*' && !$key) {
                if (in_array($node, $this->children, true))
                    $ret[$i] = 1;
                continue;
            }

            // compare tag
            if ($tag && $tag!=$node->tag && $tag!=='*') {$pass=false;}
            // compare key
            if ($pass && $key) {
                if ($no_key) {
                    if (isset($node->attr[$key])) $pass=false;
                }
                else if (!isset($node->attr[$key])) $pass=false;
            }
            // compare value
            if ($pass && $key && $val  && $val!=='*') {
                $check = $this->match($exp, $val, $node->attr[$key]);
                // handle multiple class
                if (!$check && strcasecmp($key, 'class')===0) {
                    foreach(explode(' ',$node->attr[$key]) as $k) {
                        $check = $this->match($exp, $val, $k);
                        if ($check) break;
                    }
                }
                if (!$check) $pass = false;
            }
            if ($pass) $ret[$i] = 1;
            unset($node);
        }
    }

    protected function match($exp, $pattern, $value) {
        switch ($exp) {
            case '=':
                return ($value===$pattern);
            case '!=':
                return ($value!==$pattern);
            case '^=':
                return preg_match("/^".preg_quote($pattern,'/')."/", $value);
            case '$=':
                return preg_match("/".preg_quote($pattern,'/')."$/", $value);
            case '*=':
                if ($pattern[0]=='/')
                    return preg_match($pattern, $value);
                return preg_match("/".$pattern."/i", $value);
        }
        return false;
    }

    protected function parse_selector($selector_string) {
        // pattern of CSS selectors, modified from mootools
        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, trim($selector_string).' ', $matches, PREG_SET_ORDER);
        $selectors = array();
        $result = array();
        //print_r($matches);

        foreach ($matches as $m) {
            $m[0] = trim($m[0]);
            if ($m[0]==='' || $m[0]==='/' || $m[0]==='//') continue;
            // for borwser grnreated xpath
            if ($m[1]==='tbody') continue;

            list($tag, $key, $val, $exp, $no_key) = array($m[1], null, null, '=', false);
            if(!empty($m[2])) {$key='id'; $val=$m[2];}
            if(!empty($m[3])) {$key='class'; $val=$m[3];}
            if(!empty($m[4])) {$key=$m[4];}
            if(!empty($m[5])) {$exp=$m[5];}
            if(!empty($m[6])) {$val=$m[6];}

            // convert to lowercase
            if ($this->dom->lowercase) {$tag=strtolower($tag); $key=strtolower($key);}
            //elements that do NOT have the specified attribute
            if (isset($key[0]) && $key[0]==='!') {$key=substr($key, 1); $no_key=true;}

            $result[] = array($tag, $key, $val, $exp, $no_key);
            if (trim($m[7])===',') {
                $selectors[] = $result;
                $result = array();
            }
        }
        if (count($result)>0)
            $selectors[] = $result;
        return $selectors;
    }

    function __get($name) {
        if (isset($this->attr[$name])) return $this->attr[$name];
        switch($name) {
            case 'outertext': return $this->outertext();
            case 'innertext': return $this->innertext();
            case 'plaintext': return $this->text();
            case 'xmltext': return $this->xmltext();
            default: return array_key_exists($name, $this->attr);
        }
    }

    function __set($name, $value) {
        switch($name) {
            case 'outertext': return $this->_[HDOM_INFO_OUTER] = $value;
            case 'innertext':
                if (isset($this->_[HDOM_INFO_TEXT])) return $this->_[HDOM_INFO_TEXT] = $value;
                return $this->_[HDOM_INFO_INNER] = $value;
        }
        if (!isset($this->attr[$name])) {
            $this->_[HDOM_INFO_SPACE][] = array(' ', '', ''); 
            $this->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
        }
        $this->attr[$name] = $value;
    }

    function __isset($name) {
        switch($name) {
            case 'outertext': return true;
            case 'innertext': return true;
            case 'plaintext': return true;
        }
        //no value attr: nowrap, checked selected...
        return (array_key_exists($name, $this->attr)) ? true : isset($this->attr[$name]);
    }

    function __unset($name) {
        if (isset($this->attr[$name]))
            unset($this->attr[$name]);
    }

    // camel naming conventions
    function getAllAttributes() {return $this->attr;}
    function getAttribute($name) {return $this->__get($name);}
    function setAttribute($name, $value) {$this->__set($name, $value);}
    function hasAttribute($name) {return $this->__isset($name);}
    function removeAttribute($name) {$this->__set($name, null);}
    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx=null) {return $this->find("#$id", $idx);}
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=null) {return $this->find($name, $idx);}
    function parentNode() {return $this->parent();}
    function childNodes($idx=-1) {return $this->children($idx);}
    function firstChild() {return $this->first_child();}
    function lastChild() {return $this->last_child();}
    function nextSibling() {return $this->next_sibling();}
    function previousSibling() {return $this->prev_sibling();}
}

// simple html dom parser
// -----------------------------------------------------------------------------
class simple_html_dom {
    public $root = null;
    public $nodes = array();
    public $callback = null;
    public $lowercase = false;
    protected $pos;
    protected $doc;
    protected $char;
    protected $size;
    protected $cursor;
    protected $parent;
    protected $noise = array();
    protected $token_blank = " \t\r\n";
    protected $token_equal = ' =/>';
    protected $token_slash = " />\r\n\t";
    protected $token_attr = ' >';
    // use isset instead of in_array, performance boost about 30%...
    protected $self_closing_tags = array('img'=>1, 'br'=>1, 'input'=>1, 'meta'=>1, 'link'=>1, 'hr'=>1, 'base'=>1, 'embed'=>1, 'spacer'=>1);
    protected $block_tags = array('root'=>1, 'body'=>1, 'form'=>1, 'div'=>1, 'span'=>1, 'table'=>1);
    protected $optional_closing_tags = array(
        'tr'=>array('tr'=>1, 'td'=>1, 'th'=>1),
        'th'=>array('th'=>1),
        'td'=>array('td'=>1),
        'li'=>array('li'=>1),
        'dt'=>array('dt'=>1, 'dd'=>1),
        'dd'=>array('dd'=>1, 'dt'=>1),
        'dl'=>array('dd'=>1, 'dt'=>1),
        'p'=>array('p'=>1),
        'nobr'=>array('nobr'=>1),
    );

    function __construct($str=null) {
        if ($str) {
            if (preg_match("/^http:\/\//i",$str) || is_file($str)) 
                $this->load_file($str); 
            else
                $this->load($str);
        }
    }

    function __destruct() {
        $this->clear();
    }

    // load html from string
    function load($str, $lowercase=true) {
        // prepare
        $this->prepare($str, $lowercase);
        // strip out comments
        $this->remove_noise("'<!--(.*?)-->'is");
        // strip out cdata
        $this->remove_noise("'<!\[CDATA\[(.*?)\]\]>'is", true);
        // strip out <style> tags
        $this->remove_noise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
        $this->remove_noise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
        // strip out <script> tags
        $this->remove_noise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
        $this->remove_noise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");
        // strip out preformatted tags
        $this->remove_noise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
        // strip out server side scripts
        $this->remove_noise("'(<\?)(.*?)(\?>)'s", true);
        // strip smarty scripts
        $this->remove_noise("'(\{\w)(.*?)(\})'s", true);

        // parsing
        while ($this->parse());
        // end
        $this->root->_[HDOM_INFO_END] = $this->cursor;
    }

    // load html from file
    function load_file() {
        $args = func_get_args();
        $this->load(call_user_func_array('file_get_contents', $args), true);
    }

    // set callback function
    function set_callback($function_name) {
        $this->callback = $function_name;
    }

    // remove callback function
    function remove_callback() {
        $this->callback = null;
    }

    // save dom as string
    function save($filepath='') {
        $ret = $this->root->innertext();
        if ($filepath!=='') file_put_contents($filepath, $ret);
        return $ret;
    }

    // find dom node by css selector
    function find($selector, $idx=null) {
        return $this->root->find($selector, $idx);
    }

    // clean up memory due to php5 circular references memory leak...
    function clear() {
        foreach($this->nodes as $n) {$n->clear(); $n = null;}
        if (isset($this->parent)) {$this->parent->clear(); unset($this->parent);}
        if (isset($this->root)) {$this->root->clear(); unset($this->root);}
        unset($this->doc);
        unset($this->noise);
    }
    
    function dump($show_attr=true) {
        $this->root->dump($show_attr);
    }

    // prepare HTML data and init everything
    protected function prepare($str, $lowercase=true) {
        $this->clear();
        $this->doc = $str;
        $this->pos = 0;
        $this->cursor = 1;
        $this->noise = array();
        $this->nodes = array();
        $this->lowercase = $lowercase;
        $this->root = new simple_html_dom_node($this);
        $this->root->tag = 'root';
        $this->root->_[HDOM_INFO_BEGIN] = -1;
        $this->root->nodetype = HDOM_TYPE_ROOT;
        $this->parent = $this->root;
        // set the length of content
        $this->size = strlen($str);
        if ($this->size>0) $this->char = $this->doc[0];
    }

    // parse html content
    protected function parse() {
        if (($s = $this->copy_until_char('<'))==='')
            return $this->read_tag();

        // text
        $node = new simple_html_dom_node($this);
        ++$this->cursor;
        $node->_[HDOM_INFO_TEXT] = $s;
        $this->link_nodes($node, false);
        return true;
    }

    // read tag info
    protected function read_tag() {
        if ($this->char!=='<') {
            $this->root->_[HDOM_INFO_END] = $this->cursor;
            return false;
        }
        $begin_tag_pos = $this->pos;
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next

        // end tag
        if ($this->char==='/') {
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            $this->skip($this->token_blank_t);
            $tag = $this->copy_until_char('>');

            // skip attributes in end tag
            if (($pos = strpos($tag, ' '))!==false)
                $tag = substr($tag, 0, $pos);

            $parent_lower = strtolower($this->parent->tag);
            $tag_lower = strtolower($tag);

            if ($parent_lower!==$tag_lower) {
                if (isset($this->optional_closing_tags[$parent_lower]) && isset($this->block_tags[$tag_lower])) {
                    $this->parent->_[HDOM_INFO_END] = 0;
                    $org_parent = $this->parent;

                    while (($this->parent->parent) && strtolower($this->parent->tag)!==$tag_lower)
                        $this->parent = $this->parent->parent;

                    if (strtolower($this->parent->tag)!==$tag_lower) {
                        $this->parent = $org_parent; // restore origonal parent
                        if ($this->parent->parent) $this->parent = $this->parent->parent;
                        $this->parent->_[HDOM_INFO_END] = $this->cursor;
                        return $this->as_text_node($tag);
                    }
                }
                else if (($this->parent->parent) && isset($this->block_tags[$tag_lower])) {
                    $this->parent->_[HDOM_INFO_END] = 0;
                    $org_parent = $this->parent;

                    while (($this->parent->parent) && strtolower($this->parent->tag)!==$tag_lower)
                        $this->parent = $this->parent->parent;

                    if (strtolower($this->parent->tag)!==$tag_lower) {
                        $this->parent = $org_parent; // restore origonal parent
                        $this->parent->_[HDOM_INFO_END] = $this->cursor;
                        return $this->as_text_node($tag);
                    }
                }
                else if (($this->parent->parent) && strtolower($this->parent->parent->tag)===$tag_lower) {
                    $this->parent->_[HDOM_INFO_END] = 0;
                    $this->parent = $this->parent->parent;
                }
                else
                    return $this->as_text_node($tag);
            }

            $this->parent->_[HDOM_INFO_END] = $this->cursor;
            if ($this->parent->parent) $this->parent = $this->parent->parent;

            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        $node = new simple_html_dom_node($this);
        $node->_[HDOM_INFO_BEGIN] = $this->cursor;
        ++$this->cursor;
        $tag = $this->copy_until($this->token_slash);

        // doctype, cdata & comments...
        if (isset($tag[0]) && $tag[0]==='!') {
            $node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until_char('>');

            if (isset($tag[2]) && $tag[1]==='-' && $tag[2]==='-') {
                $node->nodetype = HDOM_TYPE_COMMENT;
                $node->tag = 'comment';
            } else {
                $node->nodetype = HDOM_TYPE_UNKNOWN;
                $node->tag = 'unknown';
            }

            if ($this->char==='>') $node->_[HDOM_INFO_TEXT].='>';
            $this->link_nodes($node, true);
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        // text
        if ($pos=strpos($tag, '<')!==false) {
            $tag = '<' . substr($tag, 0, -1);
            $node->_[HDOM_INFO_TEXT] = $tag;
            $this->link_nodes($node, false);
            $this->char = $this->doc[--$this->pos]; // prev
            return true;
        }

        if (!preg_match("/^[\w-:]+$/", $tag)) {
            $node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until('<>');
            if ($this->char==='<') {
                $this->link_nodes($node, false);
                return true;
            }

            if ($this->char==='>') $node->_[HDOM_INFO_TEXT].='>';
            $this->link_nodes($node, false);
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        // begin tag
        $node->nodetype = HDOM_TYPE_ELEMENT;
        $tag_lower = strtolower($tag);
        $node->tag = ($this->lowercase) ? $tag_lower : $tag;

        // handle optional closing tags
        if (isset($this->optional_closing_tags[$tag_lower]) ) {
            while (isset($this->optional_closing_tags[$tag_lower][strtolower($this->parent->tag)])) {
                $this->parent->_[HDOM_INFO_END] = 0;
                $this->parent = $this->parent->parent;
            }
            $node->parent = $this->parent;
        }

        $guard = 0; // prevent infinity loop
        $space = array($this->copy_skip($this->token_blank), '', '');

        // attributes
        do {
            if ($this->char!==null && $space[0]==='') break;
            $name = $this->copy_until($this->token_equal);
            if($guard===$this->pos) {
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                continue;
            }
            $guard = $this->pos;

            // handle endless '<'
            if($this->pos>=$this->size-1 && $this->char!=='>') {
                $node->nodetype = HDOM_TYPE_TEXT;
                $node->_[HDOM_INFO_END] = 0;
                $node->_[HDOM_INFO_TEXT] = '<'.$tag . $space[0] . $name;
                $node->tag = 'text';
                $this->link_nodes($node, false);
                return true;
            }

            // handle mismatch '<'
            if($this->doc[$this->pos-1]=='<') {
                $node->nodetype = HDOM_TYPE_TEXT;
                $node->tag = 'text';
                $node->attr = array();
                $node->_[HDOM_INFO_END] = 0;
                $node->_[HDOM_INFO_TEXT] = substr($this->doc, $begin_tag_pos, $this->pos-$begin_tag_pos-1);
                $this->pos -= 2;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $this->link_nodes($node, false);
                return true;
            }

            if ($name!=='/' && $name!=='') {
                $space[1] = $this->copy_skip($this->token_blank);
                $name = $this->restore_noise($name);
                if ($this->lowercase) $name = strtolower($name);
                if ($this->char==='=') {
                    $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                    $this->parse_attr($node, $name, $space);
                }
                else {
                    //no value attr: nowrap, checked selected...
                    $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
                    $node->attr[$name] = true;
                    if ($this->char!='>') $this->char = $this->doc[--$this->pos]; // prev
                }
                $node->_[HDOM_INFO_SPACE][] = $space;
                $space = array($this->copy_skip($this->token_blank), '', '');
            }
            else
                break;
        } while($this->char!=='>' && $this->char!=='/');

        $this->link_nodes($node, true);
        $node->_[HDOM_INFO_ENDSPACE] = $space[0];

        // check self closing
        if ($this->copy_until_char_escape('>')==='/') {
            $node->_[HDOM_INFO_ENDSPACE] .= '/';
            $node->_[HDOM_INFO_END] = 0;
        }
        else {
            // reset parent
            if (!isset($this->self_closing_tags[strtolower($node->tag)])) $this->parent = $node;
        }
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return true;
    }

    // parse attributes
    protected function parse_attr($node, $name, &$space) {
        $space[2] = $this->copy_skip($this->token_blank);
        switch($this->char) {
            case '"':
                $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('"'));
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                break;
            case '\'':
                $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_SINGLE;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('\''));
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                break;
            default:
                $node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
                $node->attr[$name] = $this->restore_noise($this->copy_until($this->token_attr));
        }
    }

    // link node's parent
    protected function link_nodes(&$node, $is_child) {
        $node->parent = $this->parent;
        $this->parent->nodes[] = $node;
        if ($is_child)
            $this->parent->children[] = $node;
    }

    // as a text node
    protected function as_text_node($tag) {
        $node = new simple_html_dom_node($this);
        ++$this->cursor;
        $node->_[HDOM_INFO_TEXT] = '</' . $tag . '>';
        $this->link_nodes($node, false);
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return true;
    }

    protected function skip($chars) {
        $this->pos += strspn($this->doc, $chars, $this->pos);
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
    }

    protected function copy_skip($chars) {
        $pos = $this->pos;
        $len = strspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        if ($len===0) return '';
        return substr($this->doc, $pos, $len);
    }

    protected function copy_until($chars) {
        $pos = $this->pos;
        $len = strcspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return substr($this->doc, $pos, $len);
    }

    protected function copy_until_char($char) {
        if ($this->char===null) return '';

        if (($pos = strpos($this->doc, $char, $this->pos))===false) {
            $ret = substr($this->doc, $this->pos, $this->size-$this->pos);
            $this->char = null;
            $this->pos = $this->size;
            return $ret;
        }

        if ($pos===$this->pos) return '';
        $pos_old = $this->pos;
        $this->char = $this->doc[$pos];
        $this->pos = $pos;
        return substr($this->doc, $pos_old, $pos-$pos_old);
    }

    protected function copy_until_char_escape($char) {
        if ($this->char===null) return '';

        $start = $this->pos;
        while(1) {
            if (($pos = strpos($this->doc, $char, $start))===false) {
                $ret = substr($this->doc, $this->pos, $this->size-$this->pos);
                $this->char = null;
                $this->pos = $this->size;
                return $ret;
            }

            if ($pos===$this->pos) return '';

            if ($this->doc[$pos-1]==='\\') {
                $start = $pos+1;
                continue;
            }

            $pos_old = $this->pos;
            $this->char = $this->doc[$pos];
            $this->pos = $pos;
            return substr($this->doc, $pos_old, $pos-$pos_old);
        }
    }

    // remove noise from html content
    protected function remove_noise($pattern, $remove_tag=false) {
        $count = preg_match_all($pattern, $this->doc, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);

        for ($i=$count-1; $i>-1; --$i) {
            $key = '___noise___'.sprintf('% 3d', count($this->noise)+100);
            $idx = ($remove_tag) ? 0 : 1;
            $this->noise[$key] = $matches[$i][$idx][0];
            $this->doc = substr_replace($this->doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
        }

        // reset the length of content
        $this->size = strlen($this->doc);
        if ($this->size>0) $this->char = $this->doc[0];
    }

    // restore noise to html content
    function restore_noise($text) {
        while(($pos=strpos($text, '___noise___'))!==false) {
            $key = '___noise___'.$text[$pos+11].$text[$pos+12].$text[$pos+13];
            if (isset($this->noise[$key]))
                $text = substr($text, 0, $pos).$this->noise[$key].substr($text, $pos+14);
        }
        return $text;
    }

    function __toString() {
        return $this->root->innertext();
    }

    function __get($name) {
        switch($name) {
            case 'outertext': return $this->root->innertext();
            case 'innertext': return $this->root->innertext();
            case 'plaintext': return $this->root->text();
        }
    }

    // camel naming conventions
    function childNodes($idx=-1) {return $this->root->childNodes($idx);}
    function firstChild() {return $this->root->first_child();}
    function lastChild() {return $this->root->last_child();}
    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx=null) {return $this->find("#$id", $idx);}
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=-1) {return $this->find($name, $idx);}
    function loadFile() {$args = func_get_args();$this->load(call_user_func_array('file_get_contents', $args), true);}
}



if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);				// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2);			// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);			// Join query strings
	define('HTTP_URL_STRIP_USER', 8);			// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);			// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);			// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);			// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);			// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);		// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);		// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host
	
	// Build an URL
	// The parts of the second URL will be merged into the first according to the flags argument. 
	// 
	// @param	mixed			(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	// @param	mixed			Same as the first argument
	// @param	int				A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	// @param	array			If set, it will be filled with the parts of the composed url like parse_url() would return 
	function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
	{
		$keys = array('user','pass','port','path','query','fragment');
		
		// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}
		
		// Parse the original URL
		$parse_url = parse_url($url);
		
		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
			$parse_url['scheme'] = $parts['scheme'];
		if (isset($parts['host']))
			$parse_url['host'] = $parts['host'];
		
		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
					$parse_url[$key] = $parts[$key];
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				else
					$parse_url['path'] = $parts['path'];
			}
			
			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
					$parse_url['query'] .= '&' . $parts['query'];
				else
					$parse_url['query'] = $parts['query'];
			}
		}
			
		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
				unset($parse_url[$key]);
		}
		
		
		$new_url = $parse_url;
		
		return 
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
		;
	}
}
endif;
