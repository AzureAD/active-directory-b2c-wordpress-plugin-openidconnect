<?php 

use \Firebase\JWT\JWT;

/** 
 * A class to verify an id_token, following the implicit flow
 * defined by the OpenID Connect standard.
 */
class B2C_Token_Checker {
	
	private $id_token_array = array(); // still encoded
	private $head = array(); // decoded
	private $payload = array(); // decoded
	private $clientID = '';
	private $endpoint_handler; 
	
	function __construct($id_token, $clientID, $policy_name) {
		
		$this->clientID = $clientID;
		$this->endpoint_handler = new B2C_Endpoint_Handler($policy_name);
		$this->split_id_token($id_token);
	}
	
	/** 
	 * Converts base64url encoded string into base64 encoded string.
	 * Also adds the necessary padding to the base64 encoded string.
	 */
	private function convert_base64url_to_base64($data) {
		return str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT);
	}
	
	/**
	 * Splits the id token into an array of header, payload, and signature.
	 */
	private function split_id_token($id_token) {

		// Split the token into Header, Payload, and Signature, and decode
		$this->id_token_array = explode('.', $id_token, 3);
		$this->head = json_decode(base64_decode($this->id_token_array[0]), true);
		$this->payload = json_decode(base64_decode($this->id_token_array[1]), true);
	}
	
	/** 
	 * Validates the RSA signature on the token.
	 */
	private function validate_signature() {
		
		//require_once "phpseclib/Crypt/RSA.php";
		
		// Get kid from header
		$kid = $this->head['kid'];
		
		// For each JwksURI, get the public key and verify the signature
		$key_datas = $this->endpoint_handler->get_jwks_uri_data();
		foreach ($key_datas as $key_data) {
			
			// Iterate through each key type until the one that matches the "kid" is found
			$keys = json_decode($key_data, true)['keys'];
			foreach ($keys as $key) {
				
				if ($key['kid'] == $kid) {
					$e = $key['e'];
					$n = $key['n'];
					break; 
				}
			}
			
			// 'e' and 'n' are base64 URL encoded, change to just base64 encoding
			$e = $this->convert_base64url_to_base64($e);
			$n = $this->convert_base64url_to_base64($n);

			// Convert RSA(e,n) format to PEM format
			$rsa = new Crypt_RSA();
			$rsa->setPublicKey('<RSAKeyValue>
				<Modulus>' . $n . '</Modulus>
				<Exponent>' . $e . '</Exponent>
				</RSAKeyValue>');
			$public_key = $rsa->getPublicKey();
			
			// Construct data and signature for verification
			$to_verify_data = $this->id_token_array[0] . "." . $this->id_token_array[1];
			$to_verify_sig = base64_decode($this->convert_base64url_to_base64(($this->id_token_array[2])));
			
			try {
				$jwt = $this->id_token_array[0] . "." . $this->id_token_array[1] . "." .$this->id_token_array[2];
				$decoded = JWT::decode($jwt, $public_key, array('HS256', 'HS384', 'HS512', 'RS256'));
			}
			catch (Exception $e) {
				echo 'error: ' . $e->getMessage();
				return false;
			}
			
		}
		
		// Returns true when verified successfully
		return true; 
	}
	
	/**
	 * Validates audience, not_before, expiration_time, and issuer claims.
	 */
	private function validate_claims() {
		
		$audience = $this->payload['aud']; // Should be app's clientID
		if ($audience != $this->clientID) {
			return false;
		}
		
		$cur_time = time();
		$not_before = $this->payload['nbf']; // epoch time, time after which token is valid (so basically nbf < cur time < exp)
		$expiration = $this->payload['exp']; // epoch time, check that the token is still valid
		if ($not_before > $cur_time) {
			return false;
		}
		if ($cur_time > $expiration) {
			return false;
		}
		
		// The Issuer Identifier for the OpenID Provider MUST exactly match the value of the iss (issuer) Claim.
		$iss_token = $this->payload['iss']; 
		$iss_metadata = $this->endpoint_handler->get_issuer();
		if ($iss_token != $iss_metadata) {
			return false;
		}
		
		return true;
	}
	
	/** 
	 * Verifies both the signature and claims of the ID token.
	 */
	public function authenticate() {
		
		if ($this->validate_signature() == false) {
			return false;
		}
		if ($this->validate_claims() == false) {
			return false;
		}
		return true;
	}
	
	/** 
	 * Extracts a claim from the ID token.
	 */
	public function get_claim($name) {
		return $this->payload[$name];
	}
}


