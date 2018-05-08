<?php

/**
 * Plugin Name: Microsoft Azure Active Directory B2C Authentication
 * Plugin URI: https://github.com/AzureAD/active-directory-b2c-wordpress-plugin-openidconnect
 * Description: A plugin that allows users to log in using B2C policies
 * Version: 1.0
 * Author: Microsoft
 * Author URI: https://azure.microsoft.com/en-us/documentation/services/active-directory-b2c/
 * License: MIT License (https://raw.githubusercontent.com/AzureAD/active-directory-b2c-wordpress-plugin-openidconnect/master/LICENSE)
 */

 
//*****************************************************************************************


/** 
 * Requires the autoloaders.
 */
require 'autoload.php';
require 'vendor/autoload.php';

/**
 * Defines the response string posted by B2C.
 */
define('B2C_RESPONSE_MODE', 'id_token');

// Adds the B2C Options page to the Admin dashboard, under 'Settings'.
if (is_admin()) $b2c_settings_page = new B2C_Settings_Page();
$b2c_settings = new B2C_Settings();


//*****************************************************************************************


/**
 * Redirects to B2C on a user login request.
 */
function b2c_login() {
	try {
		$b2c_endpoint_handler = new B2C_Endpoint_Handler(B2C_Settings::$generic_policy);
		$authorization_endpoint = $b2c_endpoint_handler->get_authorization_endpoint()."&state=generic";
		wp_redirect($authorization_endpoint);
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}
	exit;
}

/** 
 * Redirects to B2C on user logout.
 */
function b2c_logout() {
	try {
		$signout_endpoint_handler = new B2C_Endpoint_Handler(B2C_Settings::$generic_policy);
		$signout_uri = $signout_endpoint_handler->get_end_session_endpoint();
		wp_redirect($signout_uri);
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}
	exit;
}

/** 
 * Verifies the id_token that is POSTed back to the web app from the 
 * B2C authorization endpoint. 
 */
function b2c_verify_token() {
	try {
		if (isset($_POST['error'])) {
			echo 'Unable to log in';
			echo '<br/>error:' . $_POST['error'];
			echo '<br/>error_description:' . $_POST['error_description'];
			exit;
		}

		if (isset($_POST[B2C_RESPONSE_MODE])) {	
			// Check which authorization policy was used
			switch ($_POST['state']) {
				case 'generic': 
					$policy = B2C_Settings::$generic_policy;
					break;
				case 'admin':
					$policy = B2C_Settings::$admin_policy;
					break;
				case 'edit_profile':
					$policy = B2C_Settings::$edit_profile_policy;
					break;
				default:
					// Not a B2C request, ignore.
					return;
			}	
			
			// Verifies token only if the checkbox "Verify tokens" is checked on the settings page
			$token_checker = new B2C_Token_Checker($_POST[B2C_RESPONSE_MODE], B2C_Settings::$clientID, $policy);
			if (B2C_Settings::$verify_tokens) {
				$verified = $token_checker->authenticate();
				if ($verified == false) wp_die('Token validation error');
			}
			
			// Use the email claim to fetch the user object from the WP database
			$email = $token_checker->get_claim('emails');
			$email = $email[0];
			$user = WP_User::get_data_by('email', $email);
			
			// Get the userID for the user
			if ($user == false) { // User doesn't exist yet, create new userID
				
				$first_name = $token_checker->get_claim('given_name');
				$last_name = $token_checker->get_claim('family_name');

				$our_userdata = array (
						'ID' => 0,
						'user_login' => $email,
						'user_pass' => NULL,
						'user_registered' => true,
						'user_status' => 0,
						'user_email' => $email,
						'display_name' => $first_name . ' ' . $last_name,
						'first_name' => $first_name,
						'last_name' => $last_name
						);

				$userID = wp_insert_user( $our_userdata ); 

				// Allows custom fields sent over the payload to be saved in Wordpress
				do_action('b2c_new_userdata', $userID, $token_checker->get_payload());
			} else if ($policy == B2C_Settings::$edit_profile_policy) { // Update the existing user w/ new attritubtes
				
				$first_name = $token_checker->get_claim('given_name');
				$last_name = $token_checker->get_claim('family_name');
				
				$our_userdata = array (
										'ID' => $user->ID,
										'display_name' => $first_name . ' ' . $last_name,
										'first_name' => $first_name,
										'last_name' => $last_name
										);
													
				$userID = wp_update_user( $our_userdata );

				// Allows custom fields sent over the payload to be updated in Wordpress
				do_action('b2c_update_userdata', $userID, $token_checker->get_payload());
			} else {
				$userID = $user->ID;
			}
			
			// Check if the user is an admin and needs MFA
			$wp_user = new WP_User($userID); 
			if (in_array('administrator', $wp_user->roles)) {
					
				// If user did not authenticate with admin_policy, redirect to admin policy
				if (mb_strtolower($token_checker->get_claim('tfp')) != mb_strtolower(B2C_Settings::$admin_policy)) {
					$b2c_endpoint_handler = new B2C_Endpoint_Handler(B2C_Settings::$admin_policy);
					$authorization_endpoint = $b2c_endpoint_handler->get_authorization_endpoint().'&state=admin';
					wp_redirect($authorization_endpoint);
					exit;
				}
			}
			
			// Set cookies to authenticate on WP side
			wp_set_auth_cookie($userID);
				
			// Redirect to home page
			wp_safe_redirect(site_url() . '/');
			exit;
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		exit;
	}
}

/** 
 * Redirects to B2C's edit profile policy when user edits their profile.
 */
function b2c_edit_profile() {
	
	// Check to see if user was requesting the edit_profile page, if so redirect to B2C
	$pagename = $_SERVER['REQUEST_URI'];
	$parts = explode('/', $pagename);
	$len = count($parts);
	if ($len > 1 && $parts[$len-2] == "wp-admin" && $parts[$len-1] == "profile.php") {
		
		// Return URL for edit_profile endpoint
		try {
			$b2c_endpoint_handler = new B2C_Endpoint_Handler(B2C_Settings::$edit_profile_policy);
			$authorization_endpoint = $b2c_endpoint_handler->get_authorization_endpoint().'&state=edit_profile';
			wp_redirect($authorization_endpoint);
		}
		catch (Exception $e) {
			echo $e->getMessage();
		}
		exit;
	}
}

/** 
 * Hooks onto the WP login action, so when user logs in on WordPress, user is redirected
 * to B2C's authorization endpoint. 
 */
add_action('wp_authenticate', 'b2c_login');

/**
 * Hooks onto the WP page load action, so when user request to edit their profile, 
 * they are redirected to B2C's edit profile endpoint.
 */
add_action('wp_loaded', 'b2c_edit_profile');

/** 
 * Hooks onto the WP page load action. When B2C redirects back to WordPress site,
 * if an ID token is POSTed to a special path, b2c-token-verification, this verifies 
 * the ID token and authenticates the user.
 */
add_action('wp_loaded', 'b2c_verify_token');

/**
 * Hooks onto the WP logout action, so when a user logs out of WordPress, 
 * they are redirected to B2C's logout endpoint.
 */
add_action('wp_logout', 'b2c_logout');

