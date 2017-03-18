<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_Categories extends Model {	
	public function load($parameters, $template, $page_data, $page_class) {
		// PARAMS
		$parent_id = 0;
		$content_type_id = 10;
		$limit = null;
		$offset = null;
				
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			if ($key == 'limit') $$key = " LIMIT ".(int)$val;
			else $$key = $val;
		}
		
		// PAGE ID
		$page_id = CMS::$products_page_id;
		
		// MODELS
		$this->categories = new Model_Manager_Products_Categories($content_type_id);
		
		// CSS / JS
		//$page_class->tpl->css_file[] = 'assets/plugins/products/categories.css';
		
		// SUB PAGE ALIAS
		$page_alias = CMS::getPluginPageAlias($page_data);
		
		// GET CURR CATEGORY
		$category_id = CMS::getPageAliasParam('c', $page_alias);
		$curr_category[0] = array();
		if (!empty($category_id)) $curr_category = $this->categories->getCategories($category_id, $this->lang_id, array('from_status_id' => '10'));
		$tpl_data['curr_category'] = $curr_category[0];
				
		// ROOT LIST
		$parent_id_list = array();
		if (!empty($curr_category[0]['root_id_list'])) {
			$parents_list = explode('---', $curr_category[0]['root_id_list']);
			for($i=0; $i<count($parents_list); $i++) if (!empty($parents_list[$i])) $parent_id_list[] = $parents_list[$i];
		}
		$tpl_data['root_id'] = $parent_id_list;
				
		// GET CATEGORIES
		$categories = $this->categories->getCategories(null, $this->lang_id, array('parent_id' => $parent_id, 'from_status_id' => '10'));
		for ($i=0; $i<count($categories); $i++) $parent_id_list[] = $categories[$i]['id'];
		
		$tpl_data['categories'] = $categories;
		
		// GET OTHER CATEGORIES
		//$tmp_categories = array();
		//if (!empty($parent_id_list)) $tmp_categories = $this->categories->getCategories(null, $this->lang_id, array('parent_id' => $parent_id_list, 'from_status_id' => '10'));
		
		//$tpl_data['categories'] = array_merge($categories, $tmp_categories);
		
		$product_page_data = CMS::getDocuments($page_id, null, null, $this->lang_id);
		$tpl_data['page_data'] = isset($product_page_data[0]['id'])?$product_page_data[0]:array();
		
		$tpl_data['action'] = 'categories';		
		return $this->tpl->factory($template, $tpl_data);
	}
}