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
</div>