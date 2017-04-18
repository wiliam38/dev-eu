<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Products_Shipping extends Controller_Manager_Class {
	var $content_type_id = 10;
	
	public function before() {
		parent::before();
		
		// MODELS
		$this->shippings = Model::factory('manager_products_shippings');
		$this->products = new Model_Manager_Products_Products($this->content_type_id);
	} 		
	
	public function action_load() {
		if ($this->initForm('manager')) {						
			// PARAMS
			$this->tpl->js_file = array_merge($this->tpl->js_file, array(		'assets/modules/manager/products/shipping.js' ));
						
			// LANGUAGES
			$tpl_data['languages']= CMS::getLanguages(null, null, 5);
			
			// DATA
			$tpl_data['shippings'] = $this->shippings->getShippings();
			
			$tpl_data['action'] = 'load';			
			$this->tpl->data_panel = $this->tpl->factory('manager/products/shipping',$tpl_data);
		}
	}
	
	public function action_view() {
		$this->auto_render = FALSE;
		$ret_data = array(
			'status' => '0',
			'response' => '');
		
		if ($this->role('manager')) {
			// LANGUAGES
			$tpl_data['languages']= CMS::getLanguages(null, null, 5);
			
			// SHIPPING DATA
			$shippings = $this->shippings->getShippings($this->request->param('id'));
			$tpl_data['data'] = isset($shippings[0])?$shippings[0]:array();			
			
			$tpl_data['action'] = 'view';			
			$ret_data['response'] = $this->tpl->factory('manager/products/shipping',$tpl_data)->render();
			$ret_data['status'] = '1';
		}

		echo json_encode($ret_data);
	}

	public function action_edit() {
		$this->auto_render = FALSE;
		$ret_data = array(
			'status' => '0',
			'response' => '');
		
		if ($this->role('manager')) {
			// LANGUAGES
			$tpl_data['languages'] = CMS::getLanguages(null, null, 5);
			$tpl_data['invoice_languages'] = CMS::getLanguages(array(1,3), null, 5);
			
			// SHIPPING DATA
			$shippings = $this->shippings->getShippings($this->request->param('id'));
			$tpl_data['data'] = isset($shippings[0])?$shippings[0]:array();
			$tpl_data['currencies'] = $this->products->getCurrencies();
			$tpl_data['vat_types'] = CMS::getTypes('products_vat_type_id');
			$tpl_data['status'] = CMS::getStatus('shippings_status_id');
			
			
			$tpl_data['action'] = 'edit';			
			$ret_data['response'] = $this->tpl->factory('manager/products/shipping',$tpl_data)->render();
			$ret_data['status'] = '1';
		}

		echo json_encode($ret_data);
	}

	public function action_save() {
		$this->auto_render = FALSE;
		$ret_data = array(
			'status' => '0',
			'response' => '');
		
		if ($this->role('manager')) {
			// SAVE
			$shipping_id = $this->shippings->save($this->request->post());
			
			if (!empty($shipping_id)) {
				// LANGUAGES
				$tpl_data['languages']= CMS::getLanguages(null, null, 5);
				
				// SHIPPING DATA
				$shippings = $this->shippings->getShippings($shipping_id);
				$tpl_data['data'] = isset($shippings[0])?$shippings[0]:array();				
				
				$tpl_data['action'] = 'view';			
				$ret_data['response'] = $this->tpl->factory('manager/products/shipping',$tpl_data)->render();
				$ret_data['status'] = '1';
			}
		}

		echo json_encode($ret_data);
	}

	public function action_delete() {
		$this->auto_render = FALSE;
		$ret_data = array(
			'status' => '0',
			'response' => '');
		
		if ($this->role('manager')) {
			// SAVE
			$deleted = $this->shippings->delete($this->request->param('id'));
			
			if ($deleted) {
				$ret_data['status'] = '1';
			}
		}

		echo json_encode($ret_data);
	}
}