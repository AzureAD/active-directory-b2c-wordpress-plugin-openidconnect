<?php

/**
 * Plugin Name: b2c_authentication
 * Plugin URI: https://github.com/AzureAD/active-directory-b2c-wordpress-plugin-openidconnect
 * Description: A plugin that allows users to log in using B2C policies
 * Version: 1.0
 * Author: Olena Huang
 */

////////////////////////////////////////////////////////////////////////////////

// Turn on error reporting, for debugging
error_reporting(E_ALL);

// Adds the b2c options page for the admin
require_once 'settings_page.php';

// On user login, redirects to B2C
function b2c_login() {
	
	require_once 'settings.php';
	require_once 'EndpointHandler.php';
	
	// Redirect to sign up/sign in page
	$endpoint_handler = new EndpointHandler($generic_policy);
	$authorization_endpoint = $endpoint_handler->getAuthorizationEndpoint()."&state=generic";
	wp_redirect($authorization_endpoint);
	exit;
}

// On user logout, redirects to B2C
function b2c_logout() {
	
	require_once 'settings.php';
	require_once 'EndpointHandler.php';
	
	// Redirect to logout page
	$signout_endpoint_handler = new EndpointHandler($generic_policy);
	$signout_uri = $signout_endpoint_handler->getEndSessionEndpoint();
	wp_redirect($signout_uri);
	exit;
}

// Verifies the id_token that is POSTed back to the web app from the B2C authorization endpoint
function b2c_verify_token() {
	
	// If and only if ID token is POSTed to the /b2c-token-verification path, 
	// proceeds with verifying the ID token. The path check ensures that other plugins
	// which may POST id tokens do not conflict with this plugin.
	$pagename = $_SERVER['REQUEST_URI'];
	if ($pagename == '/b2c-token-verification' && isset($_POST['id_token'])) {
		
		require_once 'settings.php';
		require_once 'TokenChecker.php';
		
		// Check which authorization policy was used
		$action = $_POST['state'];
		if ($action == "generic") $policy = $generic_policy;
		if ($action == "admin") $policy = $admin_policy;
		if ($action == "edit_profile") $policy = $edit_profile_policy;
		
		// Verifies token only if the checkbox "Verify tokens" is checked on the settings page
		$tokenChecker = new TokenChecker($_POST['id_token'], $clientID, $policy);
		if ($verify_tokens) {
			$verified = $tokenChecker->authenticate();
			if ($verified == false) wp_die('Token validation error');
		}
		
		// Use the email claim to fetch the user object from the WP database
		$email = $tokenChecker->getClaim("emails");
		$email = $email[0];
		$user = WP_User::get_data_by("email", $email);
		
		// Get the userID for the user
		if ($user == false) { // User doesn't exist yet, create new userID
			
			$first_name = $tokenChecker->getClaim("given_name");
			$last_name = $tokenChecker->getClaim("family_name");

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
		}
		else if ($policy == $edit_profile_policy) { // Update the existing user w/ new attritubtes
			
			$first_name = $tokenChecker->getClaim("given_name");
			$last_name = $tokenChecker->getClaim("family_name");
			
			$our_userdata = array (
									'ID' => $user->ID,
									'display_name' => $first_name . ' ' . $last_name,
									'first_name' => $first_name,
									'last_name' => $last_name
									);
												
			$userID = wp_update_user( $our_userdata );
		}
		else {
			$userID = $user->ID;
		}
		
		// Check if the user is an admin and needs MFA
		$wp_user = new WP_User($userID); 
		if (in_array('administrator', $wp_user->roles)) {
				
			// If user did not authenticate with admin_policy, redirect to admin policy
			if ($tokenChecker->getClaim("acr") != $admin_policy) {
				$endpoint_handler = new EndpointHandler($admin_policy);
				$authorization_endpoint = $endpoint_handler->getAuthorizationEndpoint()."&state=admin";
				wp_redirect($authorization_endpoint);
				exit;
			}
		}
		
		// Set cookies to authenticate on WP side
		wp_set_auth_cookie($userID);
			
		// Redirect to home page
		wp_safe_redirect('/');
		exit;
	}
}

// When user edits their profile, redirects to B2C's edit profile policy
function b2c_edit_profile() {
	
	// Check to see if user was requesting the edit_profile page, if so redirect to B2C
	$pagename = $_SERVER['REQUEST_URI'];
	if ($pagename == '/wp-admin/profile.php') {
		
		require_once 'settings.php';
		require_once 'EndpointHandler.php';
		
		// Return URL for edit_profile endpoint
		$endpoint_handler = new EndpointHandler($edit_profile_policy);
		$authorization_endpoint = $endpoint_handler->getAuthorizationEndpoint()."&state=edit_profile";
		wp_redirect($authorization_endpoint);
		exit;
	}
}

// This hooks onto the WP login action.
// When user logs in on WordPress, this redirects to B2C's authorization endpoint
add_action('wp_authenticate', 'b2c_login');

// This hooks onto the WP page load action. WP doesn't have an edit profile hook,
// so this checks if the path requested in for profile edit.
// When user request to edit their profile, redirect to B2C's edit profile endpoint
add_action('wp_loaded', 'b2c_edit_profile');

// This hooks onto the WP page load action. When B2C redirects back to WordPress site,
// if an ID token is POSTed to a special path, b2c-token-verification, this verifies 
// the ID token and authenticates the user.
add_action('wp_loaded', 'b2c_verify_token');

// This hooks onto the WP logout action.
// When a user logs out of WordPress, this redirects to B2C's logout endpoint
add_action('wp_logout', 'b2c_logout');
?>




