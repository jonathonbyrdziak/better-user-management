<H2 class="registration-title"><?php echo ucwords($type); ?></H2>
<?php
$form = new ValidForm( 'registerform', '', bum_get_permalink_registration() );

$form->addField( 'user_login', 'Username', VFORM_STRING,
	array( 'required' => true ),
	array( 'required' => 'You need a username.' ),
	array( 'tip' => 'Usernames cannot be changed.' )
);
	
$form->addField( 'user_email', 'Email', VFORM_EMAIL,
	array( 'required' => true ),
	array( 'required' => 'You need an email.', 'type' => 'Email not valid.' )
);
	
$form->addField( 'user_email1', 'Confirm Email', VFORM_EMAIL,
	array( 'required' => true ),
	array( 'required' => 'You need an email.', 'type' => 'Email not valid.' )
);

$form->addField( 'user_type', '', VFORM_HIDDEN,
	array(),
	array(),
	array( 'default' => $type )
);

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

$form->addParagraph("A password will be e-mailed to you", "");
$form->setSubmitLabel("Register");

echo $form->toHtml();
?>