<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Orders_Orders extends Model {
	/*
	 * DISCOUNT LEVEL
	 * 		1 - Grāmatas atlaide
	 * 		2 - Apjoma atlaide
	 * 		3 - Atlaides kods
	 * 		4 - Like atlaide
	 */

	public function __construct() {
		parent::__construct();

		$this->session_id = $this->session->id();
	}

	public function getOrders($id = null, $owner_user_id = null, $filter_data = array(), $limit = null, $offset = null, $order_by = '"orders.date" DESC, IF (IFNULL("orders.number",0) = 0, 999999999, "orders.number") DESC, "orders.creation_datetime" DESC', $count_all = false) {
		$lang_id = $this->lang_id;
		if (empty($lang_id) || !is_numeric($lang_id)) $lang_id = 1;
			
		// SELECT
		$res = $this->db->select();

		// FROM
		$res->from('orders')
			->join('order_details', 'LEFT')
				->on('orders.id', '=', 'order_details.order_id')
			->join('product_references', 'LEFT')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('products', 'LEFT')
				->on('product_references.product_id', '=', 'products.id')
			->join('product_categories', 'LEFT')
				->on('products.id', '=', 'product_categories.product_id')
			->join('status', 'LEFT')
				->on('orders.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr("'orders_status_id'"))
			->join(array('status', 'pay_status'), 'LEFT')
				->on('orders.pay_status_id', '=', 'pay_status.status_id')
				->on('pay_status.table_status_name', '=', DB::expr("'orders_pay_status_id'"))
			->join(array('status', 'coffee_gift_status'), 'LEFT')
				->on('orders.coffee_gift_status_id', '=', 'coffee_gift_status.status_id')
				->on('coffee_gift_status.table_status_name', '=', DB::expr("'orders_coffee_gift_status_id'"))
			->join('currencies', 'LEFT')
				->on('orders.currency_id', '=', 'currencies.id')
			->join(array('types', 'pay_types'), 'LEFT')
				->on('pay_types.type_id', '=', 'orders.pay_type_id')
				->on('pay_types.table_type_name', '=', DB::expr("'orders_pay_type_id'"))
			->join(array('type_contents', 'pay_type_contents'), 'LEFT')
				->on('pay_type_contents.type_id', '=', 'pay_types.id')
				->on('pay_type_contents.language_id', '=', DB::expr($lang_id))
			->join(array('users', 'tmp_owner_users'), 'LEFT')
				->on('orders.tmp_owner_user_id', '=', 'tmp_owner_users.id');

		// WHERE
		if (!is_null($id))
			$res->where('orders.id', '=', $id);
		if (!is_null($owner_user_id))
			$res->where('orders.owner_user_id', '=', $owner_user_id);

		if (!empty($filter_data['search'])) {
			$search_sql = $this->db->select('DISTINCT "orders.id"')
				->from('orders')
				->join('order_details', 'LEFT')
					->on('order_details.order_id', '=', 'orders.id')
				->join('product_references', 'LEFT')
					->on('product_references.id', '=', 'order_details.product_reference_id')			
				->join('products', 'LEFT')				
					->on('product_references.product_id', '=', 'products.id')
				->join('product_contents', 'LEFT')
					->on('product_contents.product_id', '=', 'products.id');
			$search_array = explode(' ', $filter_data['search']);
			for ($i=0; $i<count($search_array); $i++) {
				$search_sql->where('CONVERT(CONCAT(\'19B\',IF(CHAR_LENGTH("orders.number") < 3,LPAD(IFNULL("orders.number",\'\'),3,\'0\'),IFNULL("orders.number",\'\')),\' \',IFNULL("orders.contact_name",\'\'),\' \',IFNULL("orders.company",\'\'),\' \',IFNULL("orders.reg_nr",\'\'),\' \',IFNULL("orders.vat_nr",\'\'),\' \',IFNULL("orders.email",\'\'),\' \',IFNULL("orders.address",\'\'),\' \',IFNULL("orders.phone",\'\'),\' \',IFNULL("orders.notes",\'\'),\' \',IFNULL("product_contents.1_title",\'\'),\' \',IFNULL("product_references.reference",\'\'),\' \',IFNULL("product_references.code",\'\')) USING UTF8)', 'LIKE', '%'.$search_array[$i].'%');
			}			
			$res->where('orders.id', 'IN', $search_sql);
		}

		if (isset($filter_data['status_id'])) {
			$res->where('orders.status_id', '=', $filter_data['status_id']);
			if ($filter_data['status_id'] == '1')
				$res->where('orders.creation_session_id', '=', $this->session_id);
		}
		if (isset($filter_data['from_status_id']))
			$res->where('orders.status_id', '>=', $filter_data['from_status_id']);
		if (isset($filter_data['in_status_id']))
			$res->where('orders.status_id', 'IN', $filter_data['in_status_id']);

		if (isset($filter_data['date_from']))
			$res->where('orders.date', '>=', CMS::date($filter_data['date_from']));
		if (isset($filter_data['date_to']))
			$res->where('orders.date', '<=', CMS::date($filter_data['date_to']));

		// ORDER BY
		if (!empty($order_by))
			$res->order_by($order_by);

		// GROUP BY
		$res->group_by('orders.id');

		// ONLY FOR COUNT ROWS
		if ($count_all) {
			$res->select(array('COUNT(DISTINCT "orders.id")', 'cnt'));
			$db_data = $res->execute()->as_array();

			return count($db_data);
		}

		// LIMIT
		if (!is_null($limit)) {
			// SELECT
			$res->select(array('orders.id', 'id'));

			// LIMIT
			$res->limit($limit);
			if (!is_null($offset))
				$res->offset($offset);

			// DATA
			$db_data = $res->execute()->as_array();

			$id_list = array();
			foreach ($db_data as $key => $val)
				$id_list[] = $val['id'];
			$res->where('orders.id', 'IN', !empty($id_list) ? $id_list : array(-1));

			$res->limit(NULL);
			$res->offset(NULL);
		}
		
		// NON STOCK COFFEE
		$non_stock_coffee_sql = $this->db->select('COUNT("order_details.id")')
			->from('order_details')
			->join('product_references')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('product_categories')
				->on('product_references.product_id', '=', 'product_categories.product_id')
			->join('category_settings', 'LEFT')
				->on('product_categories.category_id', '=', 'category_settings.category_id')
				->on('category_settings.id', '=', DB::expr('19'))
			->where('order_details.order_id', '=', DB::expr('orders.id'))
			->where_open()
				->where('product_categories.category_id', '=', '1')
				->or_where('category_settings.id', '=', '19')
			->where_close()
			->where('ROUND("order_details.qty",0)', '>', DB::expr('ROUND(product_references.balance,0)'));
			
		// NON STOCK
		$non_stock_sql = $this->db->select('COUNT("order_details.id")')
			->from('order_details')
			->join('product_references')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('product_categories')
				->on('product_references.product_id', '=', 'product_categories.product_id')
			->join('category_settings', 'LEFT')
				->on('product_categories.category_id', '=', 'category_settings.category_id')
				->on('category_settings.id', '=', DB::expr('19'))
			->where('order_details.order_id', '=', DB::expr('orders.id'))
			->where('product_categories.category_id', '!=', '1')
			->where('category_settings.id', 'IS', DB::expr('NULL'))
			->where('ROUND("order_details.qty",0)', '>', DB::expr('ROUND(product_references.balance,0)'));
			
		// DATA
		$res->select(
				array('orders.id', 'id'), 
				array('orders.owner_user_id', 'owner_user_id'), 
				array('orders.tmp_owner_user_id', 'tmp_owner_user_id'), 
				array('CONCAT(IFNULL("tmp_owner_users.first_name",\'\'), \' \', IFNULL("tmp_owner_users.last_name",\'\'), \' (\', IFNULL("tmp_owner_users.email",\'\'), IF(TRIM(IFNULL("tmp_owner_users.company",\'\')) != \'\',CONCAT(\'; \',TRIM(IFNULL("tmp_owner_users.company",\'\'))),\'\'), \')\')', 'tmp_owner_user_value'),
				array('orders.date', 'date'), 
				array('orders.number', 'real_number'), 
				array('orders.currency_id', 'currency_id'), 
				array('orders.shipping_id', 'shipping_id'), 
				array('orders.shipping_statoil_id', 'shipping_statoil_id'), 
				array('orders.shipping_statoil_address', 'shipping_statoil_address'), 
				array('orders.shipping_pickup_time', 'shipping_pickup_time'),
				array('orders.shipping_info', 'shipping_info'), 
				array('orders.shipping_total', 'shipping_total'), 
				array('IF(IFNULL("orders.no_vat",0) != 1,
							ROUND("orders.shipping_total" * (1 + IFNULL("orders.shipping_vat",0) / 100),2),
							ROUND("orders.shipping_total",2))', 'shipping_total_vat'), 
				array('IF(IFNULL("orders.no_vat",0) != 1,
							"orders.shipping_vat",
							0)', 'shipping_vat'), 
				array(	$this->db->select('COUNT("invoices.id")')
							->from('invoices')
							->where('invoices.order_id', '=', DB::expr('orders.id'))
							->where('invoices.shipping', '=', '1'), 'shipping_issued'),
				array('orders.invoice_lang', 'invoice_lang'), 
				array('orders.discount_code', 'discount_code'), 
				array('orders.contact_name', 'contact_name'), 
				array('orders.company', 'company'), 
				array('orders.reg_nr', 'reg_nr'), 
				array('orders.vat_nr', 'vat_nr'), 
				array('IFNULL("orders.no_vat",0)', 'no_vat'), 
				array('orders.email', 'email'), 
				array('orders.address', 'address'), 
				array('orders.phone', 'phone'), 
				array('orders.pay_type_id', 'pay_type_id'), 
				array('orders.notes', 'notes'), 
				array('orders.pdf_file', 'pdf_file'), 
				array('orders.status_id', 'status_id'), 
				array('pay_status.status_id', 'pay_status_id'), 
				array('IF("orders.pay_status_id"=10,"orders.pay_date",NULL)', 'pay_date'),
				array('orders.user_id', 'user_id'), 
				array('orders.datetime', 'datetime'), 
				array('orders.creation_user_id', 'creation_user_id'), 
				array('orders.creation_datetime', 'creation_datetime'), 
				array('orders.creation_session_id', 'creation_session_id'), 
				array('IF (CHAR_LENGTH("orders.number") < 3,LPAD("orders.number",3,\'0\'),"orders.number")', 'number'), 
				array('currencies.symbol', 'curr_symbol'), 
				array('currencies.name', 'curr_name'), 
				array('pay_types.name', 'pay_type_name'), 
				array('pay_types.description', 'pay_type_description'), 
				array('pay_type_contents.name', 'pay_type_l_name'), 
				array('status.description', 'status_description'), 
				array('pay_status.description', 'pay_status_description'), 
				
				array('IF(SUM(IF("product_categories.category_id" = 8, 1, 0)) > 0, 1, 0)', 'pro_order'),
				
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * "order_details.qty",2)),
							SUM(ROUND("order_details.price" * "order_details.qty",2)) )', 'total'), 
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * "order_details.qty",2)) + ROUND("orders.shipping_total" * (1 + "orders.shipping_vat" / 100),2),
							SUM(ROUND("order_details.price" * "order_details.qty",2)) + ROUND("orders.shipping_total",2) )', 'total_total'), 
				
				array('SUM("order_details.qty" * (CASE WHEN "orders.status_id" >= 10 THEN IFNULL("order_details.coffee_gift_amount",0) ELSE (CASE WHEN "products.coffee_gift_active" = 1 THEN IFNULL("products.coffee_gift_amount",0) ELSE 0 END) END))', 'sum_of_coffee_gift_amount'), 
				array('IFNULL("orders.coffee_gift_status_id",0)', 'coffee_gift_status_id'), 
				array('coffee_gift_status.description', 'coffee_gift_status_description'), 
				array('orders.coffee_gift_datetime', 'coffee_gift_datetime'), 
				array('DATE_ADD("orders.date", INTERVAL 30 DAY)', 'coffee_gift_valid_till'), 
				array('	CASE WHEN DATE_ADD("orders.date", INTERVAL 30 DAY) < DATE(NOW()) THEN 1 ELSE 0 END', 'coffee_gift_expired'),
				
				array($non_stock_sql, 'non_stock'),
				array($non_stock_coffee_sql, 'non_stock_coffee') );

		// GROUP BY
		$res->group_by(
			'orders.id',
			'orders.owner_user_id',
			'orders.tmp_owner_user_id',
			'tmp_owner_users.first_name',
			'tmp_owner_users.last_name',
			'tmp_owner_users.email',
			'tmp_owner_users.company',
			'orders.date',
			'orders.number',
			'orders.currency_id',
			'orders.shipping_id',
			'orders.shipping_statoil_id',
			'orders.shipping_statoil_address',
			'orders.shipping_pickup_time',
			'orders.shipping_info',
			'orders.shipping_total',
			'orders.no_vat',
			'orders.shipping_vat',
			'orders.invoice_lang',
			'orders.discount_code',
			'orders.contact_name',
			'orders.company',
			'orders.reg_nr',
			'orders.vat_nr',
			'orders.email',
			'orders.address',
			'orders.phone',
			'orders.pay_type_id',
			'orders.notes',
			'orders.pdf_file',
			'orders.status_id',
			'pay_status.status_id',
			'orders.pay_status_id',
			'orders.pay_date',
			'orders.user_id',
			'orders.datetime',
			'orders.creation_user_id',
			'orders.creation_datetime',
			'orders.creation_session_id',
			'orders.number',
			'currencies.symbol',
			'currencies.name',
			'pay_types.name',
			'pay_types.description',
			'pay_type_contents.name',
			'status.description',
			'pay_status.description',
			'orders.coffee_gift_status_id',
			'coffee_gift_status.description',
			'orders.coffee_gift_datetime',
			'orders.date' );

		// GET DATA
		$db_data = $res->execute()->as_array();

		return $db_data;
	}

	public function getOrderDetails($id = null, $order_id = null, $filter_data = array(), $lang_tag = null) {
		$lang_id = $this->lang_id;
		if (!empty($lang_tag)) {
			$lang_data = CMS::getLanguages(null, $lang_tag);
			if (count($lang_data) > 0) $lang_id = $lang_data[0]['id'];
		}
			
		
		// FILTER
		$filter = " ";
		if (!is_null($id))
			$filter .= " AND order_details.id = :id ";
		if (!is_null($order_id))
			$filter .= " AND order_details.order_id = :order_id ";

		// GET DATA
		$sql = "SELECT
					order_details.id																		AS id,		
					order_details.qty																		AS qty,	
					order_details.stock_qty																	AS stock_qty,	
					order_details.order_qty																	AS order_qty,	
					IF(IFNULL(orders.no_vat,0)!=1,order_details.vat,0)										AS vat,
					order_details.product_title																AS product_title,	
					order_details.product_code																AS product_code,	
					order_details.product_reference															AS product_reference,	
					
					orders.id																				AS order_id,	
					
					currencies.symbol																		AS curr_symbol,
					currencies.name																			AS curr_name,
					
					products.id																				AS product_id,
					product_references.id																	AS product_reference_id,
					product_references.image_src 															AS product_image_src,
					product_references.balance																AS product_reference_balance,
					product_references.reference                                                            AS product_reference_reference,
					product_references.code                                                                 AS product_reference_code,
					CASE 
						WHEN products.coffee_gift_active = 1 THEN products.coffee_gift_amount
						ELSE 0
					END																						AS product_coffee_gift_amount,
					
					product_categories.category_id															AS category_id,
					category_contents.alias																	AS l_category_alias,
					category_contents.title																	AS l_category_title,
					:pro_sub_category_id																	AS pro_sub_category_id,
					
					order_details.price																		AS price,						
					IF(IFNULL(orders.no_vat,0) != 1,
						ROUND(order_details.price * (1 + IFNULL(order_details.vat,0) / 100),2),
						order_details.price )																AS full_price,					
					
					order_details.original_price															AS original_price,				
					IF(IFNULL(orders.no_vat,0) != 1,
						ROUND(order_details.original_price * (1 + IFNULL(order_details.vat,0) / 100),2),
						order_details.original_price )														AS full_original_price,	
					
					order_details.discount_percents															AS discount_percents,
					order_details.discount_level															AS discount_level,		
					products.discount_color																	AS discount_color,			
					order_details.coffee_gift_amount														AS coffee_gift_amount,
					
					unit_types.description																	AS unit_type_description,
					
					order_details.qty - (	SELECT IFNULL(SUM(IFNULL(invoice_details.qty,0)),0)
											FROM invoice_details
											WHERE invoice_details.order_detail_id = order_details.id)		AS balance_qty,
										
					product_contents.id																		AS l_id,
					product_contents.language_id															AS l_language_id,
					product_contents.1_title																AS l_title,
					product_contents.1_description															AS l_description,
					product_contents.alias																	AS l_alias
				FROM
					order_details
					JOIN orders ON
						orders.id = order_details.order_id	
					LEFT JOIN currencies ON
						orders.currency_id = currencies.id
					JOIN product_references ON
						order_details.product_reference_id = product_references.id					
					JOIN products ON
						product_references.product_id = products.id 
					LEFT JOIN product_contents ON
						products.id = product_contents.product_id AND
						product_contents.language_id = :lang_id
					LEFT JOIN product_categories ON
						products.id = product_categories.product_id
					LEFT JOIN category_contents ON
						product_categories.category_id = category_contents.category_id AND
						category_contents.language_id = :lang_id 
					LEFT JOIN types unit_types ON
						unit_types.type_id = order_details.unit_type_id AND
						unit_types.table_type_name = 'products_unit_type_id'
				WHERE
					1 = 1
					" . $filter . "
				ORDER BY
					order_details.item ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':id', $id);
		$res->bind(':order_id', $order_id);
		$res->bind(':lang_id', $lang_id);
		
		// PRO CATEGORY
		$pro_category_setting_id_sql = $this->db->select('category_settings.id')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_settings')->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->where('category_setting_values.category_setting_id', 'IN', array(18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('IF("category_setting_values.category_setting_id" = 19, 1, 0) DESC')
			->order_by('category_settings.order_index')
			->limit(1);
		$res->bind(':pro_sub_category_id', $pro_category_setting_id_sql);

		$db_data = $res->execute()->as_array();

		return $db_data;
	}

	public function getOrderTotal($order_id) {
		$db_data = $this->db->select(
				array('SUM("order_details.qty")', 'qty'), 
				array('COUNT("order_details.qty")', 'item_qty'), 
									
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "order_details.qty",2)) / (1 + AVG("order_details.vat")/100),
							SUM(ROUND("order_details.price" * "order_details.qty",2)) )', 'price_wo_vat'), 
				
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "order_details.qty",2)),
							SUM(ROUND("order_details.price" * "order_details.qty",2)) )', 'price'), 
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "order_details.qty",2)) - (SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "order_details.qty",2)) / (1 + AVG("order_details.vat")/100)),
							0 )', 'vat'), 
				array('currencies.symbol', 'curr_symbol'), 
				array('ROUND(SUM(CASE WHEN "products.coffee_gift_active" = 1 THEN (IFNULL("products.coffee_gift_amount",0) * "order_details.qty") ELSE 0 END), 2)', 'product_coffee_gift_amount'), 
				array('ROUND(SUM(IFNULL("order_details.coffee_gift_amount",0) * "order_details.qty"), 2)', 'coffee_gift_amount'))
			->from('orders')
			->join('order_details', 'LEFT')
				->on('order_details.order_id', '=', 'orders.id')
			->join('product_references', 'LEFT')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('products', 'LEFT')
				->on('product_references.product_id', '=', 'products.id')
			->join('currencies', 'LEFT')
				->on('orders.currency_id', '=', 'currencies.id')
			->where('orders.id', '=', $order_id)
			->group_by(
				'currencies.symbol',
				'orders.no_vat' )
			->execute()
			->as_array();

		return $db_data[0];
	}

	public function getCurrencies() {
		$sql = "SELECT
					currencies.id				AS id,
					currencies.name				AS name,
					currencies.symbol			AS symbol
				FROM
					currencies";
		$res = $this->db->query(Database::SELECT, $sql);

		return $res->execute()->as_array();
	}

	public function addOrder($data) {
		$sql = "INSERT INTO orders (
					owner_user_id,
					date,
					number,
					currency_id,
					contact_name,
					company,
					reg_nr,
					vat_nr,
					email,
					address,
					phone,
					pay_type_id,
					pay_status_id,
					pay_date,
					notes,
					status_id,
					coffee_gift_status_id,
					coffee_gift_datetime,
					user_id,
					datetime,
					creation_user_id,
					creation_datetime,
					creation_session_id )
				SELECT
					users.id,
					NOW(),
					'',
					1,
					CONCAT(IFNULL(users.first_name,''), ' ', IFNULL(users.last_name,'')),
					users.company,
					users.reg_nr,
					users.vat_nr,
					users.email,
					users.address,
					users.phone,
					1,
					1,
					null,
					'',
					1,
					10,
					null,
					:user_id,
					NOW(),
					:user_id,
					NOW(),
					:session_id
				FROM
					users
				WHERE
					users.id = :owner_user_id ";
		$res = $this->db->query(Database::INSERT, $sql);
		$res->bind(':user_id', $this->user_id);
		$res->bind(':owner_user_id', $data['owner_user_id']);
		$res->bind(':session_id', $this->session_id);

		$db_data = $res->execute();

		// GET ORDER
		$order_data = $this->getOrders($db_data[0]);

		return $order_data[0];
	}

	public function addOrderDetail($data) {
		$sql = "SELECT order_details.id
				FROM order_details
				WHERE 
					order_details.order_id = :order_id AND
					order_details.product_reference_id = :product_reference_id ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':product_reference_id', $data['product_reference_id']);
		$res->bind(':order_id', $data['order_id']);
		$db_data = $res->execute()->as_array();
		
		$pro_coffee_coef = null;
		$pro_machines_coef = null;
		$pro_accessories_coef = null;
		$price_coef = 1;
		if ($data['category_id'] == 8 && $data['pro_sub_category_id'] == 19) {
			if (is_numeric($data['pro_coffee_coef'])) {
				$pro_coffee_coef = $data['pro_coffee_coef'];
				$price_coef = $data['pro_coffee_coef'];
			} else {
				$pro_coffee_coef = 1;
				$price_coef = 1;
			}
		} 
		if ($data['category_id'] == 8 && $data['pro_sub_category_id'] == 18) {
			if (is_numeric($data['pro_machines_coef'])) {
				$pro_machines_coef = $data['pro_machines_coef'];
				$price_coef = $data['pro_machines_coef'];
			} else {
				$pro_machines_coef = 1;
				$price_coef = 1;
			}
		} 
		if ($data['category_id'] == 8 && $data['pro_sub_category_id'] == 20) {
			if (is_numeric($data['pro_accessories_coef'])) {
				$pro_accessories_coef = $data['pro_accessories_coef'];
				$price_coef = $data['pro_accessories_coef'];
			} else {
				$pro_accessories_coef = 1;
				$price_coef = 1;
			}
		}
		 
		if (count($db_data) == 0) {
			// ADD NEW
			$sql = "INSERT INTO order_details (
						order_id,
						item,
						product_reference_id,
						product_code,
						product_reference,
						product_title,
						qty,
						unit_type_id,
						price,
						original_price,
						discount_percents,
						discount_level,
						pro_coffee_coef,
						pro_machines_coef,
						pro_accessories_coef,
						coffee_gift_amount,
						vat,
						user_id,
						datetime,
						creation_user_id,
						creation_datetime )
					SELECT
						:order_id,
						IFNULL(MAX(order_details.item),0) + 1,
						product_references.id,
						product_references.code,
						product_references.reference,
						product_contents.1_title,
						:qty,
						products.unit_type_id,
						CASE 
							WHEN products.discount_active = 1 THEN products.discount_price * :price_coef
							ELSE products.price * :price_coef
						END,
						products.price * :price_coef,
						CASE 
							WHEN products.discount_active = 1 THEN 100 - (products.discount_price * 100 / NULLIF(products.price,0))
							ELSE 0
						END,
						CASE 
							WHEN products.discount_active = 1 THEN 1
							ELSE 0
						END,
						:pro_coffee_coef,
						:pro_machines_coef,
						:pro_accessories_coef,
						0,
						vat_types.value,
						:user_id,
						NOW(),
						:user_id,
						NOW()
					FROM
						product_references
						JOIN products ON
							product_references.product_id = products.id
						LEFT JOIN product_contents ON
							products.id = product_contents.product_id AND
							product_contents.language_id = :lang_id
						LEFT JOIN order_details ON
							order_details.order_id = :order_id
						LEFT JOIN types vat_types ON
							vat_types.type_id = products.vat_type_id AND
							vat_types.table_type_name = 'products_vat_type_id'
					WHERE
						product_references.id = :product_reference_id
					GROUP BY
						product_references.id,
						product_references.code,
						product_references.reference,
						product_contents.1_title,
						products.unit_type_id,
						products.discount_active,
						products.discount_price,
						products.price,
						vat_types.value ";
			$res = $this->db->query(Database::INSERT, $sql);
			$res->bind(':user_id', $this->user_id);
			$res->bind(':product_reference_id', $data['product_reference_id']);
			$res->bind(':order_id', $data['order_id']);
			$res->bind(':lang_id', $this->lang_id);
			$res->bind(':qty', $data['qty']);
			$res->bind(':pro_coffee_coef', $pro_coffee_coef);
			$res->bind(':pro_machines_coef', $pro_machines_coef);
			$res->bind(':pro_accessories_coef', $pro_accessories_coef);
			$res->bind(':price_coef', $price_coef);

			$db_data = $res->execute();

			$order_detail_id = $db_data[0];
		} else {
			// UPDATE QTY
			$sql = "UPDATE 
						order_details
					SET
						qty = qty + :qty,
						user_id = :user_id,
						datetime = NOW()
					WHERE 
						order_details.id = :order_detail_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':qty', $data['qty']);
			$res->bind(':user_id', $this->user_id);
			$res->bind(':order_detail_id', $db_data[0]['id']);
			$sub_data = $res->execute();

			$order_detail_id = $db_data[0]['id'];
		}

		// GET ORDER
		$order_detail_data = $this->getOrderDetails($order_detail_id);

		if ($order_detail_data[0]['category_id'] == 1) {
			$order_detail_data[0]['qty'] = round($order_detail_data[0]['qty'] / 10) * 10;
			$this->update_qty($order_detail_id, $order_detail_data[0]['qty']);

			// GET ORDER
			$order_detail_data = $this->getOrderDetails($order_detail_id);
		}

		return $order_detail_data[0];
	}

	//
	// ADD TO ORDER
	//
	public function add_to_order($data) {
		$status = array('status' => 0, 'error' => '', 'response' => '');

		// GET CURRENT ORDER
		$order = $this->getOrders(null, $this->user_id, array('status_id' => '1'));
		if (count($order) == 0) {
			// DELETE OLD DRAFT ORDERS
			$sql = "DELETE FROM 
						order_details
					WHERE 
						order_details.order_id IN (	SELECT orders.id 
													FROM orders
													WHERE
														orders.owner_user_id = :user_id AND		
														orders.status_id = 1 AND
														orders.creation_session_id != :session_id ) ";
			$res = $this->db->query(Database::DELETE, $sql);
			$res->bind(':user_id', $this->user_id);
			$res->bind(':session_id', $this->session_id);
			$db_data = $res->execute();

			$sql = "DELETE FROM 
						orders
					WHERE
						orders.owner_user_id = :user_id AND		
						orders.status_id = 1 AND
						orders.creation_session_id != :session_id ";
			$res = $this->db->query(Database::DELETE, $sql);
			$res->bind(':user_id', $this->user_id);
			$res->bind(':session_id', $this->session_id);
			$db_data = $res->execute();

			// CREATE NEW
			$order_data = array('owner_user_id' => $this->user_id);
			$order = $this->addOrder($order_data);
		} else {
			$order = $order[0];
		}

		if (!empty($order)) {
			// GET PRODUCT DATA
			$this->products = new Model_Manager_Products_Products(10);
			$product_data = $this->products->getProductReferences($data['product_reference_id']);
			
			if ($product_data[0]['category_id'] == 8 && empty($this->user_data['pro_category'])) {
				// PRO
				$status = array('status' => 0, 'error' => __('orders.pro_category_not_allowed'), 'response' => '');				
			} else {
				// ADD PRODUCT
				$pro_coffee_coef = 1;
				$pro_machines_coef = 1;
				$pro_accessories_coef = 1;
				if (is_numeric($this->user_data['pro_coffee_coef'])) $pro_coffee_coef = $this->user_data['pro_coffee_coef'];
				if (is_numeric($this->user_data['pro_machines_coef'])) $pro_machines_coef = $this->user_data['pro_machines_coef'];
				if (is_numeric($this->user_data['pro_accessories_coef'])) $pro_accessories_coef = $this->user_data['pro_accessories_coef'];
				
				$order_detail_data = array(
					'order_id' => $order['id'], 
					'product_reference_id' => $data['product_reference_id'], 
					'qty' => $data['qty'],
					'category_id' => $product_data[0]['category_id'],
					'pro_sub_category_id' => $product_data[0]['pro_sub_category_id'],
					'pro_coffee_coef' => $pro_coffee_coef,
					'pro_machines_coef' => $pro_machines_coef,
					'pro_accessories_coef' => $pro_accessories_coef );
	
				$order_detail_data = $this->addOrderDetail($order_detail_data);
	
				// UPDATE LEVEL 2 DISCOUNT
				$this->discount_level_2($order['id']);
	
				$my_order = CMS::getDocuments(37, null, null, $this->lang_id);
				$status = array('status' => 1, 'error' => '', 'response' => $my_order[0]['full_alias']);
			}
		}

		return $status;
	}

	public function approveOrder($data) {
		$number = 0;
		if ($data['status_id'] >= 10) {
			$sql = "SELECT IFNULL(MAX(orders.number),0) AS nr
					FROM orders";
			$db_data = $this->db->query(Database::SELECT, $sql)->execute()->as_array();
			$number = str_pad($db_data[0]['nr'] + 1, 3, '0', STR_PAD_LEFT);

			$this->updateBalance($data['order_id'], '-');
		}

		// UPDATE DETAILS DATA
		$db_data = $this->db->update('order_details')
			->set(array(
				'coffee_gift_amount' => $this->db->select('CASE WHEN "products.coffee_gift_active" = 1 THEN IFNULL("products.coffee_gift_amount", 0) ELSE 0 END')
											->from('product_references')
											->join('products', 'LEFT')->on('product_references.product_id', '=', 'products.id')
											->where('product_references.id', '=', DB::expr('order_details.product_reference_id')) ))
			->where('order_details.order_id', '=', $data['order_id'])
			->execute();		

		if ($data['status_id'] == 10) {
			$sql = "UPDATE
						orders
					SET
						date = NOW(),
						number = :number,
						status_id = :status_id,
						owner_user_id = IF(IFNULL(orders.tmp_owner_user_id,0)!=0,orders.tmp_owner_user_id,orders.owner_user_id),
						tmp_owner_user_id = 0,
						user_id = :user_id,
						datetime = NOW()
					WHERE
						orders.id = :order_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			
			$res->bind(':number', $number);
			$res->bind(':status_id', $data['status_id']);
			$res->bind(':order_id', $data['order_id']);
			$res->bind(':user_id', $this->user_id);
			$db_data = $res->execute();
		} else {
			// CHECK VAT 0%
			$no_vat = 0;		
			$vat_nr = trim($data['vat_nr']);
			$vat_nr = str_replace(' ', '', $vat_nr);
			$vat_pattern = CMS::getSettings('orders.0_vat_nr_patterns');
			$vat_pattern = explode('|',$vat_pattern);
			for($i=0; $i<count($vat_pattern); $i++) {
				$pattern = '/^'.$vat_pattern[$i].'$/i';
				if (preg_match($pattern, $vat_nr)) $no_vat = 1;
			}
			
			// TMP OWNER USER ID
			if (!isset($data['tmp_owner_user_id'])) $data['tmp_owner_user_id'] = null;
			if (!$this->user->logged_in('manager') && !$this->user->logged_in('admin')) $data['tmp_owner_user_id'] = null;
			
			
			$sql = "UPDATE
						orders
					SET
						date = NOW(),
						discount_code = '',
						number = :number,
						contact_name = :contact_name,
						company = :company,
						reg_nr = :reg_nr,
						vat_nr = :vat_nr,
						no_vat = :no_vat,
						email = :email,
						address = :address,
						phone = :phone,
						pay_type_id = :pay_type_id,
						notes = :notes,
						status_id = :status_id,
						tmp_owner_user_id = :tmp_owner_user_id,
						user_id = :user_id,
						datetime = NOW()
					WHERE
						orders.id = :order_id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			
			$res->bind(':number', $number);
			$res->bind(':contact_name', $data['contact_name']);
			$res->bind(':company', $data['company']);
			$res->bind(':reg_nr', $data['reg_nr']);
			$res->bind(':vat_nr', $data['vat_nr']);
			$res->bind(':no_vat', $no_vat);
			$res->bind(':email', $data['email']);
			$res->bind(':address', $data['address']);
			$res->bind(':phone', $data['phone']);
			$res->bind(':pay_type_id', $data['pay_type_id']);
			$res->bind(':notes', $data['notes']);
			$res->bind(':status_id', $data['status_id']);
			$res->bind(':tmp_owner_user_id', $data['tmp_owner_user_id']);
			$res->bind(':order_id', $data['order_id']);
			$res->bind(':user_id', $this->user_id);
			$db_data = $res->execute();
		}

		

		return $data['order_id'];
	}

	public function updateShippingData($data) {
		$sql = "UPDATE
					orders
				SET
					shipping_id = :shipping_id,
					shipping_statoil_id = :shipping_statoil_id,
					shipping_statoil_address = :shipping_statoil_address,
					shipping_pickup_time = :shipping_pickup_time,
					shipping_info = :shipping_info,
					shipping_total = :shipping_total,
					shipping_vat = :shipping_vat,
					invoice_lang = :invoice_lang,
					user_id = :user_id,
					datetime = NOW()
				WHERE
					orders.id = :order_id ";
		$res = $this->db->query(Database::UPDATE, $sql);

		$shipping_statoil_id = null;
		$shipping_statoil_address = '';
		if (!empty($data['shipping_statoil_id'])) {
			// STATOIL DATA
			$statoil_data = $this->getStatoilAddress($data['shipping_statoil_id'], $this->lang_id);
			if (count($statoil_data) > 0) {
				$shipping_statoil_id = $statoil_data[0]['id'];
				$shipping_statoil_address = $statoil_data[0]['name'] . ' - ' . $statoil_data[0]['address'];
			}
		}

		$res->bind(':shipping_id', $data['shipping_id']);
		$res->bind(':shipping_statoil_id', $shipping_statoil_id);
		$res->bind(':shipping_statoil_address', $shipping_statoil_address);
		$res->bind(':shipping_pickup_time', $data['shipping_pickup_time']);
		$res->bind(':shipping_info', $data['shipping_info']);
		$res->bind(':shipping_total', $data['shipping_total']);
		$res->bind(':shipping_vat', $data['shipping_vat']);
		$res->bind(':invoice_lang', $data['invoice_lang']);
		$res->bind(':order_id', $data['order_id']);
		$res->bind(':user_id', $this->user_id);
		$db_data = $res->execute();

		return $data['order_id'];
	}

	public function createSendPDF($order_id, $send_email = true) {
		// GET ORDER
		$user_data = $this->user->userData($this->user_id);
		$order_data = $this->getOrders($order_id);

		//
		// GENERATE PDF
		//

		// SET LOCALE
		setlocale(LC_TIME, 'lv_LV.utf8');

		$tpl_data['order'] = $order_data[0];
		$order_details = $this->getOrderDetails(null, $order_id, array(), $order_data[0]['invoice_lang']);
		$tpl_data['products'] = $order_details;

		// COMPANY DATA
		$company = array('name' => CMS::getSettings('order.company', $this->lang_id), 'reg_nr' => CMS::getSettings('order.reg_nr', $this->lang_id), 'address' => CMS::getSettings('order.address', $this->lang_id), 'bank' => CMS::getSettings('order.bank', $this->lang_id), 'bank_code' => CMS::getSettings('order.bank_code', $this->lang_id), 'account' => CMS::getSettings('order.account', $this->lang_id));
		$tpl_data['company'] = $company;

		// TOTALS
		$total_data = array('total' => 0, 'total_vat' => 0, 'vat' => 0);

		$total_data = $this->getOrderTotal($order_id);
		$total_data['vat'] = round($total_data['vat'], 2);
		$total_data['total_vat'] = round($total_data['price'], 2);

		$total_data['total_vat'] += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100), 2);
		$total_data['vat'] += round($order_data[0]['shipping_total'] * ($order_data[0]['shipping_vat'] / 100), 2);
		
		if ($order_data[0]['invoice_lang'] == 'en') $total_data['total_vat_lv'] = $this->number_to_en($total_data['total_vat']);
		else $total_data['total_vat_lv'] = $this->number_to_lv($total_data['total_vat']);
		
		$tpl_data['total'] = $total_data;

		// COFFEE GIFT
		$order_coffee_gift_info = '';
		if ($total_data['coffee_gift_amount'] > 0) {
			$coffee_amount = $total_data['coffee_gift_amount'] . ' ' . $order_data[0]['curr_symbol'];
			$def_lang = CMS::getLanguages(CMS::$lang_id);
			$order_coffee_gift_info = CMS::getLexicons('emails.order_coffee_gift_info', array(), $def_lang[0]['tag']);
			$order_coffee_gift_info = str_replace(':amount', $coffee_amount, $order_coffee_gift_info);
		}
		$tpl_data['order_coffee_gift_info'] = $order_coffee_gift_info;

		// DOCUMENT LANGUAGE
		$doc_lang = mb_strtolower($order_data[0]['invoice_lang']);
		if (!file_exists($this->base_path.'application/views/plugins/orders/'.$doc_lang.'_invoice_pdf.tpl')) $doc_lang = 'lv';
		
		$tpl_data['action'] = 'load';
		$pdf = View_MPDF::factory('plugins/orders/'.$doc_lang.'_invoice_pdf', $tpl_data);

		// SAVE TO FILE
		if (!file_exists($this->base_path . 'files/orders'))
			mkdir($this->base_path . 'files/orders');
		$filename = 'files/orders/invoice-' . $order_id . '.pdf';
		file_put_contents($this->base_path . $filename, $pdf);

		$sql = "UPDATE
					orders
				SET
					pdf_file = :pdf_file,
					user_id = :user_id,
					datetime = NOW()
				WHERE
					orders.id = :order_id ";
		$res = $this->db->query(Database::UPDATE, $sql);
		$pdf_file = 'plugins/orders/bill/' . $order_id;
		$res->bind(':pdf_file', $pdf_file);
		$res->bind(':order_id', $order_id);
		$res->bind(':user_id', $this->user_id);
		$db_data = $res->execute();

		//
		// SEND EMAIL
		//
		if ($send_email) {
			$mail_to = $order_data[0]['email'];
			$mail_from = CMS::getSettings('default.email');
			$mail_from_name = CMS::getSettings('default.site_name');
			if (Valid::email($mail_to) AND Valid::email($mail_from)) {
				// RENDER CONTENT
				if ($order_data[0]['pay_status_id'] < 10)
					$body = CMS::getLexicons('emails.order_placed');
				else
					$body = CMS::getLexicons('emails.order_paid');

				// COFFEE GIFT
				$coffee_gift = '';
				if ($total_data['coffee_gift_amount'] > 0) {
					$amount = $total_data['coffee_gift_amount'] . ' ' . $order_data[0]['curr_symbol'];
					$coffee_gift = '<p>' . CMS::getLexicons('emails.order_coffee_gift_info') . '</p>';
					$coffee_gift = str_replace(':amount', $amount, $coffee_gift);
				}
				$body = str_replace(':coffee_gift_info', $coffee_gift, $body);

				// SEND MAIL
				$this->email = Model::factory('manager_emails');

				// SEND EMAIL TO BUYER
				$mail_data = array('from_email' => $mail_from, 'from_name' => $mail_from_name, 'to_email' => $mail_to, 'subject' => $mail_from_name . ' pasūtijums', 'body' => $body, 'body_type' => 'text/html', 'attachments' => array($filename));
				$new_mail = $this->email->add_email($mail_data);

				// SEND EMAIL TO ADMIN
				$mail_data = array('from_email' => $mail_to, 'from_name' => $mail_from_name, 'to_email' => $mail_from, 'subject' => $mail_from_name . ' jauns pasūtijums', 'body' => 'Mājas lapā 19bar.eu ir jauns pasūtījums. Sīkāka informācijas pielikumā.', 'body_type' => 'text/html', 'attachments' => array($filename));
				$new_mail = $this->email->add_email($mail_data);

				$this->email->send_all_emails();
			}
		}
	}

	public function getInvoices($id, $order_id = null, $filter_data = array(), $limit = null, $offset = null, $order_by = '"invoices.date", IF (IFNULL("orders.number",0) = 0, 999999999, "orders.number"), "invoices.number", "invoices.id"') {
		$lang_id = $this->lang_id;
		if (empty($lang_id) || !is_numeric($lang_id)) $lang_id = 1;
		
		$sql = $this->db->select(
				array('invoices.id', 'id'),
				array('invoices.order_id', 'order_id'),
				array('invoices.number', 'number'),
				array('invoices.date', 'date'),
				array('invoices.shipping', 'shipping'),
				
				array('CONCAT(IF(CHAR_LENGTH("orders.number") < 3,LPAD("orders.number",3,\'0\'),"orders.number"),IF("invoices.number" IS NOT NULL,CONCAT(\'/\',"invoices.number"),\'\'))', 'full_number'), 
				
				array('orders.id', 'ord_id'), 
				array('orders.owner_user_id', 'owner_user_id'), 
				array('orders.date', 'ord_date'), 
				array('orders.number', 'ord_number'), 
				array('orders.invoice_lang', 'invoice_lang'),
				array('orders.currency_id', 'currency_id'), 
				array('orders.shipping_id', 'shipping_id'), 
				array('orders.shipping_statoil_id', 'shipping_statoil_id'), 
				array('orders.shipping_statoil_address', 'shipping_statoil_address'), 
				array('orders.shipping_pickup_time', 'shipping_pickup_time'),
				array('orders.shipping_info', 'shipping_info'), 
				array('orders.shipping_total', 'shipping_total'), 
				
				array('IF("invoices.shipping"=1,"orders.shipping_total",0)', 'shipping_total'),
				array('IF("invoices.shipping"=1,
						 IF(IFNULL("orders.no_vat",0) != 1,
							ROUND("orders.shipping_total" * (1 + IFNULL("orders.shipping_vat",0) / 100),2),
							ROUND("orders.shipping_total",2)),0)', 'shipping_total_vat'),
				array('IF("invoices.shipping"=1,
						 IF(IFNULL("orders.no_vat",0) != 1,
							"orders.shipping_vat",
							0),0)', 'shipping_vat'),
				
				array('orders.discount_code', 'discount_code'), 
				array('orders.contact_name', 'contact_name'), 
				array('orders.company', 'company'), 
				array('orders.reg_nr', 'reg_nr'), 
				array('orders.vat_nr', 'vat_nr'), 
				array('IFNULL("orders.no_vat",0)', 'no_vat'), 
				array('orders.email', 'email'), 
				array('orders.address', 'address'), 
				array('orders.phone', 'phone'), 
				array('orders.pay_type_id', 'pay_type_id'), 
				array('orders.notes', 'notes'),
				array('orders.pdf_file', 'pdf_file'), 
				array('orders.status_id', 'status_id'), 
				array('orders.pay_status_id', 'pay_status_id'), 
				array('IF("orders.pay_status_id"=10,"orders.pay_date",NULL)', 'pay_date'),
				array('orders.user_id', 'user_id'), 
				array('orders.datetime', 'datetime'), 
				array('orders.creation_user_id', 'creation_user_id'), 
				array('orders.creation_datetime', 'creation_datetime'), 
				array('orders.creation_session_id', 'creation_session_id'), 
				array('currencies.symbol', 'curr_symbol'), 
				array('currencies.name', 'curr_name'), 
				array('pay_types.name', 'pay_type_name'), 
				array('pay_types.description', 'pay_type_description'), 
				array('pay_type_contents.name', 'pay_type_l_name'), 
				array('status.description', 'status_description'), 
				array('pay_status.description', 'pay_status_description'), 
				
				array('IF(SUM(IF("product_categories.category_id" = 8, 1, 0)) > 0, 1, 0)', 'pro_order'),
				
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * "invoice_details.qty",2)),
							SUM(ROUND("order_details.price" * "invoice_details.qty",2)) )', 'total'), 
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * "invoice_details.qty",2)) + ROUND("orders.shipping_total" * (1 + "orders.shipping_vat" / 100),2),
							SUM(ROUND("order_details.price" * "invoice_details.qty",2)) + ROUND("orders.shipping_total",2) )', 'total_total'), 
				
				array('SUM("invoice_details.qty" * (CASE WHEN "orders.status_id" >= 10 THEN IFNULL("order_details.coffee_gift_amount",0) ELSE (CASE WHEN "products.coffee_gift_active" = 1 THEN IFNULL("products.coffee_gift_amount",0) ELSE 0 END) END))', 'sum_of_coffee_gift_amount'), 
				array('IFNULL("orders.coffee_gift_status_id",0)', 'coffee_gift_status_id'), 
				array('coffee_gift_status.description', 'coffee_gift_status_description'), 
				array('orders.coffee_gift_datetime', 'coffee_gift_datetime'), 
				array('DATE_ADD("orders.date", INTERVAL 30 DAY)', 'coffee_gift_valid_till'), 
				array('	CASE WHEN DATE_ADD("orders.date", INTERVAL 30 DAY) < DATE(NOW()) THEN 1 ELSE 0 END', 'coffee_gift_expired') )
			->from('invoices')
			->join('orders')
				->on('invoices.order_id', '=', 'orders.id')
			->join('order_details', 'LEFT')
				->on('orders.id', '=', 'order_details.order_id')
			->join('invoice_details', 'LEFT')
				->on('invoice_details.order_detail_id', '=', 'order_details.id')
				->on('invoice_details.invoice_id', '=', 'invoices.id')
			->join('product_references', 'LEFT')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('products', 'LEFT')
				->on('product_references.product_id', '=', 'products.id')
			->join('product_categories', 'LEFT')
				->on('products.id', '=', 'product_categories.product_id')
			->join('status', 'LEFT')
				->on('orders.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr("'orders_status_id'"))
			->join(array('status', 'pay_status'), 'LEFT')
				->on('orders.pay_status_id', '=', 'pay_status.status_id')
				->on('pay_status.table_status_name', '=', DB::expr("'orders_pay_status_id'"))
			->join(array('status', 'coffee_gift_status'), 'LEFT')
				->on('orders.coffee_gift_status_id', '=', 'coffee_gift_status.status_id')
				->on('coffee_gift_status.table_status_name', '=', DB::expr("'orders_coffee_gift_status_id'"))
			->join('currencies', 'LEFT')
				->on('orders.currency_id', '=', 'currencies.id')
			->join(array('types', 'pay_types'), 'LEFT')
				->on('pay_types.type_id', '=', 'orders.pay_type_id')
				->on('pay_types.table_type_name', '=', DB::expr("'orders_pay_type_id'"))
			->join(array('type_contents', 'pay_type_contents'), 'LEFT')
				->on('pay_type_contents.type_id', '=', 'pay_types.id')
				->on('pay_type_contents.language_id', '=', DB::expr($lang_id))
			->group_by(
				'invoices.id',
				'invoices.order_id',
				'invoices.number',
				'invoices.date',
				'invoices.shipping',
				'orders.shipping_total',
				'orders.id',
				'orders.owner_user_id',
				'orders.date',
				'orders.number',
				'orders.invoice_lang',
				'orders.currency_id',
				'orders.shipping_id',
				'orders.shipping_statoil_id',
				'orders.shipping_statoil_address',
				'orders.shipping_pickup_time',
				'orders.shipping_info',
				'orders.shipping_total',
				'orders.no_vat',
				'orders.shipping_vat',
				'orders.discount_code',
				'orders.contact_name',
				'orders.company',
				'orders.reg_nr',
				'orders.vat_nr',
				'orders.email',
				'orders.address',
				'orders.phone',
				'orders.pay_type_id',
				'orders.notes',
				'orders.pdf_file',
				'orders.status_id',
				'orders.pay_status_id',
				'orders.pay_date',
				'orders.user_id',
				'orders.datetime',
				'orders.creation_user_id',
				'orders.creation_datetime',
				'orders.creation_session_id',
				'currencies.symbol',
				'currencies.name',
				'pay_types.name',
				'pay_types.description',
				'pay_type_contents.name',
				'status.description',
				'pay_status.description',
				'orders.coffee_gift_status_id',
				'coffee_gift_status.description',
				'orders.coffee_gift_datetime',
				'orders.date' )
			->order_by($order_by);		
			
		if (!empty($id)) $sql->where('invoices.id', '=', $id);
		if (!empty($order_id)) $sql->where('invoices.order_id', '=', $order_id);
		
		if (isset($filter_data['date_from'])) $sql->where('invoices.date', '>=', CMS::date($filter_data['date_from']));
		if (isset($filter_data['date_to'])) $sql->where('invoices.date', '<=', CMS::date($filter_data['date_to']));
			
		return $sql->execute()->as_array();
	}
	
	public function getInvoiceDetails($id = null, $invoice_id = null, $filter_data = array(), $lang_tag = null) {
		$lang_id = $this->lang_id;
		if (!empty($lang_tag)) {
			$lang_data = CMS::getLanguages(null, $lang_tag);
			if (count($lang_data) > 0) $lang_id = $lang_data[0]['id'];
		}
			
		
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND invoice_details.id = :id ";
		if (!is_null($invoice_id)) $filter .= " AND invoice_details.invoice_id = :invoice_id ";

		// GET DATA
		$sql = "SELECT
					order_details.id																		AS id,		
					invoice_details.qty																		AS qty,	
					IF(IFNULL(orders.no_vat,0)!=1,order_details.vat,0)										AS vat,
					order_details.product_title																AS product_title,	
					order_details.product_code																AS product_code,	
					order_details.product_reference															AS product_reference,	
					
					orders.id																				AS order_id,	
					
					currencies.symbol																		AS curr_symbol,
					currencies.name																			AS curr_name,
					
					products.id																				AS product_id,
					product_references.id																	AS product_reference_id,
					product_references.image_src 															AS product_image_src,
					product_references.balance																AS product_reference_balance,
					CASE 
						WHEN products.coffee_gift_active = 1 THEN products.coffee_gift_amount
						ELSE 0
					END																						AS product_coffee_gift_amount,
					
					product_categories.category_id															AS category_id,
					category_contents.alias																	AS l_category_alias,
					category_contents.title																	AS l_category_title,
					:pro_sub_category_id																	AS pro_sub_category_id,
					
					order_details.price																		AS price,						
					IF(IFNULL(orders.no_vat,0) != 1,
						ROUND(order_details.price * (1 + IFNULL(order_details.vat,0) / 100),2),
						order_details.price )																AS full_price,					
					
					order_details.original_price															AS original_price,				
					IF(IFNULL(orders.no_vat,0) != 1,
						ROUND(order_details.original_price * (1 + IFNULL(order_details.vat,0) / 100),2),
						order_details.original_price )														AS full_original_price,	
					
					order_details.discount_percents															AS discount_percents,
					order_details.discount_level															AS discount_level,		
					products.discount_color																	AS discount_color,			
					order_details.coffee_gift_amount														AS coffee_gift_amount,
					
					unit_types.description																	AS unit_type_description,
					
					order_details.qty - (	SELECT IFNULL(SUM(IFNULL(invoice_details.qty,0)),0)
											FROM invoice_details
											WHERE invoice_details.order_detail_id = order_details.id)		AS balance_qty,
										
					product_contents.id																		AS l_id,
					product_contents.language_id															AS l_language_id,
					product_contents.1_title																AS l_title,
					product_contents.1_description															AS l_description,
					product_contents.alias																	AS l_alias
				FROM
					invoice_details
					JOIN order_details ON
						invoice_details.order_detail_id = order_details.id
					JOIN orders ON
						orders.id = order_details.order_id	
					LEFT JOIN currencies ON
						orders.currency_id = currencies.id
					JOIN product_references ON
						order_details.product_reference_id = product_references.id					
					JOIN products ON
						product_references.product_id = products.id 
					LEFT JOIN product_contents ON
						products.id = product_contents.product_id AND
						product_contents.language_id = :lang_id
					LEFT JOIN product_categories ON
						products.id = product_categories.product_id
					LEFT JOIN category_contents ON
						product_categories.category_id = category_contents.category_id AND
						category_contents.language_id = :lang_id 
					LEFT JOIN types unit_types ON
						unit_types.type_id = order_details.unit_type_id AND
						unit_types.table_type_name = 'products_unit_type_id'
				WHERE
					1 = 1
					" . $filter . "
				ORDER BY
					order_details.item ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':id', $id);
		$res->bind(':invoice_id', $invoice_id);
		$res->bind(':lang_id', $lang_id);
		
		// PRO CATEGORY
		$pro_category_setting_id_sql = $this->db->select('category_settings.id')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_settings')->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->where('category_setting_values.category_setting_id', 'IN', array(18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('IF("category_setting_values.category_setting_id" = 19, 1, 0) DESC')
			->order_by('category_settings.order_index')
			->limit(1);
		$res->bind(':pro_sub_category_id', $pro_category_setting_id_sql);

		$db_data = $res->execute()->as_array();

		return $db_data;
	}
	
	public function getInvoiceTotal($invoice_id) {
		$db_data = $this->db->select(
				array('SUM("invoice_details.qty")', 'qty'), 
				array('COUNT("invoice_details.qty")', 'item_qty'), 
									
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "invoice_details.qty",2)) / (1 + AVG("order_details.vat")/100),
							SUM(ROUND("order_details.price" * "invoice_details.qty",2)) )', 'price_wo_vat'), 
				
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "invoice_details.qty",2)),
							SUM(ROUND("order_details.price" * "invoice_details.qty",2)) )', 'price'), 
				array('IF(IFNULL("orders.no_vat",0) != 1,
							SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "invoice_details.qty",2)) - (SUM(ROUND(ROUND("order_details.price" * (1 + "order_details.vat" / 100),2) * "invoice_details.qty",2)) / (1 + AVG("order_details.vat")/100)),
							0 )', 'vat'), 
				array('currencies.symbol', 'curr_symbol'), 
				array('ROUND(SUM(CASE WHEN "products.coffee_gift_active" = 1 THEN (IFNULL("products.coffee_gift_amount",0) * "invoice_details.qty") ELSE 0 END), 2)', 'product_coffee_gift_amount'), 
				array('ROUND(SUM(IFNULL("order_details.coffee_gift_amount",0) * "invoice_details.qty"), 2)', 'coffee_gift_amount'))
			->from('invoices')
			->join('invoice_details', 'LEFT')
				->on('invoices.id', '=', 'invoice_details.invoice_id')
			->join('orders')
				->on('orders.id', '=', 'invoices.order_id')
			->join('order_details', 'LEFT')
				->on('order_details.order_id', '=', 'orders.id')
				->on('order_details.id', '=', 'invoice_details.order_detail_id')
			->join('product_references', 'LEFT')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('products', 'LEFT')
				->on('product_references.product_id', '=', 'products.id')
			->join('currencies', 'LEFT')
				->on('orders.currency_id', '=', 'currencies.id')
			->where('invoices.id', '=', $invoice_id)
			->group_by(
				'currencies.symbol',
				'orders.no_vat')
			->execute()
			->as_array();

		return $db_data[0];
	}

	public function issuePavadzime($order_id, $type, $shipping = 0, $order_detail_list = array(), $post_data = array()) {
		// CONFIG
		if (!is_numeric($type)) $type = 1;
		if (empty($shipping)) $shipping = 0; else $shipping = 1;
		if ($type == 1) $shipping = 1;
		if (empty($order_detail_list)) $order_detail_list = array('0');
		$number = null;
		
		// GET INVOICE DATA
		$all_inv_data = $this->db->select(
				array('MAX("invoices.number")', 'max_number'),
				array('MAX("invoices.shipping")', 'max_shipping'),
				array('COUNT("invoices.id")', 'cnt'))
			->from('invoices')
			->where('invoices.order_id', '=', $order_id)
			->execute()
			->as_array();
		
		if (!empty($all_inv_data[0]['max_shipping'])) $shipping = 0;
		if (empty($all_inv_data[0]['max_number']) && empty($all_inv_data[0]['cnt']) && $type == 1) $number = null;
		else $number = (!is_numeric($all_inv_data[0]['max_number'])?0:$all_inv_data[0]['max_number'])+1;
		
		// GET ALLOWED ORDER DETAILS
		$order_details_sql = $this->db->select(
				array('order_details.id', 'id'),
				array('"order_details.qty" - IFNULL(SUM(IFNULL("invoice_details.qty",0)),0)', 'balance_qty') )
			->from('order_details')
			->join('invoice_details', 'LEFT')
				->on('order_details.id', '=', 'invoice_details.order_detail_id')
			->where('order_details.order_id', '=', $order_id)
			->group_by(
				'order_details.id',
				'order_details.qty' )
			->having('"order_details.qty" - IFNULL(SUM(IFNULL("invoice_details.qty",0)),0)', '>', DB::expr('0'));
		if ($type == 2) $order_details_sql->where('order_details.id', 'IN', $order_detail_list);
		$order_details = $order_details_sql->execute()->as_array();
		
		if (count($order_details) > 0 || $shipping == 1) {
			// CREATE INVOICE
			$db_data = $this->db->insert('invoices', array(
					'order_id',
					'number',
					'date',
					'shipping',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$order_id,
					$number,
					DB::expr('NOW()'),
					$shipping,
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
			$invoice_id = $db_data[0];	
			
			for ($i=0; $i<count($order_details); $i++) {
				$qty = $order_details[$i]['balance_qty'];
				if ($type == 2 && isset($post_data['issue_order_detail_qty_'.$order_details[$i]['id']]) && is_numeric($post_data['issue_order_detail_qty_'.$order_details[$i]['id']]) && $qty >= $post_data['issue_order_detail_qty_'.$order_details[$i]['id']]) {
					$qty = $post_data['issue_order_detail_qty_'.$order_details[$i]['id']];
				}
				
				$db_data = $this->db->insert('invoice_details', array(
					'invoice_id',
					'order_detail_id',
					'qty',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$invoice_id,
					$order_details[$i]['id'],
					$qty,				
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();	
			}	
			
			// UPDATE ORDER STATUS
			$this->setStatusFromPavadzime($order_id);
		}
		
		
		//
		// SEND EMAIL
		/*
		// TODO - Pareizs pavadzīmes epasta teksts - Šobrīd šo iespēju netaisām un epastu par pavadzīmi nesūtām
		$invoice_data = $this->getInvoices($invoice_id);
		
		$mail_to = $invoice_data[0]['email'];
		$mail_from = CMS::getSettings('default.email');
		$mail_from_name = CMS::getSettings('default.site_name');
		if (Valid::email($mail_to) AND Valid::email($mail_from)) {
			// GET TOTAL DATA
			$total_data = $this->getInvoiceTotal($invoice_id);
			$total_data['total_vat'] = round($total_data['price'], 2);
			$total_data['total_vat'] += round($invoice_data[0]['shipping_total'] * (1 + $invoice_data[0]['shipping_vat'] / 100), 2);

			// RENDER CONTENT
			$body = CMS::getLexicons('emails.order_paid_bank');
			$body = str_replace(':person_name', $invoice_data[0]['contact_name'], $body);
			$body = str_replace(':order_nr', '19B' . $invoice_data[0]['full_number'], $body);
			$body = str_replace(':oredr_value', $total_data['total_vat'] . ' ' . $invoice_data[0]['curr_symbol'], $body);

			// COFFEE GIFT
			$coffee_gift = '';
			if ($total_data['coffee_gift_amount'] > 0) {
				$amount = $total_data['coffee_gift_amount'] . ' ' . $invoice_data[0]['curr_symbol'];
				$coffee_gift = '<p>' . CMS::getLexicons('emails.order_coffee_gift_info') . '</p>';
				$coffee_gift = str_replace(':amount', $amount, $coffee_gift);
			}
			$body = str_replace(':coffee_gift_info', $coffee_gift, $body);

			// SEND MAIL
			$this->email = Model::factory('manager_emails');

			// PAVADZIME
			$filename = 'files/orders/pavadzime-' . $invoice_id . '.pdf';
			if (!file_exists($this->base_path . $filename))
				$this->createPavadzime($invoice_id);

			// SEND EMAIL TO BUYER
			$mail_data = array('from_email' => $mail_from, 'from_name' => $mail_from_name, 'to_email' => $mail_to, 'subject' => CMS::getLexicons('emails.order_paid_bank_title'), 'body' => $body, 'body_type' => 'text/html', 'attachments' => array($filename));
			$new_mail = $this->email->add_email($mail_data);

			$this->email->send_all_emails();
		}
		*/
		
		return $invoice_id;
	}

	public function createPavadzime($invoice_id) {
		// GET ORDER
		$user_data = $this->user->userData($this->user_id);
		$inv_data = $this->getInvoices($invoice_id);

		//
		// GENERATE PDF
		//

		// SET LOCALE
		setlocale(LC_TIME, 'lv_LV.utf8');

		$tpl_data['invoice'] = $inv_data[0];
		$invoice_details = $this->getInvoiceDetails(null, $invoice_id, array(), $inv_data[0]['invoice_lang']);
		$tpl_data['products'] = $invoice_details;

		// COMPANY DATA
		$company = array('name' => CMS::getSettings('order.company', $this->lang_id), 'reg_nr' => CMS::getSettings('order.reg_nr', $this->lang_id), 'address' => CMS::getSettings('order.address', $this->lang_id), 'bank' => CMS::getSettings('order.bank', $this->lang_id), 'bank_code' => CMS::getSettings('order.bank_code', $this->lang_id), 'account' => CMS::getSettings('order.account', $this->lang_id));
		$tpl_data['company'] = $company;

		// TOTALS
		$total_data = array('total' => 0, 'total_vat' => 0, 'vat' => 0);

		$total_data = $this->getInvoiceTotal($invoice_id);
		$total_data['vat'] = round($total_data['vat'], 2);
		$total_data['total_vat'] = round($total_data['price'], 2);

		$total_data['total_vat'] += round($inv_data[0]['shipping_total'] * (1 + $inv_data[0]['shipping_vat'] / 100), 2);
		$total_data['vat'] += round($inv_data[0]['shipping_total'] * ($inv_data[0]['shipping_vat'] / 100), 2);
		
		if ($inv_data[0]['invoice_lang'] == 'en') $total_data['total_vat_lv'] = $this->number_to_en($total_data['total_vat']);
		else $total_data['total_vat_lv'] = $this->number_to_lv($total_data['total_vat']);
		
		$tpl_data['total'] = $total_data;

		// COFFEE GIFT
		$order_coffee_gift_info = '';
		if ($total_data['coffee_gift_amount'] > 0) {
			$coffee_amount = $total_data['coffee_gift_amount'] . ' ' . $inv_data[0]['curr_symbol'];
			$def_lang = CMS::getLanguages(CMS::$lang_id);
			$order_coffee_gift_info = CMS::getLexicons('emails.order_coffee_gift_info', array(), $def_lang[0]['tag']);
			$order_coffee_gift_info = str_replace(':amount', $coffee_amount, $order_coffee_gift_info);
		}
		$tpl_data['order_coffee_gift_info'] = $order_coffee_gift_info;

		// DOCUMENT LANGUAGE
		$doc_lang = mb_strtolower($inv_data[0]['invoice_lang']);
		if (!file_exists($this->base_path.'application/views/plugins/orders/'.$doc_lang.'_pavadzime_pdf.tpl')) $doc_lang = 'lv';

		$tpl_data['action'] = 'load';
		$tpl_data['base_url'] = $this->base_url;
		$pdf = View_MPDF::factory('plugins/orders/'.$doc_lang.'_pavadzime_pdf', $tpl_data);

		// SAVE TO FILE
		if (!file_exists($this->base_path . 'files/orders'))
			mkdir($this->base_path . 'files/orders');
		$filename = 'files/orders/pavadzime-' . $invoice_id . '.pdf';
		file_put_contents($this->base_path . $filename, $pdf);
	}

	/*
	 * AdvancePayments
	 */
	public function getAdvancePayments($date_from, $date_to) {			
		// SELECT
		$sub_inv_sql = $this->db->select('IF(IFNULL(SUM("invoices.shipping"),0)>0,1,0)')
			->from('invoices')
			->where('invoices.order_id', '=', DB::expr('orders.id'))
			->where('invoices.date', '<=', DB::expr("'".CMS::date($date_to)."'"));
		$sub_inv = $sub_inv_sql->compile(Database::instance());
			
		$sub_sql = $this->db->select(
				array('orders.id', 'id'), 
				array('orders.contact_name', 'contact_name'), 
				array('orders.company', 'company'), 
				array('IF("orders.pay_status_id"=10,"orders.pay_date",NULL)', 'pay_date'),
				array('IF (CHAR_LENGTH("orders.number") < 3,LPAD("orders.number",3,\'0\'),"orders.number")', 'number'), 
				
				array('IF(IFNULL("orders.no_vat",0) != 1,
							(ROUND(ROUND("order_details.price" * (1 + IFNULL("order_details.vat",0) / 100),2) * ("order_details.qty" - SUM(IF("invoices.id" IS NOT NULL,IFNULL("invoice_details.qty",0),0))),2)),
							(ROUND("order_details.price" * ("order_details.qty" - SUM(IF("invoices.id" IS NOT NULL,IFNULL("invoice_details.qty",0),0))),2)) )', 'total'),
				array('IF(IFNULL("orders.no_vat",0) != 1,
							ROUND("orders.shipping_total" * (1 + "orders.shipping_vat" / 100) * (1 - ('.$sub_inv.')),2),
							ROUND("orders.shipping_total" * (1 - ('.$sub_inv.')),2) )', 'total_shipping') )
			->from('orders')
			->join('order_details', 'LEFT')
				->on('orders.id', '=', 'order_details.order_id')
			->join('invoices', 'LEFT')
				->on('invoices.order_id', '=', 'orders.id')
				->on('invoices.date', '<=', DB::expr("'".CMS::date($date_to)."'"))
			->join('invoice_details', 'LEFT')
				->on('order_details.id', '=', 'invoice_details.order_detail_id')
				->on('invoice_details.invoice_id', '=', 'invoices.id')
			->where('orders.status_id', '>=', '10')
			->where('orders.pay_status_id', '=', '10')
			->where('orders.pay_date', '>=', CMS::date($date_from))
			->where('orders.pay_date', '<=', CMS::date($date_to))
			->group_by(
				'orders.id',
				'orders.contact_name',
				'orders.company',
				'orders.pay_status_id',
				'orders.pay_date',
				'orders.number',
				'orders.no_vat',
				'order_details.id',
				'order_details.price',
				'order_details.vat',
				'order_details.qty',
				'orders.shipping_total',
				'orders.shipping_vat' )
			->order_by('"orders.pay_date" ASC, "orders.number" ASC');
			
		$db_data = $this->db->select(
				array('data.id', 'id'),
				array('data.contact_name', 'contact_name'), 
				array('data.company', 'company'), 
				array('data.pay_date', 'pay_date'),
				array('data.number', 'number'),
				array('SUM("data.total") + (SUM("data.total_shipping") / IF(COUNT("data.id")>0,COUNT("data.id"),1))', 'total_total') )
			->from(array($sub_sql, 'data'))
			->group_by(
				'data.id',
				'data.contact_name',
				'data.company',
				'data.pay_date',
				'data.number' )
			->having('SUM("data.total") + (SUM("data.total_shipping") / IF(COUNT("data.id")>0,COUNT("data.id"),1))', '!=', '0')
			->order_by('"data.pay_date" ASC, "data.number" ASC')
			->execute()
			->as_array();
			
		return $db_data;
	}

	//
	// CHECK CHECKOUT
	//
	public function check_checkout($data) {
		$status = array('status' => 0, 'error' => '', 'response' => '');

		$order_data = $this->getOrders($data['order_id'], $this->user_id, array('status_id' => '1'));
		if (count($order_data) > 0) {
			// CHECK ORDER BALANCE
			if ($this->checkOrderBalance($order_data[0]['id'])) {
				$this->session->set('current_order_id', $order_data[0]['id']);
				$status['status'] = '1';
			} else {
				$status['status'] = '0';
				$status['error'] = __('order_checkout.error_order_balance_coffee');
				
				if (!empty($order_data[0]['non_stock'])) $status['error'] .= '<br/>'.__('order_checkout.error_order_balance').'<br/>'.__('order_checkout.error_order_balance_delivery');

				// GET LIST DATA
				$tpl_data['action'] = 'configure_data';
				$tpl_data['products'] = $this->getOrderDetails(null, $data['order_id']);

				// BOOK DATA
				$product_page = CMS::getDocuments(CMS::$products_page_id);
				$tpl_data['product_page'] = isset($product_page[0]) ? $product_page[0] : array();

				$status['configure_data'] = $this->tpl->factory('plugins/orders/current_order', $tpl_data)->render();
			}
		} else {
			$this->session->delete('current_order_id');
			$status['error'] = CMS::getLexicons('current_order.no_current_order');
		}

		return $status;
	}

	//
	// CHECK PAYMENT
	//
	public function check_payment($data) {
		$status = array('status' => 0, 'error' => '', 'response' => '');
		
		// GET ORDER DATA
		$order_data = $this->getOrders($data['order_id'], $this->user_id, array('status_id' => '1'));

		if (count($order_data) == 0) {
			$this->session->delete('current_order_id');
			$status['error'] = $status['error'] .= CMS::getLexicons('current_order.no_current_order') . '<br/>';
		} else {
			$order_data = $order_data[0];
			
			// SHIPPING
			if (!empty($data['shipping_id'])) {
				$cms_shippings = Model::factory('manager_products_shippings');
				$shippings = $cms_shippings->getShippings($data['shipping_id'], $this->lang_id, array('from_status_id' => '10', 'order_id' => $order_data['id']));
				if (count($shippings) > 0) {
					$data['shipping_info'] = $shippings[0]['l_description'];
					$data['shipping_vat'] = $shippings[0]['vat_type_value'];
					$data['invoice_lang'] = $shippings[0]['invoice_language_tag'];
					$data['shipping_total'] = $shippings[0]['total'];
					
					/* Neizmantojam Qty
					if (empty($shippings[0]['price_qty']) || $shippings[0]['price_qty'] == 0)
						$data['shipping_total'] = $shippings[0]['price'];
					else {
						$total_qty = $this->db->select(array('SUM("order_details.qty")', 'sum'))->from('order_details')->where('order_details.order_id', '=', $data['order_id'])->execute()->as_array();
						$data['shipping_total'] = $shippings[0]['price'] * $total_qty[0]['sum'];
					}
					*/
	
					if ($data['shipping_id'] == 5 && empty($data['shipping_statoil_id'])) $status['error'] .= CMS::getLexicons('order_checkout.error_shipping_statoil') . '<br/>';
					if ($data['shipping_id'] == 9 && empty($data['shipping_pickup_time'])) $status['error'] .= CMS::getLexicons('order_checkout.error_pickup_time') . '<br/>';
				} else {
					$status['error'] .= CMS::getLexicons('order_checkout.error_shipping') . '<br/>';
				}
			} else {
				$status['error'] .= CMS::getLexicons('order_checkout.error_shipping') . '<br/>';
			}
			
			// CHECK ORDER BALANCE
			if ($this->checkOrderBalance($order_data['id'])) {
				if (!empty($status['error'])) {
					$status['status'] = '0';
				} else {
					$order_id = $this->updateShippingData($data);
					$status['status'] = '1';
				}
			} else {
				$status['status'] = '3';
			}
		}

		return $status;
	}

	//
	// CHECK PAYMENT
	//
	public function check_payment2($data) {
		$status = array('status' => 0, 'error' => '', 'response' => '');
		
		//setlocale(LC_ALL, 'de_DE.utf8');
		//setlocale(LC_ALL, 'ru_RU.utf8');

		if (empty($data['pay_type_id']))
			$status['error'] .= CMS::getLexicons('order_checkout.error_pay_type') . '<br/>';
		if (empty($data['contact_name']) || preg_match('/[1234567890,.\'"\\/\\\\`~!@#$%^&*()_+=?><|]/i', $data['contact_name']))
			$status['error'] .= CMS::getLexicons('order_checkout.error_contact_name') . '<br/>';
		if (empty($data['email']) || !Valid::email($data['email']))
			$status['error'] .= CMS::getLexicons('order_checkout.error_email') . '<br/>';
		if (empty($data['phone']))
			$status['error'] .= CMS::getLexicons('order_checkout.error_phone') . '<br/>';
		if (!empty($data['vat_nr']) && !preg_match('/^[a-zA-Z][a-zA-Z][0-9]+$/i', trim($data['vat_nr']))) 
			$status['error'] .= CMS::getLexicons('user_registration.error_vat_nr').'<br/>';
		if (!empty($data['company']) && empty($data['reg_nr']) && empty($data['vat_nr'])) 
			$status['error'] .= CMS::getLexicons('user_registration.error_company_reg_vat').'<br/>';
		
		
		$order_data = $this->getOrders($data['order_id'], $this->user_id, array('status_id' => '1'));
		if (count($order_data) == 0) {
			$this->session->delete('current_order_id');
			$status['error'] = $status['error'] .= CMS::getLexicons('current_order.no_current_order') . '<br/>';
		} else {
			$order_data = $order_data[0];
			if (!in_array($order_data['shipping_id'], array(5,9)) && empty($data['address']))
				$status['error'] .= CMS::getLexicons('order_checkout.error_address') . '<br/>';
		}

		// CHECK ORDER BALANCE
		if ($this->checkOrderBalance($order_data['id'])) {
			if (!empty($status['error'])) {
				$status['status'] = '0';
			} else {
				// SAVE DATA
				if ($data['pay_type_id'] == '2') {
					// CREDIT CARD
					$data['status_id'] = '1';
					$data['order_id'] = $order_data['id'];
					$order_id = $this->approveOrder($data);

					$status['status'] = '2';
					$status['order_id'] = $order_id;
				} else {
					// SHOW CONFIRM
					
					// PLACE ORDER
					$data['status_id'] = '1';
					$data['order_id'] = $order_data['id'];
					$order_id = $this->approveOrder($data);
		
					$status['status'] = '1';
				}
			}
		} else {
			$status['status'] = '3';
		}

		return $status;
	}

	function check_confirm($data) {
		$order_data = $this->getOrders($data['order_id'], $this->user_id, array('status_id' => '1'));
		
		if (count($order_data) > 0) {
			// PLACE ORDER
			$data['status_id'] = '10';
			$data['order_id'] = $order_data[0]['id'];
			$order_id = $this->approveOrder($data);
	
			// CREATE PDF AND SEND MAIL
			$this->createSendPDF($order_id);
	
			$this->session->set('current_order_id_done', '1');
		} else {	
			return false;
		}
		
		return true;
	}

	function number_to_lv($skaitlis) {
		$numtotext = Model::factory('manager_orders_numtotextlv');
		return $numtotext->PriceToText($skaitlis, 'EUR', true, true);
	}
	function number_to_en($skaitlis) {
		$numtotext = Model::factory('manager_orders_numtotexten');
		return $numtotext->PriceToText($skaitlis, 'EUR', true, true);
	}

	public function update_qty($order_detail_id, $qty) {
		$sql = "UPDATE
					order_details
				SET
					qty = :qty,
					user_id = :user_id,
					datetime = NOW()
				WHERE
					order_details.id = :order_detail_id ";
		$res = $this->db->query(Database::UPDATE, $sql);
		$res->bind(':order_detail_id', $order_detail_id);
		$res->bind(':qty', $qty);
		$res->bind(':user_id', $this->user_id);
		$db_data = $res->execute();

		if ($qty <= 0) {
			$this->db->delete('order_details')->where('order_details.id', '=', $order_detail_id)->execute();
		}
	}

	public function checkOrderBalance($order_id) {
		// USE ONLY FOR COFFEE
		$db_data = $this->db->select(
				array('order_details.id', 'id'), 
				array('order_details.qty', 'qty'), 
				array('product_references.balance', 'balance'))
			->from('order_details')
			->join('product_references')
				->on('order_details.product_reference_id', '=', 'product_references.id')
			->join('product_categories')
				->on('product_references.product_id', '=', 'product_categories.product_id')
			->join('category_settings', 'LEFT')
				->on('product_categories.category_id', '=', 'category_settings.category_id')
			->where('order_details.order_id', '=', $order_id)
			->where_open()
				->where('product_categories.category_id', '=', '1')
				->or_where_open()
					->where('product_categories.category_id', '=', '8')
					->where('category_settings.id', '=', '19')
				->or_where_close()
			->where_close()
			->group_by(
				'order_details.id',
				'order_details.qty',
				'product_references.balance' )
			->having('ROUND("order_details.qty",0)', '>', DB::expr('ROUND(product_references.balance,0)'))
			->execute()
			->as_array();

		if (count($db_data) > 0) {
			return false;
		} else {
			return true;
		}
		
		// NOT USED
		/*
		$db_data = $this->db->select(array('order_details.id', 'id'), array('order_details.qty', 'qty'), array('product_references.balance', 'balance'))->from('order_details')->join('product_references')->on('order_details.product_reference_id', '=', 'product_references.id')->where('order_details.order_id', '=', $order_id)->having('order_details.qty', '>', DB::expr('product_references.balance'))->execute()->as_array();

		if (count($db_data) > 0) {
			return false;
		} else {
			return true;
		}
		*/
	}

	public function discount_level_2($order_id) {
		// GET TOTAL DATA
		$total = $this->db->select(
				array('SUM(ROUND(("order_details.original_price" * (1 + IF(IFNULL("orders.no_vat",0)!=1,"order_details.vat",0) / 100)),2) * "order_details.qty")', 'price'), 
				array('SUM("order_details.qty")', 'qty'))
			->from('order_details')
			->join('orders')
				->on('order_details.order_id', '=', 'orders.id')
			->where('order_details.order_id', '=', $order_id)
			->where('order_details.discount_level', '!=', '1')
			->execute()
			->as_array();

		$discount_percents = 0;
		$discount_level = 0;

		switch(CMS::getSettings('discounts_level_2.type')) {
			case 'price' :
				// GET LEVEL 2 DISCOUNTS
				$discounts_data = $this->db->select(array('discounts.value', 'value'), array('discounts.percents', 'percents'))->from('discounts')->where('discounts.level', '=', '2')->where('discounts.type', '=', 'price')->order_by('discounts.value', 'DESC')->execute()->as_array();

				for ($i = 0; $i < count($discounts_data) && $discount_percents == 0; $i++) {
					if ($total[0]['price'] >= $discounts_data[$i]['value']) {
						$discount_percents = $discounts_data[$i]['percents'];
					}
				}
				break;
			case 'qty' :
				// GET LEVEL 2 DISCOUNTS
				$discounts_data = $this->db->select(array('discounts.value', 'value'), array('discounts.percents', 'percents'))->from('discounts')->where('discounts.level', '=', '2')->where('discounts.type', '=', 'qty')->order_by('discounts.value', 'DESC')->execute()->as_array();

				for ($i = 0; $i < count($discounts_data) && $discount_percents == 0; $i++) {
					if ($total[0]['qty'] >= $discounts_data[$i]['value']) {
						$discount_percents = $discounts_data[$i]['percents'];
					}
				}
				break;
			default :
				break;
		}

		// UPDATE DISCOUNT
		if (empty($discount_percents) || !is_numeric($discount_percents))
			$discount_percents = 0;
		if ($discount_percents > 0)
			$discount_level = 2;

		$this->db->update('order_details')->set(array('price' => DB::expr('order_details.original_price * (1 - (' . $discount_percents . ' / 100))'), 'discount_percents' => $discount_percents, 'discount_level' => $discount_level))->where('order_details.order_id', '=', $order_id)->where('order_details.discount_level', '!=', '1')->execute();
	}

	public function getStatoilAddress($id, $lang_id) {
		$sql = $this->db->select(array('types.id', 'id'), array('types.description', 'name'), array('types.value', 'code'), array('type_contents.name', 'address'))->from('types')->join('type_contents')->on('type_contents.type_id', '=', 'types.id')->where('types.table_type_name', '=', 'shippings_statoil_id')->where('type_contents.language_id', '=', $lang_id)->order_by('types.order_index')->order_by('types.description');

		if (!empty($id))
			$sql->where('types.id', '=', $id);

		return $sql->execute()->as_array();
	}

	public function setStatus($order_id, $status_id) {
		// UPDATE BALANCE
		$order_data = $this->getOrders($order_id);
		$total_data = $this->getOrderTotal($order_id);
		if ($order_data[0]['status_id'] < 10 && $status_id >= 10) {
			$this->updateBalance($order_id, '-');
		} elseif ($order_data[0]['status_id'] >= 10 && $status_id < 10) {
			$this->updateBalance($order_id, '+');
		}

		$this->db->update('orders')->set(array('status_id' => $status_id, 'user_id' => $this->user_id, 'datetime' => DB::expr('NOW()')))->where('orders.id', '=', $order_id)->execute();
		
		// UPDATE STATUSS TO ISSUED
		if ($status_id == 20) $this->setStatusFromPavadzime($order_id);
	}

	public function setPayStatus($order_id, $pay_status_id, $pay_date) {
		if ($pay_status_id < 10) $pay_data = null;
		
		$this->db->update('orders')
			->set(array(
				'pay_status_id' => $pay_status_id, 
				'pay_date' => CMS::date($pay_date),
				'user_id' => $this->user_id, 
				'datetime' => DB::expr('NOW()')))
			->where('orders.id', '=', $order_id)
			->execute();
			
		//
		// SEND EMAIL
		// 
		if ($pay_status_id >= 10) {
			$order_data = $this->getOrders($order_id);
			
			$mail_to = $order_data[0]['email'];
			$mail_from = CMS::getSettings('default.email');
			$mail_from_name = CMS::getSettings('default.site_name');
			if (Valid::email($mail_to) AND Valid::email($mail_from)) {
				// GET TOTAL DATA
				$total_data = $this->getOrderTotal($order_id);
				$total_data['total_vat'] = round($total_data['price'], 2);
				$total_data['total_vat'] += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100), 2);
	
				// RENDER CONTENT
				$body = CMS::getLexicons('emails.order_paid_bank');
				$body = str_replace(':person_name', $order_data[0]['contact_name'], $body);
				$body = str_replace(':order_nr', '19B' . $order_data[0]['number'], $body);
				$body = str_replace(':oredr_value', number_format($total_data['total_vat'],2,'.','') . ' ' . $order_data[0]['curr_symbol'], $body);
	
				// COFFEE GIFT
				$coffee_gift = '';
				if ($total_data['coffee_gift_amount'] > 0) {
					$amount = $total_data['coffee_gift_amount'] . ' ' . $order_data[0]['curr_symbol'];
					$coffee_gift = '<p>' . CMS::getLexicons('emails.order_coffee_gift_info') . '</p>';
					$coffee_gift = str_replace(':amount', $amount, $coffee_gift);
				}
				$body = str_replace(':coffee_gift_info', $coffee_gift, $body);
	
				// SEND MAIL
				$this->email = Model::factory('manager_emails');
	
				// PAVADZIME
				$filename = 'files/orders/invoice-' . $order_id . '.pdf';
				if (!file_exists($this->base_path . $filename)) $this->createSendPDF($order_id, false);
	
				// SEND EMAIL TO BUYER
				$mail_data = array('from_email' => $mail_from, 'from_name' => $mail_from_name, 'to_email' => $mail_to, 'subject' => CMS::getLexicons('emails.order_paid_bank_title'), 'body' => $body, 'body_type' => 'text/html', 'attachments' => array($filename));
				$new_mail = $this->email->add_email($mail_data);
	
				$this->email->send_all_emails();
			}
		}
	}
	
	public function setStatusFromPavadzime($order_id) {
		// GET INVOICES DATA
		$invoices = $this->getInvoices(null, $order_id);
		
		// UPDATE ONLY WITH INVOICES DATA
		if (count($invoices) > 0) {
			// GET INVOICE DATA
			$inv_data = $this->db->select(array('MAX("invoices.shipping")', 'max_shipping'))
				->from('invoices')
				->where('invoices.order_id', '=', $order_id)
				->execute()
				->as_array();
			
			if (!empty($inv_data[0]['max_shipping'])) {	
				// GET ALLOWED ORDER DETAILS
				$ord_det_data = $this->db->select(
						array('order_details.id', 'id'),
						array('order_details.qty', 'qty') )
					->from('order_details')
					->join('invoice_details', 'LEFT')
						->on('order_details.id', '=', 'invoice_details.order_detail_id')
					->where('order_details.order_id', '=', $order_id)
					->group_by(
						'order_details.id',
						'order_details.qty' )
					->having('order_details.qty', '>', DB::expr('IFNULL(SUM(IFNULL(invoice_details.qty,0)),0)'))
					->execute()
					->as_array();	
				if (count($ord_det_data) > 0) $status_id = 30;
				else $status_id = 40;
			} else {
				$status_id = 30;
			}
			
			// UPDATE STATUS DATA
			$this->db->update('orders')
				->set(array(
					'status_id' => $status_id,
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('orders.id', '=', $order_id)
				->where('orders.status_id', '<', '50')
				->where('orders.status_id', '>=', '10')
				->execute();
		}
	}

	public function setCoffeeGiftStatus($order_id, $status_id) {
		$update_data = array('coffee_gift_status_id' => $status_id);
		if ($status_id == 20)
			$update_data['coffee_gift_datetime'] = DB::expr('NOW()');
		else
			$update_data['coffee_gift_datetime'] = DB::expr('NULL');

		$this->db->update('orders')->set($update_data)->where('orders.id', '=', $order_id)->execute();
	}

	public function updateBalance($order_id, $operation) {
		$this->products = new Model_Manager_Products_Products(10);

		$order_details = $this->getOrderDetails(null, $order_id);
		
		for ($i = 0; $i < count($order_details); $i++) {
			$ref_data = $this->products->getProductReferences($order_details[$i]['product_reference_id']);

			if ($operation == '-') {
				$stock_qty = $order_details[$i]['qty'];
				$order_qty = 0;
				
				$balance = $ref_data[0]['balance'] - $order_details[$i]['qty'];
				if ($balance < 0) {
					$balance = 0;
					$stock_qty = $ref_data[0]['balance'];
					$order_qty = $order_details[$i]['qty'] - $ref_data[0]['balance'];
				}

				$this->db->update('order_details')->set(array('stock_qty' => $stock_qty, 'order_qty' => $order_qty))->where('order_details.id', '=', $order_details[$i]['id'])->execute();
				
				$this->db->update('product_references')->set(array('balance' => $balance))->where('product_references.id', '=', $order_details[$i]['product_reference_id'])->execute();
			} elseif ($operation == '+') {
				$stock_qty = 0;
				$order_qty = 0;
				
				$balance = $ref_data[0]['balance'] + $order_details[$i]['stock_qty'];
				if ($balance < 0) $balance = 0;
				
				$this->db->update('order_details')->set(array('stock_qty' => $stock_qty, 'order_qty' => $order_qty))->where('order_details.id', '=', $order_details[$i]['id'])->execute();

				$this->db->update('product_references')->set(array('balance' => $balance))->where('product_references.id', '=', $order_details[$i]['product_reference_id'])->execute();
			}
		}
	}

}
