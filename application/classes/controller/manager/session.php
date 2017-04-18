<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Session extends Controller_Template {
	public $template = "manager/login/template";
	
	public function before() {
		parent::before();
		
		$this->tpl = $this->template;
		
		// PARAMS
		$this->tpl->base_url = URL::base(TRUE, FALSE);
		
		// I18N
		$this->manager_lang = Kohana::$config->load('manager.language');
		I18n::lang('manager-'.$this->manager_lang);
	}
	
	public function action_login() {		
		if ($this->user->logged_in('manager')) {
			$this->request->redirect('/manager/home/load');
		}	
		
		$user_name = $this->request->post('username');
		$password = $this->request->post('password');
		
		if ($user_name != '' && $password != '') {
			// CHECK USER
			$status = $this->user->login($user_name, $password,FALSE,'manager');

			if ($status)
			{								
				$this->request->redirect('/manager/home/load');
			}
			else
			{						
				$this->tpl->username = $user_name;
				$errors = $this->user->get_errors();
				
				switch ($errors[0]) {
					case '{{+user_login.error_inactive}}': $this->tpl->error = "User not activated!"; break;
					case '{{+user_login.error_no_rights}}': $this->tpl->error = "User have no rights to login!"; break;
					default: $this->tpl->error = "Wrong username or password!"; break;					
				}
			}
		}
	
		// INIT
		$this->template->css_file = array(	
			"assets/modules/manager/global/style.css",
			"assets/modules/manager/login/style.css",
			"assets/libs/jquery-ui-aristo/Aristo.css" );
		$this->template->js_file = array(	
			"assets/libs/jquery/jquery.min.js",
			"assets/libs/jquery-ui/jquery-ui.custom.min.js",
			"assets/modules/manager/login/login.js" );
	
		// SHOW LOGIN SCREEN
		$this->template->content = View::factory('manager/login/login');	
		
		
	}
	
	public function action_logout() {		
		$this->user->logout();
		
		$this->request->redirect('/manager/session/login');
	}
	
	public function action_active() {
		// PARAMS
		$this->auto_render = FALSE;
				
		if ($this->user->logged_in('manager')) {
			echo "1";
		} 
	}
}