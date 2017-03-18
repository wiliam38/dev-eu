<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_Recipes extends Model {
	
	public function __construct() {
		parent::__construct();
		
		$this->recipes = Model::factory('manager_reciepes_reciepes');
	}	
	
	public function load($parameters, $template, $page_data, $page_class) {
		// PARAMS
		$limit = 5;		
		$sticky_limit = 0;
		
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			$$key = $val;
		}
		
		// CSS / JS
		$page_class->tpl->css_file[] = 'assets/plugins/recipes/recipes.css';
		$page_class->tpl->js_file[] = 'assets/plugins/recipes/recipes.js';
		
		// SUB PAGE ALIAS
		$page_alias = CMS::getPluginPageAlias($page_data);	
		
		// PAGINATE
		$paginate = CMS::getPageAliasParam('p', $page_alias);
		if (empty($paginate)) $paginate = 1;
		
		// RECIPE ID
		$recipe_id = CMS::getPageAliasParam('i', $page_alias); 
		if (!empty($recipe_id)) {
			// GET RECIPES
			$recipes = $this->recipes->getReciepes($recipe_id, $this->lang_id, array('from_status_id' => '10', 'page_alias' => $page_alias));
		}
		
		if (empty($recipes[0]['id'])) {
			//
			// LIST
			//
			
			//
			// GET RECIPES
			//
			$filter_data = array(
				'from_status_id' => '10',
				'coffee_id' => $page_class->request->post('coffee_id') );
				
			// GET RECIPES
			$tpl_data['recipes'] = $this->recipes->getReciepes(null, $this->lang_id, $filter_data);
			
			// GET COFFEE
			$this->products =  new Model_Manager_Products_Products(10);
			$filter_data['category_id'] = 1;
			$tpl_data['coffee'] = $this->products->getProducts(null, $this->lang_id, $filter_data, null, null, 'ISNULL("l_coffee_order_index"), "l_coffee_order_index" ASC, "l_product_order_index" ASC, "product_contents.1_title" ASC');	
				
			// FORM DATA
			$tpl_data['post_data'] = $page_class->request->post();
			
			// RECIPES PAGE
			$recipes_page = CMS::getDocuments(CMS::$recipes_page_id, null, null, $this->lang_id);
			$tpl_data['recipes_page'] = $recipes_page[0];
			
			$tpl_data['action'] = 'list';		
			return $this->tpl->factory($template, $tpl_data);			
		} else {
			//
			// VIEW
			//
			$tpl_data['recipe'] = $recipes[0];
			
			// MATERIALS
			$tpl_data['materials'] = $this->recipes->getReciepeMaterials(null, $recipe_id, $this->lang_id, array('not_category_id' => '1'));
			
			// COFFEE
			$tpl_data['coffee'] = $this->recipes->getReciepeMaterials(null, $recipe_id, $this->lang_id, array('category_id' => '1'));
			
			$tpl_data['page'] = $page_data['page_data'];
			
			$tpl_data['action'] = 'view';		
			return $this->tpl->factory($template, $tpl_data);
		}		
	}

	public function replace_latvian($title) {
		$title = str_replace('Ā','A',$title);
		$title = str_replace('ā','a',$title);
		$title = str_replace('č','c',$title);
		$title = str_replace('Č','C',$title);
		$title = str_replace('ē','e',$title);
		$title = str_replace('Ē','E',$title);
		$title = str_replace('ģ','g',$title);
		$title = str_replace('Ģ','G',$title);
		$title = str_replace('ī','i',$title);
		$title = str_replace('Ī','I',$title);
		$title = str_replace('ķ','k',$title);
		$title = str_replace('Ķ','K',$title);
		$title = str_replace('ļ','l',$title);
		$title = str_replace('Ļ','L',$title);
		$title = str_replace('ņ','n',$title);
		$title = str_replace('Ņ','N',$title);
		$title = str_replace('ō','o',$title);
		$title = str_replace('Ō','O',$title);
		$title = str_replace('š','s',$title);
		$title = str_replace('Š','S',$title);
		$title = str_replace('ū','u',$title);
		$title = str_replace('Ū','U',$title);
		$title = str_replace('Ž','Z',$title);	
		$title = str_replace('ž','z',$title);	
		
		return $title;
	} 
}