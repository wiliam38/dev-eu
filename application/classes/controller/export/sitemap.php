<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Export_Sitemap extends Controller_Main {
	public $template = 'site/template/tmp';
		
	public function action_load() {		
		// PARAMS
		$this->auto_render = FALSE;
		
		$type = CMS::getGET('type');
		if (empty($type)) $type = 'google';
		
		// LINKS
		$links_data = array();
		
		//
		// GET ALL PAGES
		//
		$this->resources = Model::factory('manager_resources');
		$pages = $this->resources->getDocuments(null, null, null, null, array('sitemap' => '1'), array('1'));
		foreach($pages as $key => $link) {
			foreach($link['lang'] as $key => $lang) {
				$links[] = array(
					'link' => $this->base_url.$lang['full_page_alias'],
					'last_change' => $lang['datetime'],
					'change_frequency' => 'monthly',
					'priority' => '0.5' );
			}
		}

		// RENDER XML
		$this->tpl->links = $links;
		switch ($type) {
			case 'google':
			default:
				$xml = $this->tpl->render('export/sitemap/google');
				break;
		}		
		
		$this->response->headers('Content-Type', 'text/xml');
		$this->response->body($xml);		
	}
	
	
}