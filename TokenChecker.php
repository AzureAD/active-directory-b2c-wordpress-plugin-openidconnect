<?php 

require_once "phpseclib/Crypt/RSA.php";
require_once "EndpointHandler.php";
require_once "settings.php";
	
// A class to verify an id_token, following the implicit flow
// defined by the OpenID Connect standard
class TokenChecker {
	
	// Class variables
	private $id_token_array = array();
	private $head = "";
	private $payload = "";
	private $clientID = "";
	private $endpointHandler; 
	
	function __construct($id_token, $clientID, $policy_name) {
		
		$this->clientID = $clientID;
		$this->endpointHandler = new EndpointHandler($policy_name);
		$this->splitIdToken($id_token);
	}
	
	// Converts base64url encoded string into base64 encoded string
	// Also adds the necessary padding to the base64 encoded string
	private function convert_base64url_to_base64($input) {
			
		$padding = strlen($input) % 4;
		if ($padding > 0) {
			$input .= str_repeat("=", 4 - $padding);
		}
		return strtr($input, '-_', '+/');
	}
	
	// Splits the id token into an array of header, payload, and signature
	private function splitIdToken($id_token) {

		// Split the token into Header, Payload, and Signature, and decode
		$this->id_token_array = explode('.', $id_token);
		$this->head = base64_decode($this->id_token_array[0]);
		$this->payload = base64_decode($this->id_token_array[1]);
	}
	
	// Validates the RSA signature on the token
	private function validateSignature() {
		
		// Get kid from header
		$kid = getClaim("kid", $this->head);
		
		// Get public key
		$key_data = $this->endpointHandler->getJwksUriData();
		
		// Extract e and n from the public key
		$e_regex = '/"kid":\W*"' . $kid . '.*"e":\W*"([^"]+)/';
		$e_array = array();
		preg_match($e_regex, $key_data, $e_array);
		
		$n_regex = '/"kid":\W*"' . $kid . '.*"n":\W*"([^"]+)/';
		$n_array = array();
		preg_match($n_regex, $key_data, $n_array);
		
		// 'e' and 'n' are base64 URL encoded, change to just base64 encoding
		$e = $this->convert_base64url_to_base64($e_array[1]);
		$n = $this->convert_base64url_to_base64($n_array[1]);

		// Convert RSA(e,n) format to PEM format
		$rsa = new Crypt_RSA();
		$rsa->setPublicKey('<RSAKeyValue>
			<Modulus>' . $n . '</Modulus>
			<Exponent>' . $e . '</Exponent>
			</RSAKeyValue>');
		$public_key = $rsa->getPublicKey();
		
		// Verify Signature
		$to_verify_data = $this->id_token_array[0] . "." . $this->id_token_array[1];
		$to_verify_sig = base64_decode($this->convert_base64url_to_base64(($this->id_token_array[2])));
		$verified = openssl_verify($to_verify_data, $to_verify_sig, $public_key, OPENSSL_ALGO_SHA256);

		return $verified;	
	}
	
	// Validates audience, not_before, expiration_time, and issuer claims
	private function validateClaims() {
		
		$audience = getClaim("aud", $this->payload); // Should be app's clientID
		if ($audience != $this->clientID) return false;
		
		$cur_time = time();
		$not_before = getClaim("nbf", $this->payload); // epoch time, time after which token is valid (so basically nbf < cur time < exp)
		$expiration = getClaim("exp", $this->payload); // epoch time, check that the token is still valid
		if ($not_before > $cur_time) return false;
		if ($cur_time > $expiration) return false;
		
		// The Issuer Identifier for the OpenID Provider MUST exactly match the value of the iss (issuer) Claim.
		$iss_token = getClaim("iss", $this->payload); 
		$iss_metadata = $this->endpointHandler->getIssuer();
		if ($iss_token != $iss_metadata) return false;
		
		return true;
	}
	
	// Verifies both the signature and claims of the ID token
	public function authenticate() {
		
		if ($this->validateSignature() == false) return false;
		if ($this->validateClaims() == false) return false;
		return true;
	}
	
	// Extracts a claim from the ID token
	public function getClaim($name) {
		return getClaim($name, $this->payload);
	}
	
	// Returns the end session (aka logout) url
	public function getEndSessionEndpoint() {
		return $this->endpointHandler->getEndSessionEndpoint();
	}
	
	// Returns the id_token's header and payload, decoded
	public function getToken() {
		return $this->head . $this->payload;
	}
	
}

?>
