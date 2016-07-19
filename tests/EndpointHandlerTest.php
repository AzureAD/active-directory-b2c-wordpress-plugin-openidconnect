<?php

error_reporting(E_ALL);
use PHPUnit\Framework\TestCase;
include 'class-b2c-endpoint-handler.php';

define('METADATA', '/b2c-token-verification');

class EndpointHandlerTest extends TestCase
{
	
	protected $endpointHandler;
	
	/*protected function setUp() {
		$this->endpointHandler = new EndpointHandler();
	}*/
	
    public function testConstructor()
    {
		$metadata = 
		'{
		  "issuer": "https://login.microsoftonline.com/224c9e48-7c56-4872-b806-d5cfbe426582/v2.0/",
		  "authorization_endpoint": "https://login.microsoftonline.com/olenaolena.onmicrosoft.com/oauth2/v2.0/authorize?p=b2c_1_sign_in",
		  "token_endpoint": "https://login.microsoftonline.com/olenaolena.onmicrosoft.com/oauth2/v2.0/token?p=b2c_1_sign_in",
		  "end_session_endpoint": "https://login.microsoftonline.com/olenaolena.onmicrosoft.com/oauth2/v2.0/logout?p=b2c_1_sign_in",
		  "jwks_uri": "https://login.microsoftonline.com/olenaolena.onmicrosoft.com/discovery/v2.0/keys?p=b2c_1_sign_in",
		  "response_modes_supported": [
			"query",
			"fragment",
			"form_post"
		  ],
		  "response_types_supported": [
			"code",
			"id_token",
			"code id_token"
		  ],
		  "scopes_supported": [
			"openid"
		  ],
		  "subject_types_supported": [
			"pairwise"
		  ],
		  "id_token_signing_alg_values_supported": [
			"RS256"
		  ],
		  "token_endpoint_auth_methods_supported": [
			"client_secret_post"
		  ],
		  "claims_supported": [
			"given_name",
			"idp",
			"country",
			"city",
			"jobTitle",
			"family_name",
			"sub"
		  ]
		}';
										  
		$endpointHandler = $this->getMockBuilder('B2C_Endpoint_Handler')->disableOriginalConstructor()->getMock();
		$endpointHandler->metadata = $metadata;
		
		$EXPECTED_ISSUER = "https://login.microsoftonline.com/224c9e48-7c56-4872-b806-d5cfbe426582/v2.0/";
		$this->assertEquals($EXPECTED_ISSUER, $endpointHandler->get_issuer());
    }
	
}

?>