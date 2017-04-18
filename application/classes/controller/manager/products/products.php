<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Products_Products extends Controller_Manager_Class {
	// CATEGORY CONTENT
	var $content_type_id = 10;
	var $title_list = 'Products';
	var $title_edit = 'Product';
	var $link = 'products_products';
	var $limit = 20;
	
	public function before() {
		parent::before();
		
		$this->products = new Model_Manager_Products_Products($this->content_type_id);
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			$this->tpl->css_file[] = 'assets/modules/manager/products/products.css';
			$this->tpl->js_file[] = 'assets/modules/manager/products/products.js';
			
			// DEFAULT LANGUAGE
			$tpl_data['lang_id'] = CMS::getSettings('default.lang_id');
			
			// GET FILTER
			if (!empty($_REQUEST)) $filter_data = $this->request->post();
			
			$preg_match = str_replace('/','\/',$this->base_url.'manager/products_products/');
			if (!isset($_REQUEST['search'])) {
				$filter_data = array(
					'search' => '',
					'status_id' => array(1, 10) );
			}
			
			// PAGES
			$pages['cnt'] = $this->products->getProducts(null, null, $filter_data, null, null, array(), true);	
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			if ($pages['offset'] < 0) $pages['offset'] = 0;			
			$pages['show_pages'] = 5;
			
			$tpl_data['paginate'] = $pages;	
			
			// ORDER BY
			if (empty($filter_data['order_by'])) $filter_data['order_by'] = '1-a';
			
			$order_data = explode('-',$filter_data['order_by']);
			if (empty($order_data)) $order_data = array('1','a');
			$order_by = '"reference_reference" ASC';
			if (isset($order_data[0])) {
				switch ($order_data[0]) {
					case '1': default: $order_by = '"reference_reference"'; break;
					case '2': $order_by = '"product_contents.1_title"'; break;
					case '3': $order_by = '"category_contents.title"'; break;
					case '4': $order_by = '"status.name"'; break;
					case '5': $order_by = '"balance"'; break;
				}
				if (isset($order_data[1]) && $order_data[1] == 'd') $order_by .= ' DESC';
				else $order_by .= ' ASC';
			}
			
			// GET PRODUCTS
			$tpl_data['products'] = $this->products->getProducts(null, null, $filter_data, $pages['limit'], $pages['offset'], $order_by);	
			$tpl_data['product_status'] = CMS::getStatus('products_status_id');
			
			// CATEGORIES
			$this->categories = new Model_Manager_Products_Categories($this->content_type_id);
			$tpl_data['categories'] = $this->categories->getCategories();			
			
			// DATA PANEL
			$tpl_data['filter'] = $filter_data;
			$tpl_data['action'] = 'list';	
			$tpl_data['page_title'] = $this->title_list;
			$tpl_data['link'] = $this->link;	
			$this->tpl->data_panel = $this->tpl->factory('manager/products/products',$tpl_data);
		}
	}
	
	public function action_details() {
		// PARAMS
		$this->auto_render = FALSE;
			
		$product_id = $this->request->post('product_id');
		if ($this->initForm('manager') && is_numeric($product_id)) {
			$product_data = $this->products->getProducts($product_id);
			if (count($product_data) == 1) {
				// PRICES
				$tpl_data['prices'] = $this->products->getProductReferences(null, $product_id);
				
				$tpl_data['action'] = 'details';	
				echo $this->tpl->factory('manager/products/products', $tpl_data)->render();
			}
		}
	}
	
	public function action_balance_update() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'balance' => 0,
			'product_balance' => 0);
		
		$product_reference_id = $this->request->post('product_reference_id');
		$balance = $this->request->post('balance');
		if ($this->initForm('manager') && is_numeric($product_reference_id) && is_numeric($balance)) {
			$ref_data = $this->products->getProductReferences($product_reference_id);
			if (count($ref_data) == 1) {
				$this->db->update('product_references')
					->set(array('balance' => $balance))
					->where('product_references.id', '=', $product_reference_id)
					->execute();
			}
		}
		
		if (is_numeric($product_reference_id)) {
			$ref_data = $this->products->getProductReferences($product_reference_id);
			if (count($ref_data) == 1) {
				$ret_data['balance'] = number_format($ref_data[0]['balance'],0,'.','');
				$ret_data['product_balance'] = number_format($ref_data[0]['product_balance'],0,'.','');
			}	
		} 
		
		echo json_encode($ret_data);
	}	
	public function action_balance_add() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'balance' => 0,
			'product_balance' => 0);
		
		$product_reference_id = $this->request->post('product_reference_id');
		$add_qty = $this->request->post('add_qty');
		if ($this->initForm('manager') && is_numeric($product_reference_id) && is_numeric($add_qty)) {
			$ref_data = $this->products->getProductReferences($product_reference_id);
			if (count($ref_data) == 1) {		
				$this->db->update('product_references')
					->set(array('balance' => $ref_data[0]['balance'] + $add_qty))
					->where('product_references.id', '=', $product_reference_id)
					->execute();
			}
		}
		
		if (is_numeric($product_reference_id)) {
			$ref_data = $this->products->getProductReferences($product_reference_id);
			if (count($ref_data) == 1) {
				$ret_data['balance'] = number_format($ref_data[0]['balance'],0,'.','');
				$ret_data['product_balance'] = number_format($ref_data[0]['product_balance'],0,'.','');
			}
		} 
		
		echo json_encode($ret_data);
	}
	
	public function action_edit() {
		if ($this->initForm('manager')) {
			// ID
			$product_id = $this->request->param('id');
			
			// CSS / JS
			$this->tpl->js_file[] = 'assets/libs/ckeditor/ckeditor.js';
			
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/swfobject.js';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/uploadify/jquery.uploadify.v2.1.4.min.js';
			
			$this->tpl->css_file[] = 'assets/modules/manager/pages/pages.css';
			$this->tpl->css_file[] = 'assets/modules/manager/products/products_edit.css';
			$this->tpl->js_file[] = 'assets/modules/manager/products/products_edit.js';
						
			// GET PRODUCTS
			$products = $this->products->getProducts($product_id);
			$tpl_data['product'] = !empty($products[0])?$products[0]:array();
			
			// CATEGORIES
			$categories_class = new Model_Manager_Products_Categories($this->content_type_id);
			$categories = $categories_class->getCategories();
			for ($i=0; $i<count($categories); $i++) {
				$categories[$i]['settings'] = $categories_class->getCategorySettings(null, $categories[$i]['id'], CMS::$lang_id);
				for ($j=0; $j<count($categories[$i]['settings']); $j++) {
					$categories[$i]['settings'][$j]['values'] = $categories_class->getCategorySettingValues(null, $categories[$i]['settings'][$j]['id'], CMS::$lang_id);
				}
			}
			$tpl_data['categories'] = $categories;
			
			$setting_values = $this->products->getProductCategorySettingValues($product_id);
			$selected_setting_values = array();
			foreach ($setting_values as $key => $val) $selected_setting_values[] = $val['category_setting_value_id'];
			$tpl_data['selected_setting_values'] = $selected_setting_values;
			
			
			$product_categories = $this->products->getProductCategories($product_id);
			$categories_list = array();
			foreach($product_categories as $key => $value) $categories_list[] = $value['id'];
			$tpl_data['category_array'] = $categories_list;
			
			// GALLERY
			$tpl_data['gallery_data'] = $this->products->getProductImages(null, $product_id);
			
			// PRICES
			$tpl_data['prices'] = $this->products->getProductReferences(null, $product_id);
			
			// VIDEO TYPES
			$tpl_data['video_types'] = CMS::getTypes('product_contents_3_video_type_id');
			
			// RECIPES
			$this->recipe_model = Model::factory('manager_reciepes_reciepes');
			$tpl_data['recipes'] = $this->recipe_model->getReciepes();
			
			// OTHER
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['currencies'] = $this->products->getCurrencies();
			$tpl_data['status'] = CMS::getStatus('products_status_id');
			$tpl_data['units'] = CMS::getTypes('products_unit_type_id');
			$tpl_data['vat_types'] = CMS::getTypes('products_vat_type_id');
			$tpl_data['filter'] = $this->request->post();
			
			// DEF LANG ID
			$tpl_data['def_lang_id'] = CMS::getSettings('default.lang_id');
			
			$tpl_data['return_url'] = $this->request->referrer();
					
			// DATA PANEL
			$tpl_data['action'] = 'load';
			$tpl_data['page_title'] = $this->title_edit;
			$tpl_data['link'] = $this->link;	
			$this->tpl->data_panel = $this->tpl->factory('manager/products/product_edit',$tpl_data);
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
					switch ($key) {
						case 'product_content_id': $product['lang'][$this->request->post('lang_id')]['id'] = 'new'; break; 
						case 'language_id': $product['lang'][$this->request->post('lang_id')]['language_id'] = $this->request->post('lang_id'); break; 
						case '0_enabled':
						case '2_enabled':
						case '3_enabled':
						case '4_enabled':
						case '5_enabled':
						case '6_enabled':
						case '7_enabled':
						case '8_enabled':
						case '9_enabled':
							$product['lang'][$this->request->post('lang_id')][$key] = '1'; 
							break; 
						case '1_image_src':
						case '4_image_src':
						case '4_manual_src':	
						case '5_image_src':
						case '6_image_src':
						case '7_image_src':
						case '8_image_src':
						case '9_image_src':
							$product['lang'][$this->request->post('lang_id')][$key] = $this->files->copyToTmp($val[0]); 
							break;
						default:
							$product['lang'][$this->request->post('lang_id')][$key] = $val[0]; 
							break;
					}
				}
			}
			$product['lang'][$this->request->post('lang_id')]['id'] = 'new';
			$tpl_data['product'] = $product;		
			
			// VIDEO TYPES
			$tpl_data['video_types'] = CMS::getTypes('product_contents_3_video_type_id');
			
			// RECIPES
			$this->recipe_model = Model::factory('manager_reciepes_reciepes');
			$tpl_data['recipes'] = $this->recipe_model->getReciepes();
						
			$tpl_data['action'] = "lang_tab";
			echo $this->tpl->factory('manager/products/product_edit',$tpl_data);
		}
	}

	public function action_remove_lang_tab() {
		// PARAMS
		$this->auto_render = FALSE;
		$tpl_data['action'] = "lang_tab_empty";
		
		if ($this->role('manager')) {
			// LANGUAGES
			$languages = CMS::getLanguages($this->request->post('lang_id'));
			$tpl_data['lang'] = $languages[0];
			
			// REMOVE TMP IMAGES
			$lang_data = $this->request->post('lang_data');
			foreach($lang_data as $key => $val) {
				switch ($key) {
					case '1_image_src':
					case '4_image_src':
					case '4_manual_src':	
					case '5_image_src':
					case '6_image_src':
					case '7_image_src':
					case '8_image_src':
					case '9_image_src':
						FILES::removeTmpFile($val[0]); 
						break;
				}
			}
			
			echo $this->tpl->factory('manager/products/product_edit',$tpl_data);
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
			
			echo $this->tpl->factory('manager/products/product_edit',$tpl_data);
		}
	}
	
	public function action_load_price_row() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			// GET LANGUAGES
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);	
			$tpl_data['action'] = "prices_tab_edit";
			
			echo $this->tpl->factory('manager/products/product_edit',$tpl_data);
		}
	}
	
	public function action_load_recipe() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			// RECIPES
			$this->recipe_model = Model::factory('manager_reciepes_reciepes');
			$recipes = $this->recipe_model->getReciepes($this->request->post('recipe_id'), $this->request->post('lang_id'));
			$tpl_data['recipe'] = $recipes[0];
			
			$tpl_data['action'] = "recipe_view";
			$tpl_data['base_url'] = $this->base_url;
			
			echo $this->tpl->factory('manager/products/product_edit',$tpl_data);
		}
	}
	
	public function action_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$roduct_reference_id = $this->request->post('product_reference_id');
						
			if (!is_array($roduct_reference_id) || count($roduct_reference_id) == 0) {
				$status['error'] = __('Add at least one "Color / Icon"!');
				$status['status'] = '0';
			} else {						
				$this->products->save($this->request->post());
				$status['status'] = '1';
			}
		} else {
			$status['error'] = 'No rights!';
		}
		
		echo json_encode($status);
	}
	
	public function action_delete() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {			
			$status = $this->products->delete($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);
	}
	
	/*
	 * ORDER
	 */
	public function action_order() {
		if ($this->initForm('manager')) {
			$this->tpl->css_file[] = 'assets/libs/jquery-plugins/tablednd/jquery.tablednd.css';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/tablednd/jquery.tablednd.js';
			$this->tpl->js_file[] = 'assets/modules/manager/products/products_order.js';
			
			
			// FILTER DATA
			$filter_data = array(
				'status_id' => array(1, 10, 20),
				'category_setting_value_id' => $this->request->post('category_setting_value_id')!=''?array($this->request->post('category_setting_value_id')):array(-1) );
				
			// GET PRODUCTS			
			$tpl_data['products'] = $this->products->getProducts(null, CMS::$lang_id, $filter_data);	
			
			// CATEGORIES
			$this->categories = new Model_Manager_Products_Categories($this->content_type_id);
			$categories = $this->categories->getCategories(null, null, array('parent_id' => 0));
			
			for ($i=0; $i<count($categories); $i++) {
				switch ($categories[$i]['id']) {
					case 1:
						$categories[$i]['sub_categories'] = $this->categories->getCategorySettingValues(null, 17, CMS::$lang_id);
						break;
					case 2:
						$categories[$i]['sub_categories'] = $this->categories->getCategorySettingValues(null, 4, CMS::$lang_id);
						break;
					case 3:
						$categories[$i]['sub_categories'] = $this->categories->getCategorySettingValues(null, 7, CMS::$lang_id);
						break;
					case 8:
						$categories[$i]['sub_categories'] = $this->categories->getCategorySettingValues(null, array(18,19,20), CMS::$lang_id);
						break;
					default:
						$categories[$i]['sub_categories'] = array();
						break;
				}
			}
			$tpl_data['categories'] = $categories;
			
			// DATA PANEL
			$tpl_data['filter'] = $filter_data;
			$tpl_data['action'] = 'list';
			$this->tpl->data_panel = $this->tpl->factory('manager/products/products_order',$tpl_data);
		}
	}
	public function action_order_save() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if ($this->role('manager')) {
			$this->products->order_save($this->request->post());
		} else {
			$status['error'] = 'No rights!';
		}

		echo json_encode($status);	
	}
}