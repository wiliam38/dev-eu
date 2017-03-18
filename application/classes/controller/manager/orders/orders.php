<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Orders_Orders extends Controller_Manager_Class {
	var $limit = 25;
	
	public function before() {
		parent::before();
		
		$this->orders = Model::factory('manager_orders_orders');
	}
		
	public function action_load() {
		if ($this->initForm('manager')) {
			// CSS / JS
			$this->tpl->css_file[] = 'assets/modules/manager/orders/orders_list.css';
			$this->tpl->js_file[] = 'assets/modules/manager/orders/orders_list.js';
			
			$this->tpl->css_file[] = 'assets/libs/jquery-plugins/multiselect/jquery.multiSelect.iss.css';
			$this->tpl->js_file[] = 'assets/libs/jquery-plugins/multiselect/jquery.multiSelect.js';
			
			// PARAMS
			$filter_data['status_id'] = array('10','20','30','40');
			if (isset($_REQUEST['filter_status_id'])) $filter_data['status_id'] = $_REQUEST['filter_status_id'];
			$filter_data['search'] = $this->request->post('filter_search');
			$filter_data['order_by'] = $this->request->post('order_by');
			if (empty($filter_data['order_by'])) $filter_data['order_by'] = '1-d';
			$tpl_data['filter'] = $filter_data;
			
			// FILTER
			$order_filter_data = array('in_status_id' => $filter_data['status_id'], 'search' => $filter_data['search']);
			
			// PAGES
			$pages['cnt'] = $this->orders->getOrders(null, null, $order_filter_data, null, null, array(), true);	
			$pages['limit'] = $this->limit;			
			$pages['total_pages'] = ceil($pages['cnt'] / $pages['limit']);
			$pages['page'] = CMS::getGET('p');
			if ($pages['page'] < 1) $pages['page'] = 1;
			if ($pages['page'] > $pages['total_pages']) $pages['page'] = $pages['total_pages'];
			$pages['offset'] = $pages['limit'] * ($pages['page'] - 1);	
			if ($pages['offset'] < 0) $pages['offset'] = 0;			
			$pages['show_pages'] = 5;
			
			$tpl_data['paginate'] = $pages;	
			
			// ORDER BY			
			$order_data = explode('-',$filter_data['order_by']);
			if (empty($order_data)) $order_data = array('1','d');
			$order_by = '"orders.date" DESC';
			if (isset($order_data[0])) {
				switch ($order_data[0]) {
					case '1': default: $order_by = '"orders.date"'; break;
					case '2': $order_by = '"orders.number"'; break;
					case '3': $order_by = 'IF(IFNULL("orders.no_vat",0) != 1,
												SUM(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * "order_details.qty",2)) + ROUND("orders.shipping_total" * (1 + "orders.shipping_vat" / 100),2),
												SUM(ROUND("order_details.price" * "order_details.qty",2)) + ROUND("orders.shipping_total",2) )'; break;
					case '4': $order_by = 'CONCAT("orders.first_name", \' \', "orders.last_name")'; break;
					case '5': $order_by = '"status.name"'; break;
					case '6': $order_by = 'SUM("order_details.qty" * (CASE WHEN "orders.status_id" >= 10 THEN IFNULL("order_details.coffee_gift_amount",0) ELSE (CASE WHEN "products.coffee_gift_active" = 1 THEN IFNULL("products.coffee_gift_amount",0) ELSE 0 END) END))'; break;
					case '7': $order_by = '"pay_status.name"'; break;
					case '8': $order_by = 'IF("orders.pay_status_id"=10,IFNULL("orders.pay_date",\'3000-01-01\'),\'3000-01-01\')'; break;
				}
				if (isset($order_data[1]) && $order_data[1] == 'd') {
					$order_by .= ' DESC';
					if ($order_data[0] == 1) $order_by .= ', "orders.number" DESC ';
				} else {
					$order_by .= ' ASC';
					if ($order_data[0] == 1) $order_by .= ', "orders.number" ASC ';
				}
			}
			
			// GET PRODUCTS
			$tpl_data['orders'] = $this->orders->getOrders(null, null, $order_filter_data, $pages['limit'], $pages['offset'], $order_by);	
			
			// ALT
			$tpl_data['order_status'] = CMS::getStatus('orders_status_id');
			
			// DATA PANEL
			$tpl_data['action'] = 'list';	
			$this->tpl->data_panel = $this->tpl->factory('manager/orders/list',$tpl_data);
		}
	}

	public function action_details() {
		// PARAMS
		$this->auto_render = FALSE;
			
		$order_id = $this->request->post('order_id');
		if ($this->role('manager') && is_numeric($order_id)) {
			$order_data = $this->orders->getOrders($order_id);
			if (count($order_data) == 1) {
				$tpl_data['order'] = $order_data[0];
				$order_details = $this->orders->getOrderDetails(null, $order_id);
				$tpl_data['products'] = $order_details;
				
				// TOTALS
				$total_data = array(	'total' => 0,
								'total_vat' => 0,
								'vat' => 0 );
								
				$total_data =  $this->orders->getOrderTotal($order_id);	
				$total_data['vat'] = round($total_data['vat'],2);		
				$total_data['total_vat'] = round($total_data['price'],2);		
		
				$total_data['total_vat'] += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100),2);
				$total_data['vat'] += round($order_data[0]['shipping_total'] * ($order_data[0]['shipping_vat'] / 100),2);
				$tpl_data['total'] = $total_data;
				
				$tpl_data['action'] = 'details';	
				echo $this->tpl->factory('manager/orders/list', $tpl_data)->render();
			}
		}
	}

	public function action_status() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
			
		$order_id = $this->request->post('order_id');
		$status_id = $this->request->post('status_id');
		if ($this->role('manager') && is_numeric($order_id) && in_array($status_id, array(1,5,10,20,50))) {
			$order_data = $this->orders->getOrders($order_id);
			if (count($order_data) == 1) {
				$this->orders->setStatus($order_id, $status_id);
				$order_data = $this->orders->getOrders($order_id);
				
				// UPDATE NUMBER
				if ($status_id == 10 && empty($order_data[0]['real_number'])) {
					$sql = "SELECT IFNULL(MAX(orders.number),0) AS nr FROM orders";
					$db_data = $this->db->query(Database::SELECT, $sql)->execute()->as_array();
					$number = str_pad($db_data[0]['nr'] + 1, 3, '0', STR_PAD_LEFT);
					
					$this->db->update('orders')
						->set(array('number' => $number))
						->where('orders.id', '=', $order_data[0]['id'])
						->execute();			
						
					$order_data = $this->orders->getOrders($order_data[0]['id']);		
				}
				
				$tpl_data['data'] = $order_data[0];
				$tpl_data['action'] = 'view';	
				$ret_data['response'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();
				
				// VIEW DETAIL
				$tpl_data['order'] = $order_data[0];
				$order_details = $this->orders->getOrderDetails(null, $order_id);
				$tpl_data['products'] = $order_details;
				
				// TOTALS
				$total_data = array(	'total' => 0,
								'total_vat' => 0,
								'vat' => 0 );								
				$total_data =  $this->orders->getOrderTotal($order_id);	
				$total_data['vat'] = round($total_data['vat'],2);		
				$total_data['total_vat'] = round($total_data['price'],2);		
				$total_data['total_vat'] += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100),2);
				$total_data['vat'] += round($order_data[0]['shipping_total'] * ($order_data[0]['shipping_vat'] / 100),2);
				$tpl_data['total'] = $total_data;				
				$tpl_data['action'] = 'details';	
				$ret_data['response_detail'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();				
				
				$ret_data['status'] = 1;				
			}
		}
		
		echo json_encode($ret_data);
	}
	
	public function action_pay_status() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
			
		$order_id = $this->request->post('order_id');
		$pay_status_id = $this->request->post('pay_status_id');
		$pay_date = $this->request->post('pay_date');
		if ($this->role('manager') && is_numeric($order_id) && is_numeric($pay_status_id)) {
			$order_data = $this->orders->getOrders($order_id);
			if (count($order_data) == 1) {
				$this->orders->setPayStatus($order_id, $pay_status_id, $pay_date);
				$order_data = $this->orders->getOrders($order_id);
				
				$tpl_data['data'] = $order_data[0];
				$tpl_data['action'] = 'view';	
				$ret_data['response'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();
				$ret_data['status'] = 1;				
			}
		}
		
		echo json_encode($ret_data);
	}
	
	/*
	 * INVOICES
	 */
	public function action_bill() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$order_id = $this->request->param('id');
			if (is_numeric($order_id)) {
				if (file_exists($this->base_path.'files/orders/invoice-'.$order_id.'.pdf')) {
					$this->request->redirect($this->base_url.'files/orders/invoice-'.$order_id.'.pdf');
				} else {
					// CREATE BILL
					$this->orders->createSendPDF($order_id, false);
					
					$this->request->redirect($this->base_url.'files/orders/invoice-'.$order_id.'.pdf');
				}
			} else {
				echo 'ERROR!';
			}
		} else {
			echo 'ERROR!';
		}
	}
	
	public function action_issue_invoice() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
		
		if ($this->role('manager')) {
			$order_id = $this->request->post('order_id');
			$type = $this->request->post('type');
			
			if (is_numeric($order_id)) {
				// ISSUE PAVADZIME
				$invoice_id = $this->orders->issuePavadzime($order_id, $type, $this->request->post('issue_shipping'), $this->request->post('issue_order_detail_id'), $this->request->post());
				
				// VIEW ROW
				$order_data = $this->orders->getOrders($order_id);				
				$tpl_data['data'] = $order_data[0];
				$tpl_data['action'] = 'view';	
				$ret_data['response'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();
				
				// VIEW DETAIL
				$tpl_data['order'] = $order_data[0];
				$order_details = $this->orders->getOrderDetails(null, $order_id);
				$tpl_data['products'] = $order_details;
				
				// TOTALS
				$total_data = array(	'total' => 0,
								'total_vat' => 0,
								'vat' => 0 );								
				$total_data =  $this->orders->getOrderTotal($order_id);	
				$total_data['vat'] = round($total_data['vat'],2);		
				$total_data['total_vat'] = round($total_data['price'],2);		
				$total_data['total_vat'] += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100),2);
				$total_data['vat'] += round($order_data[0]['shipping_total'] * ($order_data[0]['shipping_vat'] / 100),2);
				$tpl_data['total'] = $total_data;				
				$tpl_data['action'] = 'details';	
				$ret_data['response_detail'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();				
				
				$ret_data['status'] = '1';
			} else {
				$ret_data['error'] = 'ERROR!';
			}
		} else {
			$ret_data['error'] = 'ERROR!';
		}
		
		echo json_encode($ret_data);
	}

	public function action_popup_invoices() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
		
		if ($this->role('manager')) {
			$order_id = $this->request->post('order_id');
			
			// GET PAVADZIMES
			$inv_data = $this->orders->getInvoices(null, $order_id);
			
			if (count($inv_data) == 1) {
				// OPEN PAVADZĪME
				$ret_data['response'] = $this->base_url.'manager/orders_orders/invoice/'.$inv_data[0]['id'];
				$ret_data['status'] = '1';
			} else {
				// SHOW POPUP
				$tpl_data['invoices'] = $inv_data;
				$tpl_data['action'] = 'popup_invoices';
				$ret_data['response'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();
				$ret_data['status'] = '2';
			}
			
		} else {
			$ret_data['error'] = 'ERROR!';
		}
		
		echo json_encode($ret_data);
	}	
	public function action_invoice() {
		// PARAMS
		$this->auto_render = FALSE;
		
		if ($this->role('manager')) {
			$invoice_id = $this->request->param('id');
			
			if (is_numeric($invoice_id)) {
				if (!file_exists($this->base_path.'files/orders/pavadzime-'.$invoice_id.'.pdf')) $this->orders->createPavadzime($invoice_id);
				$this->request->redirect($this->base_url.'files/orders/pavadzime-'.$invoice_id.'.pdf');
			} else {
				echo 'ERROR!';
			}
		} else {
			echo 'ERROR!';
		}
	}
	public function action_recreate_invoices() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
		
		if ($this->role('manager')) {
			$order_id = $this->request->post('order_id');
			if (is_numeric($order_id)) {
				$order_data = $this->orders->getOrders($order_id);

				if (isset($order_data[0]['status_id'])) { // && $order_data[0]['status_id'] >= 10) {
					// CREATE BILL
					$this->orders->createSendPDF($order_id, false);
					
					// CREATE PAVADZIME
					$invoices_data = $this->orders->getInvoices(null, $order_id);
					for ($i=0; $i<count($invoices_data); $i++) $this->orders->createPavadzime($invoices_data[$i]['id']);
				}					
				$ret_data['status'] = '1';
			} else {
				$ret_data['error'] = 'ERROR!';
			}
		} else {
			$ret_data['error'] = 'ERROR!';
		}
		
		echo json_encode($ret_data);
	}
	
	public function action_excel_popup() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => 1,
			'error' => '',
			'response' => '' );
		
		if ($this->role('manager')) {
			$tpl_data['action'] = 'popup';
			$ret_data['response'] = $this->tpl->factory('manager/orders/export_xml', $tpl_data)->render();
			$ret_data['status'] = '1';
		}
		
		echo json_encode($ret_data);
	}
	public function action_excel() {
		if ($this->role('manager')) {
			$ws = new Spreadsheet(array(
		    	'author'       => 'Kohana-PHPExcel',
		    	'title'	       => 'Report',
		    	'subject'      => 'Subject',
		    	'description'  => 'Description',
		    ));
			
			// GET DATA
			$date_from = $this->request->post('date_from');
			$date_to = $this->request->post('date_to');
			$type = $this->request->post('type');
			if (!is_array($type)) $type = array();
			$sheet_nr = 0;
		    
			/*
			 * Pasūtījumi
			 */
			if (in_array('ordered', $type)) {				
			    $ws->set_active_sheet($sheet_nr);
			    $as = $ws->get_active_sheet();
			    $as->setTitle('Pasūtījumi');
			    
			    $as->getDefaultStyle()->getFont()->setSize(12);
			    
			    $as->getColumnDimension('A')->setWidth(25);
			    $as->getColumnDimension('B')->setWidth(15);
			    $as->getColumnDimension('C')->setWidth(30);
			    $as->getColumnDimension('D')->setWidth(15);
			    $as->getColumnDimension('E')->setWidth(17);
			    $as->getColumnDimension('F')->setWidth(15);
			    
				$data = array(1 => array(
					'Pasūtijuma Nr.', 
					'Datums', 
					'Pasūtītājs', 
					'Summa', 
					'Apmaksas datums',
					'Statuss'
				));
			    
				// GET DATA
				$orders = $this->orders->getOrders(null, null, array('date_from' => $date_from, 'date_to' => $date_to, 'from_status_id' => 10), null, null, '"orders.date" ASC, "orders.number" ASC');
				for ($i=0; $i<count($orders); $i++) {
					$data[($i+2)] = array(
						'19B'.$orders[$i]['number'], 
						date('d.m.Y', strtotime($orders[$i]['date'])), 
						($orders[$i]['company']!=''?$orders[$i]['company']:$orders[$i]['contact_name']), 
						number_format($orders[$i]['total_total'],2,'.',''), 
						empty($orders[$i]['pay_date'])?'':date('d.m.Y', strtotime($orders[$i]['pay_date'])), 
						($orders[$i]['pay_status_id']>=10?'Apmaksāts':'Nav apmaksāts')
					);
				}

		    	$ws->set_data($data, false);
				
				$sheet_nr++;
		    }

			/*
			 * Avansa maksājumi
			 */
			if (in_array('paid', $type)) {
				if ($sheet_nr > 0) $ws->add_sheet();					
												
			    $ws->set_active_sheet($sheet_nr);
			    $as = $ws->get_active_sheet();
			    $as->setTitle('Avansa maksājumi');
			    
			    $as->getDefaultStyle()->getFont()->setSize(12);
			    
			    $as->getColumnDimension('A')->setWidth(25);
			    $as->getColumnDimension('B')->setWidth(17);
			    $as->getColumnDimension('C')->setWidth(30);
			    $as->getColumnDimension('D')->setWidth(15);
			    
				$data = array(1 => array(
					'Pasūtijuma Nr.', 
					'Apmaksas datums', 
					'Pasūtītājs', 
					'Avansa summa'
				));
			    
				// GET DATA
				$payments = $this->orders->getAdvancePayments($date_from, $date_to);
				for ($i=0; $i<count($payments); $i++) {
					$data[($i+2)] = array(
						'19B'.$payments[$i]['number'], 
						empty($payments[$i]['pay_date'])?'':date('d.m.Y', strtotime($payments[$i]['pay_date'])), 
						($payments[$i]['company']!=''?$payments[$i]['company']:$payments[$i]['contact_name']), 
						number_format($payments[$i]['total_total'],2,'.','')
					);
				}

		    	$ws->set_data($data, false);
				
				$sheet_nr++;
		    }

			/*
			 * Pavadzīmes
			 */
			if (in_array('issued', $type)) {
				if ($sheet_nr > 0) $ws->add_sheet();					
												
			    $ws->set_active_sheet($sheet_nr);
			    $as = $ws->get_active_sheet();
			    $as->setTitle('Pavadzīmes');
			    
			    $as->getDefaultStyle()->getFont()->setSize(12);
			    
			    $as->getColumnDimension('A')->setWidth(25);
			    $as->getColumnDimension('B')->setWidth(15);
			    $as->getColumnDimension('C')->setWidth(30);
			    $as->getColumnDimension('D')->setWidth(15);
			    $as->getColumnDimension('E')->setWidth(17);
			    
				$data = array(1 => array(
					'Pavadzīmes Nr.', 
					'Datums', 
					'Pasūtītājs', 
					'Summa',
					'Apmaksas datums'
				));
			    
				// GET DATA
				$invoices = $this->orders->getInvoices(null, null, array('date_from' => $date_from, 'date_to' => $date_to));
				for ($i=0; $i<count($invoices); $i++) {
					$data[($i+2)] = array(
						'19B'.$invoices[$i]['full_number'], 
						date('d.m.Y', strtotime($invoices[$i]['date'])), 
						($invoices[$i]['company']!=''?$invoices[$i]['company']:$invoices[$i]['contact_name']), 
						number_format($invoices[$i]['total_total'],2,'.',''),
						empty($invoices[$i]['pay_date'])?'':date('d.m.Y', strtotime($invoices[$i]['pay_date']))
					);
				}

		    	$ws->set_data($data, false);
				
				$sheet_nr++;
		    }

			$ws->set_active_sheet(0);
		    $ws->send(array('name'=>'pasutijumi_'.date('d-m-Y', strtotime($date_from)).'_'.date('d-m-Y', strtotime($date_to)), 'format'=>'Excel5'));
		}
	}

	/*
	 * COFFEE GIFT
	 */
	public function action_coffee_gift_popup() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => 0,
			'error' => '',
			'response' => '' );
		
		if ($this->role('manager')) {
			$order_id = $this->request->post('order_id');
			if (is_numeric($order_id)) {
				$order_data = $this->orders->getOrders($order_id);
				if (count($order_data) == 1) {				
					$tpl_data['order'] = $order_data[0];
					
					$tpl_data['action'] = 'popup';
					$ret_data['response'] = $this->tpl->factory('manager/orders/coffee_gift_popup', $tpl_data)->render();
					$ret_data['status'] = '1';				
				}
			}
		}
		
		echo json_encode($ret_data);
	}
	public function action_coffee_gift_status() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$ret_data = array(
			'status' => '0',
			'error' => '',
			'response' => '');
			
		$order_id = $this->request->post('order_id');
		$status_id = $this->request->post('status_id');
		if ($this->role('manager') && is_numeric($order_id)) {
			$order_data = $this->orders->getOrders($order_id);
			if (count($order_data) == 1) {
				$this->orders->setCoffeeGiftStatus($order_id, $status_id);
				$order_data = $this->orders->getOrders($order_id);
				
				// VIEW
				$tpl_data['data'] = $order_data[0];
				$tpl_data['action'] = 'view';	
				$ret_data['response'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();
				
				// VIEW DETAIL
				$tpl_data['order'] = $order_data[0];
				$order_details = $this->orders->getOrderDetails(null, $order_id);
				$tpl_data['products'] = $order_details;
				
				// TOTALS
				$total_data = array(	'total' => 0,
								'total_vat' => 0,
								'vat' => 0 );								
				$total_data =  $this->orders->getOrderTotal($order_id);	
				$total_data['vat'] = round($total_data['vat'],2);		
				$total_data['total_vat'] = round($total_data['price'],2);		
				$total_data['total_vat'] += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100),2);
				$total_data['vat'] += round($order_data[0]['shipping_total'] * ($order_data[0]['shipping_vat'] / 100),2);
				$tpl_data['total'] = $total_data;				
				$tpl_data['action'] = 'details';	
				$ret_data['response_detail'] = $this->tpl->factory('manager/orders/list', $tpl_data)->render();		
				
				$ret_data['status'] = 1;				
			}
		}
		
		echo json_encode($ret_data);
	}
}