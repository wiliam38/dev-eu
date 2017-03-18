<?php defined('SYSPATH') or die('No direct script access.');

class OAuth_Provider_Google extends Kohana_OAuth_Provider_Google {
	
	//
	// GET USER DATA
	//
	public function user_data($consumer, $token) {
		// Create a new GET request with the required parameters
		$request = OAuth_Request::factory('resource', 'GET', 'https://www.googleapis.com/oauth2/v1/userinfo', array(
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
