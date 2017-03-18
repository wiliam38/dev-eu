<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Products_Mfrs extends Controller_Manager_Class {
	// CATEGORY CONTENT
	var $category_content_id = 10;
	var $title_list = 'Mfrs';
	var $title_edit = 'Mfr';
	var $link = 'products_mfrs';
	
	public function before() {
		parent::before();
		
		$this->mfrs = Model::factory('manager_products_mfrs');
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			// CSS / JS
			$this->tpl->js_file[] = 'assets/modules/manager/products/mfrs.js';
			
			// GET CATEGORIES
			$tpl_data['categories'] = $this->mfrs->getMfrs();	
			
			// DATA PANEL
			$tpl_data['action'] = 'list';		
			$this->tpl->data_panel = $this->tpl->factory('manager/products/mfrs',$tpl_data);
		}
	}
	
	public function action_edit() {
		if ($this->initForm('manager')) {
			// ID
			$mfr_id = $this->request->param('id');
			
			// CSS / JS
			$this->tpl->js_file[] = 'assets/libs/ckeditor/ckeditor.js';
			$this->tpl->js_file[] = 'assets/libs/ckeditor/adapters/jquery.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->js_file[] = 'assets/modules/manager/products/mfrs_edit.js';
			
			// OTHER
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['status'] = CMS::getStatus('mfrs_status_id');
			$tpl_data['cities'] = CMSPLUS::getCities();
			
			// GET ALL CATEGORIES
			$mfrs = $this->mfrs->getMfrs($mfr_id);
			$tpl_data['mfr'] = $mfrs[0];
			
			$tpl_data['return_url'] = $this->request->referrer();
					
			// DATA PANEL
			$tpl_data['action'] = 'load';
			$this->tpl->data_panel = $this->tpl->factory('manager/products/mfr_edit',$tpl_data);
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
			$mfr['lang'][$this->request->post('lang_id')] = array(	'id' => 'new' );
			$tpl_data['mfr'] = $mfr;		
						
			$tpl_data['action'] = "lang_tab";
			echo $this->tpl->factory('manager/products/mfr_edit',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {			
			$this->mfrs->save($this->request->post());
		}
		
		if ($this->request->post('return_url') != '') $this->request->redirect($this->request->post('return_url'));
		else $this->request->redirect('manager/products_mfrs/load');
	}
	
	public function action_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->mfrs->delete($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
}