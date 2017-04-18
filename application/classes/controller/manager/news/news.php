<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_News_News extends Controller_Manager_Class {
	public $limit = 25;
	
	public function before() {
		parent::before();
		
		$this->news = Model::factory('manager_news_news');
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			// PAGES
			$pages['cnt'] = $this->news->getNews(null, null, array(), null, null, true);
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			$pages['show_pages'] = 5;
						
			$tpl_data['paginate'] = $pages;
			
			// GET PRODUCTS
			$tpl_data['news'] = $this->news->getNews(null, null, array(), $pages['limit'], $pages['offset']);	
			
			// DATA PANEL
			$tpl_data['action'] = 'list';	
			$tpl_data['def_lang_id'] = CMS::getSettings('default.lang_id');	
			$this->tpl->data_panel = $this->tpl->factory('manager/news/news',$tpl_data);
		}
	}
	
	public function action_edit() {
		if ($this->initForm('manager')) {
			// ID
			$new_id = $this->request->param('id');
			
			// CSS / JS
			$this->tpl->js_file[] = 'assets/libs/ckeditor/ckeditor.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->css_file[] = 'assets/modules/manager/news/news_edit.css';
			$this->tpl->js_file[] = 'assets/modules/manager/news/news_edit.js';
			
			$this->tpl->css_file[] = 'assets/libs/jquery-ui/jquery-ui-timepicker-addon.css';
			$this->tpl->js_file[] = 'assets/libs/jquery-ui/jquery-ui-timepicker-addon.js';	
						
			// GET PRODUCTS
			if (empty($new_id)) $new_id = 'new';
			if ($new_id != 'new') {
				$news = $this->news->getNews($new_id);
				$tpl_data['new'] = $news[0];
			}
			
			// OTHER
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['new_status'] = CMS::getStatus('new_contents_status_id');
			$tpl_data['types'] = CMS::getTypes('news_type_id');
			
			// ANSWERS
			$answers = $this->news->getAnswers(null, $new_id);
			for ($j=0; $j<count($answers); $j++) {
				if ($answers[$j]['type_id'] == 20) {
					$answers[$j]['answers'] = $this->news->getAnswers(null, null, null, array('parent_id' => $answers[$j]['id']));
				}
			}			
			$tpl_data['answers'] = $answers;			
			
			// GALLERY
			$tpl_data['gallery_data'] = $this->news->getNewImages(null, $new_id);
			
			$tpl_data['answer_types'] = CMS::getTypes('new_answers_type_id');
			
			$tpl_data['return_url'] = $this->request->referrer();
					
			// DATA PANEL
			$tpl_data['action'] = 'load';
			$this->tpl->data_panel = $this->tpl->factory('manager/news/new_edit',$tpl_data);
		}
	}

	public function action_load_lang_tab() {
		if ($this->role('manager')) {
			// PARAMS
			$this->auto_render = FALSE;		
			
			// PAGE DATA
			$lang_data = $this->request->post('lang_data');
			if (!empty($lang_data)) {				
				foreach($lang_data as $key => $val) {
					switch (substr(strstr($key,'_'),1)) {
						case 'new_content_id': $new['lang'][$this->request->post('lang_id')]['id'] = 'new'; break; 
						case 'language_id': $new['lang'][$this->request->post('lang_id')]['language_id'] = $this->request->post('lang_id'); break; 
						case 'status_id': $new['lang'][$this->request->post('lang_id')]['status_id'] = '1'; break; 
						default:	
							$new['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = $val; 
							break;
					}
				}
			}			
			$new['lang'][$this->request->post('lang_id')]['id'] = 'new';
			$tpl_data['new'] = $new;
			
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			// STATUS
			$tpl_data['new_status'] = CMS::getStatus('new_contents_status_id');
			
			$tpl_data['action'] = "lang_tab";
			echo $this->tpl->factory('manager/news/new_edit',$tpl_data);
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
			
			echo $this->tpl->factory('manager/news/new_edit', $tpl_data);
		}		
	}
	
	public function action_add_answer() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(	'status' => '0',
							'response' => '',
							'error' => '' );
		
		if ($this->role('manager')) {
			$ret_data['status'] = '1';		
			
			$tpl_data['languages'] = CMS::getLanguages();
			$tpl_data['answer_types'] = CMS::getTypes('new_answers_type_id');
			$tpl_data['action'] = 'answer_row';
			$ret_data['response'] = $this->tpl->factory('manager/news/new_edit',$tpl_data)->render();
		}
		
		echo json_encode($ret_data);
	}
	
	public function action_load_gallery_item() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			// GET LANGUAGES
			$tpl_data['languages'] = CMS::getLanguages();
			$tpl_data['data'] = array(
				'id' => 'new',
				'image_src' => $this->request->post('image_src') );	
			$tpl_data['action'] = "gallery_tab_edit";
			
			echo $this->tpl->factory('manager/news/new_edit',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {			
			$new_id = $this->news->save($this->request->post());
		}
		
		if ($this->request->post('return_url') != '') $this->request->redirect($this->request->post('return_url'));
		else $this->request->redirect('manager/news_news/load');
	}
	
	public function action_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->news->delete($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
}