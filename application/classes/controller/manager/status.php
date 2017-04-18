<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Status extends Controller_Manager_Class {
	var $role = 'manager';
	var $allowed = array( );
	
	public function before() {
		parent::before();
		
		$tmp_data = $this->db->query(Database::SELECT, "SHOW TABLES LIKE 'status_contents'")->execute()->as_array();
		$tableExists = count($tmp_data) > 0;
		if (!$tableExists) die('System Error!');
		
		// MODELS
		$this->status = Model::factory('manager_status');
	}
		
	public function action_load() {
		if ($this->initForm($this->role)) {					
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(	'assets/modules/manager/status/status.js'));
					
			// LANGUAGES
			$data['languages']= CMS::getLanguages(null, null, 5);
			
			// GET DATA
			$data['table_status_name'] = $this->allowed;
			$data['post_data'] = $this->request->post();
			$data['action'] = 'load';
			
			// GET STATUS DATA
			$category_name = $this->request->post('category_name');
			if (empty($category_name)) $category_name = CMS::getGET('category_name');
			if (!in_array($category_name, $this->allowed)) $category_name = '';
			if (empty($category_name)) $category_name = isset($data['table_status_name'][0])?$data['table_status_name'][0]:'';
			$data['post_data']['category_name'] = $category_name;
			
			if (in_array($category_name, $this->allowed)) $data['allowed'] = true;
			else $data['allowed'] = false;
			
			$data['status'] = $this->status->getStatus(null, null, $category_name);
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/status/status',$data);
		}
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => 0,
			'error' => '',
			'response' => '',
			'id' => ''
		);
		
		if ($this->role($this->role)) {	
			$data = $this->status->getStatus($this->request->post('id'));
			$data['data'] = $data[0];
			$data['action'] = 'view';
			$data['languages'] = CMS::getLanguages(null, null, 5);
			
			if (in_array($data[0]['table_status_name'], $this->allowed)) $data['allowed'] = true;
			else $data['allowed'] = false;
			
			$ret_data['response'] = $this->tpl->factory('manager/status/status', $data)->render();
			$ret_data['id'] = $this->request->post('id');
			$ret_data['status'] = 1;
		} else {
			$ret_data['error'] = __('You have no rights!');
		}
		
		echo json_encode($ret_data);
	}
	
	public function action_edit() {
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => 0,
			'error' => '',
			'response' => '',
			'id' => ''
		);
		
		// GET OLD STATUS DATA
		$id = $this->request->post('id');
		if (is_numeric($id)) {
			$status_data = $this->status->getStatus($this->request->post('id'));
			$table_status_name = $status_data[0]['table_status_name'];
		} else {
			$table_status_name = $this->request->post('table_status_name');
		}
		
		if ($this->role($this->role) && in_array($table_status_name, $this->allowed)) {	
			if ($this->request->post('id') == 'new') {
				$data[0] = array(
					'id' => 'new',
					'table_status_name' => $this->request->post('table_status_name') );
			} else {
				$data = $this->status->getStatus($this->request->post('id'));
			}
			$data['data'] = $data[0];
			$data['action'] = 'edit';
			$data['languages'] = CMS::getLanguages(null, null, 5);
			
			$ret_data['response'] = $this->tpl->factory('manager/status/status', $data)->render();
			$ret_data['id'] = $this->request->post('id');	
			$ret_data['status'] = 1;
		} else {
			$ret_data['error'] = __('You have no rights!');
		}
		
		echo json_encode($ret_data);
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => 0,
			'error' => '',
			'response' => '',
			'id' => ''
		);
		
		// GET OLD STATUS DATA
		$id = $this->request->post('id');
		if (is_numeric($id)) {
			$status_data = $this->status->getStatus($this->request->post('id'));
			$table_status_name = $status_data[0]['table_status_name'];
		} else {
			$table_status_name = $this->request->post('table_status_name');
		}
		
		if ($this->role($this->role) && in_array($table_status_name, $this->allowed)) {
			$status = $this->status->save($this->request->post('id'), $this->request->post());
			
			if (is_numeric($status)) {
				$ret_data['status'] = 1;
				$ret_data['id'] = $status;
			} else {
				$ret_data['error'] = $status;
			}
		} else {
			$ret_data['error'] = __('You have no rights!');
		}
		
		echo json_encode($ret_data);	
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => 0,
			'error' => '',
			'response' => '',
			'id' => ''
		);
		
		$status_data = $this->status->getStatus($this->request->post('id'));
		
		if ($this->role($this->role) && in_array($status_data[0]['table_status_name'], $this->allowed)) {
			$ret_data['id'] = $this->status->delete($this->request->post('id'));
			$ret_data['status'] = 1;
		} else {
			$ret_data['error'] = __('You have no rights!');
		}
		
		echo json_encode($ret_data);
	}	
}