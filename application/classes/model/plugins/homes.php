<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_Homes extends Model {
	
	public function __construct() {
		parent::__construct();
		
		$this->homes = Model::factory('manager_homes_homes');
	}	
	
	public function load($parameters, $template, $page_data, $page_class) {
		// PARAMS
		
		// CSS / JS		
		$page_class->tpl->css_file[] = 'assets/plugins/homes/homes.css';
		$page_class->tpl->js_file[] = 'assets/plugins/homes/homes.js';
		
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			$$key = $val;
		}

		//
		// GET HOMES
		//
		$tpl_data['homes'] = $this->homes->getHomes(null, $this->lang_id, array('from_status_id' => '10'));
		
		$tpl_data['action'] = 'list';		
		return $this->tpl->factory($template, $tpl_data);			
	}
}