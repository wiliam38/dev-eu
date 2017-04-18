<?php defined('SYSPATH') or die('No direct script access.');
 
class Controller extends Kohana_Controller {
	function before() {		
		// DB
		$this->db = new DB;	

		// USER AUTH
		$this->user = Auth::instance();
		
		// SESSION
		$this->session = Session::instance();	
		
		// PARAMS
		$this->base_url = URL::base(TRUE, FALSE);	
		$this->base_path = DOCROOT;	
		$this->doc_root = DOCROOT;
		
		// PROTOCOL
		$this->base_url_protocol = 'http://';
		if (strtolower(substr($this->base_url, 0, 8)) == 'https://') $this->base_url_protocol = 'https://';
		
		$this->user_id = $this->session->get('user_id');
		$this->lang_id = $this->session->get('lang_id');
		
		$this->user_data = array();
		if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
			$user_data = $this->user->userData($this->user_id);
			$this->user_data = $user_data[0];
		} elseif (!empty($this->user_id)) {
			$this->session->delete('user_id');
		}

		$route_name = Route::name($this->request->route());
		if ($route_name != 'manager' AND $route_name != 'manager_modules') {
			// SITE
			
			// CHECK LANG ID
			if (empty($this->lang_id)) $this->getLangFromUrl();
			
			// SET LOCALE
			$lang_tag = $this->session->get('lang_tag');
			SYSTEM::setLocale($lang_tag);
			
			// I18N
			I18n::lang('site-'.$lang_tag);
		}
	}
	
	private function getLangFromUrl() {
		// GET LANGUAGE
		if (isset($_SERVER['REQUEST_URI'])) {
			// THIS URL
			preg_match('/^\/([^\/]*)/', $_SERVER['REQUEST_URI'], $lang);
			if (!empty($lang[1])) {
				$lang_data = CMS::getLanguages(null, $lang[1]);
			}
						 
			// REFERER URL
			if (isset($_SERVER['HTTP_REFERER']) AND !isset($lang_data[0]['id'])) {
				preg_match('/^'.str_replace('/', '\/', $this->base_url).'([^\/]*)/', $_SERVER['HTTP_REFERER'], $lang);
				if (!empty($lang[1])) {
					$lang_data = CMS::getLanguages(null, $lang[1]); 
				}
			}		
		}
		
		// DEFAULT LANG ID
		if (!isset($lang_data[0]['id'])) {
			$lang_data = CMS::getLanguages(CMS::getSettings('default.lang_id', 'default'));
		}
		
		if (isset($lang_data[0]['id'])) {
			$this->session->set('lang_tag', $lang_data[0]['tag']);
			$this->session->set('lang_id', $lang_data[0]['id']);
			$this->lang_id = $lang_data[0]['id'];
		} 
	}
}