<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Plugins_Userslogin extends Model {
	public function __construct() {
		parent::__construct();
	}
		  
	public function load($parameters, $template, $page_data, $page_class) {
		$type = 'login';
		
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			if ($key == 'limit') $$key = ' LIMIT '.(int)$val;
			else $$key = $val;
		}

		// LOGOUT
		if (isset($_POST['login_logout'])) {
			$this->user->logout();	
			$page_class->request->redirect(URL::site($page_class->request->uri(), true));	
		}

		$result = '';

		// USER ACTIVATION
		$code = CMS::getGET('code');
		if (!empty($code)) {
			$result = '<script type="text/javascript">$().ready(function() { activate(\''.$code.'\'); });</script>';
		}
		
		return $result;
	}
	
	// 
	// LOGIN 
	//
	public function login($username, $password, $role = 'site') {
		$data['status'] = "0";
		$data['error'] = "";
		
		if (!$this->user->login($username,$password,FALSE,$role)) {			
			$errors = $this->user->get_errors();
			
			for ($i=0; $i<count($errors); $i++) {
				$data['error'] .= $errors[$i]."<br/>";
			}
		} else {
			$data['status'] = "1";
		}
		
		return $data;
	}
	
	//
	// DRAUGIEM LOGIN
	//
	public function draugiem_login($dr_user, $role = 'site') {							
		if (!empty($dr_user)) {			
			// GET USER
			$user_data = $this->user->userData(null, array('draugiem_id' => $dr_user->uid));
			
			if (count($user_data) == 0) {
				// ADD ADD USER				
				$new_user_data = array(	'first_name' => $dr_user->name,
										'last_name' => $dr_user->surname,
										'email' => '',
										'email_status_id' => 1,
										'status_id' => '10',
										'draugiem_id' => $dr_user->uid);
				$user_id = $this->user->register($new_user_data, false);
				if ($user_id) $user_role_id = $this->user->add_role($user_id, 'site');
			} 
			return $this->user->login(null, null, FALSE, $role, array('draugiem_id' => $dr_user->uid));	 		
		} 

		return false;
	}
	
	//
	// FACEBOOK LOGIN
	//
	public function facebook_login($fb_user, $role = 'site') {
		if (!empty($fb_user)) {			
			// GET USER
			$user_data = $this->user->userData(null, array('facebook_id' => $fb_user->id));
			
			if (count($user_data) == 0) {
				$db_data = $this->db->select('users.id')
					->from('users')
					->where('users.email', 'like', $fb_user->email)
					->execute()
					->as_array();
				
				if (count($db_data) > 0) {
					$sql = $this->db->update('users')
						->set(array(
							'email_status_id' => ($fb_user->verified==1)?10:1,
							'status_id' => '10',
							'facebook_id' => $fb_user->id))
						->where('users.email', 'like', $fb_user->email)
						->execute();
				} else {				
					// ADD FACEBOOK USER				
					$new_user_data = array(	'first_name' => $fb_user->first_name,
											'last_name' => $fb_user->last_name,
											'email' => $fb_user->email,
											'email_status_id' => ($fb_user->verified==1)?10:1,
											'status_id' => '10',
											'facebook_id' => $fb_user->id);
					$user_id = $this->user->register($new_user_data, false);
					if ($user_id) $user_role_id = $this->user->add_role($user_id, 'site');
				}
			}
			return $this->user->login(null, null, FALSE, $role, array('facebook_id' => $fb_user->id));			 		
		} 

		return false;
	}
	
	//
	// TWITTER LOGIN
	//
	public function twitter_login($tw_user, $role = 'site') {
		if (!empty($tw_user)) {			
			// GET USER
			$user_data = $this->user->userData(null, array('twitter_id' => $tw_user->id));
			
			if (count($user_data) == 0) {
				// ADD FACEBOOK USER								
				$first_name = substr($tw_user->name, 0, strpos($tw_user->name, ' '));
				$last_name = substr($tw_user->name, strpos($tw_user->name, ' '));
				
				$new_user_data = array(	'first_name' => $first_name,
										'last_name' => $last_name,
										'status_id' => '10',
										'twitter_id' => $tw_user->id);				
				$user_id = $this->user->register($new_user_data, false);
				$user_role_id = $this->user->add_role($user_id, 'site');	
			} 
			
			return $this->user->login(null, null, FALSE, $role, array('twitter_id' => $tw_user->id));			 		
		} 

		return false;
	}

	//
	// GOOGLE LOGIN
	//
	public function google_login($go_user, $role = 'site') {
		if (!empty($go_user)) {			
			// GET USER
			$user_data = $this->user->userData(null, array('google_id' => $go_user->id));
			
			if (count($user_data) == 0) {
				// ADD FACEBOOK USER								
				$new_user_data = array(	'first_name' => $go_user->given_name,
										'last_name' => $go_user->family_name,
										'status_id' => '10',
										'google_id' => $go_user->id);										
				$user_id = $this->user->register($new_user_data, false);
				$user_role_id = $this->user->add_role($user_id, 'site');	
			} 
			
			return $this->user->login(null, null, FALSE, $role, array('google_id' => $go_user->id));			 		
		} 

		return false;
	}





	/*
	 * 
	 * REGISTRATION
	 * 
	 */
	
	// REGISTER USER
	public function manual_registration($user_data) {
		$data['status'] = "0";
		$data['error'] = "";
		$data['user_id'] = "";
		$data['response'] = '';		
		
		$user_data['username'] = isset($user_data['username'])?$user_data['username']:'';
		$user_data['password'] = isset($user_data['password'])?$user_data['password']:'';
		$user_data['password2'] = isset($user_data['password2'])?$user_data['password2']:'';
		$user_data['email'] = isset($user_data['email'])?$user_data['email']:'';
		$user_data['status_id'] = isset($user_data['status_id'])?$user_data['status_id']:'1';
		$user_data['company'] = isset($user_data['company'])?$user_data['company']:'';
		$user_data['reg_nr'] = isset($user_data['reg_nr'])?$user_data['reg_nr']:'';
		$user_data['vat_nr'] = isset($user_data['vat_nr'])?$user_data['vat_nr']:'';
		$user_data['pro_category_request'] = !empty($user_data['pro_category_request'])?1:0;
		
		// ERRORS
		if (!isset($user_data['rights'])) $data['error'] .= CMS::getLexicons('user_registration.error_accept_right').'<br/>';
		if (empty($user_data['first_name'])) $data['error'] .= CMS::getLexicons('user_registration.error_first_name').'<br/>';
		if (empty($user_data['last_name'])) $data['error'] .= CMS::getLexicons('user_registration.error_last_name').'<br/>';
		if (!empty($user_data['company']) && empty($user_data['reg_nr']) && empty($user_data['vat_nr'])) $data['error'] .= CMS::getLexicons('user_registration.error_company_reg_vat').'<br/>';
		if (empty($user_data['email']) || !Valid::email($user_data['email'])) $data['error'] .= CMS::getLexicons('user_registration.error_email').'<br/>';
		if (empty($user_data['phone']) || !Valid::phone($user_data['phone'])) $data['error'] .= CMS::getLexicons('user_registration.error_phone').'<br/>';
		if (empty($user_data['password']) || empty($user_data['password2'])) $data['error'] .= CMS::getLexicons('user_registration.error_password').'<br/>';
		if ($user_data['pro_category_request'] == 1) {
			if (empty($user_data['company']) || empty($user_data['reg_nr'])) $data['error'] .= CMS::getLexicons('user_registration.error_company_data').'<br/>';
		}
		if (!empty($user_data['vat_nr']) && !preg_match('/^[a-zA-Z][a-zA-Z][0-9]+$/i', trim($user_data['vat_nr']))) $data['error'] .= CMS::getLexicons('user_registration.error_vat_nr').'<br/>';
		
		// ADD USER		
		if (empty($data['error'])) {
			$new_user_id = $this->user->register($user_data);		
			if (!$new_user_id) {			
				$errors = $this->user->get_errors();
				
				for ($i=0; $i<count($errors); $i++) {
					$data['error'] .= $errors[$i]."<br/>";
				}
			} else {
				$data['user_id'] = $new_user_id;
				$data['status'] = "1";
				$data['response'] = CMS::getLexicons('user_registration.registration_done');
				
				
				// ADD SITE ROLE
				$user_role = $this->user->add_role($new_user_id, 'site');
				
				// SEND EMAIL
				$mail_to = $user_data['email'];
				$mail_from = CMS::getSettings('default.email');
				$mail_from_name = CMS::getSettings('default.site_name');
				if (Valid::email($mail_to) AND Valid::email($mail_from)) {							
					// GET USER DATA
					$user_data = $this->user->userData($new_user_id);
					$user_data = $user_data[0];	
					$code = isset($user_data['activation_code'])?$user_data['activation_code']:null;		
												
					// RENDER CONTENT
					$body = CMS::getLexicons('emails.user_registration');
					$body = str_replace(':login', $user_data['username'], $body);			
					$page = CMS::getDocuments(1,null,null,$this->lang_id);						
					$link = $this->base_url.$page[0]['full_alias']."?code=".$code;
					$body = str_replace(':link', $link, $body);		
					
					// SEND MAIL
					$this->email = Model::factory('manager_emails');
					$mail_data = array(
						'from_email' => $mail_from,
						'from_name' => $mail_from_name,
						'to_email' => $mail_to,
						'subject' => CMS::getLexicons('emails.user_registration_title'),
						'body' => $body,
						'body_type' => 'text/html' );
					$new_mail = $this->email->add_email($mail_data);
					
					// PRO CATEGORY REQUEST
					if (!empty($user_data['pro_category_request'])) {
						// RENDER CONTENT
						$body = CMS::getLexicons('emails.pro_category_request');
						$body = str_replace(':email', $user_data['email'], $body);	
						$body = str_replace(':reg_nr', $user_data['reg_nr'], $body);
						$body = str_replace(':company', $user_data['company'], $body);
						
						// SEND MAIL
						$this->email = Model::factory('manager_emails');
						$mail_data = array(
							'from_email' => $mail_from,
							'from_name' => $mail_from_name,
							'to_email' => $mail_from,
							'subject' => CMS::getLexicons('emails.pro_category_request_title'),
							'body' => $body,
							'body_type' => 'text/html' );
						$new_mail = $this->email->add_email($mail_data);
					}
					
					$this->email->send_all_emails();
				}
				
				// CHECK ACTIVATION
				if ($user_data['status_id'] == '10') {
					if ($this->user->login(null, null, FALSE, 'site', array('user_id' => $data['user_id']))) {
						// RELOAD
						$data['status'] = "2";
					}
				}
			}
		}

		return $data;
	}

	// REGISTER USER
	public function user_save($user_id, $user_data) {
		$data['status'] = "0";
		$data['error'] = "";
		$data['response'] = '';		
		
		$user_data['password'] = isset($user_data['password'])?$user_data['password']:'';
		$user_data['password2'] = isset($user_data['password2'])?$user_data['password2']:'';
		$user_data['email'] = isset($user_data['email'])?$user_data['email']:'';
		$user_data['username'] = isset($user_data['username'])?$user_data['username']:'';
		$user_data['status_id'] = isset($user_data['status_id'])?$user_data['status_id']:'1';
		$user_data['company'] = isset($user_data['company'])?$user_data['company']:'';
		$user_data['reg_nr'] = isset($user_data['reg_nr'])?$user_data['reg_nr']:'';
		$user_data['vat_nr'] = isset($user_data['vat_nr'])?$user_data['vat_nr']:'';
		$user_data['pro_category_request'] = !empty($user_data['pro_category_request'])?1:0;
		
		
		// ERRORS
		if (empty($user_data['first_name'])) $data['error'] .= CMS::getLexicons('user_registration.error_first_name').'<br/>';
		if (empty($user_data['last_name'])) $data['error'] .= CMS::getLexicons('user_registration.error_last_name').'<br/>';
		if (!empty($user_data['company']) && empty($user_data['reg_nr']) && empty($user_data['vat_nr'])) $data['error'] .= CMS::getLexicons('user_registration.error_company_reg_vat').'<br/>';
		if (empty($user_data['email']) || !Valid::email($user_data['email'])) $data['error'] .= CMS::getLexicons('user_registration.error_email').'<br/>';
		if (empty($user_data['phone']) || !Valid::phone($user_data['phone'])) $data['error'] .= CMS::getLexicons('user_registration.error_phone').'<br/>';
		if (!empty($user_data['vat_nr']) && !preg_match('/^[a-zA-Z][a-zA-Z][0-9]+$/i', trim($user_data['vat_nr']))) $data['error'] .= CMS::getLexicons('user_registration.error_vat_nr').'<br/>';
		
		if (!empty($user_data['password']) || !empty($user_data['password2'])) {
			if ($user_data['password'] != $user_data['password2']) $data['error'] .= CMS::getLexicons('user_registration.error_password_not_match').'<br/>';
			elseif (strlen($user_data['password']) < 5) $data['error'] .= CMS::getLexicons('user_registration.error_password_too_short').'<br/>';
		}
		 
		
		// SAVE USER DATA	
		if (empty($data['error'])) {
			$save_data = array(
				'first_name' => $user_data['first_name'],
				'last_name' => $user_data['last_name'],
				'email' => $user_data['email'],
				'phone' => $user_data['phone'],
				'address' => $user_data['address'],
				'company' => isset($user_data['company'])?$user_data['company']:'',
				'reg_nr' => isset($user_data['reg_nr'])?$user_data['reg_nr']:null,
				'vat_nr' => isset($user_data['vat_nr'])?$user_data['vat_nr']:null);
			$this->db->update('users')
				->set($save_data)
				->where('users.id', '=', $user_id)
				->execute();
				
			// SAVE PASSWORD
			if (!empty($user_data['password']) || !empty($user_data['password2'])) {
				$this->user->set_password($user_id, $user_data['password']);
			}
				
			$data['status'] = '1';
			$data['response'] = __('user_login.profile_saved');
		}

		return $data;
	}

	// USER ACTIVATION
	public function activate($code) {
		$user = $this->user->userData(null, array('activation_code' => $code));
		
		if (count($user) > 0) {
			if ($user[0]['status_id'] < 10) {

				// ACTIVATING USER	
				if ($this->user->activate($user[0]['id'])) {
					return $user[0];
				} else {
					return false;
				}
			} elseif ($user[0]['status_id'] == 15) {
				// FORGOT PASSWORD
				return $user[0];
			} else {
				return false;
			}
		} 
		
		return false;
	}
	
	// FORGOT PASSWORD
	public function forgot_password($email) {
		$user_data = $this->user->generate_forgot_password($email);
		
		if ($user_data) {				
			// SEND EMAIL
			$mail_to = $user_data['email'];
			$mail_from = CMS::getSettings('default.email');
			$mail_from_name = CMS::getSettings('default.site_name');
			if (Valid::email($mail_to) AND Valid::email($mail_from)) {							
				$code = isset($user_data['activation_code'])?$user_data['activation_code']:null;		
											
				// RENDER CONTENT
				$body = CMS::getLexicons('emails.forgot_password');
				
				$link_page = CMS::getDocuments(1,null,null,$this->lang_id);		
				if (isset($link_page[0]['full_alias']))	$link = $this->base_url.$link_page[0]['full_alias']."?code=".$code;
				else $link = $this->base_url."?code=".$code;
				$body = str_replace(':link', $link, $body);		
				
				// SEND MAIL
				$this->email = Model::factory('manager_emails');
				$mail_data = array(
					'from_email' => $mail_from,
					'from_name' => $mail_from_name,
					'to_email' => $mail_to,
					'subject' => CMS::getLexicons('emails.forgot_password_title'),
					'body' => $body,
					'body_type' => 'text/html');
				$new_mail = $this->email->add_email($mail_data);
				$this->email->send_all_emails();
			}
		}
	}
}