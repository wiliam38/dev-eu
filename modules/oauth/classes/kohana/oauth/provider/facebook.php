<?php defined('SYSPATH') or die('No direct script access.');
/**
 * OAuth Facebook Provider
 *
 *
 * [!!] This class does not implement the Twitter API. It is only an
 * implementation of standard OAuth with Facebook as the service provider.
 *
 * @package    Kohana/OAuth
 * @category   Provider
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.0.7
 */
class Kohana_OAuth_Provider_Facebook extends OAuth_Provider {

	public $name = 'facebook';

	protected $signature = 'HMAC-SHA1';

	public function url_request_token()
	{
		return 'https://www.facebook.com/dialog/oauth';
	}

	public function url_authorize()
	{
		//return 'https://api.twitter.com/oauth/authenticate';
	}

	public function url_access_token()
	{
		return 'https://graph.facebook.com/oauth/access_token';
	}
	
	public function request_token(OAuth_Consumer $consumer, array $params = NULL)
	{
		// REDIRECT
		$params['scope'] = isset($params['scope'])?'&scope='.$params['scope']:'';	
		Request::factory()->redirect($this->url_request_token().'?&client_id='.$consumer->key.'&redirect_uri='.$consumer->callback.$params['scope']);

		return null;
	}

} // End OAuth_Provider_Twitter
