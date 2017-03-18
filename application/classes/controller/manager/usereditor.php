<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Usereditor extends Controller_Manager_Users {	
	public $template = "manager/users/user_editor";
	
	public function before() {
		parent::before();
		
		// MODEL
		$this->users = Model::factory('manager_users');
	}
	
	public function action_load() {
		if ($this->initForm('manager')) {			
			// PARAMS
			$id = $this->request->param('id');	
			if ($id == 'current') $id = $this->user_id;
	
			// PARAMS
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';			
			$this->tpl->js_file[] = 'assets/modules/manager/users/usereditor.js';
					
			if (!empty($id)) {
				// USER DATA
				if ($id == 'new') {
					$user = $this->users->getNewUsers();
				} else {
					$user = $this->users->getUsers($id);
				}
				$user_data['user'] = $user[0];		

				// ROLES
				$user_data['user_roles'] = $this->users->getUserRoles($id);
				$user_data['user_role_status'] = CMS::getStatus('user_roles_status_id');
				
				// STATUS
				$user_data['status_data'] = CMS::getStatus('users_status_id',null,$user[0]['status_id'],'selected','1');
				
				// FILTER DATA
				$user_data['filter_data'] = $this->request->post();
				
				$user_data['action'] = 'load';
				
				$this->tpl->data_panel = $this->tpl->factory('manager/users/user_editor',$user_data);
			}
		}
	}
	
	public function action_add_role_popup() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$role_id = $this->request->post('role_id');
			$roles_list = $this->request->post('roles_list');
			
			if (!$this->user->logged_in('admin')) $roles_list[] = '1';
			$roles = $this->users->getRoles(null, array('not_in' => $roles_list));
			
			// LOOP ROLES
			if (empty($role_id)) $role_id = $roles[0]['id'];
			$role_data = array();
			for($i=0; $i<count($roles); $i++) {
				if ($roles[$i]['id'] == $role_id) $role_data = $roles[$i];
			}
			
			if (count($role_data) > 0) {
				if (!empty($role_data['sql_select']) AND !empty($role_data['sql_from'])) {
					$sql = "SELECT 
								".$role_data['sql_from'].".id			AS id,
								".$role_data['sql_select']." 			AS title
							FROM 
								".$role_data['sql_from']."	
							WHERE 
								".$role_data['sql_from'].".id NOT IN :not_in 
							ORDER BY
								".$role_data['sql_select']." ";
					$res = $this->db->query(Database::SELECT, $sql);
					$not_in = $this->request->post('roles_data_list');
					if (empty($not_in)) $not_in = array('-1');
					$res->bind(':not_in', $not_in);
					$data_data = $res->execute()->as_array();
					$tpl_data['roles_data'] = $data_data;
				}
			}
			
			$tpl_data['roles'] = $roles;
			$tpl_data['role_id'] = $role_id;
			
			$tpl_data['action'] = 'roles_popup';
			echo $this->tpl->factory('manager/users/user_editor',$tpl_data);
		}
	}
	public function action_add_role_popup_get_role() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$roles_list = $this->request->post('roles_list');
			if (empty($roles_list)) $roles_list = array();
			
			if (!$this->user->logged_in('admin')) $roles_list[] = '1';
			$roles = $this->users->getRoles(null, array('not_in' => $roles_list));
			
			echo isset($roles[0]['id'])?$roles[0]['id']:'';
		}
	}
	public function action_add_role() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$user_role = array(
				'id' => 'new',
				'role_id' => $this->request->post('role_id'),
				'data_id' => $this->request->post('data_id'),
				'status_id' => '10',
				'data_title' => '' );
			
			// ROLE DATA
			$role_data = $this->users->getRoles($user_role['role_id']);
			$user_role['role_description'] = $role_data[0]['description'];
			
			// DATA
			if (!empty($role_data[0]['sql_select']) AND !empty($role_data[0]['sql_from']) AND !empty($user_role['data_id'])) {
				$sql = "SELECT ".$role_data[0]['sql_select']." AS title
						FROM ".$role_data[0]['sql_from']."	
						WHERE ".$role_data[0]['sql_from'].".id = :id ";
				$res = $this->db->query(Database::SELECT, $sql);
				$res->bind(':id', $user_role['data_id']);
				$data_data = $res->execute()->as_array();
				if (count($data_data) > 0) $user_role['data_title'] = $data_data[0]['title'];
			}
				
				
			$tpl_data['user_role'] = $user_role;
			$tpl_data['user_role_status'] = CMS::getStatus('user_roles_status_id');
			
			$tpl_data['action'] = 'roles_row';
			echo $this->tpl->factory('manager/users/user_editor',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => 0,
							'error' => null,
							'response' => '');
		
		if ($this->role('manager')) {
			$ret_data = $this->users->save($this->request->post());
		} else {
			$ret_data['error'] = 'You have no rights to edit Users!';
		}
		
		echo json_encode($ret_data);
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			$this->users->delete($this->request->post('user_id'));
		}
	}
}