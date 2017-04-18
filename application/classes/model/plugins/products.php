<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_Products extends Model {
	var $cateogry_setting_id = array(1, 2, 3, 4, 5, 7, 9, 18, 19, 20);
		
	public function load($parameters, $template, $page_data, &$page_class) {
		$this->page_class = $page_class;
		$this->page_data = $page_data;
		
		// PARAMS
		$category_id = null;
		$limit = null;
		$offset = null;
		$content_type_id = 10;
		$type = 'products'; 
		$display = 'array';
		
		// DISPLAY FROM SESSION
		if ($this->session->get('products_display') != '') $display = $this->session->get('products_display');
		
		$get_display = CMS::getGET('display');
		if (!empty($get_display)) {
			$display = $get_display;
			$this->session->set('products_display', $get_display);
		}
				
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			$$key = $val;
		}
		
		// PARAMS
		$this->limit = $limit;
		
		// CSS / JS
		$this->page_class->tpl->css_file[] = 'assets/plugins/products/products.css';
		$this->page_class->tpl->js_file[] = 'assets/plugins/products/products.js';
		
		// MODELS
		$this->products =  new Model_Manager_Products_Products($content_type_id);
		$this->categories = new Model_Manager_Products_Categories($content_type_id);
		
		// SUB PAGE ALIAS
		$this->page_alias = CMS::getPluginPageAlias($page_data);	
		
		// GET PRODUCT ID
		$product_reference_id = CMS::getPageAliasParam('k', $this->page_alias);
		$product_id = CMS::getPageAliasParam('i', $this->page_alias);
		$category_id = CMS::getPageAliasParam('c', $this->page_alias);
		
		// FILTER DATA
		$products_filter = $this->getProductFilter($category_id);	
			
		if (!empty($product_id)) {
			// SHOW PRODUCT
			$tpl_data = $this->showProduct($product_id, $category_id, $product_reference_id);
		} else  {
			// GET CATEGORIES
			$tpl_data = $this->showProductsList($category_id, $display, $products_filter);								
		}
		
		// GET CATEGORY
		$curr_categories = $this->categories->getCategories($category_id, $this->lang_id, array('from_status_id' => '10'));
		$tpl_data['left_menu'] = array();
		if (isset($curr_categories) AND count($curr_categories) > 0) {
			if ($curr_categories[0]['parent_id'] != 0) {
				$tmp_categories = $this->categories->getCategories($curr_categories[0]['parent_id'], $this->lang_id, array('from_status_id' => '10'));
				if (count($tmp_categories) > 0) $tpl_data['left_menu'][] = $tmp_categories[0];
			}			
			$tmp_categories = $this->categories->getCategories(null, $this->lang_id, array('parent_id' => $curr_categories[0]['parent_id'], 'from_status_id' => '10'));			
			$tpl_data['curr_category'] = $curr_categories[0];
		}
		
		// GET CATEGORY FILTER
		$tpl_data['category_id'] = $category_id;		
		$category_settings = $this->categories->getCategorySettings($this->cateogry_setting_id, $curr_categories[0]['id'], $this->lang_id, array('active' => 1));	
		for($i=0; $i<count($category_settings); $i++) {
			$category_settings[$i]['values'] = $this->categories->getCategorySettingValues(null, $category_settings[$i]['id'], $this->lang_id, array('active' => '1'));
		}	
		$tpl_data['category_settings'] = $category_settings;
		
		// GET PRICES
		$this->types = Model::factory('manager_types');
		$tpl_data['prices_filter'] = $this->types->getTypes(null, $this->lang_id, 'products_price_filter');
		
		// FILTER DATA
		$tpl_data['products_filter'] = $products_filter;		

		// DATA
		$tpl_data['page_data'] = $page_data['page_data'];	
				
		return $this->tpl->factory($template, $tpl_data);
	}
	
	// SHOW SUB CATEGORY
	private function showProductsList($category_id, $display, $products_filter) {
		$this->page_class->tpl->js_file[] = 'assets/libs/jquery-plugins/fullPage/jquery.fullPage2.min.js';	
		$this->page_class->tpl->js_file[] = 'assets/plugins/products/products-list.js';		
		
		// PAGINATE
		$paginate = CMS::getPageAliasParam('p', $this->page_alias);		
		$pages['cnt'] = null;
		$pages['limit'] = $this->limit;			
		$pages['page'] = $paginate;
		$pages['total_pages'] = null;
		$pages['offset'] = null;
		
		$filter_data = array(
			'category_id' => $category_id, 
			'from_status_id' => '10',
			'filter_category_setting_values' => $products_filter );

		switch($category_id) {
			case 1:
				// Kapsulas
				$filter_data['filter_category_setting_values_inactive'] = true;
				$products = $this->products->getProducts(null, $this->lang_id, $filter_data, null, null, 'ISNULL("l_coffee_order_index"), "l_coffee_order_index" ASC, "l_product_order_index" ASC, "product_contents.1_title" ASC');	
				
				// CATEGORIES
				$category_data = array();
				for ($i=0; $i<count($products); $i++) {
					$title = explode('-----', $products[$i]['l_settings_coffee_category']);					
					if (!isset($category_data[$title[0]])) $category_data[$title[0]] = array('title' => $title[0], 'cnt' => 0, 'active_cnt' => 0);
					$category_data[$title[0]]['cnt']++;
					$category_data[$title[0]]['active_cnt'] += ($products[$i]['inactive']==0?1:0);
				}	
				$tpl_data['category_data'] = $category_data;	
				
				break;
			case 2:
				// Aparāti
				if ($display == 'array') $tpl_data['show_all_colors'] = $filter_data['show_all_colors'] = true;
				$products = $this->products->getProducts(null, $this->lang_id, $filter_data, null, null, 'ISNULL("l_machines_order_index"), "l_machines_order_index" ASC, "l_product_order_index" ASC, "product_contents.1_title" ASC');	
				if ($display == 'list') {
					for ($i=0; $i<count($products); $i++) {
						$products[$i]['product_references'] = $this->products->getProductReferences(null, $products[$i]['id']);	
					}
				}
				break;
			case 8:
				$products = $this->products->getProducts(null, $this->lang_id, $filter_data, null, null, 'ISNULL("l_pro_order_index"), "l_pro_order_index" ASC, ISNULL("l_pro_order_index2"), "l_pro_order_index2" ASC, "l_product_order_index" ASC, "product_contents.1_title" ASC');	
				break;
			default:
				$products = $this->products->getProducts(null, $this->lang_id, $filter_data, null, null, 'ISNULL("l_accessories_order_index"), "l_accessories_order_index" ASC, "l_product_order_index" ASC, "product_contents.1_title" ASC');	
				break;
		}	
		$tpl_data['products'] = $products;
		
		// GET PRODUCT PRICES
		$products_id_list = array();
		foreach ($products as $key => $array) $products_id_list[] = $array['id'];
		if (!empty($products_id_list)) $prices = $this->products->getProductReferences(null, $products_id_list);
		else $prices = array();
		$prices_data = array();
		foreach ($prices as $key => $array) {
			if (!isset($prices_data[$array['product_id']])) $prices_data[$array['product_id']] = array();
			$prices_data[$array['product_id']][] = $array; 
		}
		$tpl_data['prices_data'] = $prices_data;
		
		// PAGE ALIAS
		$product_page = CMS::getDocuments(CMS::$products_page_id, null, null, $this->lang_id);
		$category_data = $this->categories->getCategories($category_id, $this->lang_id);
		$tpl_data['product_page_alias'] = $product_page[0]['full_alias'].'/'.$category_data[0]['l_full_alias'].'-c'.$category_data[0]['id'];
		
		$tpl_data['action'] = 'products_list';		
		$tpl_data['display'] = $display;
		
		return $tpl_data;
	}
	
	// SHOW PRODUCT
	private function showProduct($product_id, $category_id, $product_reference_id) {
		$this->page_class->tpl->js_file[] = 'assets/plugins/products/products-view.js';
		
		// GET PRODUCT
		$products = $this->products->getProducts($product_id, $this->lang_id, array('from_status_id' => '10'));
		
		// REDIRECT IF NO PRODUCT
		if (empty($products[0])) $this->page_class->redirect($this->page_data['full_alias']);
		
		// REFERENCES
		$tpl_data['product_references'] = $this->products->getProductReferences(null, $product_id);	
		$tpl_data['product_reference_id'] = $product_reference_id;	
		
		switch($category_id) {
			case 1:
				// Kapsulas
				
				// RECIPE PAGE
				$product_page = CMS::getDocuments(CMS::$recipes_page_id, null, null, $this->lang_id);
				$tpl_data['recipes_page_alias'] = $product_page[0]['full_alias'];
				
				// SETTINGS
				$tpl_data['cup_sizes'] = $this->products->getProductSettings($product_id, 2, $this->lang_id);
				
				break;
			case 2:
				// Aparāti
				
				// GALLERY
				$tpl_data['product_gallery'] = $this->products->getProductImages(null, $product_id, $this->lang_id);
				
				// SETTINGS
				$tpl_data['functions'] = $this->products->getProductSettings($product_id, 5, $this->lang_id);
				
				break;
			case 3:
				// Aksesuāri
				
				// COLLECTION
				$collection_products = array();
				$collection_id = explode('-----', $products[0]['l_collection']);
				if (!empty($collection_id)) {
					$filter_data = array(
						'from_status_id' => '10', 
						'category_id' => $category_id,
						'not_id' => $product_id,
						'category_setting_value_id' => $collection_id
					);
					$collection_products = $this->products->getProducts(null, $this->lang_id, $filter_data, 4, null, DB::expr('RAND()'));
				}
				$tpl_data['collection_products'] = $collection_products;
				
				break;
			case 8:
				// PRO
				
				// COLLECTION
				$collection_products = array();
				$collection_id = explode('-----', $products[0]['l_collection']);
				if (!empty($collection_id)) {
					$filter_data = array(
						'from_status_id' => '10', 
						'category_id' => $category_id,
						'not_id' => $product_id,
						'category_setting_value_id' => $collection_id
					);
					$collection_products = $this->products->getProducts(null, $this->lang_id, $filter_data, 4, null, DB::expr('RAND()'));
				}
				$tpl_data['collection_products'] = $collection_products;
				
				break;
		}
			
		// SHOW PRODUCT
		$tpl_data['product'] = $products[0];
		$tpl_data['action'] = 'product_view';
		
		// GALLERY
		$tpl_data['images'] = array();
		
		return $tpl_data;
	}

	function getProductFilter($category_id) {
		// GET OLD FILTER DATA
		$old_product_filters = $this->session->get('product_filter');
		$post_data = $this->page_class->request->post();
		$products_filter = array();
		
		$filter_inputs = array();
		if ($category_id == 1) $filter_inputs = array('filter_intensity', 'filter_cup_size', 'filter_flavor');
		elseif ($category_id == 2) $filter_inputs = array('filter_price', 'filter_type', 'filter_function');
		elseif ($category_id == 3) $filter_inputs = array('filter_category', 'filter_product_type');
		elseif ($category_id == 8) $filter_inputs = array('filter_pro_category');
		
		if (!empty($post_data['filter_post'])) {
			foreach ($filter_inputs as $key => $val) $products_filter[$val] = isset($post_data[$val])?$post_data[$val]:array();
		} else {
			foreach ($filter_inputs as $key => $val) $products_filter[$val] = isset($old_product_filters[$val])?$old_product_filters[$val]:array();
		}
		
		$this->session->set('product_filter', $products_filter);
		
		return $products_filter;
	}
}