<?php defined('SYSPATH') or die('No direct script access.');

class OAuth_Provider_Twitter extends Kohana_OAuth_Provider_Twitter {
	
	//
	// GET USER DATA
	//
	public function user_data($consumer, $token) {
		// Create a new GET request with the required parameters
		$request = OAuth_Request::factory('resource', 'GET', 'http://api.twitter.com/1.1/account/verify_credentials.json', array(
			'oauth_consumer_key' => $consumer->key,
			'oauth_token' => $token->token,
		));
		
		// Sign the request using the consumer and token
		$request->sign($this->signature, $consumer, $token);
		
		// Create a response from the request
		$response = $request->execute();
		return json_decode($response);
	}
}
