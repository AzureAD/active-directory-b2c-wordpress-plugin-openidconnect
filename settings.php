<?php

	// Parses the settings entered in by the admin on the b2c settings page

	// Gets URL of current page
	function curPageURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"])) 
			$pageURL .= "s";
		
		$pageURL .= "://";
		$pageURL .= $_SERVER["SERVER_NAME"];

		return $pageURL;
	}

	$config_elements = get_option('b2c_config_elements');
	
	// App settings and policies
	$tenant = $config_elements['b2c_aad_tenant'];
	$clientID = $config_elements['b2c_client_id'];
	$client_secret = $config_elements['b2c_client_secret'];
	$generic_policy = $config_elements['b2c_subscriber_policy_id'];
	$admin_policy = $config_elements['b2c_admin_policy_id'];
	$edit_profile_policy = $config_elements['b2c_edit_profile_policy_id'];
	$redirect_uri = urlencode('https://olenasblog.azurewebsites.net/'); 

	// Authentication Flow
	$response_type = "id_token"; // either id_token or code, depending on whether your application has enabled/disabled implicit flow
	$response_mode = "form_post"; // can also be query_string or fragment, but this code works with form_post
	$scope = "openid"; // currently, just openid supported
	
	///////////////////////////////////////////////////////////////////////////////
	$metadata_endpoint_begin = 'https://login.microsoftonline.com/'.
						 $tenant.
						 '.onmicrosoft.com/v2.0/.well-known/openid-configuration?p=';


?>