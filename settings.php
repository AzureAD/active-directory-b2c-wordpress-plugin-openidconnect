<?php

	// Gets URL of homepage
	function homePageURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"])) 
			$pageURL .= "s";
		
		$pageURL .= "://";
		$pageURL .= $_SERVER["SERVER_NAME"];

		return $pageURL;
	}

	// Get the inputs from the B2C Settings Page
	$config_elements = get_option('b2c_config_elements');
	
	// Parse the settings entered in by the admin on the b2c settings page
	$tenant = $config_elements['b2c_aad_tenant'];
	$clientID = $config_elements['b2c_client_id'];
	$generic_policy = $config_elements['b2c_subscriber_policy_id'];
	$admin_policy = $config_elements['b2c_admin_policy_id'];
	$edit_profile_policy = $config_elements['b2c_edit_profile_policy_id'];
	$redirect_uri = urlencode(homePageURL()); 
	if ($config_elements['b2c_verify_tokens']) $verify_tokens = 1;
	else $verify_tokens = 0;

	// These settings define the authentication flow, but are not configurable on the settings page
	// because this plugin is made to support OpenID Connect implicit flow with form post responses
	$response_type = "id_token"; // either id_token or code, depending on whether your application has enabled/disabled implicit flow
	$response_mode = "form_post"; // can also be query_string or fragment, but this code only supports form_post
	$scope = "openid"; // currently, just openid supported
	
	///////////////////////////////////////////////////////////////////////////////
	$metadata_endpoint_begin = 'https://login.microsoftonline.com/'.
						 $tenant.
						 '.onmicrosoft.com/v2.0/.well-known/openid-configuration?p=';


?>