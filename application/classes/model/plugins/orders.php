<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_Orders extends Model {
	public $content_type_id = 10;
	
	public function __construct() {
		parent::__construct();
		
		$this->products = new Model_Manager_Products_Products($this->content_type_id);
		$this->orders = Model::factory('manager_orders_orders');
	}	
	
	public function load($parameters, $template, $page_data, $page_class) {
		// PARAMS
		$limit = null;
		$offset = null;
		$type = 'current';
		
		$this->template = $template;
				
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			if ($key == 'limit') $$key = " LIMIT ".(int)$val;
			else $$key = $val;
		}
		
		$return = '';
		
		switch ($type) {
			case 'current':
			default:
				// CSS / JS
				//$page_class->tpl->css_file[] = 'assets/plugins/orders/current_order.css';
				//$page_class->tpl->js_file[] = 'assets/plugins/orders/current_order.js';
				
				// SUB PAGE ALIAS
				$page_alias = CMS::getPluginPageAlias($page_data);

				// STEP
				$step = CMS::getPageAliasParam('step', $page_alias);	
				if (empty($step)) $step = 1;
				
				// SHOW FIRST STEP IF ERROR
				$ses_order_id = $this->session->get('current_order_id');
				if ($step != '1' AND empty($ses_order_id)) {
					$return = $this->showConfigure();
				} else {				
					switch ($step) {
						case '1':
						default:
							// CONFIGURE
							$this->session->delete('current_order_id');
							$return = $this->showConfigure();
							break;
						case '2':
							// CHECKOUT
							$return = $this->showCheckout();
							break;
						case '3':
							// PAY WITH CARD
							$return = $this->showPayWithCard();
							break;
						case '4':
							// PAID
							$ses_order_id_done = $this->session->get('current_order_id_done');					
							if (!empty($ses_order_id_done)) {
								$return = $this->showPaid();
							} else {
								$return = $this->showConfigure();
							}
												
							$this->session->delete('current_order_id_done');
							$this->session->delete('current_order_id');
							
							break;
					}		
				}	
				break;
			case 'history': 
				// CSS / JS
				//$page_class->tpl->css_file[] = 'assets/plugins/orders/current_order.css';
				//$page_class->tpl->js_file[] = 'assets/plugins/orders/current_order.js';
				
				$return = $this->showHistory();
				break;	
		}
		
		return $return;
	}
	
	public function showOrderList() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
			if (count($order) == 0) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$order = $order[0];
				
				// GET ORDER DETAILS
				$order_details = $this->orders->getOrderDetails(null, $order['id']);
				
				// BOOK DATA
				$product_page = CMS::getDocuments(CMS::$products_page_id);
				$tpl_data['product_page'] = isset($product_page[0])?$product_page[0]:array();	
				
				if (count($order_details) == 0) {
					// NO CURRENT ORDER
					$tpl_data['action'] = 'no_order';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				} else {
					$tpl_data['order'] = $order;
					$tpl_data['products'] = $order_details;
					
					// TOTAL
					$tpl_data['total'] = $this->orders->getOrderTotal($order['id']);
				
					$tpl_data['action'] = 'list';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				}
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}
	
	public function showConfigure() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
			if (count($order) == 0) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$order = $order[0];
				
				// GET ORDER DETAILS
				$order_details = $this->orders->getOrderDetails(null, $order['id']);
				
				// BOOK DATA
				$product_page = CMS::getDocuments(CMS::$products_page_id);
				$tpl_data['product_page'] = isset($product_page[0])?$product_page[0]:array();	
				
				if (count($order_details) == 0) {
					// NO CURRENT ORDER
					$tpl_data['action'] = 'no_order';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				} else {
					$tpl_data['order'] = $order;
					$tpl_data['products'] = $order_details;
					
					// TOTAL
					$tpl_data['total'] = $this->orders->getOrderTotal($order['id']);
				
					$tpl_data['action'] = 'configure';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				}
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}
	
	public function showCheckout() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
			if (count($order) == 0 OR $order[0]['id'] != $this->session->get('current_order_id')) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$order = $order[0];
				
				// GET ORDER DETAILS
				$order_details = $this->orders->getOrderDetails(null, $order['id']);
				
				if (count($order_details) == 0) {
					// NO CURRENT ORDER
					$tpl_data['action'] = 'no_order';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				} else {
					$tpl_data['order'] = $order;
					$tpl_data['products'] = $order_details;
					$tpl_data['total_data'] =  $this->orders->getOrderTotal($order['id']);
					
					// SHIPPINGS
					$cms_shippings = Model::factory('manager_products_shippings');
					$shippings = $cms_shippings->getShippings(null, $this->lang_id, array('from_status_id' => '10', 'order_id' => $order['id']));
					/* Nezimantojam QTY
					for ($i=0; $i<count($shippings); $i++) {
						if (empty($shippings[$i]['price_qty']) || $shippings[$i]['price_qty'] == 0) $shippings[$i]['total'] = $shippings[$i]['price'];
						else $shippings[$i]['total'] = $shippings[$i]['price'] * $tpl_data['total_data']['qty'];
					}
					*/
					$tpl_data['shippings'] = $shippings;
					
					// STATOIL ADDRESS					
					$tpl_data['shippings_statoil'] = $this->orders->getStatoilAddress(null, $this->lang_id);
					
					// USER DATA
					$user_data = $this->user->userData($this->user_id);
					$tpl_data['user'] = $user_data[0];
					
					$tpl_data['action'] = 'checkout';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				}
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}

	public function showCheckout2() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
			if (count($order) == 0 OR $order[0]['id'] != $this->session->get('current_order_id')) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$order = $order[0];
				
				// GET ORDER DETAILS
				$order_details = $this->orders->getOrderDetails(null, $order['id']);
				
				if (count($order_details) == 0) {
					// NO CURRENT ORDER
					$tpl_data['action'] = 'no_order';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				} else {
					$tpl_data['order'] = $order;
					$tpl_data['products'] = $order_details;
					
					// TOTAL DATA
					$total_data = $this->orders->getOrderTotal($order['id']);
					
					$total_data['vat'] = round($total_data['vat'],2);		
					$total_data['total_vat'] = round($total_data['price'],2);				
					
					$total_data['total_vat'] += round($order['shipping_total'] * (1 + $order['shipping_vat'] / 100),2);
					$total_data['vat'] += round($order['shipping_total'] * ($order['shipping_vat'] / 100),2);
					$tpl_data['total_data'] = $total_data;
					
					// PAY TYPE
					$tpl_data['pay_types'] = CMS::getTypes('orders_pay_type_id');
					
					// USER DATA
					$user_data = $this->user->userData($this->user_id);
					$tpl_data['user'] = $user_data[0];
				
					// MANAGER ROLE
					$admin_role = false;
					if ($this->user->logged_in('manager') || $this->user->logged_in('admin')) $admin_role = true;
					$tpl_data['admin_role'] = $admin_role;
				
					$tpl_data['action'] = 'checkout2';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				}
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}

	public function showPayWithCard() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
			if (count($order) == 0 OR $order[0]['id'] != $this->session->get('current_order_id')) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$order = $order[0];
				
				// GET ORDER DETAILS
				$order_details = $this->orders->getOrderDetails(null, $order['id']);
				
				if (count($order_details) == 0) {
					// NO CURRENT ORDER
					$tpl_data['action'] = 'no_order';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				} else {
					$tpl_data['order'] = $order;
					$tpl_data['products'] = $order_details;
					
					// TOTALS
					$total_data = array(	'total' => 0,
									'total_vat' => 0,
									'vat' => 0 );
									
					$total_data =  $this->orders->getOrderTotal($order['id']);
					
					$total_data['vat'] = round($total_data['vat'],2);		
					$total_data['total_vat'] = round($total_data['price'],2);				
					
					$total_data['total_vat'] += round($order['shipping_total'] * (1 + $order['shipping_vat'] / 100),2);
					$total_data['vat'] += round($order['shipping_total'] * ($order['shipping_vat'] / 100),2);
					$tpl_data['total'] = $total_data;
					
					// USER DATA
					$user_data = $this->user->userData($this->user_id);
					$tpl_data['user'] = $user_data[0];
					
					$tpl_data['action'] = 'paywithcard';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				}
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}

	public function showConfirm() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			
			if (count($order) == 0 OR $order[0]['id'] != $this->session->get('current_order_id')) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$order = $order[0];
				
				// GET ORDER DETAILS
				$order_details = $this->orders->getOrderDetails(null, $order['id']);
				
				if (count($order_details) == 0) {
					// NO CURRENT ORDER
					$tpl_data['action'] = 'no_order';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				} else {
					$tpl_data['order'] = $order;
					$tpl_data['products'] = $order_details;
					
					// TOTALS
					$total_data = array(	'total' => 0,
									'total_vat' => 0,
									'vat' => 0 );
									
					$total_data =  $this->orders->getOrderTotal($order['id']);
					
					$total_data['vat'] = round($total_data['vat'],2);		
					$total_data['total_vat'] = round($total_data['price'],2);				
					
					$total_data['total_vat'] += round($order['shipping_total'] * (1 + $order['shipping_vat'] / 100),2);
					$total_data['vat'] += round($order['shipping_total'] * ($order['shipping_vat'] / 100),2);
					$tpl_data['total'] = $total_data;
					
					// USER DATA
					$user_data = $this->user->userData($this->user_id);
					$tpl_data['user'] = $user_data[0];
					
					$tpl_data['action'] = 'confirm';			
					$return = $this->tpl->factory($this->template, $tpl_data)->render();
				}
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}

	public function showPaid() {
		// GET ORDER
		$order = $this->orders->getOrders($this->session->get('current_order_id'));
		$tpl_data['order'] = $order[0];

		// GET ORDER DETAILS
		$tpl_data['products'] = $this->orders->getOrderDetails(null, $order[0]['id']);

		// GET ORDER TOTAL
		$tpl_data['total'] = $this->orders->getOrderTotal($order[0]['id']);
		
		if ($order[0]['pay_status_id'] >= 10) {
			$tpl_data['action'] = 'order_paid';
		} else {
			$tpl_data['action'] = 'order_placed';
		}
		
		$return = $this->tpl->factory($this->template, $tpl_data)->render();
		
		// GET 
		$page = CMS::getDocuments(38, null, null, $this->lang_id);
		$return = str_replace(':order_history', $page[0]['full_alias'], $return);
		$return = str_replace(':order_pdf', $this->base_url.'plugins/orders/bill/'.$order[0]['id'], $return);
		
		return $return;
	}
	
	//
	// HISTORY
	//
	private function showHistory() {
		if (!empty($this->user_id)) {
			// GET ORDER
			$orders = $this->orders->getOrders(null, $this->user_id, array('from_status_id' => '5'), '15');
			
			if (count($orders) == 0) {
				// NO CURRENT ORDER
				$tpl_data['action'] = 'no_order';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			} else {
				$tpl_data['orders'] = $orders;
				
				$tpl_data['action'] = 'list';			
				$return = $this->tpl->factory($this->template, $tpl_data)->render();
			}
		} else {
			// LOGIN
			$tpl_data['action'] = 'login';			
			$return = $this->tpl->factory($this->template, $tpl_data)->render();
		} 
		
		return $return;
	}
}