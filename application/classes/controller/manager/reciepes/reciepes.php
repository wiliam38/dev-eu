<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Reciepes_Reciepes extends Controller_Manager_Class {
	public $limit = 25;
	
	public function before() {
		parent::before();
		
		$this->reciepes = Model::factory('manager_reciepes_reciepes');
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			// PAGES
			$pages['cnt'] = $this->reciepes->getReciepes(null, null, array(), null, null, true);
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			$pages['show_pages'] = 5;
						
			$tpl_data['paginate'] = $pages;
			
			// GET PRODUCTS
			$tpl_data['reciepes'] = $this->reciepes->getReciepes(null, null, array(), $pages['limit'], $pages['offset']);	
			
			// DATA PANEL
			$tpl_data['action'] = 'list';	
			$tpl_data['def_lang_id'] = CMS::getSettings('default.lang_id');	
			$this->tpl->data_panel = $this->tpl->factory('manager/reciepes/reciepes',$tpl_data);
		}
	}
	
	public function action_edit() {
		if ($this->initForm('manager')) {
			// ID
			$reciepe_id = $this->request->param('id');
			
			// CSS / JS
			$this->tpl->js_file[] = 'assets/libs/ckeditor/ckeditor.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->css_file[] = 'assets/modules/manager/reciepes/reciepes_edit.css';
			$this->tpl->js_file[] = 'assets/modules/manager/reciepes/reciepes_edit.js';
			
			$this->tpl->css_file[] = 'assets/libs/jquery-ui/jquery-ui-timepicker-addon.css';
			$this->tpl->js_file[] = 'assets/libs/jquery-ui/jquery-ui-timepicker-addon.js';	
						
			// GET PRODUCTS
			if (empty($reciepe_id)) $reciepe_id = 'new';
			if ($reciepe_id != 'new') {
				$reciepes = $this->reciepes->getReciepes($reciepe_id);
				$tpl_data['reciepe'] = $reciepes[0];
			}
			
			// OTHER
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['reciepe_status'] = CMS::getStatus('reciepe_contents_status_id');			
			
			// GALLERY
			$tpl_data['gallery_data'] = $this->reciepes->getReciepeImages(null, $reciepe_id);
			
			// DIFFICULTY
			$tpl_data['difficulties'] = CMS::getTypes('reciepes_difficulty_type_id');
			
			// MATERIALS
			$tpl_data['linked_products'] = $this->reciepes->getReciepeMaterials(null, $reciepe_id, CMS::$lang_id);
			
			$tpl_data['return_url'] = $this->request->referrer();
					
			// DATA PANEL
			$tpl_data['action'] = 'load';
			$this->tpl->data_panel = $this->tpl->factory('manager/reciepes/reciepe_edit',$tpl_data);
		}
	}

	public function action_load_lang_tab() {
		if ($this->role('manager')) {
			// PARAMS
			$this->auto_render = FALSE;	
			
			// PAGE DATA
			$lang_data = $this->request->post('lang_data');
			$reciepe = array();
			if (!empty($lang_data)) {				
				foreach($lang_data as $key => $val) {
					switch (substr(strstr($key,'_'),1)) {
						case 'reciepe_content_id': $reciepe['lang'][$this->request->post('lang_id')]['id'] = 'new'; break; 
						case 'language_id': $reciepe['lang'][$this->request->post('lang_id')]['language_id'] = $this->request->post('lang_id'); break; 
						case 'status_id': $reciepe['lang'][$this->request->post('lang_id')]['status_id'] = '1'; break; 
						case 'image_src':
							$reciepe['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = FILES::copyToTmp($val); 
							break;
						default:
							$reciepe['lang'][$this->request->post('lang_id')][substr(strstr($key,'_'),1)] = $val; 
							break;
					}
				}
			}			
			$reciepe['lang'][$this->request->post('lang_id')]['id'] = 'new';	
			$tpl_data['reciepe'] = $reciepe;
			
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			// STATUS
			$tpl_data['reciepe_status'] = CMS::getStatus('reciepe_contents_status_id');
			
			$tpl_data['action'] = "lang_tab";
			echo $this->tpl->factory('manager/reciepes/reciepe_edit',$tpl_data);
		}
	}

	public function action_remove_lang_tab() {
		// PARAMS
		$tpl_data['action'] = "lang_tab_empty";
		
		if ($this->role('manager')) {
			// PARAMS
			$this->auto_render = FALSE;	
			
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			// REMOVE TMP IMAGES
			$lang_data = $this->request->post('lang_data');
			foreach($lang_data as $key => $val) {
				switch (substr(strstr($key,'_'),1)) {
					case 'image_src':
						FILES::removeTmpFile($val); 
						break;
				}
			}
			
			echo $this->tpl->factory('manager/reciepes/reciepe_edit',$tpl_data);
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
			
			echo $this->tpl->factory('manager/reciepes/reciepe_edit',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {			
			$reciepe_id = $this->reciepes->save($this->request->post());
		}
		
		if ($this->request->post('return_url') != '') $this->request->redirect($this->request->post('return_url'));
		else $this->request->redirect('manager/reciepes_reciepes/load');
	}
	
	public function action_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->reciepes->delete($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
	
	// LINKED PRODUCTS
	public function action_linked_products() {
		$this->auto_render = FALSE;
				
		if ($this->role('manager')) {
			$term = $_POST['term'];
			
			$sql = "SELECT DISTINCT
						CONCAT(product_contents.1_title,' (', IFNULL(categories.title,''), ') ')   		AS value,
						products.id			   														AS id
					FROM
						products
						LEFT JOIN product_contents ON
							product_contents.product_id = products.id AND
							product_contents.language_id = :lang_id
						LEFT JOIN product_categories ON
							products.id = product_categories.product_id
						LEFT JOIN categories ON
							product_categories.category_id = categories.id
					WHERE
						products.status_id > 0 AND
						CONCAT(product_contents.1_title,' (', IFNULL(categories.title,''), ') ') LIKE :term
					ORDER BY 
						product_contents.1_title
					LIMIT 50 ";
			$result = $this->db->query(Database::SELECT, $sql);		
			$term = '%'.$term.'%';
			$result->bind(':term', $term);	
			$result->bind(':lang_id', CMS::$lang_id);		
			$data = $result->execute()->as_array();	
			
			echo json_encode($data);
		}
	}
	public function action_load_linked_item() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			// GET LANGUAGES
			$id = $_POST['id'];
			$product_class = new Model_Manager_Products_Products(10);
			$product_data = $product_class->getProducts($id, CMS::$lang_id);
			if (count($product_data) > 0) {
				$data['data'] = $product_data[0];
				$data['action'] = "material_edit";
				
				echo $this->tpl->factory('manager/reciepes/reciepe_edit',$data);
			}
		}
	}
}