<?php

/** 
 * A class to handle both fetching and sending data to the various endpoints.
 */
class B2C_Endpoint_Handler {
	
	private $metadata = array();
	private $metadata_endpoint = '';
	
	public function __construct($policy_name) {
		$this->metadata_endpoint = B2C_Settings::metadata_endpoint_begin() . $policy_name;
		$response = wp_remote_get($this->metadata_endpoint);
		$decoded_response = json_decode($response['body'], true);
		if (empty($decoded_response) || !isset($decoded_response) )
			throw new Exception('Unable to retrieve metadata from ' . $this->metadata_endpoint);
		
		$this->metadata = $decoded_response;
	}
	
	/** 
	 * Returns the value of the issuer claim from the metadata.
	 */
	public function get_issuer() {
		return $this->metadata['issuer']; 
	}
	
	/**
	 * Returns the value of the jwks_uri claim from the metadata.
	 */ 
	public function get_jwks_uri() {
		$jwks_uri = $this->metadata['jwks_uri'];
		
		// Cast to array if not an array
		$jwks_uri = is_array($jwks_uri) ? $jwks_uri : array($jwks_uri);
		return $jwks_uri;	
	}
	
	/** 
	 * Returns the data at the jwks_uri page.
	 */ 
	public function get_jwks_uri_data() {
		$jwks_uri = $this->get_jwks_uri();
		
		$key_data = array();
		foreach ($jwks_uri as $uri) {
			$response = wp_remote_get($uri);
			array_push($key_data, $response['body']);	
		}
		return $key_data;
	}
	
	/** 
	 * Obtains the authorization endpoint from the metadata
	 * and adds the necessary query arguments.
	 */
	public function get_authorization_endpoint() {

		$authorization_endpoint = $this->metadata['authorization_endpoint'].
											'&response_type='.B2C_Settings::$response_type.
											'&client_id='.B2C_Settings::$clientID.
											'&redirect_uri='.B2C_Settings::$redirect_uri.
											'&response_mode='.B2C_Settings::$response_mode.
											'&scope='.B2C_Settings::$scope;
		return $authorization_endpoint;
	}
	
	/** 
	 * Obtains the end session endpoint from the metadata
	 * and adds the necessary query arguments.
	 */
	public function get_end_session_endpoint() {
		
		$end_session_endpoint = $this->metadata['end_session_endpoint'].
								'&redirect_uri='.B2C_Settings::$redirect_uri;
		return $end_session_endpoint;
	}
}

