<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Plugins_Class extends Controller_Main {
	public $template = "manager/template";	

	public function before() {
		parent::before();
		
		// PARAMS
		$this->auto_render = FALSE;
	}	
	
	public function action_load() {
		exit();
	}

}