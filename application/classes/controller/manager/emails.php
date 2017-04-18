<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Emails extends Controller_Manager_Class {
	public function before() {
		parent::before();
		
		// MODELS
		$this->emails = Model::factory('manager_emails');
	}
		
	public function action_load() {
		if ($this->initForm('admin')) {
		
			// GET DATA
			$data['emails'] = $this->emails->getEmails(null, array(), 100);
			$data['action'] = 'load';
			
			// PARAMS
			$this->tpl->css_file[] = 'assets/modules/manager/emails/emails.css';
			$this->tpl->js_file[] = 'assets/modules/manager/emails/emails.js';
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/emails/emails',$data);
		}
	}
	
	public function action_send_emails() {
		$this->auto_render = FALSE;
		$this->emails->send_all_emails(100);
	}
	
	public function action_cancel_email() {
		$this->auto_render = FALSE;
		
		$id = $this->request->param('id');
		$this->emails->cancel_email($id);
	}
}