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
$users = bum_register_user();

?>
<?php foreach((array)$users as $id => $user): ?>
	<a class="registration-link-candidate" href="<?php echo bum_get_permalink_registration("bump=$id"); ?>">
	<?php echo sprintf( __("Register as a %s", 'bum'), $user['name'] ); ?></a>
<?php endforeach; ?>
	
<div class="clear"></div>
<p class="registration-subnav">
	<a href="<?php echo bum_get_permalink_login(); ?>"><?php _e('Log in') ?></a> |
	<a href="<?php echo bum_get_permalink_login('action=lostpassword'); ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
</p>