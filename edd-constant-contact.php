<?php
/*
Plugin Name: Easy Digital Downloads - Constant Contact
Plugin URL: http://easydigitaldownloads.com/extension/constant-contact
Description: Include a Constant Contact signup option with your Easy Digital Downloads checkout
Version: 1.0
Author: Lorenzo Orlando Caum, Enzo12 LLC
Author URI: http://enzo12.com
Contributors: lorenzocaum
*/

// adds the settings to the Misc section
function eddconstant_contact_add_settings($settings) {
  
  $eddconstant_contact_settings = array(
		array(
			'id' => 'eddconstant_contact_settings',
			'name' => '<strong>' . __('Constant Contact Settings', 'eddconstant_contact') . '</strong>',
			'desc' => __('Configure Constant Contact Integration Settings', 'eddconstant_contact'),
			'type' => 'header'
		),
        array(
			'id' => 'eddconstant_contact_username',
			'name' => __('Username', 'eddconstant_contact'),
			'desc' => __('Enter your Constant Contact Username. It is the username that you use to log in to your Constant Contact account.', 'eddconstant_contact'),
			'type' => 'text',
			'size' => 'regular'
		),
        array(
			'id' => 'eddconstant_contact_password',
			'name' => __('Password', 'eddconstant_contact'),
			'desc' => __('Enter your Constant Contact Password. It is the password that you use to log in to your Constant Contact account.', 'eddconstant_contact'),
			'type' => 'password',
			'size' => 'regular'
		),
        array(
			'id' => 'eddconstant_contact_api',
			'name' => __('API Key', 'eddconstant_contact'),
			'desc' => __('Enter your Constant Contact API Key. You will need to register with the Constant Contact Developer Network to get an API Key.', 'eddconstant_contact'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'eddconstant_contact_list',
			'name' => __('List ID', 'eddconstant_contact'),
			'desc' => __('Enter your List ID. It will be in the form of a number.', 'eddconstant_contact'),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'eddconstant_contact_label',
			'name' => __('Checkout Label', 'eddconstant_contact'),
			'desc' => __('This is the text shown next to the signup option', 'eddconstant_contact'),
			'type' => 'text',
			'size' => 'regular'
		)
	);
	
	return array_merge($settings, $eddconstant_contact_settings);
}
add_filter('edd_settings_misc', 'eddconstant_contact_add_settings');

// adds an email to the constant contact subscription list
function eddconstant_contact_subscribe_email($email, $first_name = '', $last_name = '' ) {
	global $edd_options;
	
	if( isset( $edd_options['eddconstant_contact_api'] ) && strlen( trim( $edd_options['eddconstant_contact_api'] ) ) > 0 ) {

		if( ! isset( $edd_options['eddconstant_contact_list'] ) || strlen( trim( $edd_options['eddconstant_contact_list'] ) ) <= 0 )
			return false;
        
        require_once('inc/constant-contact.php');
        
        $constant_contact_api = new constant_contact($edd_options['eddconstant_contact_username'], $edd_options['eddconstant_contact_password'], $edd_options['eddconstant_contact_api']);
			
        $constant_contact_user = array(
				'EmailAddress' => $email,
				'FirstName' => $first_name,
				'LastName' => $last_name,
				'OptInSource' => 'ACTION_BY_CUSTOMER'
        );
			
        $constant_contact_api->add_contact($constant_contact_user, array($edd_options['eddconstant_contact_list']));
        
	}

	return false;
}

// displays the constant contact checkbox
function eddconstant_contact_constant_contact_fields() {
	global $edd_options;
	ob_start(); 
		if( isset( $edd_options['eddconstant_contact_api'] ) && strlen( trim( $edd_options['eddconstant_contact_api'] ) ) > 0 ) { ?>
		<p>
			<input name="eddconstant_contact_constant_contact_signup" id="eddconstant_contact_constant_contact_signup" type="checkbox" checked="checked"/>
			<label for="eddconstant_contact_constant_contact_signup"><?php echo isset($edd_options['eddconstant_contact_label']) ? $edd_options['eddconstant_contact_label'] : __('Sign up for our mailing list', 'eddconstant_contact'); ?></label>
		</p>
		<?php
	}
	echo ob_get_clean();
}
add_action('edd_purchase_form_before_submit', 'eddconstant_contact_constant_contact_fields', 100);

// checks whether a user should be signed up for the constant contact list
function eddconstant_contact_check_for_email_signup($posted, $user_info) {
	if($posted['eddconstant_contact_constant_contact_signup']) {

		$email = $user_info['email'];
		eddconstant_contact_subscribe_email($email, $user_info['first_name'], $user_info['last_name'] );
	}
}
add_action('edd_checkout_before_gateway', 'eddconstant_contact_check_for_email_signup', 10, 2);
