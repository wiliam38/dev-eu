<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Plugins_Ajax extends Controller_Main {
	public $template = 'site/template/tmp';
	
	public function before() {
		parent::before();		
		
		// PARAMS
		$this->auto_render = FALSE;
	}
	
		
	public function action_lexicon() {
		echo CMS::getLexicons($this->request->post('name'));
	}
}