<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Users extends Controller_Manager_Class {
	public $limit = 20;
		
	public function action_load() {
		if ($this->initForm('manager')) {
			$users = Model::factory('manager_users');
			
			// FILTER
			$filter_data = $this->request->post();
			if (empty($filter_data['order_by'])) $filter_data['order_by'] = '1-a';
			$data['filter_data'] = $filter_data;
			
			// PAGES
			$pages['cnt'] = $users->getUsers(null, array_merge($filter_data, array('count' => true)));
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			$pages['show_pages'] = 5;
						
			$data['paginate'] = $pages;
			
			// GET DATA
			$order_data = explode('-',$filter_data['order_by']);
			if (empty($order_data)) $order_data = array('1','a');
			$order_by = 'CONCAT("users.first_name",\' \',"users.last_name") ASC';
			if (isset($order_data[0])) {
				switch ($order_data[0]) {
					case '1': default: $order_by = 'CONCAT("users.first_name",\' \',"users.last_name")'; break;
					case '2': $order_by = '"users.username"'; break;
					case '3': $order_by = '"users.email"'; break;
					case '4': $order_by = '"status.name"'; break;
					case '5': $order_by = '"users.last_login"'; break;
					case '6': $order_by = 'IFNULL("users.num_logins",0)'; break;
					case '7': $order_by = 'IF(IFNULL("users.pro_category",0) = 1, CONCAT(\'X (\', ROUND("users.pro_coffee_coef",2), \',\', ROUND("users.pro_machines_coef",2), \',\', ROUND("users.pro_accessories_coef",2), \')\'), NULL)'; break;
					case '8': $order_by = '"users.company"'; break;
					case '9': $order_by = '"users.creation_datetime"'; break;
				}
				if (isset($order_data[1]) && $order_data[1] == 'd') $order_by .= ' DESC';
				else $order_by .= ' ASC';
			}
			$data['users'] = $users->getUsers(null, $filter_data, $pages['limit'], $pages['offset'], $order_by);
			$data['action'] = 'load';	
			
			// STATUS
			$data['status'] = CMS::getStatus('users_status_id');
			
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(	'assets/modules/manager/users/users.js'));
			
			// DATA PANEL
			$this->tpl->data_panel = $this->tpl->factory('manager/users/users',$data);
		}
	}

	public function action_excel() {
		if ($this->role('manager')) {
			$ws = new Spreadsheet(array(
		    	'author'       => 'Kohana-PHPExcel',
		    	'title'	       => 'Report',
		    	'subject'      => 'Subject',
		    	'description'  => 'Description',
		    ));
		    
		    $ws->set_active_sheet(0);
		    $as = $ws->get_active_sheet();
		    $as->setTitle('Lietotāji');
		    
		    $as->getDefaultStyle()->getFont()->setSize(12);
		    
		    $as->getColumnDimension('A')->setWidth(20);
		    $as->getColumnDimension('B')->setWidth(20);
		    $as->getColumnDimension('C')->setWidth(30);
		    $as->getColumnDimension('D')->setWidth(20);
		    $as->getColumnDimension('E')->setWidth(16);
		    $as->getColumnDimension('F')->setWidth(16);
		    $as->getColumnDimension('G')->setWidth(16);
		    $as->getColumnDimension('H')->setWidth(30);
		    $as->getColumnDimension('I')->setWidth(20);
		    $as->getColumnDimension('J')->setWidth(20);
		    
			$data = array(1 => array(
				'Vārds', 
				'Uzvārds', 
				'E-pasts', 
				'Kompānija', 
				'PRO sadaļa', 
				'Pasūtījumu skaits', 
				'Pasūtījumu summa', 
				'Statuss', 
				'Reģistrācijas datums', 
				'Pēdējais apmeklējums' ));
		    
			// GET DATA
			$this->users = Model::factory('manager_users');
			$users = $this->users->getUsers(null, $this->request->post());
			for ($i=0; $i<count($users); $i++) {
				$pro_category = '';
				if (!empty($users[$i]['pro_category'])) $pro_category = 'X ('.number_format($users[$i]['pro_coffee_coef'],2,'.','').', '.number_format($users[$i]['pro_machines_coef'],2,'.','').', '.number_format($users[$i]['pro_accessories_coef'],2,'.','').')';
				
				$data[($i+2)] = array(
					$users[$i]['first_name'],
					$users[$i]['last_name'],
					$users[$i]['email'],
					$users[$i]['company'],
					$pro_category,
					$users[$i]['orders_cnt'],
					number_format($users[$i]['orders_amount'],2,'.',''),
					__($users[$i]['status_description']),
					$users[$i]['creation_datetime'],
					$users[$i]['last_login']
				);
			}

		    $ws->set_data($data, false);
		    $ws->send(array('name'=>'lietotaji', 'format'=>'Excel5'));
		}
	}
}