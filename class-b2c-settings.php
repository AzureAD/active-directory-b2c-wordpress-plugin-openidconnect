<?php

class B2C_Settings {
	
	// These settings are configurable by the admin
	public static $tenant = "";
	public static $clientID = "";
	public static $generic_policy = "";
	public static $admin_policy = "";
	public static $edit_profile_policy = "";
	public static $redirect_uri = "";
	public static $verify_tokens = 1;
	public static $b2cAdmins = 1;
	public static $graphID = "";
	public static $clientSecret = "";
	
	// These settings define the authentication flow, but are not configurable on the settings page
	// because this plugin is made to support OpenID Connect implicit flow with form post responses
	public static $response_type = "id_token"; 
	public static $response_mode = "form_post"; 
	public static $scope = "openid"; 
	public static $api_version = "api-version=1.6";
	
	function __construct() {
			
		// Get the inputs from the B2C Settings Page
		$config_elements = get_option('b2c_config_elements');
			
		if (isset($config_elements)) {
		
			// Parse the settings entered in by the admin on the b2c settings page
			self::$tenant = $config_elements['b2c_aad_tenant'];
			self::$clientID = $config_elements['b2c_client_id'];
			self::$generic_policy = $config_elements['b2c_subscriber_policy_id'];
			self::$admin_policy = $config_elements['b2c_admin_policy_id'];
			self::$edit_profile_policy = $config_elements['b2c_edit_profile_policy_id'];
			self::$redirect_uri = urlencode(site_url().'/'); 
			if ($config_elements['b2c_verify_tokens']) self::$verify_tokens = 1;
			else self::$verify_tokens = 0;
			if ($config_elements['b2c_GA_Grant_Admins']) self::$b2cAdmins = 1;
			else self::$b2cAdmins = 0;
			self::$clientSecret = $config_elements['b2c_client_secret'];
			self::$graphID = $config_elements['b2c_graph_id'];
		}
	}

	static function metadata_endpoint_begin() {
		return 'https://login.microsoftonline.com/'.
				self::$tenant.
				'/v2.0/.well-known/openid-configuration?p=';
	}
}


