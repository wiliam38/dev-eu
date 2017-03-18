<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Home extends Controller_Manager_Class {	
	public function action_load() {
		if ($this->initForm('manager')) {		
			// DATA PANEL
			$this->tpl->data_panel = "Sveicināti satura vadības sitēmā!";
		}
	}
}