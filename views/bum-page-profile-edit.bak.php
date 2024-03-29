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
<?php if ( isset($_GET['updated']) ) : ?>
<div id="message" class="updated">
	<p><strong><?php _e('User updated.') ?></strong></p>
	<?php if ( $wp_http_referer && !IS_PROFILE_PAGE ) : ?>
	<p><a href="<?php echo esc_url( $wp_http_referer ); ?>"><?php _e('&larr; Back to Authors and Users'); ?></a></p>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if ( isset( $errors ) && is_wp_error( $errors ) ) : ?>
<div class="error"><p><?php echo implode( "</p>\n<p>", $errors->get_error_messages() ); ?></p></div>
<?php endif; ?>

<div class="registration-wrapper" id="page-profile">

	<strong>To change your Profile picture, <a href="http://gravatar.com" target="_blank">click here.</a></strong>

<form id="your-profile" action="<?php echo bum_get_permalink_profile(); ?>" method="post"<?php do_action('user_edit_form_tag'); ?>>
	<?php wp_nonce_field('update-user_' . $user_id) ?>
	<?php if ( $wp_http_referer ) : ?>
		<input type="hidden" name="wp_http_referer" value="<?php echo esc_url($wp_http_referer); ?>" />
	<?php endif; ?>
	<p>
	<input type="hidden" name="from" value="profile" />
	<input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />
	</p>
	
	<h3><?php _e('Personal Options'); ?></h3>
	
	<table class="form-table">
	<?php if ( rich_edit_exists() && !( IS_PROFILE_PAGE && !$user_can_edit ) ) : // don't bother showing the option if the editor has been removed ?>
		<tr>
			<th scope="row"><?php _e('Visual Editor')?></th>
			<td><label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="false" <?php checked('false', $profileuser->rich_editing); ?> /> <?php _e('Disable the visual editor when writing'); ?></label></td>
		</tr>
	<?php endif; ?>
	<?php if ( count($_wp_admin_css_colors) > 1 && has_action('admin_color_scheme_picker') ) : ?>
		<tr>
			<th scope="row"><?php _e('Admin Color Scheme')?></th>
			<td><?php do_action( 'admin_color_scheme_picker' ); ?></td>
		</tr>
	<?php
	endif; // $_wp_admin_css_colors
	if ( !( IS_PROFILE_PAGE && !$user_can_edit ) ) : ?>
		<tr>
			<th scope="row"><?php _e( 'Keyboard Shortcuts' ); ?></th>
			<td><label for="comment_shortcuts"><input type="checkbox" name="comment_shortcuts" id="comment_shortcuts" value="true" <?php if ( !empty($profileuser->comment_shortcuts) ) checked('true', $profileuser->comment_shortcuts); ?> /> <?php _e('Enable keyboard shortcuts for comment moderation.'); ?></label> <?php _e('<a href="http://codex.wordpress.org/Keyboard_Shortcuts" target="_blank">More information</a>'); ?></td>
		</tr>
	<?php endif; ?>
	
	<tr class="show-admin-bar">
	<th scope="row"><?php _e('Show Admin Bar')?></th>
	<td>
	<label for="admin_bar_front" class="admin_bar_front">
	<input name="admin_bar_front" type="checkbox" id="admin_bar_front" value="1" <?php checked( _get_admin_bar_pref( 'front', $profileuser->ID ) ); ?> />
	<?php /* translators: Show admin bar when viewing site */ _e( 'when viewing site' ); ?></label>
	<label for="admin_bar_admin">
	<input name="admin_bar_admin" type="checkbox" id="admin_bar_admin" value="1" <?php checked( _get_admin_bar_pref( 'admin', $profileuser->ID ) ); ?> />
	<?php /* translators: Show admin bar in dashboard */ _e( 'in dashboard' ); ?></label>
	</td>
	</tr>
	<?php do_action('personal_options', $profileuser); ?>
	</table>
	
	<?php
		if ( IS_PROFILE_PAGE )
			do_action('profile_personal_options', $profileuser);
	?>
	
	<h3><?php _e('Name') ?></h3>
	
	<table class="form-table">
		<tr>
			<th><label for="user_login"><?php _e('Username'); ?></label></th>
			<td><input type="text" name="user_login" id="user_login" value="<?php echo esc_attr($profileuser->user_login); ?>" disabled="disabled" class="regular-text" /> 
			<span class="description"><?php _e('Usernames cannot be changed.'); ?></span></td>
		</tr>
	
	<?php if ( !IS_PROFILE_PAGE && !is_network_admin() ) : ?>
	<tr><th><label for="role"><?php _e('Role:') ?></label></th>
	<td><select name="role" id="role">
	<?php
	// Get the highest/primary role for this user
	// TODO: create a function that does this: wp_get_user_role()
	$user_roles = $profileuser->roles;
	$user_role = array_shift($user_roles);
	
	// print the full list of roles with the primary one selected.
	wp_dropdown_roles($user_role);
	
	// print the 'no role' option. Make it selected if the user has no role yet.
	if ( $user_role )
		echo '<option value="">' . __('&mdash; No role for this site &mdash;') . '</option>';
	else
		echo '<option value="" selected="selected">' . __('&mdash; No role for this site &mdash;') . '</option>';
	?>
	</select>
	<?php endif; //!IS_PROFILE_PAGE
	
	if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && !isset($super_admins) ) { ?>
	<tr><th><label for="role"><?php _e('Super Admin'); ?></label></th>
	<td>
	<?php if ( $profileuser->user_email != get_site_option( 'admin_email' ) ) : ?>
	<p><label><input type="checkbox" id="super_admin" name="super_admin"<?php checked( is_super_admin( $profileuser->ID ) ); ?> /> <?php _e( 'Grant this user super admin privileges for the Network.' ); ?></label></p>
	<?php else : ?>
	<p><?php _e( 'Super admin privileges cannot be removed because this user has the network admin email.' ); ?></p>
	<?php endif; ?>
	</td></tr>
	<?php } ?>
	
	<tr>
		<th><label for="first_name"><?php _e('First Name') ?></label></th>
		<td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($profileuser->first_name) ?>" class="regular-text" /></td>
	</tr>
	
	<tr>
		<th><label for="last_name"><?php _e('Last Name') ?></label></th>
		<td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($profileuser->last_name) ?>" class="regular-text" /></td>
	</tr>
	
	<tr>
		<th><label for="nickname"><?php _e('Nickname'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
		<td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr($profileuser->nickname) ?>" class="regular-text" /></td>
	</tr>
	
	<tr>
		<th><label for="display_name"><?php _e('Display name publicly as') ?></label></th>
		<td>
			<select name="display_name" id="display_name">
			<?php
				$public_display = array();
				$public_display['display_username']  = $profileuser->user_login;
				$public_display['display_nickname']  = $profileuser->nickname;
				if ( !empty($profileuser->first_name) )
					$public_display['display_firstname'] = $profileuser->first_name;
				if ( !empty($profileuser->last_name) )
					$public_display['display_lastname'] = $profileuser->last_name;
				if ( !empty($profileuser->first_name) && !empty($profileuser->last_name) ) {
					$public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
					$public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
				}
				if ( !in_array( $profileuser->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
					$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;
				$public_display = array_map( 'trim', $public_display );
				$public_display = array_unique( $public_display );
				foreach ( $public_display as $id => $item ) {
			?>
				<option id="<?php echo $id; ?>" value="<?php echo esc_attr($item); ?>"<?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
			<?php
				}
			?>
			</select>
		</td>
	</tr>
	</table>
	
	<h3><?php _e('Contact Info') ?></h3>
	
	<table class="form-table">
	<tr>
		<th><label for="email"><?php _e('E-mail'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
		<td><input type="text" name="email" id="email" value="<?php echo esc_attr($profileuser->user_email) ?>" class="regular-text" />
		<?php
		$new_email = get_option( $current_user->ID . '_new_email' );
		if ( $new_email && $new_email != $current_user->user_email ) : ?>
		<div class="updated inline">
		<p><?php printf( __('There is a pending change of your e-mail to <code>%1$s</code>. <a href="%2$s">Cancel</a>'), $new_email['newemail'], esc_url( bum_get_permalink_profile().'?dismiss=' . $current_user->ID . '_new_email' ) ); ?></p>
		</div>
		<?php endif; ?>
		</td>
	</tr>
	
	<tr>
		<th><label for="url"><?php _e('Website') ?></label></th>
		<td><input type="text" name="url" id="url" value="<?php echo esc_attr($profileuser->user_url) ?>" class="regular-text code" /></td>
	</tr>
	
	<?php
		foreach (_wp_get_user_contactmethods( $profileuser ) as $name => $desc) {
	?>
	<tr>
		<th><label for="<?php echo $name; ?>"><?php echo apply_filters('user_'.$name.'_label', $desc); ?></label></th>
		<td><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr($profileuser->$name) ?>" class="regular-text" /></td>
	</tr>
	<?php
		}
	?>
	</table>
	
	<?php //do_action('edit_user_profile'); ?>
	
	<h3><?php IS_PROFILE_PAGE ? _e('About Yourself') : _e('About the user'); ?></h3>
	
	<table class="form-table">
	<tr>
		<th><label for="description"><?php _e('Biographical Info'); ?></label></th>
		<td><textarea name="description" id="description" rows="5" cols="30"><?php echo $profileuser->description; // textarea_escaped ?></textarea><br />
		<span class="description"><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></span></td>
	</tr>
	
	<?php
	$show_password_fields = apply_filters('show_password_fields', true, $profileuser);
	if ( $show_password_fields ) :
	?>
	<tr id="password">
		<th><label for="pass1"><?php _e('New Password'); ?></label></th>
		<td>
			<input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" /> <span class="description"><?php _e("If you would like to change the password type a new one. Otherwise leave this blank."); ?></span>
			<div class="clear"></div>
			<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" /> <span class="description"><?php _e("Type your new password again."); ?></span>
			<div class="clear"></div>
			<div id="pass-strength-result"><?php _e('Strength indicator'); ?></div>
			<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
		</td>
	</tr>
	<?php endif; ?>
	</table>
	
	<?php
		if ( IS_PROFILE_PAGE )
			do_action( 'show_user_profile', $profileuser );
		else
			do_action( 'edit_user_profile', $profileuser );
	?>
	
	<?php if ( count($profileuser->caps) > count($profileuser->roles) && apply_filters('additional_capabilities_display', true, $profileuser) ) { ?>
	<br class="clear" />
		<table width="99%" style="border: none;" cellspacing="2" cellpadding="3" class="editform">
			<tr>
				<th scope="row"><?php _e('Additional Capabilities') ?></th>
				<td><?php
				global $wp_roles;
				$output = '';
				foreach ( $profileuser->caps as $cap => $value ) {
					if ( !$wp_roles->is_role($cap) ) {
						if ( $output != '' )
							$output .= ', ';
						$output .= $value ? $cap : "Denied: {$cap}";
					}
				}
				echo $output;
				?></td>
			</tr>
		</table>
	<?php } ?>
	
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($user_id); ?>" />
	
	<?php bum_submit_button( IS_PROFILE_PAGE ? __('Update Profile') : __('Update User') ); ?>

</form>
</div>
<script type="text/javascript" charset="utf-8">
	if (window.location.hash == '#password') {
		document.getElementById('pass1').focus();
	}
</script>