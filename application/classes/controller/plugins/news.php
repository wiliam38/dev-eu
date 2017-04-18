<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Plugins_News extends Controller_Main {
	public $template = 'site/template/tmp';
	
	public function before() {
		parent::before();		
		
		// PARAMS
		$this->auto_render = FALSE;
		$this->news = Model::factory('Plugins_News');
	}
	
		
	public function action_vote() {
		$ret_data = $this->news->vote($this->request->post());
		echo json_encode($ret_data);
	}
}