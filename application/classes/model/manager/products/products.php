<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Products_Products extends Model {
	public function __construct($content_type_id) {
		parent::__construct();
		
		if (empty($content_type_id)) Kohana::error_handler(true, 'NOT SET content_type_id');
		else $this->content_type_id = $content_type_id;
		
		$this->files = Model::factory('manager_files');
	}

	/**
	 * @param null $id
	 * @param null $lang_id
	 * @param array $filter_data
	 * @param null $limit
	 * @param null $offset
	 * @param int $order_by
	 * @param bool $count_all
	 * @return array
	 */
	public function getProducts($id = null, $lang_id = null, $filter_data = array(), $limit = null, $offset = null, $order_by = -1, $count_all = false) {
		// SELECT
		$res = $this->db->select();
		
		// FROM
		$res->from('products');
		if (!is_null($lang_id)) {
			$res->join('product_contents')
				->on('products.id', '=', 'product_contents.product_id')
				->on('product_contents.language_id', '=', DB::expr(!empty($lang_id)?$lang_id:'-1'));
		} else {
			$res->join('product_contents', 'LEFT')->on('products.id', '=', 'product_contents.product_id');
		} 
		
		$res->join('product_categories','LEFT')->on('products.id', '=', 'product_categories.product_id');
		$res->join('categories','LEFT')->on('product_categories.category_id', '=', 'categories.id');
		$res->join('category_contents','LEFT')->on('categories.id', '=', 'category_contents.category_id');
			if (!is_null($lang_id)) $res->on('category_contents.language_id', '=', DB::expr(!empty($lang_id)?$lang_id:'-1')); 
		
		$res->join(array('categories', 'parent_categories'),'LEFT')->on('categories.parent_id', '=', 'parent_categories.id');
		$res->join(array('category_contents', 'parent_category_contents'),'LEFT')
			->on('parent_categories.id', '=', 'parent_category_contents.category_id')
			->on('parent_category_contents.language_id', '=', 'category_contents.language_id'); 
		
		// WHERE
		if (!is_null($id)) $res->where('products.id', '=', $id);
		else $res->where('products.content_type_id', '=', $this->content_type_id);
		 		
		if (isset($filter_data['from_status_id'])) $res->where('products.status_id', '>=', $filter_data['from_status_id']);
		if (isset($filter_data['not_id'])) $res->where('products.id', '!=', $filter_data['not_id']);
		if (isset($filter_data['status_id'])) $res->where('products.status_id', 'IN', $filter_data['status_id']);
		if (!empty($filter_data['reference'])) $res->where('products.reference', 'LIKE', '%'.$filter_data['reference'].'%');
		if (isset($filter_data['active'])) $res->where('products.status_id', '>=', DB::expr('10'));
		if (isset($filter_data['category_id'])) $res->where('product_categories.category_id', 'IN', is_array($filter_data['category_id'])?$filter_data['category_id']:array($filter_data['category_id']));
		if (isset($filter_data['not_product_content_id'])) {
			if (empty($filter_data['not_product_content_id'])) $filter_data['not_product_content_id'] = array(0); 
			$res->where('product_contents.id', 'NOT IN', $filter_data['not_product_content_id']);
		}
		if (isset($filter_data['category_parent_id'])) {
			$sub_sql = $this->db->select('product_categories.product_id')
				->from('categories')
				->join('product_categories')->on('categories.id', '=', 'product_categories.category_id')
				->where('categories.parent_id', 'IN', is_array($filter_data['category_parent_id'])?$filter_data['category_parent_id']:array($filter_data['category_parent_id']));
			$res->where('products.id', 'IN', $sub_sql);
		} 
		if (!empty($filter_data['search'])) {
			$search_sql = $this->db->select('DISTINCT "products.id"')
				->from('products')
				->join('product_references', 'LEFT')
					->on('product_references.product_id', '=', 'products.id')
				->join('product_contents', 'LEFT')
					->on('product_contents.product_id', '=', 'products.id');
			$search_array = explode(' ', $filter_data['search']);
			for ($i=0; $i<count($search_array); $i++) {
				$search_sql->where('CONVERT(CONCAT(IFNULL("product_contents.1_title",\'\'),\' \',IFNULL("product_references.reference",\'\'),\' \',IFNULL("product_references.code",\'\')) USING UTF8)', 'LIKE', '%'.$search_array[$i].'%');
			}			
			$res->where('products.id', 'IN', $search_sql);
		}
		
		if (isset($filter_data['category_setting_value_id'])) {
			$sub_sql = $this->db->select('product_category_settings.product_id')
				->from('product_category_settings')
				->where('product_category_settings.category_setting_value_id', 'IN', is_array($filter_data['category_setting_value_id'])?$filter_data['category_setting_value_id']:array($filter_data['category_setting_value_id']));
			$res->where('products.id', 'IN', $sub_sql);
		}
		
		$inactive_sql = DB::expr('0');
		if (isset($filter_data['filter_category_setting_values_inactive']) && $filter_data['filter_category_setting_values_inactive'] == true) {
			// SHOW AS INACTIVE PRODUCT
			$inactive_filters_cnt = 0;
			$inactive_sql_array = array();
			if (isset($filter_data['filter_category_setting_values'])) {
				foreach ($filter_data['filter_category_setting_values'] as $filter => $values) {
					if (!empty($values) && !(count($values)==1 && empty($values[0])) && !in_array($filter, array('filter_price', 'filter_function'))) {
						$inactive_filters_cnt++;
						
						$sub_sql = $this->db->select('product_category_settings.product_id')
							->from('product_category_settings')
							->where('product_category_settings.category_setting_value_id', 'IN', $values);
						$inactive_sql_array[] = 'IF ("products.id" IN ('.$sub_sql.'), 1, 0)';
					}
				}	
				if (!empty($filter_data['filter_category_setting_values']['filter_function']) && is_array($filter_data['filter_category_setting_values']['filter_function'])) {
					foreach ($filter_data['filter_category_setting_values']['filter_function'] as $key => $val) {
						$inactive_filters_cnt++;
						
						$sub_sql = $this->db->select('product_category_settings.product_id')
							->from('product_category_settings')
							->where('product_category_settings.category_setting_value_id', '=', $val);
						$inactive_sql_array[] = 'IF ("products.id" IN ('.$sub_sql.'), 1, 0)';
					}
				}	
				if (!empty($filter_data['filter_category_setting_values']['filter_price'][0])) {					
					$prices = explode('-',$filter_data['filter_category_setting_values']['filter_price'][0]);
					if (count($prices) == 2 && is_numeric($prices[0]) && is_numeric($prices[1])) {
						$inactive_filters_cnt++;
						
						$inactive_sql_array[] = 'IF (
							ROUND("products.price" * (1 + IFNULL("vat_types.value",0) / 100), 2) >= '.$prices[0].' AND
							ROUND("products.price" * (1 + IFNULL("vat_types.value",0) / 100), 2) < '.$prices[1].', 1, 0)';
					}
				}

				if (count($inactive_sql_array) > 0) {
					$inactive_sql = 'IF (('.implode(' + ', $inactive_sql_array).') >= '.$inactive_filters_cnt.', 0, 1)';
				} 
			}
		} else {
			// HIDE FILTERD PRODUCTS		
			if (isset($filter_data['filter_category_setting_values'])) {
				foreach ($filter_data['filter_category_setting_values'] as $filter => $values) {
					if (!empty($values) && !(count($values)==1 && empty($values[0])) && !in_array($filter, array('filter_price', 'filter_function'))) {
						$sub_sql = $this->db->select('product_category_settings.product_id')
							->from('product_category_settings')
							->where('product_category_settings.category_setting_value_id', 'IN', $values);
						$res->where('products.id', 'IN', $sub_sql);
					}
				}	
				if (!empty($filter_data['filter_category_setting_values']['filter_function']) && is_array($filter_data['filter_category_setting_values']['filter_function'])) {
					foreach ($filter_data['filter_category_setting_values']['filter_function'] as $key => $val) {
						$sub_sql = $this->db->select('product_category_settings.product_id')
							->from('product_category_settings')
							->where('product_category_settings.category_setting_value_id', '=', $val);
						$res->where('products.id', 'IN', $sub_sql);
					}
				}	
				if (!empty($filter_data['filter_category_setting_values']['filter_price'][0])) {
					$prices = explode('-',$filter_data['filter_category_setting_values']['filter_price'][0]);
					if (count($prices) == 2 && is_numeric($prices[0]) && is_numeric($prices[1])) {
						$res->where('ROUND("products.price" * (1 + IFNULL("vat_types.value",0) / 100), 2)', '>=', $prices[0]);
						$res->where('ROUND("products.price" * (1 + IFNULL("vat_types.value",0) / 100), 2)', '<', $prices[1]);
					}
				}			
			}
		}
		
		if (isset($filter_data['parent_category_id'])) {
			$sub_sql = $this->db->select('product_categories.product_id')
				->from('categories')
				->join('product_categories')->on('categories.id', '=', 'product_categories.category_id')				
				->join(array('categories', 'parent_categories_1'), 'LEFT')->on('categories.parent_id', '=', 'parent_categories_1.id')
				->join(array('categories', 'parent_categories_2'), 'LEFT')->on('parent_categories_1.parent_id', '=', 'parent_categories_2.id')
				->join(array('categories', 'parent_categories_3'), 'LEFT')->on('parent_categories_2.parent_id', '=', 'parent_categories_3.id')
				->join(array('categories', 'parent_categories_4'), 'LEFT')->on('parent_categories_3.parent_id', '=', 'parent_categories_4.id')
				->join(array('categories', 'parent_categories_5'), 'LEFT')->on('parent_categories_4.parent_id', '=', 'parent_categories_5.id')
				->join(array('categories', 'parent_categories_6'), 'LEFT')->on('parent_categories_5.parent_id', '=', 'parent_categories_6.id')
				->join(array('categories', 'parent_categories_7'), 'LEFT')->on('parent_categories_6.parent_id', '=', 'parent_categories_7.id')
				->join(array('categories', 'parent_categories_8'), 'LEFT')->on('parent_categories_7.parent_id', '=', 'parent_categories_8.id')
				->join(array('categories', 'parent_categories_9'), 'LEFT')->on('parent_categories_8.parent_id', '=', 'parent_categories_9.id')
				->join(array('categories', 'parent_categories_10'), 'LEFT')->on('parent_categories_9.parent_id', '=', 'parent_categories_10.id')			
				
				->where('categories.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_1.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_2.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_3.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_4.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_5.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_6.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_7.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_8.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_9.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']))
				->or_where('parent_categories_10.id', 'IN', is_array($filter_data['parent_category_id'])?$filter_data['parent_category_id']:array($filter_data['parent_category_id']));
			$res->where('products.id', 'IN', $sub_sql);
		} 
		
		// GROUP BY
		if (isset($filter_data['show_all_colors']) && $filter_data['show_all_colors'] == true) {
			$res->select(
					array('product_references.id', 'product_reference_id'),
					array('product_references.image_src', 'product_reference_image_src') );
			$res->join('product_references')->on('products.id', '=', 'product_references.product_id');
			
			
			$res->group_by(
				'product_references.id',
				'product_references.image_src' );
		} else {
			$res->group_by('products.id');
		}

		// ONLY FOR COUNT ROWS
		if ($count_all) {
			$res->select(array('COUNT("*")', 'cnt'));
			$db_data = $res->execute()->as_array();
                  
			return count($db_data);
		}
                        
		// LIMIT
		if(!is_null($limit)) {
			// SELECT
			$res->select(array('products.id', 'id'));
                                                
			// LIMIT
			$res->limit($limit);
			if (!is_null($offset)) $res->offset($offset);
                  
			// DATA
			$db_data = $res->execute()->as_array();         
                                    
			$id_list = array();
			foreach($db_data as $key => $val) $id_list[] = $val['id'];
			$res->where('products.id', 'IN', !empty($id_list)?$id_list:array(-1));
                  
			$res->limit(NULL);
			$res->offset(NULL);                 
		}
		
		// SELECT
		$cat_sql = $this->db->select('CONCAT("category_contents.full_alias", \'-c\', "category_contents.category_id")')
				->from('product_categories')
				->join('category_contents')
					->on('category_contents.category_id', '=', 'product_categories.category_id')
				->where('product_categories.product_id', '=', DB::expr('products.id'))
				->where('category_contents.language_id', '=', $this->lang_id)
				->order_by('product_categories.id')
				->limit(1);
				
		// SETTINGS
		$functions_sql = $this->db->select('GROUP_CONCAT("category_setting_values.image_src" ORDER BY "category_setting_values.order_index" SEPARATOR \'-----\')')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', '=', 5)
			->where('product_category_settings.product_id', '=', DB::expr('products.id'));
			
		$coffee_category_sql = $this->db->select('GROUP_CONCAT("category_setting_value_contents.title" ORDER BY "category_setting_values.order_index" SEPARATOR \'-----\')')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_setting_value_contents')
				->on('category_setting_values.id', '=', 'category_setting_value_contents.category_setting_value_id')
			->where('category_setting_values.category_setting_id', '=', 17)
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->where('category_setting_value_contents.language_id', '=', DB::expr('product_contents.language_id'));
			
		$intensity_sql = $this->db->select('category_setting_value_contents.title')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_setting_value_contents')
				->on('category_setting_values.id', '=', 'category_setting_value_contents.category_setting_value_id')
			->where('category_setting_values.category_setting_id', '=', 1)
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->where('category_setting_value_contents.language_id', '=', DB::expr('product_contents.language_id'))
			->order_by('"category_setting_value_contents.title" DESC')
			->limit(1);
			
		$collection_sql = $this->db->select('GROUP_CONCAT("category_setting_values.id" ORDER BY "category_setting_values.order_index" SEPARATOR \'-----\')')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', 'IN', array(7,18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'));
			
		$balance_sql = $this->db->select('SUM(IFNULL("product_references.balance", 0))')
			->from('product_references')
			->where('product_references.product_id', '=', DB::expr('products.id'));
		
		// ORDER
		$coffee_order_sql = $this->db->select('category_setting_values.order_index')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', '=', 17)
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('category_setting_values.order_index')
			->limit(1);			
		$machines_order_sql = $this->db->select('category_setting_values.order_index')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', '=', 4)
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('category_setting_values.order_index')
			->limit(1);			
		$accessories_order_sql = $this->db->select('category_setting_values.order_index')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', '=', 7)
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('category_setting_values.order_index')
			->limit(1);
		$pro_order_sql = $this->db->select('category_settings.order_index')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_settings')->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->where('category_setting_values.category_setting_id', 'IN', array(18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('category_settings.order_index')
			->limit(1);
		$pro_order_sql2 = $this->db->select('category_setting_values.order_index')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', 'IN', array(18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('category_setting_values.order_index')
			->limit(1);
		$pro_category_setting_id_sql = $this->db->select('category_settings.id')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_settings')->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->where('category_setting_values.category_setting_id', 'IN', array(18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('IF("category_setting_values.category_setting_id" = 19, 1, 0) DESC')
			->order_by('category_settings.order_index')
			->limit(1);
		$product_order_sql = $this->db->select('product_category_settings.order_index')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->where('category_setting_values.category_setting_id', 'IN', array(17,4,7,18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('product_category_settings.order_index')
			->limit(1);
			
		$res->select(
			array('products.id','id'),
			array('products.reference','reference'),
			array('products.mfr_id','mfr_id'),
			array('products.price','price'),
			array('"products.price" * (1 + IFNULL("vat_types.value",0) / 100)','price_pvn'),
			array('products.currency_id','currency_id'),				
			array('products.discount_active','discount_active'),		
			array('products.discount_price','discount_price'),	
			array('products.discount_color', 'discount_color'),
			array('"products.discount_price" * 100 / NULLIF("products.price",0)', 'discount_percents'),
			array('"products.discount_price" * (1 + IFNULL("vat_types.value",0) / 100)','discount_price_pvn'),		
			array('products.coffee_gift_active','coffee_gift_active'),		
			array('products.coffee_gift_amount','coffee_gift_amount'),	
			array('products.new','new'),		
			array('products.gift','gift'),	
			array('products.datetime','datetime'),		
			array($inactive_sql, 'inactive'),
			
			array('CONCAT("product_contents.alias", \'-i\', "products.id")', 'product_alias'),
			array($cat_sql, 'product_category_alias'),
			
			array('products.main_image_id','main_image_id'),
			array('product_images.image_src','main_image_src'),
			
			array('main_product_references.id','reference_image_id'),
			array('main_product_references.image_src','reference_image_src'),
			array('main_product_references.reference','reference_reference'),
			array('main_product_references.balance','reference_balance'),
			
			array('products.unit_type_id','unit_type_id'),
			array('products.vat_type_id','vat_type_id'),
			
			array('products.status_id','status_id'),
			array('status.name','status_name'),
			array('status.description','status_description'),
			
			array('currencies.name','curr_symbol'),
			
			array($balance_sql, 'balance'),
			
			array('product_contents.id','l_id'),
			array('product_contents.language_id','l_language_id'),
			array('product_contents.1_title','l_1_title'),
			array('product_contents.1_flavor', 'l_1_flavor'),
			array('product_contents.1_description','l_1_description'),
			array('product_contents.1_image_src','l_1_image_src'),
			
			array('IFNULL("product_contents.0_enabled",0)', 'l_0_enabled'),
			array('product_contents.0_recipe_id', 'l_0_recipe_id'),
			array('reciepe_contents.title', 'l_0_recipe_title'),
			array('reciepe_contents.intro', 'l_0_recipe_intro'),
			array('reciepe_contents.content', 'l_0_recipe_content'),
			array('reciepe_contents.image_src', 'l_0_recipe_image_src'),			
			
			array('IFNULL("product_contents.2_enabled",0)','l_2_enabled'),
			array('product_contents.2_features','l_2_features'),
			
			array('IFNULL("product_contents.3_enabled",0)','l_3_enabled'),
			array('product_contents.3_video_link','l_3_video_link'),
			array('product_contents.3_video_type_id','l_3_video_type_id'),
			array('video_types.value', 'l_3_video_provider'),
			
			array('IFNULL("product_contents.4_enabled",0)','l_4_enabled'),
			array('product_contents.4_content','l_4_content'),
			array('product_contents.4_image_src','l_4_image_src'),
			array('product_contents.4_manual_src','l_4_manual_src'),
			
			array('IFNULL("product_contents.5_enabled",0)','l_5_enabled'),
			array('product_contents.5_title','l_5_title'),
			array('product_contents.5_content','l_5_content'),
			array('product_contents.5_image_src','l_5_image_src'),
			array('product_contents.5_image_position','l_5_image_position'),
			
			array('IFNULL("product_contents.6_enabled",0)','l_6_enabled'),
			array('product_contents.6_title','l_6_title'),
			array('product_contents.6_content','l_6_content'),
			array('product_contents.6_image_src','l_6_image_src'),
			array('product_contents.6_image_position','l_6_image_position'),
			
			array('IFNULL("product_contents.7_enabled",0)','l_7_enabled'),
			array('product_contents.7_title','l_7_title'),
			array('product_contents.7_content','l_7_content'),
			array('product_contents.7_image_src','l_7_image_src'),
			array('product_contents.7_image_position','l_7_image_position'),
			
			array('IFNULL("product_contents.8_enabled",0)','l_8_enabled'),
			array('product_contents.8_title','l_8_title'),
			array('product_contents.8_content','l_8_content'),
			array('product_contents.8_image_src','l_8_image_src'),
			array('product_contents.8_image_position','l_8_image_position'),
			
			array('IFNULL("product_contents.9_enabled",0)','l_9_enabled'),
			array('product_contents.9_title','l_9_title'),
			array('product_contents.9_content','l_9_content'),
			array('product_contents.9_image_src','l_9_image_src'),
			array('product_contents.9_image_position','l_9_image_position'),
			
			array('product_contents.alias','l_alias'),
			array('(	SELECT "categories.title"
						FROM "product_categories" JOIN "categories" ON "product_categories.category_id" = "categories.id"
						WHERE "product_categories.product_id" = "products.id"
						ORDER BY "categories.order_index"
						LIMIT 1 )','category_list'),
						
			array('categories.id', 'category_id'),
			array('categories.parent_id', 'category_prent_id'),
			array('category_contents.alias', 'l_category_alias'),
			array('category_contents.title', 'l_category_title'),
			
			array('parent_category_contents.alias', 'l_category_parent_alias'),
			array('parent_category_contents.title', 'l_category_parent_title'),
			
			array($functions_sql, 'l_settings_functions'),
			array($coffee_category_sql, 'l_settings_coffee_category'),
			array($intensity_sql, 'l_intensity'),
			array($collection_sql, 'l_collection'),
			array($pro_category_setting_id_sql, 'pro_category_setting_id'),

			array($coffee_order_sql, 'l_coffee_order_index'),
			array($machines_order_sql, 'l_machines_order_index'),
			array($accessories_order_sql, 'l_accessories_order_index'),
			array($pro_order_sql, 'l_pro_order_index'),
			array($pro_order_sql2, 'l_pro_order_index2'),
			array($product_order_sql, 'l_product_order_index') );

		// FROM
		$res->join('status','LEFT')
			->on('products.status_id', '=', 'status.status_id')
			->on('status.table_status_name', '=', DB::expr("'products_status_id'"));
		$res->join(array('types', 'vat_types'),'LEFT')
			->on('products.vat_type_id', '=', 'vat_types.type_id')
			->on('vat_types.table_type_name', '=', DB::expr("'products_vat_type_id'"));
		$res->join(array('types', 'video_types'),'LEFT')
			->on('product_contents.3_video_type_id', '=', 'video_types.type_id')
			->on('video_types.table_type_name', '=', DB::expr("'product_contents_3_video_type_id'"));
		$res->join('product_images','LEFT')->on('products.main_image_id', '=', 'product_images.id');
		$res->join(array('product_references','main_product_references'),'LEFT')->on('products.main_product_reference_id', '=', 'main_product_references.id');
		$res->join('currencies','LEFT')->on('products.currency_id', '=', 'currencies.id');
		$res->join('reciepe_contents','LEFT')
			->on('product_contents.0_recipe_id', '=', 'reciepe_contents.reciepe_id')
			->on('reciepe_contents.language_id', '=', 'category_contents.language_id')
			->on('reciepe_contents.status_id', '>=', DB::expr('10'));
		
		// ORDER BY
		if (is_numeric($order_by) && $order_by == -1) {
			if (isset($filter_data['category_id']) || isset($filter_data['category_parent_id'])) {
				$order_by = '"categories.order_index" ASC, "product_categories.order_index" ASC, "l_product_order_index" ASC, "products.order_index" ASC';
			} else {
				$order_by = '"l_product_order_index" ASC, "products.order_index" ASC';
			}
		}
		if (!empty($order_by)) $res->order_by($order_by);
		
		// GROUP BY
		$res->group_by(
			'product_contents.id',
			'products.id',
			'products.reference',
			'products.mfr_id',
			'products.price',
			'vat_types.value',
			'products.currency_id',
			'products.discount_active',
			'products.discount_price',
			'products.discount_color',
			'products.discount_price',
			'products.price',
			'products.discount_price',
			'products.coffee_gift_active',
			'products.coffee_gift_amount',
			'products.new',
			'products.gift',
			'products.datetime',
			'product_contents.alias',
			'products.main_image_id',
			'product_images.image_src',
			'main_product_references.id',
			'main_product_references.image_src',
			'main_product_references.reference',
			'main_product_references.balance',
			'products.unit_type_id',
			'products.vat_type_id',
			'products.status_id',
			'status.name',
			'status.description',
			'currencies.name',
			'product_contents.id',
			'product_contents.language_id',
			'product_contents.1_title',
			'product_contents.1_flavor',
			'product_contents.1_description',
			'product_contents.1_image_src',
			'product_contents.0_enabled',
			'product_contents.0_recipe_id',
			'reciepe_contents.title',
			'reciepe_contents.intro',
			'reciepe_contents.content',
			'reciepe_contents.image_src',
			'product_contents.2_enabled',
			'product_contents.2_features',
			'product_contents.3_enabled',
			'product_contents.3_video_link',
			'product_contents.3_video_type_id',
			'video_types.value',
			'product_contents.4_enabled',
			'product_contents.4_content',
			'product_contents.4_image_src',
			'product_contents.4_manual_src',
			'product_contents.5_enabled',
			'product_contents.5_title',
			'product_contents.5_content',
			'product_contents.5_image_src',
			'product_contents.5_image_position',
			'product_contents.6_enabled',
			'product_contents.6_title',
			'product_contents.6_content',
			'product_contents.6_image_src',
			'product_contents.6_image_position',
			'product_contents.7_enabled',
			'product_contents.7_title',
			'product_contents.7_content',
			'product_contents.7_image_src',
			'product_contents.7_image_position',
			'product_contents.8_enabled',
			'product_contents.8_title',
			'product_contents.8_content',
			'product_contents.8_image_src',
			'product_contents.8_image_position',
			'product_contents.9_enabled',
			'product_contents.9_title',
			'product_contents.9_content',
			'product_contents.9_image_src',
			'product_contents.9_image_position',
			'product_contents.alias',
			'categories.id',
			'categories.parent_id',
			'category_contents.alias',
			'category_contents.title',
			'parent_category_contents.alias',
			'parent_category_contents.title' );
		//die($res->compile(Database::instance()));
		// GET DATA
		$db_data = $res->execute()->as_array();
            
		// LANG ARRAY
		if (is_null($lang_id)) $db_data =  CMS::langArray($db_data);
		
		return $db_data;
	}
	
	public function getCurrencies() {
		$db_data = $this->db->select(
				array('currencies.id', 'id'),
				array('currencies.name', 'name'),
				array('currencies.symbol', 'symbol') )
			->from('currencies')
			->execute()
			->as_array();

		return $db_data;
	}
	
	public function getProductImages($id = null, $product_id = null, $lang_id = null) {
		 if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		 
		$res = $this->db->select(
			array('product_images.id', 'id'),
			array('product_images.image_src', 'image_src'),
			
			array('product_image_contents.id', 'l_id'),
			array('product_image_contents.language_id', 'l_language_id'),
			array('product_image_contents.title', 'l_title'),
			array('product_image_contents.description', 'l_description') );
			
		$res->from('product_images');
		$res->join('product_image_contents', 'LEFT')
			->on('product_images.id', '=', 'product_image_contents.product_image_id');
			if (!is_null($lang_id)) $res->on('product_image_contents.language_id', '=', DB::expr($lang_id));
		$res->join('products')->on('product_images.product_id', '=', 'products.id'); 
		
		if (!is_null($id)) $res->where('product_images.id', '=', $id);		
		else {
			$res->where('products.content_type_id', '=', $this->content_type_id);
			$res->where('product_images.product_id', '=', $product_id);
		}
		
		$res->order_by('product_images.order_index', 'ASC');
		
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}

	public function getProductReferences($id = null, $product_id = null) {
		$balance_sql = $this->db->select('SUM(IFNULL("product_references.balance", 0))')
			->from('product_references')
			->where('product_references.product_id', '=', DB::expr('products.id'));
			
		$pro_sub_category_id_sql = $this->db->select('category_settings.id')
			->from('product_category_settings')
			->join('category_setting_values')->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_settings')->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->where('category_setting_values.category_setting_id', 'IN', array(18,19,20))
			->where('product_category_settings.product_id', '=', DB::expr('products.id'))
			->order_by('IF("category_setting_values.category_setting_id" = 19, 1, 0) DESC')
			->order_by('category_settings.order_index')
			->limit(1);
		
		$res = $this->db->select(
			array('product_references.id', 'id'),
			array('product_references.product_id', 'product_id'),
			array('product_references.image_src', 'image_src'),
			array('product_references.code', 'code'),
			array('product_references.reference', 'reference'),
			array('product_references.color', 'color'),
			array('product_references.balance', 'balance'),
			array('product_references.order_index', 'order_index'),
			array('product_categories.category_id', 'category_id'),
			
			array($balance_sql, 'product_balance'),
			array($pro_sub_category_id_sql, 'pro_sub_category_id') );
			
		$res->from('product_references')
			->join('products')
				->on('product_references.product_id', '=', 'products.id')
			->join('product_categories', 'LEFT')
				->on('products.id', '=', 'product_categories.product_id');
			
		if (!is_null($id)) $res->where('product_references.id', '=', $id);
		else {
			$res->where('products.content_type_id', '=', $this->content_type_id);
			$res->where('product_references.product_id', 'IN', is_array($product_id)?$product_id:array($product_id));
		}
		
		$res->order_by('product_references.order_index', 'ASC');
		
		return $res->execute()->as_array();
	}
	
	public function getProductSettings($product_id, $category_setting_id, $lang_id = null) {
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = $this->lang_id;
		
		$db_data = $this->db->select(
				array('category_settings.id', 'category_setting_id'),
				array('category_setting_value_contents.title', 'title'),
				array('category_setting_values.image_src', 'image_src'))
			->from('product_category_settings')
			->join('category_setting_values')
				->on('product_category_settings.category_setting_value_id', '=', 'category_setting_values.id')
			->join('category_setting_value_contents')
				->on('category_setting_value_contents.category_setting_value_id', '=', 'category_setting_values.id')
				->on('category_setting_value_contents.language_id', '=', DB::expr($lang_id))
			->join('category_settings')
				->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->where('product_category_settings.product_id', '=', $product_id)
			->where('category_setting_values.category_setting_id', '=', $category_setting_id)
			->where('category_settings.id', 'NOT IN', array(4))
			->order_by('category_settings.order_index')
			->order_by('category_settings.id')
			->execute()
			->as_array();
			
		return $db_data;
	}
	
	public function getProductCategories($product_id) {
		$db_data = $this->db->select(
				array('product_categories.category_id', 'id'),
				array('categories.title', 'title') )
			->from('product_categories')
			->join('categories')->on('product_categories.category_id', '=', 'categories.id')
			->where('product_categories.product_id', '=', $product_id)
			->order_by('categories.order_index', 'ASC')
			->execute()
			->as_array();
		
		return $db_data;
	}

	function getProductCategorySettingValues($product_id) {
		$db_data = $this->db->select(
				array('product_category_settings.category_setting_value_id', 'category_setting_value_id'),
				array('product_category_settings.order_index', 'order_index') )
			->from('product_category_settings')
			->where('product_category_settings.product_id', '=', $product_id)
			->execute()
			->as_array();
		
		return $db_data;
	}
	
	public function save($data) {
		$this->resources = Model::factory('manager_resources');
		
		// PRICE UPDATE
		/*
		 * ŠO vairs nezimantojam, jo ļaujam glabāt cenu ar 4 skaitļiem aiz komata bez pvn
		$vat_data = CMS::getTypes('products_vat_type_id', $data['vat_type_id']);
		if (!empty($vat_data[0]['value']) && is_numeric($vat_data[0]['value']) && $vat_data[0]['value'] > 0) {
			$data['price'] = $data['price'] / (1 + $vat_data[0]['value'] / 100);
			$data['discount_price'] = $data['discount_price'] / (1 + $vat_data[0]['value'] / 100);
		}
		*/
		
		if ($data['product_id'] == 'new') {
			// INSERT
			$db_data = $this->db->insert('products', array(
					'parent_id',
					'reference',
					'price',
					'currency_id',
					'unit_type_id',
					'vat_type_id',
					'discount_active',
					'discount_price',
					'discount_color',
					'coffee_gift_active',		
					'coffee_gift_amount',	
					'new',
					'gift',
					'status_id',
					'content_type_id',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime'))
				->values(array(
					0,
					$data['reference'],
					str_replace(',', '.', $data['price']),
					$data['currency_id'],
					$data['unit_type_id'],
					$data['vat_type_id'],
					(isset($data['discount_active'])?'1':'0'),
					str_replace(',', '.', $data['discount_price']),
					$data['discount_color'],
					(isset($data['coffee_gift_active'])?'1':'0'),		
					$data['coffee_gift_amount'],	
					(isset($data['new'])?'1':'0'),
					(isset($data['gift'])?'1':'0'),
					$data['status_id'],
					$this->content_type_id,
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()')) )
				->execute();
				
			$product_id = $db_data[0];
			
			//
			// CATEGORIES
			//		
			$category_list = empty($_POST['category_id'])?array('NULL'):$_POST['category_id'];
			$db_data = $this->db->insert('product_categories', array(
					'product_id',
					'category_id',
					'user_id',
					'datetime'))
				->select(	$this->db->select(
								array(DB::expr($product_id), 'product_id'),
								array('id', 'category_id'),
								array(DB::expr($this->user_id), 'user_id'),
								array(DB::expr('NOW()'), 'datetime') )
							->from('categories')
							->where('categories.id', '=', $category_list)
							->where('categories.id', 'NOT IN', 	$this->db->select('product_categories.category_id')
																	->from('product_categories')
																	->where('product_categories.product_id', '=', $product_id)) )
				->execute();
		} else {			
			$db_data = $this->db->update('products')
				->set(array(
					'reference' => $data['reference'],
					'price' => str_replace(',', '.', $data['price']),
					'currency_id' => $data['currency_id'],
					'unit_type_id' => $data['unit_type_id'],
					'vat_type_id' => $data['vat_type_id'],
					'discount_active' => (isset($data['discount_active'])?'1':'0'),
					'discount_price' => $data['discount_price'],
					'discount_color' => $data['discount_color'],
					'coffee_gift_active' => (isset($data['coffee_gift_active'])?'1':'0'),		
					'coffee_gift_amount' => $data['coffee_gift_amount'],
					'new' => (isset($data['new'])?'1':'0'),
					'gift' => (isset($data['gift'])?'1':'0'),
					'status_id' => $data['status_id'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('products.id', '=', $data['product_id'])
				->where('products.content_type_id', '=', $this->content_type_id)
				->execute();
				
				$product_id = $data['product_id'];
		}
		
		// SAVE SETTING VALUES
		$product_category_data = $this->getProductCategories($product_id);
		
		$product_category_values = $this->getProductCategorySettingValues($product_id);
		$product_category_values_orders = array();
		for($i=0; $i<count($product_category_values); $i++) {
			$product_category_values_orders[$product_category_values[$i]['category_setting_value_id']] = $product_category_values[$i]['order_index'];
		}
		
		
		$db_data = $this->db->delete('product_category_settings')
			->where('product_category_settings.product_id', '=', $product_id)
			->execute();	
		if (!empty($data[$product_category_data[0]['id'].'_setting_value_id'])) {
			for($i=0; $i<count($data[$product_category_data[0]['id'].'_setting_value_id']); $i++) {
				$order_index = isset($product_category_values_orders[$data[$product_category_data[0]['id'].'_setting_value_id'][$i]])?$product_category_values_orders[$data[$product_category_data[0]['id'].'_setting_value_id'][$i]]:999999;
				
				$db_data = $this->db->insert('product_category_settings', array(
						'product_id',
						'category_setting_value_id',
						'order_index'))
					->values(array(
						$product_id,
						$data[$product_category_data[0]['id'].'_setting_value_id'][$i],
						$order_index))
					->execute();
			}
		}
		
		//
		// LOOP PRODUCT CONTENTS
		//
		
		// UPDATE LANGUAGES
		$lang = isset($data['language_id'])?$data['language_id']:array();
		
		$needed_product_content_id = array();
		for ($i=0; $i<count($lang); $i++) {
			if (isset($data['product_content_id'][$i]) AND $data['product_content_id'][$i] != 'none') {
				$alias = $this->resources->title_to_alias($data['1_title'][$i]);
					
				if ($data['product_content_id'][$i] == 'new') {
					// INSERT
					$db_data = $this->db->insert('product_contents', array(
							'product_id',
							'language_id',
							'1_title',
							'1_flavor',
							'1_description',		
							'0_enabled',
							'0_recipe_id',					
							'2_enabled',
							'2_features',
							'3_enabled',
							'3_video_link',
							'3_video_type_id',
							'4_enabled',
							'4_content',
							'5_enabled',
							'5_title',
							'5_content',
							'5_image_position',
							'6_enabled',
							'6_title',
							'6_content',
							'6_image_position',
							'7_enabled',
							'7_title',
							'7_content',
							'7_image_position',
							'8_enabled',
							'8_title',
							'8_content',
							'8_image_position',
							'9_enabled',
							'9_title',
							'9_content',
							'9_image_position',
							'alias',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$product_id,
							$lang[$i],
							$data['1_title'][$i],
							$data['1_flavor'][$i],
							$data['1_description'][$i],
							isset($data['0_enabled'][$i])?1:0,
							$data['0_recipe_id'][$i],
							isset($data['2_enabled'][$i])?1:0,
							$data['2_features'][$i],
							isset($data['3_enabled'][$i])?1:0,
							$data['3_video_link'][$i],
							$data['3_video_type_id'][$i],
							isset($data['4_enabled'][$i])?1:0,
							$data['4_content'][$i],
							isset($data['5_enabled'][$i])?1:0,
							$data['5_title'][$i],
							$data['5_content'][$i],
							$data['5_image_position'][$i],
							isset($data['6_enabled'][$i])?1:0,
							$data['6_title'][$i],
							$data['6_content'][$i],
							$data['6_image_position'][$i],
							isset($data['7_enabled'][$i])?1:0,
							$data['7_title'][$i],
							$data['7_content'][$i],
							$data['7_image_position'][$i],
							isset($data['8_enabled'][$i])?1:0,
							$data['8_title'][$i],
							$data['8_content'][$i],
							$data['8_image_position'][$i],
							isset($data['9_enabled'][$i])?1:0,
							$data['9_title'][$i],
							$data['9_content'][$i],
							$data['9_image_position'][$i],
							$alias,
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()') ))
						->execute();
						
					$product_content_id = $db_data[0];			
				} else {
					// UPDATE
					$db_data = $this->db->update('product_contents')
						->set(array(
							'1_title' => $data['1_title'][$i],
							'1_flavor' => $data['1_flavor'][$i],
							'1_description' => $data['1_description'][$i],
							'0_enabled' => isset($data['0_enabled'][$i])?1:0,
							'0_recipe_id' => $data['0_recipe_id'][$i],
							'2_enabled' => isset($data['2_enabled'][$i])?1:0,
							'2_features' => $data['2_features'][$i],
							'3_enabled' => isset($data['3_enabled'][$i])?1:0,
							'3_video_link' => $data['3_video_link'][$i],
							'3_video_type_id' => $data['3_video_type_id'][$i],
							'4_enabled' => isset($data['4_enabled'][$i])?1:0,
							'4_content' => $data['4_content'][$i],
							'5_enabled' => isset($data['5_enabled'][$i])?1:0,
							'5_title' => $data['5_title'][$i],
							'5_content' => $data['5_content'][$i],
							'5_image_position' => $data['5_image_position'][$i],
							'6_enabled' => isset($data['6_enabled'][$i])?1:0,
							'6_title' => $data['6_title'][$i],
							'6_content' => $data['6_content'][$i],
							'6_image_position' => $data['6_image_position'][$i],
							'7_enabled' => isset($data['7_enabled'][$i])?1:0,
							'7_title' => $data['7_title'][$i],
							'7_content' => $data['7_content'][$i],
							'7_image_position' => $data['7_image_position'][$i],
							'8_enabled' => isset($data['8_enabled'][$i])?1:0,
							'8_title' => $data['8_title'][$i],
							'8_content' => $data['8_content'][$i],
							'8_image_position' => $data['8_image_position'][$i],
							'9_enabled' => isset($data['9_enabled'][$i])?1:0,
							'9_title' => $data['9_title'][$i],
							'9_content' => $data['9_content'][$i],
							'9_image_position' => $data['9_image_position'][$i],
							'alias' => $alias,
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()') ) )
						->where('product_contents.id', '=', $data['product_content_id'][$i])
						->where('product_contents.language_id', '=', $lang[$i])
						->execute();
						
					$product_content_id = $data['product_content_id'][$i];
				}	
				$needed_product_content_id[] = $product_content_id;

				//
				// IMAGES AND FILES
				//
				$product_data = $this->getProducts($product_id, $lang[$i]);
				
				$image_src_1 = $this->files->update_image2('files/products/', $data['1_image_src'][$i], $product_data[0]['l_1_image_src']);	
				$image_src_4 = $this->files->update_image2('files/products/', $data['4_image_src'][$i], $product_data[0]['l_4_image_src']);
				$manual_src_4 = $this->files->update_file('files/products/', $data['4_manual_src'][$i], $product_data[0]['l_4_manual_src']);
				$image_src_5 = $this->files->update_image2('files/products/', $data['5_image_src'][$i], $product_data[0]['l_5_image_src']);
				$image_src_6 = $this->files->update_image2('files/products/', $data['6_image_src'][$i], $product_data[0]['l_6_image_src']);
				$image_src_7 = $this->files->update_image2('files/products/', $data['7_image_src'][$i], $product_data[0]['l_7_image_src']);
				$image_src_8 = $this->files->update_image2('files/products/', $data['8_image_src'][$i], $product_data[0]['l_8_image_src']);
				$image_src_9 = $this->files->update_image2('files/products/', $data['9_image_src'][$i], $product_data[0]['l_9_image_src']);	
				
				$this->db->update('product_contents')
					->set(array(
						'1_image_src' => $image_src_1,
						'4_image_src' => $image_src_4,
						'4_manual_src' => $manual_src_4,
						'5_image_src' => $image_src_5,
						'6_image_src' => $image_src_6,
						'7_image_src' => $image_src_7,
						'8_image_src' => $image_src_8,
						'9_image_src' => $image_src_9 ))
					->where('product_contents.id', '=', $product_content_id)
					->execute();				
			}
		}	

		// DELETE REMOVED TRANSLATIONS
		$del_product_data = $this->getProducts($product_id, null, array('not_product_content_id' => $needed_product_content_id));
		if (!empty($del_product_data[0]['lang'])) {
			foreach ($del_product_data[0]['lang'] as $key => $val) {
				FILES::deleteFile($val['1_image_src']);
				FILES::deleteFile($val['4_image_src']);
				FILES::deleteFile($val['4_manual_src']);
				FILES::deleteFile($val['5_image_src']);
				FILES::deleteFile($val['6_image_src']);
				FILES::deleteFile($val['7_image_src']);
				FILES::deleteFile($val['8_image_src']);
				FILES::deleteFile($val['9_image_src']);
				
				$this->db->delete('product_contents')
					->where('product_contents.product_id', '=', $product_id)
					->where('product_contents.id', '=', $val['id'])
					->execute();
			}
		}												
		
		//
		// LOOP GALLERY
		//		
		$needed_images = array();
		
		if (isset($data['product_image_id'])) {
			for ($i=0; $i < count($data['product_image_id']); $i++) {
				// IMAGE ID
				if ($data['product_image_id'][$i] == "new" OR $data['product_image_id'][$i] == "") {
					// IMAGES
					$image_src = $this->files->update_image2('files/products/', $data['product_image_src'][$i], '');							
					
					$db_data = $this->db->insert('product_images', array(
							'product_id',
							'image_src',
							'order_index',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$product_id,
							$image_src,
							$i,
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
						
					$product_image_id = $db_data[0];
				} else {
					$product_image_id = $data['product_image_id'][$i];
				}
				
				// NEEDED IMAGE
				$needed_images[] = $product_image_id;
				
				// UPDATE MAIN IMAGE
				if ($data['product_main_image'][$i] == "1") {
					$db_data = $this->db->update('products')
						->set(array(
							'main_image_id' => $product_image_id))
						->where('products.id', '=', $product_id)
						->execute();
				}

				for ($q=0; $q<count($lang); $q++) {
					if (isset($data[$lang[$q].'_product_image_content_id'][$i])) {
						if (!empty($data[$lang[$q].'_product_image_content_id']) && preg_match('/^[0-9]*$/', $data[$lang[$q].'_product_image_content_id'][$i]) == 1) {
							// UPDATE
							$this->db->update('product_image_contents')
								->set(array(
									'title' => $data[$lang[$q].'_product_image_content_title'][$i],
									'user_id' => $this->user_id,
									'datetime' => DB::expr('NOW()')))
								->where('product_image_contents.id', '=', $data[$lang[$q].'_product_image_content_id'][$i])
								->execute();					
						} else {
							// INSERT 
							$db_data = $this->db->insert('product_image_contents', array(
									'product_image_id',
									'language_id',
									'title',
									'user_id',
									'datetime',
									'creation_user_id',
									'creation_datetime'))
								->values(array(
									$product_image_id,
									$lang[$q],
									$data[$lang[$q].'_product_image_content_title'][$i],
									$this->user_id,
									DB::expr('NOW()'),
									$this->user_id,
									DB::expr('NOW()')))
								->execute();
						}						
					}
				}
			}
		}
		
		// DELETE IMAGES
		if (empty($needed_images)) $needed_images[] = 'null';
		$db_data = $this->db->select(array('product_images.id', 'id'))
			->from('product_images')
			->where('product_images.id', 'NOT IN', $needed_images)
			->where('product_images.product_id', '=', $product_id)
			->execute()
			->as_array();	
		
		for ($i=0; $i<count($db_data); $i++) {
			$this->deleteImage($db_data[$i]['id']);
		}	

		//
		// LOOP PRICES
		//		
		$needed_references = array();
			
		if (isset($data['product_reference_id'])) {
			for ($i=0; $i < count($data['product_reference_id']); $i++) {
				if ($data['product_reference_id'][$i] == "new" OR $data['product_reference_id'][$i] == "") {
					$db_data = $this->db->insert('product_references', array(
							'product_id',
							'code',
							'reference',
							'color',
							'order_index',
							'active',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$product_id,
							$data['product_reference_code'][$i],
							$data['product_reference_reference'][$i],
							$data['product_reference_color'][$i],
							$data['product_reference_order_index'][$i],
							1,
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()')))
						->execute();						
					$product_reference_id = $db_data[0];
				} else {
					// UPDATE
					$this->db->update('product_references')
						->set(array(
							'code' => $data['product_reference_code'][$i],
							'reference' => $data['product_reference_reference'][$i],
							'color' => $data['product_reference_color'][$i],
							'order_index' => $data['product_reference_order_index'][$i],
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()')))
						->where('product_references.id', '=', $data['product_reference_id'][$i])
						->where('product_references.product_id', '=', $product_id)
						->execute();					
					
					$product_reference_id = $data['product_reference_id'][$i];
				}

				// UPDATE IMAGE
				$product_references = $this->getProductReferences($product_reference_id);
				$image_src = $this->files->update_image2('files/products/', $data['product_reference_image_src'][$i], $product_references[0]['image_src']);	
				$db_data = $this->db->update('product_references')
					->set(array('image_src' => $image_src))
					->where('product_references.id', '=', $product_reference_id)
					->execute();
				
				// NEEDED REFERENCE
				$needed_references[] = $product_reference_id;
			}
		}
		
		// DELETE PRICES
		if (empty($needed_references)) $needed_references[] = 'null';
		$ref_data = $this->db->select(array('product_references.id', 'id'))
			->from('product_references')
			->where('product_references.id', 'NOT IN', $needed_references)
			->where('product_references.product_id', '=', $product_id)
			->execute()
			->as_array();
		for($i=0; $i<count($ref_data); $i++) {
			$this->deleteProductReference($ref_data[$i]['id']);
		}
			
		// SET MAIN REFERENCE
		$product_references = $this->getProductReferences(null, $product_id);
		if (count($product_references) > 0) {
			$this->db->update('products')
				->set(array('main_product_reference_id' => $product_references[0]['id']))
				->where('products.id', '=', $product_id)
				->execute();
		}		
	}

	public function delete($data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($data['product_id'])) {
			// CHECK FOR ACTIVE ORDERS
			
			// DELETE IMAGES
			$sql = "SELECT
						product_images.id			AS id
					FROM
						product_images
					WHERE
						product_images.product_id = :product_id ";
			$result = $this->db->query(Database::SELECT, $sql);
			$result->bind(':product_id', $data['product_id']);
			$db_data = $result->execute()->as_array();			
			for ($i=0; $i<count($db_data); $i++) {
				$this->deleteImage($db_data[$i]['id']);
			}

			// DELETE IMAGES AND FILES
			$product_data = $this->getProducts($data['product_id']);
			if (isset($product_data[0]['lang'])) {
				foreach($product_data[0]['lang'] as $l => $val) {
					$this->files->deleteFile($val['1_image_src']);
					$this->files->deleteFile($val['4_image_src']);
					$this->files->deleteFile($val['4_manual_src']);
					$this->files->deleteFile($val['5_image_src']);
					$this->files->deleteFile($val['6_image_src']);
					$this->files->deleteFile($val['7_image_src']);
					$this->files->deleteFile($val['8_image_src']);
					$this->files->deleteFile($val['9_image_src']);
				}	
			}

			// DELETE REFERENCES
			$db_data = $this->db->select(
					array('product_references.id', 'id'))
				->from('product_references')
				->where('product_references.product_id', '=', $data['product_id'])
				->execute()
				->as_array();
			for ($i=0; $i<count($db_data); $i++) {
				$this->deleteProductReference($db_data[$i]['id']);
			}
				
			$db_data = $this->db->delete('product_references')
				->where('product_references.product_id', '=', $data['product_id'])
				->execute();
			
			// DELETE SETTING VALUES
			$db_data = $this->db->delete('product_category_settings')
				->where('product_category_settings.product_id', '=', $data['product_id'])
				->execute();
			
			// DELETE CATEGORIES
			$this->deleteProductCategories($data['product_id']);
			
			// DELETE CONTENTS
			$sql = "DELETE FROM product_contents
					WHERE product_contents.product_id = :product_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':product_id', $data['product_id']);
			$db_data = $result->execute();
			
			$sql = "DELETE FROM products
					WHERE products.id = :product_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':product_id', $data['product_id']);
			$db_data = $result->execute();
			
			$status = array(	'status' => '1',
								'error' => '',
								'response' => '');
		} 
		
		return $status;	
	}
	
	public function deleteProductCategories($product_id = null, $category_id = null) {
		if (!empty($product_id) || !empty($category_id)) {
			$filter = " 1 = 0 ";	
			if (!is_null($product_id)) $filter = " product_categories.product_id = :product_id ";
			if (!is_null($category_id)) $filter = " product_categories.category_id = :category_id ";
			
			$sql = "DELETE FROM 
						product_categories
					WHERE 
						".$filter." ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':product_id', $product_id);
			$result->bind(':category_id', $category_id);
			$db_data = $result->execute();	
		}
	}

	public function deleteImage($product_image_id) {
		// REMOVE IMAGE
		$image_data = $this->getProductImages($product_image_id);
		
		if (count($image_data) > 0) {
			$this->files->deleteFile($image_data[0]['image_src']);
			
			$sql = "DELETE FROM product_image_contents
					WHERE product_image_contents.product_image_id = :product_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':product_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
			
			$sql = "DELETE FROM product_images
					WHERE product_images.id = :product_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':product_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
		}
	}
	
	public function deleteProductReference($id) {
		$ref_data = $this->getProductReferences($id);
		
		if (count($ref_data) > 0) {
			$this->files->deleteFile($ref_data[0]['image_src']);
			
			$this->db->delete('product_references')
				->where('product_references.id', '=', $id)
				->execute();	
		}
	}
	
	/*
	 * ORDER
	 */
	public function order_save($data) {
		for ($i=0; $i<count($data['order']); $i++) {
			if (!empty($data['category_setting_value_id'])) {
				// UPDATE CATEGORY
				$db_data = $this->db->update('product_category_settings')
					->set(array('order_index' => $i))
					->where('product_category_settings.category_setting_value_id', '=', $data['category_setting_value_id'])
					->where('product_category_settings.product_id', '=', $data['order'][$i])
					->execute();
			} else {
				// UPDATE PRODUCTS
				//$db_data = $this->db->update('products')
				//	->set(array('order_index' => $i))
				//	->where('products.id', '=', $data['order'][$i])
				//	->execute();
			}
		}
	}
}