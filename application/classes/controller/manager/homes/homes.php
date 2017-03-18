<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Homes_Homes extends Controller_Manager_Class {
	public $limit = 25;
	
	public function before() {
		parent::before();
		
		$this->homes = Model::factory('manager_homes_homes');
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			// PAGES
			$pages['cnt'] = $this->homes->getHomes(null, null, array(), null, null, true);
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			$pages['show_pages'] = 5;
						
			$tpl_data['paginate'] = $pages;
			
			// GET PRODUCTS
			$tpl_data['homes'] = $this->homes->getHomes(null, null, array(), $pages['limit'], $pages['offset']);	
			
			// DATA PANEL
			$tpl_data['action'] = 'list';	
			$tpl_data['def_lang_id'] = CMS::getSettings('default.lang_id');	
			$this->tpl->data_panel = $this->tpl->factory('manager/homes/homes',$tpl_data);
		}
	}
	
	public function action_edit() {
		if ($this->initForm('manager')) {
			// ID
			$home_id = $this->request->param('id');
			
			// CSS / JS
			$this->tpl->js_file[] = 'assets/libs/ckeditor/ckeditor.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->js_file[] = 'assets/modules/manager/homes/homes_edit.js';
						
			// GET PRODUCTS
			if (empty($home_id)) $home_id = 'new';
			if ($home_id != 'new') {
				$homes = $this->homes->getHomes($home_id);
				$tpl_data['home'] = $homes[0];
			}
			
			// OTHER
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['home_status'] = CMS::getStatus('home_contents_status_id');
			$tpl_data['color_types'] = CMS::getTypes('homes_color_type_id');
			
			$tpl_data['return_url'] = $this->request->referrer();
					
			// DATA PANEL
			$tpl_data['action'] = 'load';
			$this->tpl->data_panel = $this->tpl->factory('manager/homes/home_edit',$tpl_data);
		}
	}

	public function action_load_lang_tab() {
		if ($this->role('manager')) {
			// PARAMS
			$this->auto_render = FALSE;		
			
			// PAGE DATA
			$lang_data = $this->request->post('lang_data');
			if (!empty($lang_data)) {
				// FILES MODEL
				$this->files = Model::factory('manager_files');
				
				foreach($lang_data as $key => $val) {
					switch (substr(strstr($key,'_'),1)) {
						case 'new_content_id': $home['lang'][$this->request->post('lang_id')]['id'] = 'new'; break; 
						case 'language_id': $home['lang'][$this->request->post('lang_id')]['language_id'] = $this->request->post('lang_id'); break; 
						case 'status_id': $home['lang'][$this->request->post('lang_id')]['status_id'] = '1'; break; 
						default:	
							$home['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = $val; 
							break;
					}
				}
			}			
			$home['lang'][$this->request->post('lang_id')]['id'] = 'new';
			$tpl_data['home'] = $home;
			
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			// STATUS
			$tpl_data['home_status'] = CMS::getStatus('home_contents_status_id');
			
			$tpl_data['action'] = "lang_tab";
			echo $this->tpl->factory('manager/homes/home_edit',$tpl_data);
		}
	}
	
	public function action_remove_lang_tab() {
		if ($this->role('manager')) {
			// PARAMS
			$this->auto_render = FALSE;		
			$tpl_data['action'] = "lang_tab_empty";
			
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			echo $this->tpl->factory('manager/homes/home_edit', $tpl_data);
		}		
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {			
			$home_id = $this->homes->save($this->request->post());
		}
		
		if ($this->request->post('return_url') != '') $this->request->redirect($this->request->post('return_url'));
		else $this->request->redirect('manager/homes_homes/load');
	}
	
	public function action_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->homes->delete($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
}