<?php defined('SYSPATH') or die('No direct script access.');

/*
	SQL:
		INSERT INTO plugins (name, class, template, parameters, type_id, user_id, user_datetime)
		VALUES ('user_login', 'Controller_Plugins_Userlogin', 'plugins/userlogin/login', '', 1, 1, NOW());
		
	FORM:		
		
	FILES:
		Class: this file
		View: views/plugins/userlogin/login
		
	POST VARIABLES
		login_username - username for user login
		login_password - password for user login
		login_logout - user logs out
 */
class Controller_Plugins_Userlogin extends Controller_Plugins_Class {
	public function before() {
		parent::before();
				
		// MODELS
		$this->login = Model::factory('plugins_userslogin');
	}
		
	// PROFILE
	public function action_profile() {
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
		
		if ($this->user->logged_in('site')) {
			// USER LOGED IN
			$tpl_data['action'] = 'loged_in';
			
			$user_data = $this->user->userData($this->session->get('user_id'));
			$tpl_data['user'] = $user_data[0];
			
			$ret_data['status'] = '2';
		} else {
			// LOGIN FORM
			$tpl_data['action'] = 'login';
			
			$ret_data['status'] = '1';
		}
				
		//$data['lang_tag'] = $page_class->tpl->lang_tag;
		//$data['page_id'] = $page_data['page_id'];
		//$data['referrer_url'] = $page_class->request->referrer();
		
		$ret_data['response'] = $this->tpl->factory('plugins/userlogin/login', $tpl_data)->render();
		
		echo json_encode($ret_data);	
	}
	
	// LOGIN
	public function action_login_login() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$data = $this->login->login($_POST['email'], $_POST['password']);
		
		$data['hello_text'] = '';
		if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
			$user_data = $this->user->userData($this->session->get('user_id'));
			$data['hello_text'] = __('user_login.hello').' '.$user_data[0]['first_name'].' '.$user_data[0]['last_name'];
		}
		
		echo json_encode($data);		
	}	
	
	// LOGOUT
	public function action_logout() {
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
			
		// LOGOUT
		$this->user->logout();
		
		echo json_encode($ret_data);		
	}	
	
	// ACTIVATE
	public function action_activate() {
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
			
		// USER ACTIVATION
		$code = $this->request->post('code');
		if (!empty($code)) {
			$user_data = $this->login->activate($code);
			if ($user_data) {
				if ($user_data['status_id'] < 10) {
					// ACTIVATED	
					$tpl_data['activated'] = true;
					$tpl_data['action'] = "loged_in";	
					
					$tpl_data['user'] = $user_data;
				} elseif ($user_data['status_id'] == 15)	{
					// PASWORD RESTORE
					$tpl_data['action'] = "forgot_password_form";	
				}
				
				// AUTOMATIC LOGIN
				$this->user->login(null, null, FALSE, 'site', array('user_id' => $user_data['id']));
				
				$ret_data['response'] = $this->tpl->factory('plugins/userlogin/login', $tpl_data)->render();					
				$ret_data['status'] = '1';
			}
		} 
		
		echo json_encode($ret_data);
	}
	
	// FORGOT PASSWORD
	public function action_forgot_password_from() {
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
			
		$tpl_data['action'] = "forgot_password";			
		$ret_data['response'] = $this->tpl->factory('plugins/userlogin/login', $tpl_data)->render();
		
		echo json_encode($ret_data);	
	}
	
	public function action_forgot_password() {
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
		
		// PARAMS
		$this->auto_render = FALSE;
		
		if (isset($_REQUEST['email']) && Valid::email($_REQUEST['email']) && $this->request->is_ajax()) {
			$this->login->forgot_password($_REQUEST['email']);
			$ret_data['response'] = CMS::getLexicons('user_forgot_password.sent');
			$ret_data['status'] = '1';
		} else {
			$ret_data['error'] = __('user_forgot_password.error');
		}
		
		echo json_encode($ret_data);
	}
	public function action_forgot_password_change() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$result['status'] = '0';
		$result['error'] = array();
		$result['response'] = '';
				
		if (empty($_REQUEST['password'])) $result['error'][] = CMS::getLexicons('user_registration.error_password');
		elseif (!is_null($_REQUEST['password2']) AND $_REQUEST['password'] != $_REQUEST['password2']) $result['error'][] = CMS::getLexicons('user_registration.error_password_not_match');
		elseif (strlen($_REQUEST['password']) < 5) $result['error'][] = CMS::getLexicons('user_registration.error_password_too_short');
		
		if ($this->user_id && count($result['error']) == 0) {
			$result = $this->user->set_password($this->user_id, $_REQUEST['password']);
		} else {
			$result['error'] = implode('<br/>', $result['error']);
		}
		
		echo json_encode($result);
	}
	
	//
	// REGISTRATION
	//
	public function action_register_registration() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$data = $this->login->manual_registration($_POST);	
		
		echo json_encode($data);
	}
	
	//
	// USER SAVE
	//
	public function action_save_user() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->user->logged_in('site')) {
			$data = $this->login->user_save($this->user_id, $_REQUEST);	
		} else {
			$data = array(
				'status' => '0',
				'error' => 'Error',
				'response' => '');
		}
		
		echo json_encode($data);
	}
	
	//
	//
	// DRAUGIEM
	//
	//
	public function action_draugiem_login() {
		// Load a consumer from config file.
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.draugiem'));		
		$consumer->callback($this->base_url.'plugins/userlogin/draugiem_auth');

		// The provider actually makes the requests
		$draugiem = OAuth_Provider::factory('draugiem');
		$token = $draugiem->request_token($consumer);
	}
	public function action_draugiem_auth() {
		// DRAUGIEM CODE		
		$dr_auth_status = CMS::getGET('dr_auth_status');
		$request_token['code'] = CMS::getGET('dr_auth_code');
		
		if ($dr_auth_status == 'ok') {
			// LOGIN OK
			// Rebuild the consumer and provider
			$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.draugiem'));
			$consumer->callback($this->base_url.'plugins/userlogin/draugiem_auth');

			$draugiem = OAuth_Provider::factory('draugiem');

			// Exchange the request token for a longer term access token
			$dr_user = $draugiem->user_data($consumer, $request_token);	
			
			// DRAUGIEM LOGIN
			$status = $this->login->draugiem_login($dr_user);
			
			} else {
			$status = false;
		}
				
		if ($status) {		
			// REDIRECT
			if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
				$user_data = $this->user->userData($this->session->get('user_id'));
				$hello_text = __('user_login.hello').' '.$user_data[0]['first_name'].' '.$user_data[0]['last_name'];
			}
			
			echo '	<script type="text/javascript">
						window.opener.updateHello('.json_encode($hello_text).');
						window.opener.closeProfile();
						self.close();
					</script>';
		} else {
			$errors = implode('<br/>', $this->user->get_errors());			
			
			echo '	<script type="text/javascript">
						window.opener.errorOAuth("'.$errors.'");
						self.close();
					</script>';
		}
	}
	
	//
	//
	// FACEBOOK 
	//
	//
	public function action_facebook_login() {
		// Load a consumer from config file.
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.facebook'));		
		$consumer->callback($this->base_url.'plugins/userlogin/facebook_auth');

		// The provider actually makes the requests
		$facebook = OAuth_Provider::factory('facebook');
		$token = $facebook->request_token($consumer, array('scope' => Kohana::$config->load('oauth.facebook.scope')));
	}
	public function action_facebook_auth() {
		// FACEBOOK CODE		
		$request_token['code'] = CMS::getGET('code');
				
		// Rebuild the consumer and provider
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.facebook'));
		$consumer->callback($this->base_url.'plugins/userlogin/facebook_auth');
		
		$facebook = OAuth_Provider::factory('facebook');

		// Exchange the request token for a longer term access token
		$fb_user = $facebook->user_data($consumer, $request_token);	
		
		// FACEBOOK LOGIN
		if ($this->login->facebook_login($fb_user)) {		
			// REDIRECT
			if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
				$user_data = $this->user->userData($this->session->get('user_id'));
				$hello_text = __('user_login.hello').' '.$user_data[0]['first_name'].' '.$user_data[0]['last_name'];
			}
			
			echo '	<script type="text/javascript">
						window.opener.updateHello('.json_encode($hello_text).');
						window.opener.closeProfile();
						self.close();
					</script>';
		} else {
			$errors = implode('<br/>', $this->user->get_errors());			
			
			echo '	<script type="text/javascript">
						window.opener.errorOAuth("'.$errors.'");
						self.close();
					</script>';
		}
	}

	//
	//
	// TWITTER
	//
	//
	public function action_twitter_login() {
		// Load a consumer from config file.
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.twitter'));		
		$consumer->callback($this->base_url.'plugins/userlogin/twitter_auth');

		// The provider actually makes the requests
		$provider = OAuth_Provider::factory('twitter');
		$request_token = $provider->request_token($consumer);
      	$this->session->set('oauth_token', $request_token);
        
		// Once we have the request token, we redirect to Twitter to confirm.
		Request::factory()->redirect($provider->authorize_url($request_token));
	}
	public function action_twitter_auth() {		
		// Pull our request token out of the session. We use get_once because request tokens are single use
		$request_token = $this->session->get_once('oauth_token');

		// Add the verifier from the query parameters
		$request_token->verifier(CMS::getGET('oauth_verifier'));

		// Rebuild the consumer and provider
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.twitter'));
		$twitter = OAuth_Provider::factory('twitter');

		// Exchange the request token for a longer term access token
		$access_token = $twitter->access_token($consumer, $request_token);		
		$this->session->set( 'oauth_access_token', $access_token );
      		
		
		// GET TWITTER USER DATA
		$tw_user = $twitter->user_data($consumer, $access_token);
		
		// FACEBOOK LOGIN
		if ($this->login->twitter_login($tw_user)) {		
			// REDIRECT
			if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
				$user_data = $this->user->userData($this->session->get('user_id'));
				$hello_text = __('user_login.hello').' '.$user_data[0]['first_name'].' '.$user_data[0]['last_name'];
			}
			
			echo '	<script type="text/javascript">
						window.opener.updateHello('.json_encode($hello_text).');
						window.opener.closeProfile();
						self.close();
					</script>';
		} else {
			$errors = implode('<br/>', $this->user->get_errors());		
			
			echo '	<script type="text/javascript">
						window.opener.errorOAuth("'.$errors.'");
						self.close();
					</script>';
		}
	}

	//
	//
	// GOOGLE
	//
	//
	public function action_google_login() {
		// Load a consumer from config file.
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.google'));		
		$consumer->callback($this->base_url.'plugins/userlogin/google_auth');

		// The provider actually makes the requests
		$provider = OAuth_Provider::factory('google');
		$request_token = $provider->request_token($consumer, Kohana::$config->load('oauth.google'));
      	$this->session->set('oauth_token', $request_token);
        
		// Once we have the request token, we redirect to Twitter to confirm.
		Request::factory()->redirect($provider->authorize_url($request_token));
	}
	public function action_google_auth() {
		// Pull our request token out of the session. We use get_once because request tokens are single use
		$request_token = $this->session->get_once('oauth_token');

		// Add the verifier from the query parameters
		$request_token->verifier(CMS::getGET('oauth_verifier'));

		// Rebuild the consumer and provider
		$consumer = OAuth_Consumer::factory(Kohana::$config->load('oauth.google'));
		$google = OAuth_Provider::factory('google');

		// Exchange the request token for a longer term access token
		$access_token = $google->access_token($consumer, $request_token);		
		$this->session->set( 'oauth_access_token', $access_token );
      		
		
		// GET TWITTER USER DATA
		$go_user = $google->user_data($consumer, $access_token);
				
		// FACEBOOK LOGIN
		if ($this->login->google_login($go_user)) {		
			// REDIRECT
			if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
				$user_data = $this->user->userData($this->session->get('user_id'));
				$hello_text = __('user_login.hello').' '.$user_data[0]['first_name'].' '.$user_data[0]['last_name'];
			}
			
			echo '	<script type="text/javascript">
						window.opener.updateHello('.json_encode($hello_text).');
						window.opener.closeProfile();
						self.close();
					</script>';
		} else {
			$errors = implode('<br/>', $this->user->get_errors());			
			
			echo '	<script type="text/javascript">
						window.opener.errorOAuth("'.$errors.'");
						self.close();
					</script>';
		}
	}
}