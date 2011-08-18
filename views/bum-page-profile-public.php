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

?>
<div class="profile_wrapper">
	<h2><a href="<?php echo bum_get_permalink_profile("bumu=$user_id"); ?>"><?php echo ucwords(strtolower($bum_public_user->display_name)); ?></a></h2>
	<?php do_action('notifications'); ?>
	
	<div class="profile_avatar">
		<?php echo get_avatar( $bum_public_user->ID, $size, null, $bum_public_user->user_login ); ?> 
	</div>
	<div class="fullname">
		<p><?php echo $bum_public_user->description; ?></p>
	</div>
	<div class="clear"></div>
</div>