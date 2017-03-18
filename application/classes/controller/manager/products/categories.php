<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Products_Categories extends Controller_Manager_Class {
	// CATEGORY CONTENT
	var $content_type_id = 10;
	var $title_list = 'Kategorijas';
	var $title_edit = 'Kategorija';
	var $title_setting_list = 'Kategorijas parametri';
	var $link = 'products_categories';
	
	public function before() {
		parent::before();
		
		$this->categories = new Model_Manager_Products_Categories($this->content_type_id);
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			// CSS / JS
			$this->tpl->js_file[] = 'assets/modules/manager/products/categories.js';
			
			// DEFAULT LANGUAGE
			$tpl_data['lang_id'] = CMS::getSettings('default.lang_id');
			
			// GET CATEGORIES
			$tpl_data['categories'] = $this->categories->getCategories();	
			
			// DATA PANEL
			$tpl_data['action'] = 'list';	
			$tpl_data['page_title'] = $this->title_list;
			$tpl_data['link'] = $this->link;		
			$this->tpl->data_panel = $this->tpl->factory('manager/products/categories',$tpl_data);
		}
	}
	
	public function action_edit() {
		if ($this->initForm('manager')) {
			// ID
			$category_id = $this->request->param('id');
			$parent_id = $this->request->param('opt');
			
			// CSS / JS
			$this->tpl->js_file[] = 'assets/libs/ckeditor/ckeditor.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->js_file[] = 'assets/modules/manager/products/categories_edit.js';
						
			// GET CATEGORIES
			$filter_data = array();
			if (empty($category_id) || $category_id == 'new') {
				if (!empty($parent_id)) $filter_data['parent_id'] = $parent_id;
				else $filter_data['parent_id'] = 0;
			}			
			$categories = $this->categories->getCategories($category_id, null, $filter_data);
			$tpl_data['category'] = $categories[0];
			
			// GALLERY
			$tpl_data['gallery_data'] = $this->categories->getCategoryImages(null, $category_id);
			
			// OTHER
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['status'] = CMS::getStatus('categories_status_id');
			$tpl_data['types'] = CMS::getTypes('categories_type_id');
			
			// GET ALL CATEGORIES
			$tpl_data['all_categories'] = $this->categories->getCategories();
			
			// DEF LANG ID
			$tpl_data['def_lang_id'] = CMS::getSettings('default.lang_id');
			
			$tpl_data['return_url'] = $this->request->referrer();
					
			// DATA PANEL
			$tpl_data['action'] = 'load';
			$tpl_data['page_title'] = $this->title_edit;
			$tpl_data['link'] = $this->link;	
			$this->tpl->data_panel = $this->tpl->factory('manager/products/category_edit',$tpl_data);
		}
	}

	public function action_load_lang_tab() {
		if ($this->role('manager')) {
			// PARAMS
			$this->auto_render = FALSE;			
			
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			// PRODUCT DATA
			if ($this->request->post('lang_data') != '') {
				// FILES MODEL
				$this->files = Model::factory('manager_files');
				
				foreach($this->request->post('lang_data') as $key => $val) {
					switch (substr(strstr($key,'_'),1)) {
						case 'category_content_id': $category['lang'][$this->request->post('lang_id')]['id'] = 'new'; break; 
						case 'language_id': $category['lang'][$this->request->post('lang_id')]['language_id'] = $this->request->post('lang_id'); break; 
						case 'title':
						case 'description':
							$category['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = $val; 
							break;
					}
				}
			}			
			$category['lang'][$this->request->post('lang_id')]['id'] = 'new';
			$tpl_data['category'] = $category;		
						
			$tpl_data['action'] = "lang_tab";
			echo $this->tpl->factory('manager/products/category_edit',$tpl_data);
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
			
			echo $this->tpl->factory('manager/products/category_edit', $tpl_data);
		}		
	}

	public function action_load_gallery_item() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$tpl_data['data']['image_src'] = $this->request->post('image_src');
			
			// GET LANGUAGES
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);	
			$tpl_data['action'] = "gallery_tab_edit";
			
			echo $this->tpl->factory('manager/products/category_edit',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {			
			$this->categories->save($this->request->post());
		}
		
		if ($this->request->post('return_url') != '') $this->request->redirect($this->request->post('return_url'));
		else $this->request->redirect('manager/products_categories/load');
	}
	
	public function action_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->categories->delete($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}

	/*
	 * SETTINGS
	 */
	public function action_settings() {
		if ($this->initForm('manager')) {
			// PARAMS
			$category_id = $this->request->param('id');
			
			// CSS / JS
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->css_file[] = 'assets/modules/manager/products/category_settings.css';
			$this->tpl->js_file[] = 'assets/modules/manager/products/category_settings.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			// DEFAULT LANGUAGE
			$tpl_data['lang_id'] = CMS::getSettings('default.lang_id');
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			
			// GET CATEGORIES
			$categories = $this->categories->getCategories($category_id, $tpl_data['lang_id']);
			$tpl_data['category'] = $categories[0];
			
			$category_settings = $this->categories->getCategorySettings(null, $category_id);	
			for ($i=0; $i<count($category_settings); $i++) {
				$category_settings[$i]['values'] = $this->categories->getCategorySettingValues(null, $category_settings[$i]['id']);
			}
			$tpl_data['category_settings'] = $category_settings;
			
			// DATA PANEL
			$tpl_data['action'] = 'list';	
			$tpl_data['page_title'] = $this->title_setting_list;
			$tpl_data['link'] = $this->link;		
			$this->tpl->data_panel = $this->tpl->factory('manager/products/category_settings',$tpl_data);
		}
	}
	public function action_settings_view() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'error' => '',
							'response' => '',
							'sub_data' => '');
		
		if ($this->role('manager')) {
			$id = $this->request->post('id');
			if (is_numeric($id)) $settings = $this->categories->getCategorySettings($id);
			$tpl_data['data'] = $settings[0];	
			$tpl_data['category_setting'] = $settings[0];			
			$tpl_data['action'] = 'view';
			
			
			// DEFAULT LANGUAGE
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			
			$ret_data['response'] = $this->tpl->factory('manager/products/category_settings', $tpl_data)->render();
			$ret_data['status'] = 1;
			
			// SUB LOAD FOR NEW DATA
			if ($this->request->post('sub_load') === true) {
				// GET CATEGORY DATA
				$tpl_data['action'] = 'sub_load';
				$tpl_data['category_setting_values'] = array();
				$ret_data['sub_data'] = $this->tpl->factory('manager/products/category_settings', $tpl_data)->render();
			}
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($ret_data);
	}
	public function action_settings_edit() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$id = $this->request->post('id');
			if (is_numeric($id)) {
				$settings = $this->categories->getCategorySettings($id);
			} else {
				$settings[0] = array(
					'id' => 'new',
					'category_id' => $this->request->post('category_id') );
			}
			$tpl_data['data'] = $settings[0];		
			$tpl_data['action'] = 'edit';
			
			// DEFAULT LANGUAGE
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			
			$tpl_data['types'] = CMS::getTypes('category_settings_type_id');
			$tpl_data['statuses'] = CMS::getStatus('category_settings_status_id');
			
			$ret_data['response'] = $this->tpl->factory('manager/products/category_settings', $tpl_data)->render();
			$ret_data['status'] = 1;
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($ret_data);
	}
	public function action_settings_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$id = $this->categories->saveCategorySettings($this->request->post());
			
			if ($this->request->post('id') == 'new') $this->request->post('sub_load', true);
			$this->request->post('id', $id);
			$this->action_settings_view();			
			exit();			
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($ret_data);
	}
	public function action_settings_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->categories->deleteCategorySettings($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
	
	
	public function action_settings_sub_view() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$id = $this->request->post('id');
			if (is_numeric($id)) $setting_values = $this->categories->getCategorySettingValues($id);
			$tpl_data['data'] = $setting_values[0];		
			$tpl_data['action'] = 'sub_view';
			
			// DEFAULT LANGUAGE
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			
			$ret_data['response'] = $this->tpl->factory('manager/products/category_settings', $tpl_data)->render();
			$ret_data['status'] = 1;
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($ret_data);
	}
	public function action_settings_sub_edit() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$id = $this->request->post('id');
			if (is_numeric($id)) {
				$setting_values = $this->categories->getCategorySettingValues($id);
			} else {
				$settings = $this->categories->getCategorySettings($this->request->post('category_setting_id'));
				
				$setting_values[0] = array(
					'id' => 'new',
					'category_setting_id' => $settings[0]['id'],					
					'category_setting_type_id' => $settings[0]['type_id'] );
			}
			$tpl_data['data'] = $setting_values[0];		
			$tpl_data['action'] = 'sub_edit';
			
			// DEFAULT LANGUAGE
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			
			$tpl_data['statuses'] = CMS::getStatus('category_setting_values_status_id');
			
			$ret_data['response'] = $this->tpl->factory('manager/products/category_settings', $tpl_data)->render();
			$ret_data['status'] = 1;
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($ret_data);
	}
	public function action_settings_sub_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$id = $this->categories->saveCategorySettingValues($this->request->post());
			
			$this->request->post('id', $id);
			$this->action_settings_sub_view();			
			exit();			
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($ret_data);
	}
	public function action_settings_sub_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->categories->deleteCategorySettingValues($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
}