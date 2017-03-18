<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Plugins extends Controller_Manager_Class {
	public function before() {
		parent::before();
		
		// MODELS
		$this->plugins = Model::factory('manager_plugins');
	}
		
	public function action_load() {
		if ($this->initForm('admin')) {
		
			// GET DATA
			$data['plugins'] = $this->plugins->getPlugins();
			$data['action'] = 'load';
			
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(	'assets/modules/manager/plugins/plugins.js'));
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/plugins/plugin',$data);
		}
	}
	
	public function action_edit() {
		$this->auto_render = FALSE;
		if ($this->role('admin')) {
			if ($this->request->post('id') == 'new') {
				$data = $this->plugins->getNewPlugins();
			} else {
				$data = $this->plugins->getPlugins($this->request->post('id'));
			}
			$data['data'] = $data[0];
			$data['action'] = 'edit';
			$data['status'] = CMS::getTypes('plugins_type_id',null,$data['data']['type_id'],'selected');
			
			$data['data']['parameters'] = str_replace('|', chr(13), $data['data']['parameters']);
			
			echo $this->tpl->factory('manager/plugins/plugin', $data);	
		}
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		if ($this->role('admin')) {
			$data = $this->plugins->getPlugins($this->request->post('id'));
			$data['data'] = $data[0];
			$data['action'] = 'view';
			
			echo $this->tpl->factory('manager/plugins/plugin', $data);
		}
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('admin')) {
			echo $this->plugins->delete($this->request->post('id'));
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		if ($this->role('admin')) {
			echo $this->plugins->save($this->request->post('id'), $this->request->post());
		}		
	}
	
	public function action_generate_files() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->plugins->generate_files();
		}
	}
}