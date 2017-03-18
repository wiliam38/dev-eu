<?php defined('SYSPATH') or die('No direct script access.');

class OAuth_Provider_Facebook extends Kohana_OAuth_Provider_Facebook {
	//
	// OAUTH DATA
	//
	public function user_data($consumer, $token) {
		if (empty($token['code'])) {
			return false;
		} else {		
			$token_url = $this->url_access_token()."?" . 
						"client_id=" . $consumer->key . 
						"&client_secret=" . $consumer->secret . 
						"&code=" . $token['code'] .
						"&redirect_uri=" . $consumer->callback;
		
			$response = file_get_contents($token_url);
			$params = null;
			parse_str($response, $params);
	
			$graph_url = "https://graph.facebook.com/me?"
				. 'fields=id,name,first_name,last_name,link,work,education,gender,email,timezone,locale,verified,updated_time'
				. '&access_token=' . $params['access_token'];
	
			/**
			 	[id] => 100000810264983 
			 	[name] => Jānis Daukšts 
			 	[first_name] => Jānis 
			 	[last_name] => Daukšts 
			 	[link] => http://www.facebook.com/profile.php?id=100000810264983 
			 	[work] => Array ( [0] => stdClass Object ( [employer] => stdClass Object ( [id] => 213996028639517 [name] => Hanzas Elektronika ) ) ) 
			 	[education] => Array ( [0] => stdClass Object ( [school] => stdClass Object ( [id] => 112921535407813 [name] => Ogres ģimnāzija ) [type] => High School ) [1] => stdClass Object ( [school] => stdClass Object ( [id] => 110881702269558 [name] => RTU ) [type] => College ) ) 
			 	[gender] => male 
			 	[email] => jdauksts@gmail.com 
			 	[timezone] => 3 
			 	[locale] => en_US 
			 	[verified] => 1 
			 	[updated_time] => 2011-08-30T19:48:37+0000 )
			*/
	
			return json_decode(file_get_contents($graph_url));
		}
	}
}