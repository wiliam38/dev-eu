<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Templates extends Controller_Manager_Class {
	public function before() {
		parent::before();
		
		// MODELS
		$this->templates = Model::factory('manager_templates');
	}
		
	public function action_load() {
		if ($this->initForm('admin')) {		
			// GET DATA
			$data['plugins'] = $this->templates->getTemplates();
			$data['action'] = 'load';
			
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(	'assets/modules/manager/templates/templates.js'));
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/templates/template',$data);
		}
	}
	
	public function action_edit() {
		$this->auto_render = FALSE;
		if ($this->role('admin')) {	
			if ($this->request->post('id') == 'new') {
				$data = $this->templates->getNewTemplates();
			} else {
				$data = $this->templates->getTemplates($this->request->post('id'));
			}
			$data['data'] = $data[0];
			$data['action'] = 'edit';
			$data['status'] = CMS::getTypes('templates_type_id',null,$data['data']['type_id'],'selected');
			
			echo $this->tpl->factory('manager/templates/template', $data);	
		}
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		if ($this->role('admin')) {	
			$data = $this->templates->getTemplates($this->request->post('id'));
			$data['data'] = $data[0];
			$data['action'] = 'view';
			
			echo $this->tpl->factory('manager/templates/template', $data);
		}
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('admin')) {
			echo $this->templates->delete($this->request->post('id'));
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		if ($this->role('admin')) {
			echo $this->templates->save($this->request->post('id'), $this->request->post());
		}	
	}
}