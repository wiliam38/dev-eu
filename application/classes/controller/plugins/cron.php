<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Plugins_Cron extends Controller_Main {
	public $template = 'site/template/tmp';
	
	public function before() {
		parent::before();		
		
		// PARAMS
		$this->auto_render = FALSE;
		
		ini_set('max_execution_time', '0');
		ini_set('memory_limit', '-1');
	}
	
		
	public function action_jobs() {
		$cron_class = Model::factory('site_cron'); 
		
		// CHANGE PAGE STATUS
		$cron_class->updatePageStatus(array('1'));
			
		// CLEAR SESSION
		$cron_class->clearSessionData();		
		
		// CLEAR TMP DIR
		$cron_class->clearTmpDir();
	}
}