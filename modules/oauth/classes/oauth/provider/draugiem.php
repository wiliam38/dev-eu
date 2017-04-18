<?php defined('SYSPATH') or die('No direct script access.');

class OAuth_Provider_Draugiem extends Kohana_OAuth_Provider_Draugiem {
	//
	// OAUTH DATA
	//
	public function user_data($consumer, $token) {
		if (empty($token['code'])) {
			return false;
		} else {		
			$token_url = $this->url_access_token()."?" . 
						"action=authorize" .
						"&app=" . $consumer->secret . 
						"&code=" . $token['code'];
		
			$response = file_get_contents($token_url);
			
			/**
				[apikey] => 123
				[uid] => 123 
				[language] => lv 
				[users] => Object ( 
					[123] => Object ( 
						[uid] => 123 
						[name] => vards
						[surname] => uzvards
						[nick] => 
						[place] => 
						[img] =>  
						[imgi] => 
						[imgm] => 
						[imgl] => 
						[sex] => M 
						[birthday] => 
						[age] => 
						[adult] => 
						[type] => User_Default 
						[created] => 
						[deleted] => ) 
					) 
				)
			*/
			
			$response_data = json_decode($response);
			if (!empty($response_data->uid)) {
				$uid = $response_data->uid;
				$user_obj = $response_data->users->$uid;	
			} else {
				$user_obj = false;
			}
			
			return $user_obj;
		}
	}
}