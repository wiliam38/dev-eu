<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Pages extends Controller_Manager_Class {	
    public $template = "manager/pages/pages";
	
	public function action_load() {	
		if ($this->initForm('manager')) {
			// PARAMS
			$id = $this->request->param('id');	
			
			// PARAMS
			$this->tpl->css_file = array_merge($this->tpl->css_file, array(		'assets/modules/manager/pages/pages.css',
				
																				'assets/libs/jquery-ui/jquery-ui-timepicker-addon.css'));
			
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(		'assets/modules/manager/pages/pages.js',
																				'assets/libs/ckeditor/ckeditor.js',
																				'assets/modules/manager/pages/ckeditor_local_pages.js',
																				
																				'assets/libs/jquery-ui/jquery-ui-timepicker-addon.js',
				
																				'assets/libs/jquery-plugins/uploadify/swfobject.js',
																				'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js'));
							
			if (!empty($id)) {
				
				// LANGUAGES
				$page_data['languages'] = CMS::getLanguages(null, null, 5);
				
				// PAGE DATA
				if ($id == 'new') {
					$from_id = $this->request->param('opt');
					if (!empty($from_id)) $this->session->set('opened_id_list', $this->session->get('opened_id_list').','.$from_id);
					$page = $this->resources->getNewDocuments($from_id);
				} elseif ($id == 'new-copy') {
					$from_id = $this->request->param('opt');
					$page = $this->resources->getDocuments($from_id);
					
					// UPDATE DATA
					$page[0]['id'] = 'new';
					$page[0]['image_src'] = '';
					
					// LOOP LANGUAGES
					foreach($page[0]['lang'] as $key => $value) {
						if (!empty($page[0]['lang'][$key]['id'])) {
							// UPDATE DATA
							$page[0]['lang'][$key]['id'] = 'new';
							$page[0]['lang'][$key]['title_image_src'] = '';
							$page[0]['lang'][$key]['image_src'] = '';
							$page[0]['lang'][$key]['pub_date'] = '';
							$page[0]['lang'][$key]['unpub_date'] = '';
							$page[0]['lang'][$key]['menu_image_src'] = '';
							$page[0]['lang'][$key]['status_id'] = 1;
						}
					}					
				} else {
					$page = $this->resources->getDocuments($id);
				}
				if (count($page) > 0)
					$page_data['page'] = $page[0];		
				else 
					$this->request->redirect('manager/home');
				
				// TEMPLATES
				$page_data['templates'] = CMS::getTemplates();
				
				// STATUS
				$page_data['page_status'] = CMS::getStatus('page_contents_status_id');
				
				// PARENTS
				$parents_data = $this->resources->getDocuments(null,null,null,0);
				$page_data['parents_data'] = $parents_data;	
							
				$tree_data = array();
				$this->getTreeData($tree_data, 0, 0, $parents_data);
				$page_data['tree_data'] = $tree_data;
				
				// GALLERY
				$page_data['gallery_data'] = $this->resources->getDocumentImages(null, $id);
				
				// DEF LANG ID
				$page_data['def_lang_id'] = CMS::getSettings('default.lang_id');
				
				// TYPES
				$page_data['content_types'] = CMS::getTypes('page_contents_content_type_id');
				$page_data['target_types'] = CMS::getTypes('page_contents_target_type_id');
				
				$page_data['action'] = 'load';
				
				$this->tpl->data_panel = $this->tpl->factory('manager/pages/pages',$page_data);
			}
		}		
	}
	
	public function action_load_lang_tab() {
		// PARAMS
		$this->tpl->action = "lang_tab";
		
		if ($this->role('manager')) {
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$this->tpl->lang = $languages[0];
			
			// STATUS
			$this->tpl->page_status = CMS::getStatus('page_contents_status_id');
			
			// TYPES
			$this->tpl->content_types = CMS::getTypes('page_contents_content_type_id');
			$this->tpl->target_types = CMS::getTypes('page_contents_target_type_id');
			
			// PAGE DATA
			$lang_data = $this->request->post('lang_data');
			if (!empty($lang_data)) {
				// FILES MODEL
				$this->files = Model::factory('manager_files');
				
				foreach($lang_data as $key => $val) {
					switch (substr(strstr($key,'_'),1)) {
						case 'page_content_id': $page['lang'][$this->request->post('lang_id')]['id'] = 'new'; break; 
						case 'language_id': $page['lang'][$this->request->post('lang_id')]['language_id'] = $this->request->post('lang_id'); break; 
						case 'status_id': $page['lang'][$this->request->post('lang_id')]['status_id'] = '1'; break; 
						case 'pub_date':
						case 'unpub_date':
						case 'title':
						case 'intro':
						case 'alias':
						case 'target_type_id':
						case 'menu_title':
						case 'keywords':
						case 'description':	
						case 'content_type_id':	
						case 'content':	
						case 'redirect_link':
							$page['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = $val; 
							break;
						case 'searchable':
						case 'menu_hide':
							$page['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = '1'; 
							break; 
						case 'title_image_src':
						case 'image_src':
						case 'menu_image_src':	
							$page['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = $this->files->copyToTmp($val); 
							break;
					}
				}
			}			
			$page['lang'][$this->request->post('lang_id')]['id'] = 'new';
			
			// TEMPLATE DATA
			$this->template_class = Model::factory('manager_templates');
			$template_data = $this->template_class->getTemplates($this->request->post('template_id'));
			
			$page['conf_title_image'] = isset($template_data[0]['conf_title_image'])?$template_data[0]['conf_title_image']:null;
			$page['conf_introtext'] = isset($template_data[0]['conf_introtext'])?$template_data[0]['conf_introtext']:null;
			$page['conf_image'] = isset($template_data[0]['conf_image'])?$template_data[0]['conf_image']:null;
			$page['conf_menu_image'] = isset($template_data[0]['conf_menu_image'])?$template_data[0]['conf_menu_image']:null;
			$page['conf_seo'] = isset($template_data[0]['conf_seo'])?$template_data[0]['conf_seo']:null;
			$page['conf_target'] = isset($template_data[0]['conf_target'])?$template_data[0]['conf_target']:null;
			$page['conf_gallery'] = isset($template_data[0]['conf_gallery'])?$template_data[0]['conf_gallery']:null;
			
			$this->tpl->page = $page;
		}		
	}

	public function action_remove_lang_tab() {
		// PARAMS
		$this->tpl->action = "lang_tab_empty";
		
		if ($this->role('manager')) {
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$this->tpl->lang = $languages[0];
			
			// REMOVE TMP IMAGES
			$lang_data = $this->request->post('lang_data');
			foreach($lang_data as $key => $val) {
				switch (substr(strstr($key,'_'),1)) {
					case 'title_image_src':
					case 'image_src':
					case 'menu_image_src':	
						FILES::removeTmpFile($val); 
						break;
				}
			}
		}		
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
			
			echo $this->tpl->factory('manager/pages/pages',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$page_id = $this->resources->save($this->request->post('page_id'), $this->request->post());			
		}
		
		// REDIRECT
		if (isset($page_id)) $this->request->redirect($this->base_url."manager/pages/load/".$page_id);
		else $this->request->redirect($this->base_url."manager");
		
	}
	
	public function action_delete() {
		$this->auto_render = FALSE;
		if ($this->role('manager')) {
			// PARAMS
			$id = $this->request->post('page_id');
			
			$doc = $this->resources->getDocuments($id);
			if ($doc[0]['type_id'] == '1') {
				$this->resources->delete($id);
			}	
		}
	}
	
	public function action_load_sub_tree() {		
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$data['action'] = "tree_items";
			$data['base_url'] = $this->base_url;
			
			// LANGUAGES
			$data['pages_tree'] = array();
			$parent_id = $this->request->post('parent_id');
			if ($parent_id != '') {
				$data['parent_id'] = $parent_id;
				$data['pages_tree'] = $this->resources->getDocuments(null,$parent_id);
			}
	        
			// PAGE DATA
			echo $this->tpl->factory('manager/resource_tree/resource_tree', $data);		
		}
	}
	
	public function action_set_toggles() {
		// PARAMS
		$this->auto_render = FALSE;		
		if ($this->role('manager')) {
			$this->session->set('opened_id_list', $this->request->post('opened_id'));
		}
	}
	
	public function action_get_toggles() {
		// PARAMS
		$this->auto_render = FALSE;	
		if ($this->role('manager')) {	
			echo $this->session->get('opened_id_list');
		}
	}

	public function action_set_tree_hidden() {
		// PARAMS
		$this->auto_render = FALSE;		
		if ($this->role('manager')) {
			$this->session->set('tree_hidden', $this->request->post('hidden'));
		}
	}

	/*
	 * CLEAR CACHE
	 */
	public function action_clear_cache() {
		// PARAMS
		$this->auto_render = FALSE;	
		if ($this->role('manager')) {
			$this->resources->clear_cache();
		}	
	}
	
	/*
	private function delete($id) {
		$data = $this->getPages($id);
		
		// DELETE IMAGES
		$dir = $_SERVER['DOCUMENT_ROOT']."/files/resources/".$id."/";
		if (file_exists($dir)) {
			// REMOVE FILES
			$mydir = opendir($dir);
			while(false !== ($file = readdir($mydir))) {
				if($file != "." && $file != "..") {
					chmod($dir.$file, 0777);
					if(is_dir($dir.$file)) {
						chdir('.');
						destroy($dir.$file.'/');
						rmdir($dir.$file) or DIE("couldn't delete $dir$file<br />");
					}
					else
						unlink($dir.$file) or DIE("couldn't delete $dir$file<br />");
				}
			}
			closedir($mydir);
			
			// REMOVE DIR
			rmdir($dir);
		}
		
		// DELETE PAGE
		$sql = "DELETE FROM site_page_contents
				WHERE site_page_contents.page_id = ".$this->db->escape($id)." ";
		$result = $this->db->query($sql);
		$sql = "DELETE FROM site_pages
				WHERE site_pages.id = ".$this->db->escape($id)." ";
		$result = $this->db->query($sql);
		
		// DELET SUB PAGES
		$data = $this->getPages(null, $id);
		for ($i=0; $i<count($data); $i++) {
			$this->delete($data[$i]['id']);
		}		
	}
	*/
	
	private function getTreeData(&$tree_data, $level, $parent_id, $parents_data) {
		for ($i=0; $i<count($parents_data); $i++) {
			if ($parents_data[$i]['parent_id'] == $parent_id) {
				$prefix = '';
				for ($j=0; $j<$level; $j++) $prefix .= '    ';
				$tree_data[] = array($prefix.$parents_data[$i]['admin_title'], '{$base_url}{page id='.$parents_data[$i]['id'].' name=full_alias}');
				$this->getTreeData($tree_data, $level+1, $parents_data[$i]['id'], $parents_data);
			}
		}
	}
}