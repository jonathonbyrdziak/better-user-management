<H2 class="registration-title"><?php echo ucwords($type); ?></H2>
<form class="registration-form" name="registerform" id="registerform" action="<?php echo bum_get_permalink_registration(); ?>" method="post">
	
	<?php do_action('bum_register_form'); ?>
	
	<p class="registration-p"><?php _e('A password will be e-mailed to you.', 'bum'); ?></p>
	<br class="clear" />
	
	<p class="submit"><input class="registration-submit" type="submit" name="wp-submit" value="<?php esc_attr_e('Register'); ?>" /></p>
</form>

<p class="registration-subnav">
	<a href="<?php echo bum_get_permalink_login(); ?>"><?php _e('Log in') ?></a> |
	<a href="<?php echo bum_get_permalink_registration(); ?>"><?php _e('Go back') ?></a>
</p>
