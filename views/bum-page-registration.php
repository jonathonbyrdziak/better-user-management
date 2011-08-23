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
global $bum_errors;
$type = bum_get_registration_type();

?>
<div class="registration-wrapper">
<?php if ( !get_option('users_can_register') ): ?>

	<H2 class="registration-title"><?php echo __('Sorry!', 'bum')?></H2>
	<p><?php echo sprintf(__('Registration for %s is currently closed.', 'bum'), get_bloginfo('site')); ?></p>

<?php elseif ( is_wp_error($bum_errors) ): ?>

	<H2 class="registration-title"><?php echo __('Oops, our bad..', 'bum')?></H2>
	<p><?php echo sprintf(__("We apologize, but there's been an error with our registration form. Please contact administration ( %s ) and let us know what information that you tried to register with. We'll fix it asap.", 'bum'), get_bloginfo('admin_email')); ?></p>
	
	<p class="registration-error">
	<?php foreach((array)$bum_errors->errors as $error => $message): ?>
		<?php echo $message[0].'<br/>'; ?>
	<?php endforeach; ?>
	</p>
	
	<?php 
	if ($view = bum_get_show_view('bum-page-registration-'.$_REQUEST['user_type']))
	{
		echo $view;
	}
	else 
	{
		bum_show_view("bum-page-registration-form");
	}
	?>
	
<?php elseif (!empty($_POST)): ?>

	<H2 class="registration-title"><?php echo __('Registration Complete', 'bum')?></H2>
	<p><?php echo sprintf(__("Thank you for registering with %s. Please check your email as we've sent you your login url and credentials.", 'bum'), get_bloginfo('site')); ?></p>
	
	<?php bum_show_view("bum-page-login"); ?>
	
<?php else:?>
	
	<?php $pages = get_registration_pages(); ?>
	<?php foreach((array)$pages as $page): ?>
		<a class="registration-link-candidate" href="<?php echo bum_get_permalink_registration("user_type=".$page['role']); ?>">
		<?php echo sprintf( __("Register as a %s", 'bum'), $page['name'] ); ?></a>
	<?php endforeach; ?>
	
	<p class="registration-subnav">
		<a href="<?php echo bum_get_permalink_login(); ?>"><?php _e('Log in') ?></a> |
		<a href="<?php echo bum_get_permalink_login('action=lostpassword'); ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
	</p>

<?php endif; ?>
	<div class="clear"></div>
</div>