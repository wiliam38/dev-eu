<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Users extends Model {	
	
	//
	// GET USERS
	//
	public function getUsers($id = null, $filter_data = array(), $limit = null, $offset = null, $order_by = 'CONCAT("users.first_name",\' \',"users.last_name") ASC') {
		$sql = $this->db->select()
			->from('users')
			->join('status', 'LEFT')
				->on('users.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr('\'users_status_id\''))
			->where('users.status_id', '>', '0');
				
		// FILTER
		if (!is_null($id)) $sql->where('users.id', '=', $id);
		if (isset($filter_data['username'])) $sql->where('users.username', '=', $filter_data['username']);
		if (!$this->user->logged_in('admin')) $sql->where('users.id', '>', '1');
		if (!empty($filter_data['search'])) {
			$search_array = explode(' ', $filter_data['search']);
			$sql->where_open();
			for ($i=0; $i<count($search_array); $i++) {
				$sql->where('CONCAT(IFNULL("users.first_name",\'\'), \' \', IFNULL("users.last_name",\'\'), \' \', IFNULL("users.email",\'\'), \' \', IFNULL("users.username",\'\'), \' \', IFNULL("users.company",\'\'), \' \', IFNULL("users.reg_nr",\'\'), \' \', IFNULL("users.vat_nr",\'\'), \' \', IFNULL("users.phone",\'\'))', 'LIKE', '%'.$search_array[$i].'%');
			}
			$sql->where_close();
		}
		if (!empty($filter_data['status_id'])) $sql->where('users.status_id', 'IN', is_array($filter_data['status_id'])?$filter_data['status_id']:array($filter_data['status_id']));
		
		if (isset($filter_data['count'])) {
			$sql->select(
					array(DB::expr('COUNT("users.id")'), 'count'));
		} else {
			// ORDERS CNT
			$ord_cnt = $this->db->select('COUNT(DISTINCT("orders.id"))')
				->from('orders')
				->where('orders.owner_user_id', '=', DB::expr('users.id'))
				->where('orders.status_id', '>=', '10');
			
			// ORDERS TOTAL
			$ord_amount = $this->db->select('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * "order_details.qty",2)) + ROUND("orders.shipping_total" * (1 + "orders.shipping_vat" / 100),2),
							SUM(ROUND("order_details.price" * "order_details.qty",2)) + ROUND("orders.shipping_total",2) )')
				->from('orders')
				->join('order_details')->on('order_details.order_id', '=', 'orders.id')
				->where('orders.owner_user_id', '=', DB::expr('users.id'))
				->where('orders.status_id', '>=', '50')
				->group_by(
					'orders.no_vat',
					'orders.shipping_total',
					'orders.shipping_vat' )
				->limit(1);
			
			$sql->select(
					array('users.id', 'id'),
					array('users.first_name', 'first_name'),
					array('users.last_name', 'last_name'),
					array('users.username', 'username'),
					array('users.password', 'password'),
					array('users.email', 'email'),
					array('users.phone', 'phone'),
					array('users.pro_category', 'pro_category'),
					array('users.pro_coffee_coef', 'pro_coffee_coef'),
					array('users.pro_machines_coef', 'pro_machines_coef'),
					array('users.pro_accessories_coef', 'pro_accessories_coef'),
					array('users.company', 'company'),
					array('users.reg_nr', 'reg_nr'),
					array('users.vat_nr', 'vat_nr'),
					array('users.status_id', 'status_id'),
					array('users.activation_code', 'activation_code'),	
					array('status.name', 'status_name'),
					array('status.description', 'status_description'),
					array('IFNULL("users.num_logins",0)', 'num_logins'),
					array('users.creation_datetime', 'creation_datetime'),
					array('users.last_login', 'last_login'),
					
					array($ord_cnt, 'orders_cnt'),
					array($ord_amount, 'orders_amount') )
				->order_by($order_by);
			if (!is_null($limit) && $limit > 0) $sql->limit($limit);
			if (!is_null($offset) && $offset > 0) $sql->offset($offset);
		}
		
		$db_data = $sql->execute()->as_array();
		if (isset($filter_data['count'])) $db_data = $db_data[0]['count'];
		
		return $db_data;
	} 

	//
	// NEW USER
	//
	public function getNewUsers() {
		$sql = $this->db->select(
				array(DB::expr('\'new\''), 'id'),
				array(DB::expr('\'\''), 'first_name'),
				array(DB::expr('\'\''), 'last_name'),
				array(DB::expr('\'\''), 'username'),
				array(DB::expr('\'\''), 'password'),
				array(DB::expr('\'\''), 'email'),
				array(DB::expr('\'\''), 'phone'),
				array(DB::expr('0'), 'pro_category'),
				array(DB::expr('1'), 'pro_coffee_coef'),
				array(DB::expr('1'), 'pro_machines_coef'),
				array(DB::expr('1'), 'pro_accessories_coef'),
				array('status.status_id', 'status_id'),
				array('status.name', 'status_name'))
			->from('status')
			->where('status.status_id', '=', '1')
			->where('status.table_status_name', '=', DB::expr('\'users_status_id\''));

		return $sql->execute()->as_array();
	}
	
	//
	// GET ROLES
	//
	public function getUserRoles($user_id) {
		// FILTER
		$filter = " ";
		if (!$this->user->logged_in('admin')) $filter .= " AND roles.id > 1";		
		
		if ($user_id == 'new') $user_id = '-1';		
		$sql = "SELECT
					user_roles.id								AS id,
					user_roles.data_id							AS data_id,
					''											AS data_title,
					user_roles.status_id						AS status_id,
					
					roles.id									AS role_id,
					roles.name									AS role_name,
					roles.description							AS role_description,
					roles.sql_select							AS role_sql_select,
					roles.sql_from								AS role_sql_from					
				FROM
					user_roles
					JOIN roles ON
						user_roles.role_id = roles.id
				WHERE
					user_roles.user_id = :user_id
					".$filter."
				ORDER BY
					roles.id,
					user_roles.data_id ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':user_id', $user_id);
		$user_roles = $result->execute()->as_array();
		
		for($i=0; $i<count($user_roles); $i++) {
			if (!empty($user_roles[$i]['role_sql_select']) AND !empty($user_roles[$i]['role_sql_from']) AND !empty($user_roles[$i]['data_id'])) {
				$sql = "SELECT ".$user_roles[$i]['role_sql_select']." AS title
						FROM ".$user_roles[$i]['role_sql_from']."	
						WHERE ".$user_roles[$i]['role_sql_from'].".id = :id ";
				$res = $this->db->query(Database::SELECT, $sql);
				$res->bind(':id', $user_roles[$i]['data_id']);
				$data_data = $res->execute()->as_array();
				if (count($data_data) > 0) $user_roles[$i]['data_title'] = $data_data[0]['title'];
			}
		}
		
		return $user_roles;
	}
	
	//
	// GET ROLES
	//
	public function getRoles($id = null, $filter_data = array()) {
		// FILTER
		$filter = ' ';	
		if (!empty($id)) $filter .= " AND roles.id = :id ";
		if (!empty($filter_data['not_in'])) $filter .= " AND roles.id NOT IN :not_in ";
		
		$sql = "SELECT
					roles.*
				FROM
					roles
				WHERE	
					1 = 1
					".$filter."
				ORDER BY
					roles.id ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':id', $id);
		
		if (!empty($filter_data['not_in'])) $res->bind(':not_in', $filter_data['not_in']);
		
		$db_data = $res->execute()->as_array();
		
		return $db_data;	
	}
	
	//
	//
	//
	// JOBS
	//
	//
	//
	
	public function save($data) {
		$ret_data = array(	'status' => 0,
							'error' => null,
							'response' => '');
		
		// CHACEK USERNAME
		if (isset($data['username'])) {
			if (empty($data['username'])) $ret_data['error'][] = 'Wrong username!';
			else {
				$users = $this->getUsers(null, array('username' => $data['username']));
				if (count($users) > 0 AND $users[0]['id'] != $data['user_id']) $ret_data['error'][] = 'Username already exists!';
			}
		}
		if ($data['password'] != $data['password2']) $ret_data['error'][] = 'Password doesn\'t match!';
		if (!empty($data['email']) && !Valid::email($data['email'])) $ret_data['error'][] = 'Wrong e-mail!';
		
		if (count($ret_data['error']) == 0) {		
			// HASH PASSW
			$passw = Auth::instance()->hash($data['password']);
			
			if ($data['user_id'] == 'new') {
				// INSERT
				$sql = "INSERT INTO users (
							first_name,
							last_name,
							username,
							password,
							email,
							phone,
							pro_category,
							pro_coffee_coef,
							pro_machines_coef,
							pro_accessories_coef,
							company,
							reg_nr,
							vat_nr,
							status_id,
							creation_datetime,
							datetime )
						VALUES (
							:first_name,
							:last_name,
							:username,
							:password,
							:email,
							:phone,
							:pro_category,
							:pro_coffee_coef,
							:pro_machines_coef,
							:pro_accessories_coef,
							:company,
							:reg_nr,
							:vat_nr,
							:status_id,
							NOW(),
							NOW() )";
				$result = $this->db->query(Database::INSERT, $sql);
			} else {
				$update_passw = " ";
				if ($data['password'] == $data['password2'] AND $data['password'] != '') 
				
					$update_passw = " , password = :password ";
				$sql = "UPDATE
							users
						SET
							first_name = :first_name,
							last_name = :last_name,
							email = :email,
							phone = :phone,
							pro_category = :pro_category,
							pro_coffee_coef = :pro_coffee_coef,
							pro_machines_coef = :pro_machines_coef,
							pro_accessories_coef = :pro_accessories_coef,
							company = :company,
							reg_nr = :reg_nr,
							vat_nr = :vat_nr,
							status_id = :status_id,
							datetime = NOW()
							".$update_passw."
						WHERE
							users.id = :user_id ";
				$result = $this->db->query(Database::UPDATE, $sql);
			}
			
			// DATA UPDATE
			$data['pro_category'] = isset($data['pro_category'])?1:0;
			
			$result->bind(':user_id', $data['user_id']);
			$result->bind(':first_name', $data['first_name']);
			$result->bind(':last_name', $data['last_name']);
			$result->bind(':username', $data['username']);
			$result->bind(':password', $passw);
			$result->bind(':email', $data['email']);		
			$result->bind(':phone', $data['phone']);	
			$result->bind(':pro_category', $data['pro_category']);	
			$result->bind(':pro_coffee_coef', $data['pro_coffee_coef']);
			$result->bind(':pro_machines_coef', $data['pro_machines_coef']);
			$result->bind(':pro_accessories_coef', $data['pro_accessories_coef']);
			$result->bind(':company', $data['company']);
			$result->bind(':reg_nr', $data['reg_nr']);
			$result->bind(':vat_nr', $data['vat_nr']);
			$result->bind(':status_id', $data['status_id']);
			
			$db_data = $result->execute();	
			$user_id = $data['user_id'];
			if ($user_id == 'new') $user_id = $db_data[0];	
			
			//
			// UPDATE ROLES
			//
			$needed_roles = array('-1');
			
			if (!empty($data['roles_user_role_id'])) {
				for($i=0; $i<count($data['roles_user_role_id']); $i++) {
					if (empty($data['roles_user_role_id'][$i]) || $data['roles_user_role_id'][$i] == 'new') {
						// INSERT
						$sql = "INSERT INTO user_roles (
									user_id,
									role_id,
									data_id,
									status_id )
								VALUES (
									:user_id,
									:role_id,
									:data_id,
									:status_id ) ";
						$result = $this->db->query(Database::INSERT, $sql);						
					} else {
						// UPDATE
						$sql = "UPDATE user_roles 
								SET	status_id = :status_id 
								WHERE user_roles.id = :user_role_id ";
						$result = $this->db->query(Database::INSERT, $sql);		
					}
                    if ($data['roles_data_id'][$i] == '') $data['roles_data_id'][$i] = 0;                    
					$result->bind(':user_role_id', $data['roles_user_role_id'][$i]);
					$result->bind(':user_id', $user_id);
					$result->bind(':role_id', $data['roles_role_id'][$i]);
					$result->bind(':data_id', $data['roles_data_id'][$i]);
					$result->bind(':status_id', $data['roles_status_id'][$i]);
					$db_data = $result->execute();
								
					$user_role_id = $data['roles_user_role_id'][$i];
					if (empty($user_role_id) OR $user_role_id == 'new') $user_role_id = $db_data[0];			
						
					$needed_roles[] = $user_role_id;
				}
			}
			
			// DELETE ROLES
			$roles_list = array();
			if (!$this->user->logged_in('admin')) $roles_list[] = '1';
			if ($data['user_id']==1) { $roles_list[] = '1'; $roles_list[] = '2'; }
			
			$filter = ' ';
			if (count($roles_list) > 0) $filter .= " AND user_roles.role_id NOT IN :roles_list ";
			
			$sql = "DELETE FROM user_roles
					WHERE 
						user_roles.user_id = :user_id AND
						user_roles.id NOT IN :needed_roles
						".$filter." ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':user_id', $user_id);
			$result->bind(':needed_roles', $needed_roles);
			$result->bind(':roles_list', $roles_list);			
			$data = $result->execute();	
			
			$ret_data['status'] = '1';
		} else {
			$ret_data['error'] = implode('<br/>', $ret_data['error']);
			$ret_data['status'] = '0';
		}
		
		return $ret_data;
	}
	
	public function delete($user_id) {
		if ($user_id > 1) {
			// DELETE USER ROLES
			$sql = "DELETE FROM user_roles
					WHERE user_roles.user_id = :user_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':user_id', $user_id);
			$data = $result->execute();	
			
			// REMOVE IMAGE
			$user_data = $this->user->userData($user_id);
			$this->files = Model::factory('Manager_files');
			if (!empty($user_data[0]['image_src'])) $this->files->deleteFile($user_data[0]['image_src']);
			
			// DELETE USER
			$sql = "DELETE FROM users
					WHERE users.id = :user_id ";
			$result = $this->db->query(Database::UPDATE, $sql);
			$result->bind(':user_id', $user_id);
			$data = $result->execute();	
		}
	}
}