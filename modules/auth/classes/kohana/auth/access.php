<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Auth_Access extends Auth {
	private $errors = array();
	private $data = array();
	public $roles = array();
	

	public function __construct($config = array()) {
		parent::__construct($config);
		
		// DEFINE
		$this->db = new DB;
		$this->session = Session::instance();
	}

	// 
	// LOGIN
	//
	public function _login($username, $password, $remember = false, $role = 'manager', $oauth_data = array())
	{		
		// DELETE ALL SAVED ROLES
		foreach ($this->roles as $key => $val) unset($this->roles[$key]);
				
		// CREATE HASHED PASSWORD
		if (is_string($password)) $password = $this->hash($password);
		
		$auth_filter = "	(	(	users.username = :username AND
									IFNULL(users.username,'') != '' ) OR
								(	users.email = :username AND
									IFNULL(users.email,'') != '' ) ) AND
							users.password = :password ";
		// OAUTH METHODS
		if (!empty($oauth_data['facebook_id'])) $auth_filter = " users.facebook_id = :facebook_id ";
		if (!empty($oauth_data['google_id'])) $auth_filter = " users.google_id = :google_id ";
		if (!empty($oauth_data['twitter_id'])) $auth_filter = " users.twitter_id = :twitter_id ";
		if (!empty($oauth_data['draugiem_id'])) $auth_filter = " users.draugiem_id = :draugiem_id ";
		
		// FORCE LOGIN
		if (!empty($oauth_data['user_id'])) $auth_filter = " users.id = :user_id ";
				
		// CHECK USER
		$sql = "SELECT
					users.id						AS id,
					users.status_id					AS status_id,
					user_roles.id					AS user_role_id,
					roles.parent_id					AS role_parent_id,
					parent_user_roles.id			AS parent_user_role_id	
				FROM
					users
					LEFT JOIN roles ON
						roles.name = :role
					LEFT JOIN user_roles ON
						user_roles.user_id = users.id AND
						user_roles.role_id = roles.id AND
						user_roles.status_id >= 10
					LEFT JOIN roles parent_roles ON
						roles.parent_id = parent_roles.id
					LEFT JOIN user_roles parent_user_roles ON
						parent_user_roles.role_id = parent_roles.id AND
						parent_user_roles.user_id = users.id AND
						parent_user_roles.status_id >= 10
				WHERE
					".$auth_filter." ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':role', $role);
		$res->bind(':username', $username);
		$res->bind(':password', $password);
		
		// OAUTH METHODS
		if (!empty($oauth_data['facebook_id'])) $res->bind(':facebook_id', $oauth_data['facebook_id']);
		if (!empty($oauth_data['google_id'])) $res->bind(':google_id', $oauth_data['google_id']);
		if (!empty($oauth_data['twitter_id'])) $res->bind(':twitter_id', $oauth_data['twitter_id']);
		if (!empty($oauth_data['draugiem_id'])) $res->bind(':draugiem_id', $oauth_data['draugiem_id']);
		
		// FORCE LOGIN
		if (!empty($oauth_data['user_id'])) $res->bind(':user_id', $oauth_data['user_id']);
		
		$usr = $res->execute()->as_array();
		
		if (count($usr) == 0) {
			// WRONG USER
			if (empty($this->errors)) $this->errors[] = CMS::getLexicons('user_login.error_wrong_user');
			return false;
		} else {
			if ($usr[0]['status_id'] < 10) {
				// INACTIVE
				$this->errors[] = CMS::getLexicons('user_login.error_inactive');
				return false;
			}
			if (empty($usr[0]['user_role_id']) OR (!empty($usr[0]['role_parent_id']) AND empty($usr[0]['parent_user_role_id']))) {
				// NO RIGHTS
				$this->errors[] = CMS::getLexicons('user_login.error_no_rights');
				return false;
			}
			
			// FORGOR PASSWORD
			if ($usr[0]['status_id'] == 15) {
				$sql = "UPDATE
							users
						SET
							status_id = 10, 
							activation_code = ''
						WHERE
							users.id = :user_id ";
				$res = $this->db->query(Database::UPDATE, $sql);
				$res->bind(':user_id', $usr[0]['id']);
				$tmp = $res->execute();				
			}
				
			// USER STATISTIC
			
			$last_login_method = 'username';
			if (!empty($oauth_data['user_id'])) $last_login_method = 'force login';
			elseif (!empty($oauth_data['draugiem_id'])) $last_login_method = 'draugiem';
			elseif (!empty($oauth_data['twitter_id'])) $last_login_method = 'twitter';
			elseif (!empty($oauth_data['google_id'])) $last_login_method = 'google';
			elseif (!empty($oauth_data['facebook_id'])) $last_login_method = 'facebook';
			
			
			$sql = "UPDATE
						users
					SET
						last_login = NOW(), 
						last_login_method = :last_login_method,
						num_logins = IFNULL(num_logins,0) + 1, 
						last_ip = :client_id 
					WHERE
						users.id = :user_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':client_id', Request::$client_ip);
			$res->bind(':user_id', $usr[0]['id']);
			$res->bind(':last_login_method', $last_login_method);
			$tmp = $res->execute();	
			
			// UPDATE SESSION AND LOGOUT OTHER USERS
			$session_id = $this->session->id();
			$sql = "UPDATE 
						sessions
					SET
						user_id = :user_id,
						ip_address = :ip_address
					WHERE
						sessions.session_id = :session_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':ip_address', Request::$client_ip);
			$res->bind(':user_id', $usr[0]['id']);
			$res->bind(':session_id', $session_id);
			$tmp = $res->execute();
			
			// REMOVE OTHER LOGINS
			$sql = "DELETE FROM 
						sessions
					WHERE
						sessions.session_id != :session_id AND
						sessions.user_id = :user_id ";
			$res = $this->db->query(Database::DELETE, $sql);
			$res->bind(':user_id', $usr[0]['id']);
			$res->bind(':session_id', $session_id);
			$tmp = $res->execute();
			
			
			// SESSION
			$this->session->set('user_id',$usr[0]['id']);
			
			// Complete the login
			$status = $this->complete_login($username);
			
			// CREATE ORDER
			$cart_product_id = $this->session->get('cart_product_id');
			if (!empty($cart_product_id)) {
				$this->session->delete('cart_product_id');
				$orders_class = Model::factory('manager_orders_orders');
				$orders_class->add_to_order(array('product_id' => $cart_product_id));
			}
			
			return $status;
		}

		// Login failed
		return false;
	}

	//
	// LOGOUT
	//
	public function logout($destroy = FALSE, $logout_all = FALSE) {
		$this->session->delete('user_id');			
		
		// PARENT LOGOUT
		parent::logout($destroy, $logout_all);
	}

	//
	// CHECK ROLE
	//
	public function logged_in($role = null, $data_id = null)
	{
		if (!empty($role)) {
			if (!is_array($role) && isset($this->roles[$role.$data_id])) return $this->roles[$role.$data_id];
			else {
				$user_id = Session::instance()->get('user_id');
				if (!is_array($role)) $role = array($role);
				
				// DATA
				$data_filter = " ";
				if (!is_null($data_id)) $data_filter = " AND user_roles.data_id = :data_id ";
				
				$sql = "SELECT
							user_roles.id				AS user_role_id,
							roles.parent_id				AS role_parent_id,
							parent_user_roles.id		AS parent_user_role_id		
						FROM
							users
							JOIN roles ON
								roles.name IN :roles_list
							JOIN user_roles ON
								user_roles.role_id = roles.id AND
								user_roles.user_id = users.id AND
								user_roles.status_id >= 10
								".$data_filter."
							LEFT JOIN roles parent_roles ON
								roles.parent_id = parent_roles.id
							LEFT JOIN user_roles parent_user_roles ON
								parent_user_roles.role_id = parent_roles.id AND
								parent_user_roles.user_id = users.id AND
								parent_user_roles.status_id >= 10
						WHERE	
							users.status_id >= 10 AND			
							user_roles.user_id = :user_id ";
				$result = $this->db->query(Database::SELECT, $sql);
				$result->bind(':user_id', $user_id);
				$result->bind(':roles_list', $role);
				
				$result->bind(':data_id', $data_id);
			
				$roles = $result->execute()->as_array();
							
				// Make sure all the roles are valid ones
				if (count($roles) == 0 OR empty($roles[0]['user_role_id']) OR (!empty($roles[0]['role_parent_id']) AND empty($roles[0]['parent_user_role_id']))) {
					if (count($role) == 1) $this->roles[$role[0].$data_id] = false;
					return false;
				} else {
					if (count($role) == 1) $this->roles[$role[0].$data_id] = true;
					return true;
				}
			}
		} 
		
		return false;
	}
	
	//
	// USER REGISTRATION
	//
	public function register($user_data = array(), $check_errors = true) {
		
		$username = isset($user_data['username'])?trim($user_data['username']):null;
		$email = isset($user_data['email'])?trim($user_data['email']):null;
		$email_status_id = isset($user_data['email_status_id'])?$user_data['email_status_id']:'1';
		$password = isset($user_data['password'])?$user_data['password']:null;
		$password2 = isset($user_data['password2'])?$user_data['password2']:null;
		$phone = isset($user_data['phone'])?$user_data['phone']:null;
		$reg_nr = isset($user_data['reg_nr'])?$user_data['reg_nr']:null;
		$address = isset($user_data['address'])?$user_data['address']:null;
		$first_name = isset($user_data['first_name'])?trim($user_data['first_name']):'';
		$last_name = isset($user_data['last_name'])?trim($user_data['last_name']):'';
		$status_id = isset($user_data['status_id'])?$user_data['status_id']:'1';
		
		$company = isset($user_data['company'])?$user_data['company']:'';
		$vat_nr = isset($user_data['vat_nr'])?$user_data['vat_nr']:'';
		$pro_category_request = !empty($user_data['pro_category_request'])?1:0;
		
		$facebook_id = isset($user_data['facebook_id'])?$user_data['facebook_id']:null;
		$google_id = isset($user_data['google_id'])?$user_data['google_id']:null;
		$twitter_id = isset($user_data['twitter_id'])?$user_data['twitter_id']:null;
		$draugiem_id = isset($user_data['draugiem_id'])?$user_data['draugiem_id']:null;
		
		if ($check_errors) {
			// CHECK USER AND PASSWORD
			//if (empty($username)) $this->errors[] = CMS::getLexicons('user_registration.error_login');
			if (empty($password)) $this->errors[] = CMS::getLexicons('user_registration.error_password');
			elseif (!is_null($password2) AND $password != $password2) $this->errors[] = CMS::getLexicons('user_registration.error_password_not_match');
			elseif (strlen($password) < 5) $this->errors[] = CMS::getLexicons('user_registration.error_password_too_short');
			if (empty($email) OR !Valid::email($email)) $this->errors[] = CMS::getLexicons('user_registration.error_email'); 
			if (isset($user_data['captcha']) AND !Captcha::valid($user_data['captcha'])) $this->errors[] = CMS::getLexicons('user_registration.error_captcha'); 
		}
		
		// CHECK USER EXIST
		$sql = "SELECT
					users.id			AS id,
					users.username		AS username,
					users.email			AS email
				FROM
					users
				WHERE
					(	users.username = :username AND
						IFNULL(users.username,'') != '' ) OR
					(	users.email = :email AND
						IFNULL(users.email,'') != '' ) ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':username', $username);
		$result->bind(':email',$email);
		$users = $result->execute()->as_array();
		
		if ((count($users) > 0 OR count($this->errors) > 0)) {
			if (count($users) > 0 AND !empty($username) AND $username == $users[0]['username'] ) $this->errors[] = CMS::getLexicons('user_registration.error_user_exist');
			if (count($users) > 0 AND !empty($email) AND $email == $users[0]['email'] ) $this->errors[] = CMS::getLexicons('user_registration.error_email_exist');
			return false;
		} else {
			$password = !empty($password)?$this->hash($password):'';	
			
			$sql = "INSERT INTO users (
						first_name, 
						last_name,
						username,
						email,
						email_status_id,
						phone,
						reg_nr,
						address,
						password,
						datetime,
						creation_datetime,
						num_logins,
						status_id,
						activation_code,
						
						company,
						vat_nr,
						pro_category_request,
						
						facebook_id,
						google_id,
						twitter_id,
						draugiem_id )
					VALUES (
						:first_name,
						:last_name,
						:username,
						:email,
						:email_status_id,
						:phone,
						:reg_nr,
						:address,
						:password,
						NOW(),
						NOW(),
						0,
						:status_id,
						md5(CONCAT(NOW(),:username,:email)),
						
						:company,
						:vat_nr,
						:pro_category_request,
						
						:facebook_id,
						:google_id,
						:twitter_id,
						:draugiem_id )";
			$result = $this->db->query(Database::INSERT, $sql);
			$result->bind(':first_name',$first_name);
			$result->bind(':last_name',$last_name);
			$result->bind(':username',$username);
			$result->bind(':email',$email);
			$result->bind(':email_status_id',$email_status_id);
			$result->bind(':phone',$phone);
			$result->bind(':reg_nr',$reg_nr);
			$result->bind(':address',$address);
			$result->bind(':password',$password);
			$result->bind(':status_id',$status_id);
			
			$result->bind(':company',$company);
			$result->bind(':vat_nr',$vat_nr);
			$result->bind(':pro_category_request',$pro_category_request);
			
			$result->bind(':facebook_id',$facebook_id);
			$result->bind(':google_id',$google_id);
			$result->bind(':twitter_id',$twitter_id);
			$result->bind(':draugiem_id',$draugiem_id);
			
			$new_user = $result->execute();
			
			return $new_user[0];
		}
	}
	public function user_update($user_id, $data) {
		$return_data = array(	'status' => '0',
								'error' => '',
								'response' => '');
								
		// USER DATA
		$user_data = $this->userData($user_id);
		
		// CHECK
		if (isset($data['username']) AND empty($data['username'])) $return_data['error'][] .= CMS::getLexicons('user_registration.error_login');
		if (isset($data['password'])) {
			if (empty($data['password'])) $return_data['error'][] = CMS::getLexicons('user_registration.error_password');
			elseif (!is_null($data['password2']) AND $data['password'] != $data['password2']) $return_data['error'][] = CMS::getLexicons('user_registration.error_password_not_match');
			elseif (strlen($password) < 5) $return_data['error'][] = CMS::getLexicons('user_registration.error_password_too_short');
			else unset($data['password2']);
		}
		if (isset($data['email']) AND (empty($data['email']) OR !Valid::email($data['email']))) $return_data['error'][] = CMS::getLexicons('user_registration.error_email'); 
		
		if (empty($return_data['error'])) {
			if (isset($data['email'])) {
				if ($user_data[0]['email'] != $data['email']) {
					$data['email_status_id'] = '1';
					$data['activation_code'] = md5(time().$data['email']);
				}
			}
			
			// UPDATE USER
			$data['datetime'] = CMS::date(time());
			$db_data = DB::update('users')
				->set($data)
				->where('id', '=', $user_id)
				->execute();
			
			$return_data['status'] = '1';
			return $return_data;
		} else {
			$return_data['error'] = implode('<br/>', $return_data['error']);
			return $return_data;
		} 
	}
	
	public function status($user_id, $new_status_id) {
		$sql = "UPDATE
					users
				SET
					status_id = :status_id
				WHERE
					users.id = :user_id ";
		$result = $this->db->query(Database::UPDATE, $sql);
		$result->bind(':status_id', $new_status_id);
		$result->bind(':user_id', $user_id);
		$db_data = $result->execute();
					
		return true; 	
	}
	public function activate($user_id) {
		$sql = "UPDATE
					users
				SET
					status_id = 10,
					email_status_id = 10,
					activation_code = '',
					datetime = NOW()
				WHERE
					users.id = :user_id ";
		$result = $this->db->query(Database::UPDATE, $sql);
		$result->bind(':user_id', $user_id);
		$db_data = $result->execute();
		
		return true; 	
	}
	/*
	public function user_delete($user_id) {
		// DELETE ROLES
		$res = $this->user_role_delete_all($user_id);
		
		// DELETE USER
		$sql = "DELETE FROM
					pro_users
				WHERE
					pro_users.id ? ";
		return $this->db->query($sql, array($user_id) ); 	
	} */
	
	public function change_password($user_id, $old_password, $new_password, $new_password2) {
		$result = array(	'status' => '0',
							'error' => array(),
							'response' => '' );
		
		if (is_string($old_password)) $old_password = $this->hash($old_password);
		
		// CHECK OLD PASSWORD
		$sql = "SELECT 
					*
				FROM 
					users
				WHERE
					users.id = :user_id AND
					users.password = :password ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':user_id', $user_id);
		$res->bind(':password', $old_password);
		$db_data = $res->execute()->as_array();
		if (count($db_data) == 0) $result['error'][] = CMS::getLexicons('user_registration.error_password');
		if (empty($new_password) OR $new_password != $new_password2) $result['error'][] = CMS::getLexicons('user_registration.error_password_not_match');
		elseif (strlen($new_password) < 5) $result['error'][] = CMS::getLexicons('user_registration.error_password_too_short');
		
		$user_data = $this->userData($user_id);
		if (empty($result['error']) AND !empty($user_data[0]['username']) AND !empty($user_data[0]['password'])) {
			if (is_string($new_password)) $new_password = $this->hash($new_password);
			
			// CHANGE PASSWORD
			$sql = "UPDATE users
					SET password = :password
					WHERE users.id = :user_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':user_id', $user_id);
			$res->bind(':password', $new_password);
			$db_data = $res->execute();
			
			$result['status'] = '1';
			$result['response'] .= CMS::getLexicons('user_registration.password_changed');		
		} 
		$result['error'] = implode('<br/>', $result['error']);
		
		return $result;		
	}
	public function set_username_password($user_id, $username, $new_password, $new_password2) {
		$result = array(	'status' => '0',
							'error' => null,
							'response' => '' );
		
		// CHECK USER EXIST
		$sql = "SELECT users.*
				FROM users
				WHERE users.username = :username ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':username', $username);
		$users = $res->execute()->as_array();		
		if (count($users) > 0 OR empty($username)) $result['error'][] = CMS::getLexicons('user_registration.error_user_exist');
		if (empty($new_password) OR $new_password != $new_password2) $result['error'][] = CMS::getLexicons('user_registration.error_password_not_match');
		elseif (strlen($new_password) < 5) $result['error'][] = CMS::getLexicons('user_registration.error_password_too_short');
		
		$user_data = $this->userData($user_id);
		if (empty($result['error']) AND (empty($user_data[0]['username']) OR empty($user_data[0]['password']))) {
			if (is_string($new_password)) $new_password = $this->hash($new_password);
			
			// CHANGE PASSWORD
			$sql = "UPDATE 
						users
					SET 
						password = :password,
						username = :username
					WHERE 
						users.id = :user_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':user_id', $user_id);
			$res->bind(':password', $new_password);
			$res->bind(':username', $username);
			$db_data = $res->execute();
			
			$result['status'] = '1';
			$result['response'] .= CMS::getLexicons('my_account.change_password');		
		} 
		
		if (count($result['error']) > 0) $result['error'] = implode('<br/>', $result['error']);
		
		return $result;		
	}
	public function set_password($user_id, $new_password) {
		// CHANGE PASSWORD
		$sql = "UPDATE users
				SET password = :password
				WHERE users.id = :user_id ";
		$res = $this->db->query(Database::UPDATE, $sql);
		$res->bind(':user_id', $user_id);
		if (is_string($new_password)) $new_password = $this->hash($new_password);
		$res->bind(':password', $new_password);
		$db_data = $res->execute();
		
		$result['status'] = '1';
		$result['error'] = '';
		$result['response'] = CMS::getLexicons('user_registration.password_set');
		
		return $result;	
	}
	public function generate_forgot_password($username_email = null) {		
		$sql = "SELECT
					users.*
				FROM
					users
				WHERE
					users.status_id >= 10 AND
					(	(	users.username = :username_email AND
							IFNULL(users.username,'') != '' ) OR
						(	users.email = :username_email ) ) ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':username_email', $username_email);	
		$user_data = $res->execute()->as_array();
		
		if (count($user_data) > 0) {			
			// SET ACTIVATION CODE
			$sql = "UPDATE 
						users
					SET 
						status_id = 15,
						activation_code = :activation_code
					WHERE 
						users.id = :user_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':user_id', $user_data[0]['id']);
			$activation_code = md5($user_data[0]['username'].$user_data[0]['email'].$user_data[0]['password'].time());
			$res->bind(':activation_code', $activation_code);
			$db_data = $res->execute();	
			
			$user_data[0]['activation_code'] = $activation_code;
		} 
		
		return empty($user_data[0]['id'])?false:$user_data[0];	
	}
		
	//
	// USER ROLES
	//	
	public function add_role($user_id, $role, $data_id = null, $status_id = '10') {
		$sql = "SELECT
					user_roles.id
				FROM
					user_roles
					JOIN roles ON
						roles.name = :role
				WHERE
					user_roles.user_id = :user_id AND
					user_roles.role_id = roles.id AND
					IFNULL(user_roles.data_id,0) = IFNULL(:data_id,0) ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':role', $role);
		$result->bind(':user_id', $user_id);
		$result->bind(':data_id', $data_id);
		$roles = $result->execute()->as_array();
		
		if (count($roles) == 0) {
			$sql = "INSERT INTO user_roles (
				 		user_id, 
				 		role_id,
				 		data_id,
				 		status_id ) 
				 	SELECT
				 		:user_id, 
				 		roles.id,
				 		:data_id,
				 		:status_id
					FROM
						roles
					WHERE
						roles.name = :role ";
			$result = $this->db->query(Database::INSERT, $sql);
			$result->bind(':role', $role);
			$result->bind(':user_id', $user_id);
			$result->bind(':data_id', $data_id);
			$result->bind(':status_id', $status_id);
			$role_data = $result->execute();
			
			return $role_data[0];
		} else {
			$this->site_errors[] = CMS::getLexicons('user_registrations.error_role_exist');
			return false;
		}
	}
	public function get_role_data($role, $from_status_id = '10') {
		$user_id = Session::instance()->get('user_id');
		
		$sql = "SELECT
					user_roles.data_id			AS data_id
				FROM
					user_roles
					JOIN roles ON
						user_roles.role_id = roles.id						
				WHERE
					user_roles.status_id >= :status_id AND
					user_roles.user_id = :user_id AND
					roles.name = :role 
				ORDER BY
					user_roles.data_id DESC";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':role', $role);
		$result->bind(':status_id', $from_status_id);
		$result->bind(':user_id', $user_id);
		
		return $result->execute()->as_array();		
	}
	/*
	public function user_role_status($user_id, $role, $data_id, $new_status_id) {
		$sql = "UPDATE
					pro_user_roles
				SET
					status_id = ?
				WHERE
					pro_user_roles.id IN (	SELECT
												pro_user_roles.id 
											FROM
												pro_user_roles
												JOIN pro_roles ON
													pro_roles.name = ?
											WHERE
												pro_user_roles.user_id = ? AND
												IFNULL(pro_user_roles.data_id,0) = IFNULL(?,0) AND
												pro_user_roles.role_id = pro_roles.id ) ";
		return $this->db->query($sql, array($new_status_id, $role, $user_id, $data_id) ); 	
	}
	public function user_role_delete($user_id, $role) {
		$sql = "DELETE FROM
					pro_user_roles
				WHERE
					pro_user_roles.id IN (	SELECT
												pro_user_roles.id 
											FROM
												pro_user_roles
												JOIN pro_roles ON
													pro_roles.name = ?
											WHERE
												pro_user_roles.user_id = ? AND
												pro_user_roles.role_id = pro_roles.id ) ";
		return $this->db->query($sql, array($role, $user_id) ); 	
	}
	public function user_role_delete_all($user_id) {
		$sql = "DELETE FROM
					pro_user_roles
				WHERE
					pro_user_roles.user_id = ? ";
		return $this->db->query($sql, array($user_id) ); 	
	}
	*/
	
	
	//
	// GET ERRORS
	//
	public function get_errors() {
		if (count($this->errors) == 0) return false;
		else return $this->errors;
	}
	
	//
	// USER DATA
	//
	public function userData($id = null, $filter_data = array()) {		
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND users.id = :id ";
		
		if (isset($filter_data['username'])) $filter .= " AND users.username = :username ";
		if (isset($filter_data['email'])) $filter .= " AND users.email = :email ";
		if (isset($filter_data['activation_code'])) $filter .= " AND users.activation_code = :activation_code ";
		
		if (isset($filter_data['facebook_id'])) $filter .= " AND users.facebook_id = :facebook_id ";
		if (isset($filter_data['google_id'])) $filter .= " AND users.google_id = :google_id ";
		if (isset($filter_data['twitter_id'])) $filter .= " AND users.twitter_id = :twitter_id ";
		if (isset($filter_data['draugiem_id'])) $filter .= " AND users.draugiem_id = :draugiem_id ";
		
		if ($filter == " ") $filter = " AND 1 = 0 ";
		
		$sql = "SELECT
					users.*,	
					CONCAT(users.first_name,' ',users.last_name)	AS full_name,
					status.name										AS status_name,
					email_status.name								AS email_status_name
				FROM
					users
					LEFT JOIN status ON
						status.table_status_name = 'users_status_id' AND
						status.status_id = users.status_id
					LEFT JOIN status email_status ON
						email_status.table_status_name = 'users_email_status_id' AND
						email_status.status_id = users.email_status_id
				WHERE
					users.status_id > 0
					".$filter."
				ORDER BY
					CONCAT(users.first_name,' ',users.last_name) ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		
		if (isset($filter_data['username'])) $result->bind(':username', $filter_data['username']);
		if (isset($filter_data['email'])) $result->bind(':email', $filter_data['email']);
		if (isset($filter_data['activation_code'])) $result->bind(':activation_code', $filter_data['activation_code']);	
			
		if (isset($filter_data['facebook_id'])) $result->bind(':facebook_id', $filter_data['facebook_id']);
		if (isset($filter_data['google_id'])) $result->bind(':google_id', $filter_data['google_id']);
		if (isset($filter_data['twitter_id'])) $result->bind(':twitter_id', $filter_data['twitter_id']);
		if (isset($filter_data['draugiem_id'])) $result->bind(':draugiem_id', $filter_data['draugiem_id']);
		$user = $result->execute()->as_array();
		
		return $user;
	} 
	
	/*
	 * GET TOTAL OF USERS
	 */
	public function getUsersTotal($roles = array('site', 'admin', 'manager')) {
		$db_data = $this->db->select(
				array('COUNT(DISTINCT "users.id")', 'total'),
				array('COUNT(DISTINCT IF("users.status_id" >= 10, "users.id", NULL))', 'active'),
				array('COUNT(DISTINCT IF("users.status_id" < 10, "users.id", NULL))', 'inactive'))
			->from('users')
			->join('user_roles')
				->on('users.id', '=', 'user_roles.user_id')
			->join('roles')
				->on('user_roles.role_id', '=', 'roles.id')
			->where('user_roles.status_id', '>=', '10')
			->where('roles.name', 'IN', $roles)
			->execute()
			->as_array();
			
		return $db_data[0];
	} 

	/*
	 * MERGE USERS
	 */
	public function merge_users($to_user_id, $from_user_id, $backup_user = false) {
		if (!empty($from_user_id) AND !empty($to_user_id)) {
			if ($backup_user) {
				//
				// BACKUP USER
				//
				$db_data = DB::select('*')
						->from('users')
						->where('id', '=', $from_user_id)
						->execute()
						->as_array();
				$db_data[0]['linked_user_id'] = $to_user_id;
				$db_data = DB::insert('users_linked', array_keys($db_data[0]))
						->values(array_values($db_data[0]))
						->execute();
			}
			
			//
			// UPDATE USER ROLES
			//
			$sql = "SELECT
						*
					FROM
						user_roles
					WHERE
						user_roles.user_id = :from_user_id AND
						user_roles.id NOT IN (	SELECT
													from_user_roles.id
												FROM
													user_roles from_user_roles,
													user_roles to_user_roles
												WHERE
													from_user_roles.user_id = :from_user_id AND
													to_user_roles.user_id = :to_user_id AND
													IFNULL(from_user_roles.role_id,0) = IFNULL(to_user_roles.role_id,0) AND
													IFNULL(from_user_roles.data_id,0) = IFNULL(to_user_roles.data_id,0) ) ";
			$res = $this->db->query(Database::SELECT, $sql);
			$res->bind(':from_user_id', $from_user_id);
			$res->bind(':to_user_id', $to_user_id);
			$roles_data = $res->execute()->as_array();
			
			for($i=0; $i<count($roles_data); $i++) {
				$insert_data = array(
					'user_id' => $to_user_id,
					'role_id' => $roles_data[$i]['role_id'],
					'data_id' => $roles_data[$i]['data_id'],
					'status_id' => $roles_data[$i]['status_id'] );
					
				$db_data = DB::insert('user_roles', array_keys($insert_data))
				 				->values(array_values($insert_data))
				 				->execute();
			} 
			
				
			//	
			// UPDATE USER DATA
			//
			$from_user = $this->userData($from_user_id);
			$to_user = $this->userData($to_user_id);
			
			$update_array = array();
			if (empty($to_user[0]['first_name']) AND !empty($from_user[0]['first_name'])) $update_array['first_name'] = $from_user[0]['first_name'];		
			if (empty($to_user[0]['last_name']) AND !empty($from_user[0]['last_name'])) $update_array['last_name'] = $from_user[0]['last_name'];
			if (empty($to_user[0]['city']) AND !empty($from_user[0]['city'])) $update_array['city'] = $from_user[0]['city'];
			if (empty($to_user[0]['country_id']) AND !empty($from_user[0]['country_id'])) $update_array['country_id'] = $from_user[0]['country_id'];
			if (empty($to_user[0]['currency_id']) AND !empty($from_user[0]['currency_id'])) $update_array['currency_id'] = $from_user[0]['currency_id'];
			if (empty($to_user[0]['phone']) AND !empty($from_user[0]['phone'])) $update_array['phone'] = $from_user[0]['phone'];
			if (empty($to_user[0]['skype']) AND !empty($from_user[0]['skype'])) $update_array['skype'] = $from_user[0]['skype'];
			if (empty($to_user[0]['msn']) AND !empty($from_user[0]['msn'])) $update_array['msn'] = $from_user[0]['msn'];
			if (empty($to_user[0]['about_me']) AND !empty($from_user[0]['about_me'])) $update_array['about_me'] = $from_user[0]['about_me'];
			if ((empty($to_user[0]['image_src']) OR $to_user[0]['image_src'] == 'assets/plugins/my_account/img/default_profile_image.gif') AND !empty($from_user[0]['image_src']) AND $from_user[0]['image_src'] != 'assets/plugins/my_account/img/default_profile_image.gif') {
				$this->files = Model::factory('Manager_files');
				$update_array['image_src'] = $this->files->copyFile($from_user[0]['image_src'], 'files/user_profiles/'.$to_user_id.'/');			
			}
			if (empty($to_user[0]['facebook_id']) AND !empty($from_user[0]['facebook_id'])) {
				$update_array['facebook_id'] = $from_user[0]['facebook_id'];
				$db_data = DB::update('users')->set(array('facebook_id'=>''))->where('id', '=', $from_user_id)->execute();
			}
			if (empty($to_user[0]['twitter_id']) AND !empty($from_user[0]['twitter_id'])) {
				$update_array['twitter_id'] = $from_user[0]['twitter_id'];
				$db_data = DB::update('users')->set(array('twitter_id'=>''))->where('id', '=', $from_user_id)->execute();
			}
			if (empty($to_user[0]['google_id']) AND !empty($from_user[0]['google_id'])) {
				$update_array['google_id'] = $from_user[0]['google_id'];
				$db_data = DB::update('users')->set(array('google_id'=>''))->where('id', '=', $from_user_id)->execute();
			}
			if (empty($to_user[0]['druagiem_id']) AND !empty($from_user[0]['druagiem_id'])) {
				$update_array['druagiem_id'] = $from_user[0]['druagiem_id'];
				$db_data = DB::update('users')->set(array('druagiem_id'=>''))->where('id', '=', $from_user_id)->execute();
			}
			if (empty($to_user[0]['username']) AND !empty($from_user[0]['username']) AND $from_user_id > 1) {
				$update_array['username'] = $from_user[0]['username'];
				$update_array['password'] = $from_user[0]['password'];
			}
			if ($to_user[0]['screen_name_status_id'] < 10 AND $from_user[0]['screen_name_status_id'] >= 10 AND $from_user_id > 1) {
				$update_array['screen_name_status_id'] = $from_user[0]['screen_name_status_id'];
				$update_array['screen_name'] = $from_user[0]['screen_name'];
			}
			if ($to_user[0]['email_status_id'] < 10 AND $from_user[0]['email_status_id'] >= 10 AND $from_user_id > 1) {
				$update_array['email_status_id'] = $from_user[0]['email_status_id'];
				$update_array['email'] = $from_user[0]['email'];
			}
			
			$this->man_users = Model::factory('Manager_Users');
			$this->man_users->delete($from_user_id);				
			$db_data = DB::update('users')
			 		->set($update_array)
					->where('id', '=', $to_user_id)
					->execute();
					
			
			
		}
	}
	
	//
	// OAUTH DATA
	//
	public function get_facebook_data($code) {
		// CONFIG
		$config = Kohana::$config->load('oauth.facebook');
		
		// RETURN URL
		$url =  URL::base(TRUE, FALSE) . 'plugins/userlogin/facebook_auth';
		
		$token_url = "https://graph.facebook.com/oauth/access_token?"
					. "client_id=" . $config['appID'] . "&redirect_uri=" . urlencode($url)
					. "&client_secret=" . $config['appSecret'] . "&code=" . $code;
	
		$response = file_get_contents($token_url);
		$params = null;
		parse_str($response, $params);

		$graph_url = "https://graph.facebook.com/me?"
			. "access_token=" . $params['access_token'];

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
	
	
	
	
	//
	// UNUSED FUNCTIONS
	//
	public function password($username)
	{
		$sql = "SELECT users.password		AS password
				FROM users
				WHERE
					users.username = :username OR
					users.email = :username ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind('username', $username);
		$data = $res->execute()->as_array();
		
		return $data[0]['password'];
	}
	public function check_password($password)
	{
		$username = $this->get_user();
		if ($username === FALSE) return FALSE; 
		return ($password === $this->password($username));
	}

} 