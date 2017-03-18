<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Products_Shippings extends Model {	
	public function getShippings($id = null, $lang_id = null, $filter_data = array()) {
		$order_total_sql = DB::expr('0');
		if (isset($filter_data['order_id']) && is_numeric($filter_data['order_id'])) {
			$order_total_sql = $this->db->select(
					array('	IFNULL(MAX(CASE
								WHEN "product_categories.category_id" = 1 THEN IFNULL("shippings.price_coffee",0)
								WHEN "product_categories.category_id" = 2 THEN IFNULL("shippings.price_machines",0)
								ELSE IFNULL("shippings.price_other",0)
							END), IFNULL("shippings.price_other",0) )'))
				->from('order_details')
				->join('product_references')
					->on('order_details.product_reference_id', '=', 'product_references.id')
				->join('product_categories')
					->on('product_references.product_id', '=', 'product_categories.product_id')
				->where('order_details.order_id', '=', $filter_data['order_id']);
		}
		
		$sql = $this->db->select(
				array('shippings.id', 'id'),
				array('shippings.parent_id', 'parent_id'),
				array('shippings.invoice_language_id', 'invoice_language_id'),
				array('invoice_languages.name', 'invoice_language_name'),
				array('invoice_languages.tag', 'invoice_language_tag'),
				array('shippings.price_coffee', 'price_coffee'),
				array('shippings.price_machines', 'price_machines'),
				array('shippings.price_other', 'price_other'),
				array('shippings.price_qty', 'price_qty'),
				array('shippings.currency_id', 'currency_id'),
				array('currencies.name', 'currency_name'),
				array('shippings.status_id', 'status_id'),
				array('status.description', 'status_description'),
				array('shippings.order_index', 'order_index'),
				array('shippings.vat_type_id', 'vat_type_id'),
				array('vat_types.description', 'vat_type_description'),
				array('vat_types.value', 'vat_type_value'),
				
				array('lang_shippings.id', 'l_id'),
				array('lang_shippings.language_id', 'l_language_id'),
				array('lang_shippings.title', 'l_title'),
				array('lang_shippings.description', 'l_description'),
				
				array($order_total_sql, 'total') )
			->from('shippings')
			->join(array('shippings', 'lang_shippings'), 'left')
				->on('shippings.id', '=', 'lang_shippings.parent_id')
			->join(array('languages', 'invoice_languages'), 'left')
				->on('shippings.invoice_language_id', '=', 'invoice_languages.id')
			->join('currencies', 'left')
				->on('shippings.currency_id', '=', 'currencies.id')
			->join('status', 'left')
				->on('shippings.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr('\'shippings_status_id\''))
			->join(array('types', 'vat_types'), 'left')
				->on('shippings.vat_type_id', '=', 'vat_types.type_id')
				->on('vat_types.table_type_name', '=', DB::expr('\'products_vat_type_id\''))
			->where('IFNULL("shippings.parent_id",0)', '=', '0')
			->order_by('shippings.order_index')
			->order_by('shippings.id');
			
		if (!is_null($id)) $sql->where('shippings.id', '=', $id);
		if (!is_null($lang_id)) $sql->where('lang_shippings.language_id', '=', $lang_id);
		
		if (isset($filter_data['from_status_id']) && is_numeric($filter_data['from_status_id'])) $sql->where('shippings.status_id', '>=', $filter_data['from_status_id']);
			
		$data = $sql->execute()->as_array();
		
		if (is_null($lang_id)) $data = CMS::langArray($data);
		
		return $data;
	}
	
	/*
	 * JOBS
	 */
	public function save($data) {
		// SAVE MAIN DATA
		if (!empty($data['id']) && is_numeric($data['id'])) {
			// UPDATE
			$sql = $this->db->update('shippings')
				->set(array(
					'invoice_language_id' => $data['invoice_language_id'],
					'price_coffee' => $data['price_coffee'],
					'price_machines' => $data['price_machines'],
					'price_other' => $data['price_other'],
					'price_qty' => $data['price_qty'],
					'currency_id' => $data['currency_id'],
					'vat_type_id' => $data['vat_type_id'],
					'status_id' => $data['status_id'],
					'order_index' => $data['order_index'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()')))
				->where('shippings.id', '=', $data['id'])
				->execute();
			$shipping_id = $data['id'];
		} else {
			// INSERT
			$sql = $this->db->insert('shippings', array(
					'parent_id',
					'invoice_language_id',
					'price_coffee',
					'price_machines',
					'price_other',
					'price_qty',
					'currency_id',
					'vat_type_id',
					'status_id',
					'order_index',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					0,
					$data['invoice_language_id'],
					$data['price_coffee'],
					$data['price_machines'],
					$data['price_other'],
					$data['price_qty'],
					$data['currency_id'],
					$data['vat_type_id'],
					$data['status_id'],
					$data['order_index'],
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()')))
				->execute();
			$shipping_id = $sql[0];
		}
		
		// SAVE LANGUAGES
		for($i=0; $i<count($data['language_id']); $i++) {
			if (!empty($data['content_id'][$i]) && is_numeric($data['content_id'][$i])) {
				// UPDATE
				$sql = $this->db->update('shippings')
					->set(array(
						'title' => $data['title'][$i],
						'description' => $data['description'][$i],
						'user_id' => $this->user_id,
						'datetime' => DB::expr('NOW()') ))
					->where('shippings.parent_id', '=', $shipping_id)
					->where('shippings.id', '=', $data['content_id'][$i])
					->execute();
			} else {
				// INSERT
				$sql = $this->db->insert('shippings', array(
						'parent_id',
						'language_id',
						'title',
						'description',
						'user_id',
						'datetime',
						'creation_user_id',
						'creation_datetime' ))
					->values(array(
						$shipping_id,
						$data['language_id'][$i],
						$data['title'][$i],
						$data['description'][$i],
						$this->user_id,
						DB::expr('NOW()'),
						$this->user_id,
						DB::expr('NOW()') ))
					->execute();
			}
		}
		
		return $shipping_id;
	} 

	public function delete($id) {
		$sql = $this->db->delete('shippings')
			->where('shippings.id', '=', $id)
			->or_where('shippings.parent_id', '=', $id)
			->execute();
		
		return $sql>0?true:false;
	}
}