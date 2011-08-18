<H2 class="registration-title"><?php echo ucwords($type); ?></H2>
<form class="registration-form" name="registerform" id="registerform" action="<?php echo bum_get_permalink_registration(); //bum_get_permalink_registration() ?>" method="post">
	<p>
		<label><div class="registration-label"><?php _e('Username') ?></div>
		<input class="registration-text" type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(stripslashes($bum_user_login)); ?>" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label><div class="registration-label"><?php _e('E-mail') ?></div>
		<input class="registration-text" type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(stripslashes($bum_user_email)); ?>" size="25" tabindex="20" /></label>
	</p>
	
	<?php do_action('bum_register_form'); ?>
	
	<p class="registration-p"><?php _e('A password will be e-mailed to you.', 'bum'); ?></p>
	<br class="clear" />
	
	<input type="hidden" name="registration-type" value="<?php echo $type; ?>" />
	<p class="submit"><input class="registration-submit" type="submit" name="wp-submit" value="<?php esc_attr_e('Register'); ?>" /></p>
</form>

<p class="registration-subnav">
	<a href="<?php echo bum_get_permalink_login(); ?>"><?php _e('Log in') ?></a> |
	<a href="<?php echo bum_get_permalink_registration(); ?>"><?php _e('Go back') ?></a>
</p>
