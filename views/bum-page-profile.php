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
global $user_id;
$user = get_userdata($user_id);
?>
<div class="profile_wrapper">
	<h2><a href="<?php echo bum_get_permalink_profile("bumu=$user_id"); ?>"><?php echo ucwords(strtolower($user->display_name)); ?></a></h2>
	<?php do_action('notifications'); ?>
	
	<div class="profile_avatar">
		<?php echo get_avatar( $user->ID, $size, null, $user->user_login ); ?> 
		<div class="edit_profile">
			<ul class="user_menu">
			<li><a class="blue_button" href="<?php echo bum_get_permalink_profile('action=edit'); ?>">Edit my profile</a></li>
			<li><a class="blue_button" href="<?php echo bum_get_permalink_login('action=logout'); ?>">Logout</a></li>
			</ul>
		</div>
	</div>
	<div class="fullname">
		<p><?php echo $user->description; ?></p>
	</div>
	<div class="clear"></div>
	
	<div class="profile_additional">
		<?php
		do_action('show_user_profile');
		
		//extra fields go here
		if( $fields->description )
		{
			$fields = json_decode( $fields->description );
			foreach( $fields as $field )
			{
				$info = bum_get_field_info($field);
				
				//Multiple values are seperated by | ( pipe )
				if( strpos( $info['meta_value'], '|' ) !== false )
					$info['meta_value'] = str_replace( '|', ', ', $info['meta_value'] );
				
				if( $return['meta_value'] === false )
					$return['meta_value'] = 'Not set.';
				
				echo '<h2 class="bum_title">'.$info['title'].'</h2><p class="bum_value">'.$info['meta_value'].'</p>';
			}
		}
		?>
	</div>
</div>