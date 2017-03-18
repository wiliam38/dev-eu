<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Settings extends Controller_Manager_Class {
	public function before() {
		parent::before();
		
		//MODELS
		$this->settings = Model::factory('manager_settings');
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {					
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(	'assets/modules/manager/settings/settings.js'));
					
			// LANGUAGES
			$data['languages']= CMS::getLanguages(null, null, 5);
			
			// GET DATA
			$data['settings'] = $this->settings->getSettings();
			$data['action'] = 'load';
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/settings/setting',$data);
		}
	}
	
	public function action_show() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {	
			// LANGUAGES
			$page_data['languages']= CMS::getLanguages(null, null, 5);
				
			// SETTING DATA
			$category_name = $this->request->post('category_name');
			$page_data['settings'] = $this->settings->getSettings(null, null, null, $category_name);
				
			$page_data['action'] = 'show';
			echo  $this->tpl->factory('manager/settings/setting',$page_data);
		}
	}
	
	public function action_edit() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {	
			if ($this->request->post('id') == 'new') {
				$data = $this->settings->getNewSettings();
			} else {
				$data = $this->settings->getSettings(null, null, $this->request->post('id'));
			}
			$data['data'] = $data[0];
			$data['action'] = 'edit';
			$data['languages'] = CMS::getLanguages(null, null, 5);
			
			echo $this->tpl->factory('manager/settings/setting', $data);	
		}
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {	
			$data = $this->settings->getSettings(null, null, $this->request->post('id'));
			$data['data'] = $data[0];
			$data['action'] = 'view';
			$data['languages'] = CMS::getLanguages(null, null, 5);
			
			echo $this->tpl->factory('manager/settings/setting', $data);
		}
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->settings->delete($this->request->post('id'));
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->settings->save($this->request->post('id'), $this->request->post());
		}	
	}
	
	public function action_generate_files() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->settings->generate_files();
		}
	}
	
	//
	// AUTOCOMPLETE
	//
	public function action_categories() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {	
			$term = $this->request->post('term');
		
			$sql = "SELECT
							'--- ".__('ALL')." ---'													AS value,
							''																AS id
						UNION SELECT DISTINCT
							SUBSTRING(settings.name,1,LOCATE('.',settings.name)-1)   		AS value,
							SUBSTRING(settings.name,1,LOCATE('.',settings.name)-1)   		AS id
						FROM
							settings
						WHERE
							IFNULL(settings.parent_id,0) = 0 AND
							SUBSTRING(settings.name,1,LOCATE('.',settings.name)-1) != '' AND
							SUBSTRING(settings.name,1,LOCATE('.',settings.name)-1) LIKE :term
						ORDER BY 
							value ";
			$result = $this->db->query(Database::SELECT, $sql);
			$term = '%'.$term.'%';
			$result->bind(':term', $term);
			$data = $result->execute()->as_array();
		
			echo json_encode($data);
		}
	}
	
	
}