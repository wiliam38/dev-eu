<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Class extends Controller_Main {
	public $template = "manager/template";
	
	public function before() {
		parent::before();
				
		if (!$this->user->logged_in('manager'))
		{
			$this->request->redirect('/manager/session/login');
		}			
		
		// MODELS
		$this->resources = Model::factory('manager_resources');
		
		// I18N
		$this->manager_lang = Kohana::$config->load('manager.language');
		I18n::lang('manager-'.$this->manager_lang);
		$this->tpl->manager_lang = $this->manager_lang;
	}	
	
	public function initForm($role = '') {
		if ($this->role('manager')) {
			// CHANGE TEMPLATE
			$this->tpl->set_filename("manager/template");
			
			// LOAD MENU	
			$this->menu = Model::factory('manager_menu'); 
        	$this->tpl->menu_data = $this->menu->getMenuData('0');     		
			
			// LOAD PAGE TREE
			$parent_list_array = array_merge(array('0'), explode(',',$this->session->get('opened_id_list')));
			$this->tpl->parent_id = '0';
			$this->tpl->pages_tree = $this->resources->getDocuments(null, $parent_list_array);
			
			// TREE HIDDEN
			$this->tpl->tree_hidden = $this->session->get('tree_hidden');
			
			if ($role != '' && !$this->user->logged_in($role)) 
			{		
				// NO RIGHTS
				$data = array(	'action' => 'no_view' );
				$this->tpl->data_panel = $this->tpl->factory('manager/templates/rights', $data);
				
				return false;
			}		
			
			return true;
		}
	}
	
	public function role($role) {
		if (!$this->user->logged_in($role)) return false;
		else return true; 
	}
}