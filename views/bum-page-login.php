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

global $bum_action, $bum_errors, $bum_redirect_to, $bum_user, $bum_http_post, 
$bum_secure_cookie, $bum_interim_login, $bum_reauth, $bum_rememberme, $bum_messages_txt,
$bum_errors_txt;

?>
<div class="registration-form">
<?php 

//showing messages
if ( $bum_messages_txt )
	echo '<div id="login_error">' . apply_filters('login_errors', $bum_messages_txt) . "</div>\n";
if ( $bum_errors_txt )
	echo '<div id="login_error">' . apply_filters('login_errors', $bum_errors_txt) . "</div>\n";
	
switch ($bum_action) {

case 'lostpassword' :
case 'retrievepassword' :
	echo apply_filters('login_message', '<p class="message">' . __('Please enter your username or email address. You will receive a link to create a new password via email.') . '</p>');

	$form = new ValidForm( 'lostpasswordform', '', bum_get_permalink_login('action=lostpassword') );

	$form->addField( 'user_login', 'Username or E-mail', VFORM_STRING,
		array( 'required' => true ),
		array( 'required' => 'You need a username or e-mail.' ) );
		
	$form->setSubmitLabel("Get New Password");
		
	do_action('lostpassword_form');
	
	$form->addField( 'redirect_to', '', VFORM_HIDDEN,
		array(),
		array(),
		array( 'default' => esc_attr( $bum_redirect_to ) ) );
	
	echo $form->toHtml();
	?>
	<p id="nav">
	<a href="<?php echo bum_get_permalink_login() ?>"><?php _e('Log in') ?></a>
	<?php if (get_option('users_can_register')) : ?>
	 | <a href="<?php echo bum_get_permalink_login('action=register'); ?>"><?php _e('Register') ?></a>
	<?php endif; ?>
	</p>
	<?php
break;

case 'resetpass' :
case 'rp' :
	
	echo apply_filters('login_message', '<p class="message reset-pass">' . __('Enter your new password below.') . '</p>');
	?>
	<form name="resetpassform" id="resetpassform" action="<?php echo bum_get_permalink_login('action=resetpass&key=' . urlencode($_GET['key']) . '&login=' . urlencode($_GET['login']).'login_post'); ?>" method="post">
		<input type="hidden" id="user_login" value="<?php echo esc_attr( $_GET['login'] ); ?>" autocomplete="off" />
	
		<p>
			<label class="login-label"><?php _e('New password') ?></label>
			<input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" />
		</p>
		<p>
			<label class="login-label"><?php _e('Confirm new password') ?></label>
			<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
		</p>
	
		<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator'); ?></div>
		<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
	
		<div class="clear"></div>
		<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="registration-submit" value="<?php esc_attr_e('Reset Password'); ?>" tabindex="100" /></p>
	</form>
	
	<p id="nav">
	<a href="<?php echo bum_get_permalink_login(); ?>"><?php _e('Log in') ?></a>
	<?php if (get_option('users_can_register')) : ?>
	 | <a href="<?php echo bum_get_permalink_login('action=register'); ?>"><?php _e('Register') ?></a>
	<?php endif; ?>
	</p>
	
	<?php
	
break;

case 'login' :
default:
	$form = new ValidForm( 'loginform', '', bum_get_permalink_login() );

	$form->addField( 'log', 'Username', VFORM_STRING,
		array( 'required' => true ),
		array( 'required' => 'You need a username.' ),
		array( 'tip' => 'Usernames cannot be changed.' ) );
		
	$form->addField( 'pwd', 'Password', VFORM_PASSWORD,
		array( 'required' => true ),
		array( 'required' => 'Enter your password.' ) );
		
	$remember = $form->addField( 'rememberme', '', VFORM_CHECK_LIST );
	$remember->addField( 'Remember Me', 'forever' );
		
	$form->setSubmitLabel("Login");
	
	do_action('login_form');
	
	if( $bum_interim_login ) {
		$form->addField( 'interim-login', '', VFORM_HIDDEN,
			array(),
			array(),
			array( 'default' => '1' ) );
	} else {
		$form->addField( 'redirect_to', '', VFORM_HIDDEN,
			array(),
			array(),
			array( 'default' => esc_attr($bum_redirect_to) ) );
	}
	$form->addField( 'testcookie', '', VFORM_HIDDEN,
		array(),
		array(),
		array( 'default' => '1' ) );
	
	echo $form->toHtml();
	
	if ( !$bum_interim_login ) { ?>
	<p id="nav">
		<?php if ( isset($_GET['checkemail']) && in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) : ?>
		<?php elseif ( get_option('users_can_register') ) : ?>
		<a href="<?php echo bum_get_permalink_registration(); ?>"><?php _e('Register') ?></a> |
		<a href="<?php echo bum_get_permalink_login('action=lostpassword'); ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
		<?php else : ?>
		<a href="<?php echo bum_get_permalink_login('action=lostpassword'); ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
		<?php endif; ?>
	</p>
	<p id="backtoblog"><a href="<?php bloginfo('url'); ?>/" title="<?php esc_attr_e('Are you lost?') ?>"><?php printf(__('&larr; Back to %s'), get_bloginfo('title', 'display' )); ?></a></p>
	<?php } ?>
	
	<script type="text/javascript">
		function wp_attempt_focus(){
		setTimeout( function(){ try{
		<?php if ( $bum_user_login || $bum_interim_login ) { ?>
		d = document.getElementById('user_pass');
		d.value = '';
		<?php } else { ?>
		d = document.getElementById('user_login');
		<?php if ( method_exists($bum_errors, 'get_error_code') && 'invalid_username' == $bum_errors->get_error_code() ) { ?>
		if( d.value != '' )
		d.value = '';
		<?php
		}
		}?>
		d.focus();
		d.select();
		} catch(e){}
		}, 200);
		}
		
		<?php if ( !$error ) { ?>
		wp_attempt_focus();
		<?php } ?>
		if(typeof wpOnload=='function')wpOnload();
	</script>
	<?php
	do_action( 'login_footer' );

break;
} // end action switch

?></div>