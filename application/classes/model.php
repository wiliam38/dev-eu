<?php defined('SYSPATH') or die('No direct script access.');
 
class Model extends Kohana_Model {
	function __construct() {
		// DB
		$this->db = new DB;	

		// USER AUTH
		$this->user = Auth::instance();
		
		// SESSION
		$this->session = Session::instance();
		
		// SMARTY
		$this->tpl = new View;
		
		// PARAMS	
		$this->base_url = URL::base(TRUE, FALSE);	
		$this->base_path = DOCROOT;	
		$this->doc_root = DOCROOT;
		
		$this->user_id = $this->session->get('user_id');
		$this->lang_id = $this->session->get('lang_id');
		
		$this->user_data = array();
		if ($this->user->logged_in('site') || $this->user->logged_in('manager')) {
			$user_data = $this->user->userData($this->user_id);
			$this->user_data = $user_data[0];
		}
	}	
}