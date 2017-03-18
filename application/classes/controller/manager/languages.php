<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Languages extends Controller_Manager_Class {
	public function before() {
		parent::before();
		
		// MODELS
		$this->langauges = Model::factory('manager_languages');
	} 	
	public function action_load() {
		if ($this->initForm('manager')) {		
			// GET DATA
			$data['plugins'] = $this->langauges->getLanguages();
			$data['action'] = 'load';
			
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(	'assets/modules/manager/languages/languages.js'));
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/languages/language',$data);
		}
	}
	
	public function action_edit() {
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			if ($this->request->post('id') == 'new') {
				$data = $this->langauges->getNewLanguages();
			} else {
				$data = $this->langauges->getLanguages($this->request->post('id'));
			}
			$data['data'] = $data[0];
			$data['action'] = 'edit';
			$data['status'] = CMS::getStatus('languages_status_id',null,$data['data']['status_id'],'selected');
			
			echo $this->tpl->factory('manager/languages/language', $data);
		}	
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			$data = $this->langauges->getLanguages($this->request->post('id'));
			$data['data'] = $data[0];
			$data['action'] = 'view';
			
			echo $this->tpl->factory('manager/languages/language', $data);
		}
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->langauges->delete($this->request->post('id'));
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			echo $this->langauges->save($this->request->post('id'), $this->request->post());
		}		
	}
	
	public function action_default() {
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			// GET CURRENT DEFAULT
			$lang_id = CMS::getSettings('default.lang_id');
			
			// SETTINGS MODEL
			$settings = Model::factory('manager_settings');
			$settings->set('default.lang_id', $this->request->post('id'));
			
			echo $lang_id;
		}
	}
	
	
}