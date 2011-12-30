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

$user = wp_get_current_user();
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
	<?php
	$form = new ValidForm( 'your-profile', '', bum_get_permalink_profile() );

	$form->addField( 'email', 'Email', VFORM_EMAIL,
		array( 'required' => true ),
		array( 'required' => 'You need an email.', 'type' => 'Email not valid.' ),
		array( 'default' => esc_attr($profileuser->user_email) )
	);
	
	if( $wp_http_referer ) {
		$form->addField( 'wp_http_referer', '', VFORM_HIDDEN,
			array(),
			array(),
			array( 'default' => esc_url($wp_http_referer) )
		);
	}
	$form->addField( 'from', '', VFORM_HIDDEN, array(), array(), array( 'default' => 'profile' ) );
	$form->addField( 'action', '', VFORM_HIDDEN, array(), array(), array( 'default' => 'update' ) );
	$form->addField( 'user_id', '', VFORM_HIDDEN, array(), array(), array( 'default' => $user->ID ) );
	$form->addField( 'checkuser_id', '', VFORM_HIDDEN, array(), array(), array( 'default' => $user->ID ) );
	
	/*
	 * This handles extra fields ( basically reading the field info and putting it into ValidForm )
	 * Currently handles `radio`, `checkbox`, `select`, `input_text` ( text field ), and `textarea`
	 */
	if( $fields->description )
	{
		$fields = json_decode( $fields->description );
		foreach( $fields as $field )
		{
			//get info
			$info = bum_get_field_info( $field );
			$fid = 'bum_'.sanitize_title( $info['title'] );
			
			//this is handling `radio`, `checkbox`, `select`
			if( in_array( $info['cssClass'], array( 'radio', 'checkbox', 'select' ) ) )
			{
				if( $info['cssClass'] == 'radio' )
					$type = VFORM_RADIO_LIST;
				elseif( $info['cssClass'] == 'checkbox' )
					$type = VFORM_CHECK_LIST;
				else
					$type = VFORM_SELECT_LIST;
				
				//Multiple values are seperated by | ( pipe )
				if( strpos( $info['meta_value'], '|' ) !== false )
					$info['meta_value'] = explode( '|', $info['meta_value'] );
					
				$box = $form->addField( 'bum_'.$info['id'], $info['title'], $type,
					array( 'required' => ($info['required']=='false'?false:true) ),
					array( 'required' => 'The following field is required: '.$info['title'] ),
					( $info['tip'] ? array( 'tip' => $info['tip'], 'default' => $info['meta_value'] ) : array('default' => $info['meta_value']) )
				);
				
				foreach( $info['values'] as $checkbox )
					$box->addField( $checkbox->value, htmlentities( $checkbox->value ) );
			}
			
			//this is handling `input_text`, `textarea`
			if( in_array( $info['cssClass'], array( 'input_text', 'textarea' ) ) )
			{
				if( $info['cssClass'] == 'input_text' )
					$type = VFORM_STRING;
				else
					$type = VFORM_TEXT;
					
				$form->addField( 'bum_'.$info['id'], $info['values'], $type,
					array( 'required' => ($info['required']=='false'?false:true) ),
					array( 'required' => 'The following field is required: '.$info['values'] ),
					( $info['tip'] ? array( 'tip' => $info['tip'], 'default' => $info['meta_value'] ) : array('default' => $info['meta_value']) )
				);
			}
		}
	}
	
	$form->setSubmitLabel("Update Profile");

	echo $form->toHtml();
	?>
</div>