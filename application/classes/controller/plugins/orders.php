<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Plugins_Orders extends Controller_Main {
	public $template = 'site/template/tmp';
	
	public function before() {
		parent::before();		
		
		// PARAMS
		$this->auto_render = FALSE;
		
		$this->orders = Model::factory('manager_orders_orders');
		$this->orders_plugin = Model::factory('plugins_orders');
		$this->orders_plugin->template = 'plugins/orders/current_order';
	}
	
	public function action_current_order() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
		
		$status['response'] = $this->orders_plugin->showOrderList();	
		
		
		echo json_encode($status);
	}
	
	public function action_configure() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
		
		if ($this->user->logged_in('site')) {
			$status['status'] = 1;
			$status['response'] = $this->orders_plugin->showConfigure();	
		}
		
		
		echo json_encode($status);
	}	
		
	public function action_add_to_order() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '',
							'button' => '',
							'qty' => '0',
							'item_qty' => '0',
							'total' => '0',
							'currency' => CMS::getSettings('order.default_curr_symbol'));
		
		if ($this->user->logged_in('site')) {
			// ADD PRODUCT TO ORDER
			$status = $this->orders->add_to_order($_REQUEST);
			
			// CART DATA
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			$total_data = $this->orders->getOrderTotal($order[0]['id']);
			
			$status['qty'] = $total_data['qty'];
			$status['item_qty'] = $total_data['item_qty'];
			$status['price'] = $total_data['price'];
			$status['currency'] = $total_data['curr_symbol'];
			$status['button'] = '<div class="added-to-cart">'.__('products.buy_added').'</div>';
		} else {
			// SAVE BOOK TO SESSION
			$this->session->set('cart_product_id', $_REQUEST['product_id']);
			
			$login = CMS::getDocuments(44,null,null,$this->lang_id);
			
			$status = array(	'status' => 2,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => $login[0]['full_alias']);
		}
		
		echo json_encode($status);
	}
	
	public function action_checkout() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
		
		if ($this->user->logged_in('site')) {
			// CHECKOUT
			$status = $this->orders->check_checkout($_REQUEST);
			
			if ($status['status'] == 1) $status['response'] = $this->orders_plugin->showCheckout();
		} else {
			$status = array(	'status' => 0,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => '');
		}
		
		echo json_encode($status);
	}
	
	public function action_payment() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
		
		if ($this->user->logged_in('site')) {
			// CHECKOUT
			$status = $this->orders->check_payment($_REQUEST);
			
			if ($status['status'] == 1) $status['response'] = $this->orders_plugin->showCheckout2();
		} else {
			$status = array(	'status' => 0,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => '');
		}
		
		echo json_encode($status);
	}
	
	public function action_payment2() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
		
		if ($this->user->logged_in('site')) {
			// CHECKOUT
			$status = $this->orders->check_payment2($_REQUEST);
			
			if ($status['status'] == '2') {
				// PAY WITH CARD
				$status['response'] = $this->orders_plugin->showPayWithCard();			
			} else if ($status['status'] == '1') {
				// PLACED
				$status['response'] = $this->orders_plugin->showConfirm();			 	
			}			
		} else {
			$status = array(	'status' => 0,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => '');
		}
		
		echo json_encode($status);
	}

	public function action_confirm() {
		$status = array(	'status' => 1,
							'error' => '',
							'response' => '');
		
		if ($this->user->logged_in('site')) {
			$result = $this->orders->check_confirm($_REQUEST);
			
			if ($result) {
				// PLACED
				$status['response'] = $this->orders_plugin->showPaid();
			} else {
				$status = array(	'status' => 0,
									'error' => CMS::getLexicons('orders.no_user'),
									'response' => '');
			}			
		} else {
			$status = array(	'status' => 0,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => '');
		}
		
		echo json_encode($status);
	}
	
	public function action_paid() {
		// PAID
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
							
		if ($this->user->logged_in('site')) {			
			$status['response'] = $this->orders_plugin->showPaid();
			$status['status'] = 1;
		} else {
			$status = array(	'status' => 0,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => '');
		}
		
		echo json_encode($status);	
	}
	
	public function action_back() {
		$status = array(	'status' => 0,
							'error' => '',
							'response' => '');
		
		if ($this->user->logged_in('site')) {
			$status['status'] = 1;
			
			$step = $this->request->post('step');
			
			if ($step == 'list') $status['response'] = $this->orders_plugin->showOrderList();
			elseif ($step == 'configure') $status['response'] = $this->orders_plugin->showConfigure();
			elseif ($step == 'checkout') $status['response'] = $this->orders_plugin->showCheckout();
			elseif ($step == 'checkout2') $status['response'] = $this->orders_plugin->showCheckout2();
		} else {
			$status = array(	'status' => 0,
								'error' => CMS::getLexicons('orders.no_user'),
								'response' => '');
		}
		
		
		echo json_encode($status);
	}
	
	public function action_order_qty() {
		$data['qty'] = 0;
		
		if ($this->user->logged_in('site')) {
			// CURRENT ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			if (count($order) > 0) {
				$order_details = $this->orders->getOrderDetails(null, $order[0]['id']);
				$data['qty'] = count($order_details);
			}
		}
		
		
		echo json_encode($data);
	}

	public function action_update_qty() {
		$data = array(	'status' => '0',
						'qty' => '',
						'error' => '',
						'total_error' => '',
						'button' => __('products.checkout') );
						
		if ($this->user->logged_in('site')) {
			// CURRENT ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			if (count($order) > 0) {
				$show_non_stock_coffee_error = false;
				
				$order_details = $this->orders->getOrderDetails($_POST['order_detail_id'], $order[0]['id']);
				if (count($order_details) > 0) {
					if ($order_details[0]['category_id'] == 1) $_POST['qty'] = round($_POST['qty']/10)*10;
					
					// CHECK BALANCE
					if ($_POST['qty'] > $order_details[0]['product_reference_balance']) {
						// LIMIT COFFEE TO BALANCE
						if ($order_details[0]['category_id'] == 1 || ($order_details[0]['category_id'] == 8 && $order_details[0]['pro_sub_category_id'] == 19)) {
							$_POST['qty'] = $order_details[0]['product_reference_balance'];
							$show_non_stock_coffee_error = true;
						}
						$data['error'] = __('order_checkout.error_item_balance').': '.number_format($order_details[0]['product_reference_balance'],0,'.','');
					}
					
					$this->orders->update_qty($_POST['order_detail_id'], $_POST['qty']);
					
					// UPDATE LEVEL 2 DISCOUNT
					$this->orders->discount_level_2($order[0]['id']);
		
					$data['status'] = '1';
					$data['qty'] = empty($_POST['qty'])?0:$_POST['qty'];
					
					// GET TOTAL
					$data['total'] = $this->orders->getOrderTotal($order_details[0]['order_id']);
					
					$tpl_data = $data;
					$tpl_data['action'] = 'list-total';
					$data['total_table'] = $this->tpl->factory('plugins/orders/current_order', $tpl_data)->render();
				}

				// ORDER DATA
				$order = $this->orders->getOrders($order[0]['id']);
				if (!empty($order[0]['non_stock_coffee']) || $show_non_stock_coffee_error) $data['total_error'] .= __('order_checkout.error_order_balance_coffee').'<br/>';
				if (!empty($order[0]['non_stock'])) {
					$data['total_error'] .= __('order_checkout.error_order_balance').'<br/>'.__('order_checkout.error_order_balance_delivery');
					$data['button'] = __('products.checkout_order');
				}
			}
		}
		
		// VALIDATE
		if (empty($data['total']['qty'])) $data['total']['qty'] = 0;
		if (empty($data['total']['price'])) $data['total']['price'] = 0;
		
		echo json_encode($data);						
	}

	public function action_discount_share() {
		$data = array(	
			'status' => '0',
			'discount' => '0',
			'code' => '',
			'info' => '',
			'total_vat' => 0,
			'vat' => 0,
			'discaount_value' => 0 );
			
		// GET ORDER DETAILS
		$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
		if (count($order) > 0 && false) {
			$discount_percents = 0;
			$discount_level = 0;
			
			// GET LEVEL 2 DISCOUNTS
			$discounts_data = $this->db->select(
					array('discounts.value','value'),
					array('discounts.percents','percents') )
				->from('discounts')
				->where('discounts.level', '=', '4')
				->where('discounts.type', '=', 'share')
				->order_by('discounts.value', 'DESC')
				->execute()
				->as_array();
			
			
			if (!empty($discounts_data[0]['percents'])) {
				$data['status'] = '1';
				$data['discount'] = number_format($discounts_data[0]['percents'],0);
				
				// ADD DISCOUNT
				$discount_percents = $discounts_data[0]['percents'];
			}
			
			if (empty($discount_percents) || !is_numeric($discount_percents)) $discount_percents = 0;
			if ($discount_percents > 0) $discount_level = 4;
			
			$this->db->update('order_details')
				->set(array(
					'price' => DB::expr('order_details.original_price * (1 - ('.$discount_percents.' / 100))'),
					'discount_percents' => $discount_percents,
					'discount_level' => $discount_level))
				->where('order_details.order_id', '=', $order[0]['id'])
				->where('order_details.discount_level', 'NOT IN', array(1,2,3))
				->execute();
		}

		// GET TOTAL
		$total_data = $this->orders->getOrderTotal($order[0]['id']);
		
		$data['discaount_value'] = 0;	
		$data['vat'] = $total_data['vat'];		
		$data['total_vat'] = $total_data['price'];
		
		echo json_encode($data);						
	}

	/*
	 * INVOICES
	 */
	public function action_bill() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$order_id = $this->request->param('data');
		
		if (is_numeric($order_id)) {
			// GET ORDER DATA
			$order_data = $this->orders->getOrders($order_id);
			
			if (count($order_data) == 1 && (($this->user->logged_in('site') && $order_data[0]['owner_user_id'] == $this->user_id) || $this->user->logged_in('manager') || $this->user->logged_in('admin'))) {
				if (!file_exists($this->base_path.'files/orders/invoice-'.$order_id.'.pdf')) {
					// CREATE BILL
					$this->orders->createSendPDF($order_id, false);
				}
				
				$filename = $this->base_path.'files/orders/invoice-'.$order_id.'.pdf';	
				$content = file_get_contents($filename);
				
				header('Content-Type: application/pdf');
				header('Content-Length: '.strlen( $content ));
				header('Content-disposition: inline; filename="invoice-'.$order_id.'.pdf"');					
				echo $content;		
				exit();
			} 
		} 
		
		$this->request->redirect($this->base_url);
	}
	public function action_invoice() {
		// PARAMS
		$this->auto_render = FALSE;
		
		$invoice_id = $this->request->param('data');
		
		if (is_numeric($invoice_id)) {
			// GET ORDER DATA
			$invoice_data = $this->orders->getInvoices($invoice_id);
			
			if (count($invoice_data) == 1 && (($this->user->logged_in('site') && $invoice_data[0]['owner_user_id'] == $this->user_id) || $this->user->logged_in('manager') || $this->user->logged_in('admin'))) {
				if (!file_exists($this->base_path.'files/orders/pavadzime-'.$invoice_id.'.pdf')) $this->orders->createPavadzime($invoice_id);
								
				$filename = $this->base_path.'files/orders/pavadzime-'.$invoice_id.'.pdf';	
				$content = file_get_contents($filename);
				
				header('Content-Type: application/pdf');
				header('Content-Length: '.strlen( $content ));
				header('Content-disposition: inline; filename="pavadzime-'.$invoice_id.'.pdf"');					
				echo $content;		
				exit();
			}
		}
		
		$this->request->redirect($this->base_url);
	}
	
	public function action_search_user() {
		$users = array('id' => '', 'value' => '--- none ---');
		if ($this->user->logged_in('manager') || $this->user->logged_in('admin')) {
			$term = CMS::getGET('term');
			
			$user_sql = $this->db->select(
					array('users.id', 'id'),
					array('CONCAT(IFNULL("users.first_name",\'\'), \' \', IFNULL("users.last_name",\'\'), \' (\', IFNULL("users.email",\'\'), IF(TRIM(IFNULL("users.company",\'\')) != \'\',CONCAT(\'; \',TRIM(IFNULL("users.company",\'\'))),\'\'), \')\')', 'value') )
				->from('users')
				->where('users.status_id', '!=', '5')
				->order_by('IFNULL("users.first_name",\'\')')
				->order_by('IFNULL("users.last_name",\'\')')
				->order_by('IFNULL("users.email",\'\')')
				->order_by('IFNULL("users.company",\'\')')
				->limit(30);
			
			$term_data = explode(' ', $term);
			for($i=0; $i<count($term_data); $i++) {
				$user_sql->where('CONVERT(CONCAT(IFNULL("users.first_name",\'\'), \' \', IFNULL("users.last_name",\'\'), \' (\', IFNULL("users.email",\'\'), IF(TRIM(IFNULL("users.company",\'\')) != \'\',CONCAT(\'; \',TRIM(IFNULL("users.company",\'\'))),\'\'), \')\') USING UTF8)', 'LIKE', '%'.$term_data[$i].'%');
			}
			
			$users = $user_sql->execute()->as_array();
		}		
		
		echo json_encode(array_merge(array(array('id' => '', 'value' => '--- none ---')), $users));
	}

	public function action_get_user() {
		$data = array(	'status' => '0',
						'error' => '',
						'response' => array() );
		
		if ($this->user->logged_in('manager') || $this->user->logged_in('admin')) {
			$user_id = $this->request->post('user_id');
			if (!empty($user_id)) {
				// GET USER
				$user_data = $this->db->select(
						array('users.id', 'id'),
						array('CONCAT(IFNULL("users.first_name",\'\'), \' \', IFNULL("users.last_name",\'\'))', 'contact_name'),
						array('users.company', 'company'),
						array('users.reg_nr', 'reg_nr'),
						array('users.vat_nr', 'vat_nr'),
						array('users.email', 'email'),
						array('users.phone', 'phone'),
						array('users.address', 'address'))
					->from('users')
					->where('users.id', '=', $user_id)
					->execute()
					->as_array();
					
				if (count($user_data) > 0) {
					$data['response'] = $user_data[0];
					$data['status'] = '1';
				}
			}
		}
		
		echo json_encode($data);
	}
}