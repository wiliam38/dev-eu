<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Export_Products extends Controller_Main {
	public $template = 'site/template/tmp';
		
	public function action_load() {		
		// PARAMS
		$this->auto_render = FALSE;
		
		// GET PARAMS
		$lang_id = CMS::getSettings('default.lang_id');
		$lang_tag = $this->request->param('lang');
		if (!empty($lang_tag)) {
			$lang_data = CMS::getLanguages(null, $lang_tag, 10);
			$lang_id = $lang_data[0]['id'];
			$lang_tag = $lang_data[0]['tag'];
		} 
		
		//
		// GET PRODUCTS
		//
		$products = $this->getProducts($lang_id);
		
		// RENDER XML
		$this->tpl->products = $products;
		$this->tpl->lang_tag = $lang_tag;
		$this->tpl->product_page = CMS::getDocuments(CMS::$products_page_id, null, null, $lang_id);
		$xml = $this->tpl->render('export/products/shop');		
		
		$this->response->headers('Content-Type', 'text/xml');
		$this->response->body($xml);	
	}
	
	public function action_salidzini() {
		// PARAMS
		$this->auto_render = FALSE;
				
		// GET PARAMS
		$lang_id = CMS::getSettings('default.lang_id');
		$lang_tag = $this->request->param('lang');
		if (!empty($lang_tag)) {
			$lang_data = CMS::getLanguages(null, $lang_tag, 10);
			$lang_id = $lang_data[0]['id'];
			$lang_tag = $lang_data[0]['tag'];
		} 
		
		//
		// GET PRODUCTS
		//
		$products = $this->getProducts($lang_id);
		
		// RENDER XML
		$this->tpl->products = $products;
		$this->tpl->lang_tag = $lang_tag;
		$this->tpl->product_page = CMS::getDocuments(CMS::$products_page_id, null, null, $lang_id);
		$xml = $this->tpl->render('export/products/salidzini');		
		
		$this->response->headers('Content-Type', 'text/xml');
		$this->response->body($xml);		
	}

	public function action_kurpirkt() {
		// PARAMS
		$this->auto_render = FALSE;
				
		// GET PARAMS
		$lang_id = CMS::getSettings('default.lang_id');
		$lang_tag = $this->request->param('lang');
		if (!empty($lang_tag)) {
			$lang_data = CMS::getLanguages(null, $lang_tag, 10);
			$lang_id = $lang_data[0]['id'];
			$lang_tag = $lang_data[0]['tag'];
		} 
		
		//
		// GET PRODUCTS
		//
		$products = $this->getProducts($lang_id);
		
		// RENDER XML
		$this->tpl->products = $products;
		$this->tpl->lang_tag = $lang_tag;
		$this->tpl->product_page = CMS::getDocuments(CMS::$products_page_id, null, null, $lang_id);
		$xml = $this->tpl->render('export/products/kurpirkt');		
		
		$this->response->headers('Content-Type', 'text/xml');
		$this->response->body($xml);				
	}
	
	function getProducts($lang_id) {
		if (!is_numeric($lang_id)) $lang_id = 0;
		
		// CATEGORY LINK
		$category_link = $this->db->select(
			'CONCAT("category_contents.full_alias",\'-c\',"categories.id")')		
		->from('product_categories')
		->join('categories')
			->on('product_categories.category_id', '=', 'categories.id')
		->join('category_contents')
			->on('categories.id', '=', 'category_contents.category_id')
			->on('category_contents.language_id', '=', DB::expr($lang_id))
		->where('product_categories.product_id', '=', DB::expr('products.id'))
		->order_by('product_categories.order_index')
		->order_by('product_categories.id')
		->limit(1);
		
		// CATEGORY FULL NAME
		$category_full_name = $this->db->select(
			'CONCAT(IF("parent_category_contents.alias" IS NULL,\'\',CONCAT("parent_category_contents.title",\' >> \')),"category_contents.title")')		
		->from('product_categories')
		->join('categories')
			->on('product_categories.category_id', '=', 'categories.id')
		->join('category_contents')
			->on('categories.id', '=', 'category_contents.category_id')
			->on('category_contents.language_id', '=', DB::expr($lang_id))
		->join(array('categories', 'parent_categories'), 'LEFT')
			->on('categories.parent_id', '=', 'parent_categories.id')
		->join(array('category_contents', 'parent_category_contents'), 'LEFT')
			->on('parent_category_contents.category_id', '=', 'parent_categories.id')
			->on('parent_category_contents.language_id', '=', DB::expr($lang_id))
		->where('product_categories.product_id', '=', DB::expr('products.id'))
		->order_by('product_categories.order_index')
		->order_by('product_categories.id')
		->limit(1);
		
		// CATEGORY NAME
		$category_name = $this->db->select(
			'category_contents.title')		
		->from('product_categories')
		->join('categories')
			->on('product_categories.category_id', '=', 'categories.id')
		->join('category_contents')
			->on('categories.id', '=', 'category_contents.category_id')
			->on('category_contents.language_id', '=', DB::expr($lang_id))
		->where('product_categories.product_id', '=', DB::expr('products.id'))
		->order_by('product_categories.order_index')
		->order_by('product_categories.id')
		->limit(1);
		
		// PRODUCT MAIN IMAGE
		$main_image_src = $this->db->select('product_references.image_src')
			->from('product_references')
			->where('product_references.product_id', '=', DB::expr('products.id'))
			->order_by('product_references.order_index')
			->order_by('product_references.id')
			->limit(1);
		
		$products = $this->db->select(
			array('products.id', 'id'),
			array('IF("products.discount_active" = 1,"products.discount_price","products.price")', 'price'),
			array('IF("products.discount_active" = 1,"products.discount_price","products.price") * (1 + ("vat_types.value" / 100))', 'price_vat'),
			array('product_contents.1_title', 'title'),
			array('product_contents.alias', 'alias'),
			array($main_image_src, 'image_src'),
			array($category_link, 'category_full_alias'),
			array($category_name, 'category_name'),
			array($category_full_name, 'category_full_name'))
		->from('products')
		->join('product_contents')
			->on('products.id', '=', 'product_contents.product_id')
			->on('product_contents.language_id', '=', DB::expr($lang_id))
		->join('product_images', 'LEFT')
			->on('products.main_image_id', '=', 'product_images.id')
		->join(array('types', 'vat_types'))
			->on('products.vat_type_id', '=', 'vat_types.type_id')
			->on('vat_types.table_type_name', '=', DB::expr("'products_vat_type_id'"))
		->where('products.status_id', '>=', '10')
		->order_by('products.id')
		->execute()
		->as_array();

		return $products;
	}
}