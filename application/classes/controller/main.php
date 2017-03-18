<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Main extends Controller_Template {	
	public $tmplate = "manager/template";
	
	public function before() {
		parent::before();
		
		// RENAME SMARTY VARIABLE
		$this->tpl = &$this->template;	
		
		// INCLUDES
		$this->tpl->css_file = array();
		$this->tpl->js_file = array();
		
		$this->tpl->base_url = $this->base_url;

	}	
	
	
	
	
	
	
	
	
	
	
	
	
}