<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Lexicon extends Controller_Manager_Class {
	public $limit = 25;
	
	public function before() {
		parent::before();
		
		// MODELS
		$this->lexicons = Model::factory('manager_lexicons');
	} 		
	
	public function action_load() {
		if ($this->initForm('manager')) {
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(		'assets/modules/manager/lexicon/lexicon.js' ));
			$this->tpl->css_file = array_merge($this->tpl->css_file, array(		'assets/modules/manager/lexicon/lexicons.css' ));
			
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(		'assets/libs/ckeditor/ckeditor.js'));
						
			// FILTER
			$filter_data = $this->request->post();
			if (empty($filter_data['order_by'])) $filter_data['order_by'] = '1-a';
			$page_data['filter_data'] = $filter_data;
			
			// PAGES
			$pages['cnt'] = $this->lexicons->getLexicons(null, array_merge($filter_data, array('count' => true)));
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			$pages['show_pages'] = 5;
						
			$page_data['paginate'] = $pages;
						
			// LANGUAGES
			$page_data['languages'] = CMS::getLanguages(null, null, 5);
			$page_data['categories'] = $this->lexicons->getCategories();
			
			// LEXICON DATA
			$order_data = explode('-',$filter_data['order_by']);
			if (empty($order_data)) $order_data = array('1','a');
			$order_by = '"lexicons.name" ASC';
			if (isset($order_data[0])) {
				switch ($order_data[0]) {
					case '1': default: $order_by = '"lexicons.name"'; break;
					case '2': $order_by = '"lexicons.user_datetime"'; break;
				}
				if (isset($order_data[1]) && $order_data[1] == 'd') $order_by .= ' DESC';
				else $order_by .= ' ASC';
			}
			$page_data['lexicons'] = $this->lexicons->getLexicons(null, $filter_data, $pages['limit'], $pages['offset'], $order_by);
			
			$page_data['action'] = 'load';			
			$this->tpl->data_panel = $this->tpl->factory('manager/lexicon/lexicon',$page_data);
		}
	}
	
	public function action_edit() {		
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			if ($this->request->post('id') == 'new') {
				$data = $this->lexicons->getNewLexicons();
			} else {
				$data = $this->lexicons->getLexicons($this->request->post('id'));
			}
			$data['data'] = $data[0];
			$data['action'] = 'edit';
			$data['languages'] = CMS::getLanguages(null, null, 5);
			
			echo $this->tpl->factory('manager/lexicon/lexicon', $data);	
		}
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			$data = $this->lexicons->getLexicons($this->request->post('id'));
			$data['data'] = $data[0];
			$data['action'] = 'view';
			$data['languages'] = CMS::getLanguages(null, null, 5);
			
			echo $this->tpl->factory('manager/lexicon/lexicon', $data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->lexicons->save($this->request->post());
		}		
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->lexicons->delete($this->request->post('id'));
		}
	}

	public function action_generate_files() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			echo $this->lexicons->generate_files();
		}
	}
}