<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Reciepes_Reciepes extends Model {
	public function __construct() {
		parent::__construct();
		
		$this->files = Model::factory('manager_files');
	}
	
	public function getReciepes($id = null, $lang_id = null, $filter_data = array(),  $limit = null, $offset = null, $count_all = false) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		// SELECT
		$res = $this->db->select(); 
		
		// FROM
		$res->from('reciepes'); 
		$res->join('reciepe_contents', 'LEFT')
			->on('reciepes.id', '=', 'reciepe_contents.reciepe_id');
			if (!is_null($lang_id)) $res->on('reciepe_contents.language_id', '=', DB::expr($lang_id)); 
		           
		// WHERE
		if (!is_null($id)) $res->where('reciepes.id', '=', $id);
		if (isset($filter_data['from_status_id'])) $res->where('reciepe_contents.status_id', '>=', $filter_data['from_status_id']);
		if (isset($filter_data['page_alias'])) $res->where('reciepe_contents.alias', '=', $filter_data['page_alias']);
		if (isset($filter_data['not_reciepe_contnet_id'])) $res->where('reciepe_contents.id', 'NOT IN', $filter_data['not_reciepe_contnet_id']);
		if (!empty($filter_data['coffee_id'])) {
			$res->where_open();
				$res->where_open();
				foreach($filter_data['coffee_id'] as $key => $val) {
					$filter_sql = $this->db->select('reciepe_materials.reciepe_id')
						->from('reciepe_materials')
						->where('reciepe_materials.product_id', '=', $val);
					$res->where('reciepes.id', 'IN', $filter_sql);
				}			
				$res->where_close();
			$res->or_where('reciepes.order_index', '>', 0);
			$res->where_close();
		}
		   
		// ORDER BY
		$res->order_by('IF("reciepes.order_index" = 0, 0, 1)', 'DESC');
		$res->order_by('IF("reciepes.order_index" = 0, NULL, "reciepes.order_index")', 'ASC');
		$res->order_by('reciepes.id', 'DESC');
		
		// ONLY FOR COUNT ROWS
		if ($count_all) {
			$res->select(array('COUNT("reciepes.id")', 'cnt'));
			$res->group_by('reciepes.id');
			$data = $db_data = $res->execute()->as_array();
                  
			return count($data);
		}
		
		// LIMIT
		if(!is_null($limit)) {
			$tmp_res = $res;
			// SELECT
			$tmp_res->select(array('reciepes.id', 'id'));
                                                
			// LIMIT
			$tmp_res->limit($limit);
			if (!is_null($offset) && $offset > 0) $tmp_res->offset($offset);
			
			// GROUP BY			
			//$tmp_res->group_by('reciepes.id');
                  
			// DATA
			$db_data = $tmp_res->execute()->as_array();         
                                    
			$id_list = array();
			foreach($db_data as $key => $val) $id_list[] = $val['id'];
			$res->where('reciepes.id', 'IN', !empty($id_list)?$id_list:array(-1));      
			$res->limit(null);
			$res->offset(null);                   
		} 
		
		// SELECT
		$res->select(
			array('reciepes.id', 'id'),
			array('reciepes.admin_title', 'admin_title'),
			array('reciepes.difficulty_type_id', 'difficulty_type_id'),
			array('difficulty_types.name', 'difficulty_type_name'),
			array('reciepes.time', 'time'),
			array('reciepes.order_index', 'order_index'),
			array('reciepes.image_src', 'image_src'),
			array('reciepes.main_image_id', 'main_image_id'),
			array('reciepe_images.image_src', 'main_image_src'),
			
			array('reciepe_contents.id', 'l_id'),
			array('reciepe_contents.language_id', 'l_language_id'),
			array('reciepe_contents.status_id', 'l_status_id'),
			array('status.name', 'l_status_name'),
			array('status.description', 'l_status_description'),
			array('reciepe_contents.title', 'l_title'),
			array('reciepe_contents.intro', 'l_intro'),
			array('reciepe_contents.ingredients', 'l_ingredients'),
			array('reciepe_contents.content', 'l_content'),
			array('reciepe_contents.image_src', 'l_image_src'),
			array('reciepe_contents.alias', 'l_alias'),
			array('CONCAT("reciepe_contents.alias",\'-i\',"reciepes.id")', 'l_full_alias') );
		
		// FROM
		$res->join('status', 'LEFT')
			->on('reciepe_contents.status_id', '=', 'status.status_id')
			->on('status.table_status_name', '=', DB::expr("'reciepe_contents_status_id'"));
		$res->join(array('types', 'difficulty_types'), 'LEFT')
			->on('reciepes.difficulty_type_id', '=', 'difficulty_types.type_id')
			->on('difficulty_types.table_type_name', '=', DB::expr("'reciepes_difficulty_type_id'"));
		$res->join('reciepe_images', 'LEFT')
			->on('reciepe_images.reciepe_id', '=', 'reciepes.id')
			->on('reciepe_images.id', '=', 'reciepes.main_image_id');
		
		// DATA
		$db_data = $res->execute()->as_array();		
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}

	public function getReciepeImages($id = null, $reciepe_id = null, $lang_id = null) {
		 if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		 
		$res = $this->db->select(
			array('reciepe_images.id', 'id'),
			array('reciepe_images.image_src', 'image_src'),
			
			array('reciepe_image_contents.id', 'l_id'),
			array('reciepe_image_contents.language_id', 'l_language_id'),
			array('reciepe_image_contents.title', 'l_title'),
			array('reciepe_image_contents.description', 'l_description') );
			
		$res->from('reciepe_images');
		$res->join('reciepe_image_contents', 'LEFT')
			->on('reciepe_images.id', '=', 'reciepe_image_contents.reciepe_image_id');
			if (!is_null($lang_id)) $res->on('reciepe_image_contents.language_id', '=', DB::expr($lang_id)); 
		
		if (!is_null($id)) $res->where('reciepe_images.id', '=', $id);
		else $res->where('reciepe_images.reciepe_id', '=', $reciepe_id);
		
		$res->order_by('reciepe_images.order_index', 'ASC');
		
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}

	public function getReciepeMaterials($id = null, $reciepe_id = null, $lang_id = null, $filter_data = array()) {
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
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
		
		$res = $this->db->select(
				array('reciepe_materials.id', 'recipe_material_id'),
				array('reciepe_materials.reciepe_id', 'reciepe_id'),
				array('reciepe_materials.product_id', 'id'),
				array('reciepe_materials.qty', 'qty'),
				array($intensity_sql, 'intensity'),
				
				array('CONCAT("page_contents.full_alias", \'/\', "category_contents.alias", \'-c\',categories.id,\'/\',"product_contents.alias",\'-i\',products.id)', 'l_full_alias'),
				
				array('product_references.image_src', 'reference_image_src'),
				array('product_contents.1_title', 'l_1_title'),
				array('category_contents.title', 'l_category_title'),
				array('category_contents.alias', 'l_category_alias'),
				array('status.description', 'status_description') )
			->from('reciepe_materials')
			->join('products')
				->on('products.id', '=', 'reciepe_materials.product_id')
			->join('product_contents')
				->on('products.id', '=', 'product_contents.product_id')
				->on('product_contents.language_id', '=', DB::expr(!empty($lang_id)?$lang_id:'-1'))
			->join('page_contents', 'LEFT')
				->on('page_contents.page_id', '=', DB::expr(CMS::$products_page_id))
				->on('page_contents.language_id', '=', DB::expr(!empty($lang_id)?$lang_id:'-1'))
			->join('product_references', 'LEFT')
				->on('products.main_product_reference_id', '=', 'product_references.id')
			->join('status','LEFT')
				->on('products.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr("'products_status_id'"))
			->join('product_categories','LEFT')
				->on('products.id', '=', 'product_categories.product_id')
			->join('categories','LEFT')
				->on('product_categories.category_id', '=', 'categories.id')
			->join('category_contents','LEFT')
				->on('categories.id', '=', 'category_contents.category_id')
				->on('category_contents.language_id', '=', DB::expr(!empty($lang_id)?$lang_id:'-1'));
		
		if (!is_null($id)) $res->where('reciepe_materials.id', '=', $id);
		else $res->where('reciepe_materials.reciepe_id', '=', $reciepe_id);
		
		if (isset($filter_data['category_id'])) $res->where('categories.id', 'IN', is_array($filter_data['category_id'])?$filter_data['category_id']:array($filter_data['category_id'])); 
		if (isset($filter_data['not_category_id'])) $res->where('categories.id', 'NOT IN', is_array($filter_data['not_category_id'])?$filter_data['not_category_id']:array($filter_data['not_category_id'])); 
		
		$res->order_by('categories.id', 'ASC');
		$res->order_by('product_contents.1_title', 'ASC');
		
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}
		
	public function save($data) {
		$this->resources = Model::factory('manager_resources');
		
		if ($data['reciepe_id'] == 'new') {
			// INSERT
			$db_data = $this->db->insert('reciepes', array(
					'admin_title',
					'difficulty_type_id',
					'time',
					'order_index',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$data['admin_title'],
					$data['difficulty_type_id'],
					$data['time'],
					$data['order_index'],
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
				
			$reciepe_id = $db_data[0];	
		} else {
			$db_data = $this->db->update('reciepes')
				->set(array(
					'admin_title' => $data['admin_title'],
					'difficulty_type_id' => $data['difficulty_type_id'],
					'time' => $data['time'],
					'order_index' => $data['order_index'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('reciepes.id', '=', $data['reciepe_id'])
				->execute();
				
			$reciepe_id = $data['reciepe_id'];
		}
		
		// IMAGE
		/*
		$reciepe = $this->getReciepes($reciepe_id);
		$image_src = $this->files->update_image2('files/reciepes/', $data['image_src'], $reciepe[0]['image_src']);
		
		$db_data = $this->db->update('reciepes')
			->set(array('image_src' => $image_src))
			->where('reciepes.id', '=', $reciepe_id)
			->execute();
		*/
		
		//
		// LOOP PRODUCT CONTENTS
		//
		
		// UPDATE LANGUAGES		
		$needed_reciepe_content_id = array();
		if (empty($data['language_id'])) $data['language_id'] = array();
		for ($i=0; $i<count($data['language_id']); $i++) {
			if (isset($data[$data['language_id'][$i].'_reciepe_content_id']) AND $data[$data['language_id'][$i].'_reciepe_content_id'] != 'none') {
				if (empty($data[$data['language_id'][$i].'_reciepe_content_id']) OR $data[$data['language_id'][$i].'_reciepe_content_id'] == 'new') {
					// INSERT
					$db_data = $this->db->insert('reciepe_contents', array(
							'reciepe_id',
							'language_id',
							'title',
							'intro',
							'ingredients',
							'content',
							'alias',
							'status_id',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$reciepe_id,
							$data['language_id'][$i],
							$data[$data['language_id'][$i].'_title'],
							$data[$data['language_id'][$i].'_ingredients'],
							$data[$data['language_id'][$i].'_intro'],
							$data[$data['language_id'][$i].'_content'],
							$this->resources->title_to_alias($data[$data['language_id'][$i].'_title']),
							$data[$data['language_id'][$i].'_status_id'],
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()') ))
						->execute();
					$reciepe_contents_id = $db_data[0];
				} else {
					// UPDATE
					$db_data = $this->db->update('reciepe_contents')
						->set(array(
							'title' => $data[$data['language_id'][$i].'_title'],
							'ingredients' => $data[$data['language_id'][$i].'_ingredients'],
							'intro' => $data[$data['language_id'][$i].'_intro'],
							'content' => $data[$data['language_id'][$i].'_content'],
							'alias' => $this->resources->title_to_alias($data[$data['language_id'][$i].'_title']),
							'status_id' => $data[$data['language_id'][$i].'_status_id'],
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()') ))
						->where('reciepe_contents.id', '=', $data[$data['language_id'][$i].'_reciepe_content_id'])
						->execute();
					$reciepe_contents_id = $data[$data['language_id'][$i].'_reciepe_content_id'];
				}

				// IMAGE
				$reciepe = $this->getReciepes($reciepe_id, $data['language_id'][$i]);
				$image_src = $this->files->update_image2('files/reciepes/', $data[$data['language_id'][$i].'_image_src'], $reciepe[0]['l_image_src']);
				
				$db_data = $this->db->update('reciepe_contents')
					->set(array('image_src' => $image_src))
					->where('reciepe_contents.id', '=', $reciepe_contents_id)
					->execute();
					
				$needed_reciepe_content_id[] = $reciepe_contents_id;
			}
		}

		// DELETE REMOVED TRANSLATIONS
		$del_reciepe_data = $this->getReciepes($reciepe_id, null, array('not_reciepe_contnet_id' => $needed_reciepe_content_id));
		if (!empty($del_reciepe_data[0]['lang'])) {
			foreach ($del_reciepe_data[0]['lang'] as $key => $val) {
				FILES::deleteFile($val['image_src']);
				
				$this->db->delete('reciepe_contents')
					->where('reciepe_contents.reciepe_id', '=', $reciepe_id)
					->where('reciepe_contents.id', '=', $val['id'])
					->execute();
			}
		}
		
		//
		// LOOP GALLERY
		//		
		$needed_images = array();
		
		if (isset($data['reciepe_image_id'])) {
			for ($i=0; $i < count($data['reciepe_image_id']); $i++) {
				// IMAGE ID
				if (!is_numeric($data['reciepe_image_id'][$i])) {
					// IMAGES
					$image_src = $this->files->update_image2('files/reciepes/', $data['reciepe_image_src'][$i], '');							
					
					$db_data = $this->db->insert('reciepe_images', array(
							'reciepe_id',
							'image_src',
							'order_index',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$reciepe_id,
							$image_src,
							$i,
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
						
					$reciepe_image_id = $db_data[0];
				} else {
					$reciepe_image_id = $data['reciepe_image_id'][$i];
				}
				
				// NEEDED IMAGE
				$needed_images[] = $reciepe_image_id;
				
				// UPDATE MAIN IMAGE
				if ($data['reciepe_main_image'][$i] == "1") {
					$db_data = $this->db->update('reciepes')
						->set(array(
							'main_image_id' => $reciepe_image_id))
						->where('reciepes.id', '=', $reciepe_id)
						->execute();
				}

				for ($q=0; $q<count($lang); $q++) {
					if (isset($data[$lang[$q]['id'].'_reciepe_image_content_id'][$i])) {
						if (!empty($data[$lang[$q]['id'].'_reciepe_image_content_id']) && preg_match('/^[0-9]*$/', $data[$lang[$q]['id'].'_reciepe_image_content_id'][$i]) == 1) {
							// UPDATE
							$this->db->update('reciepe_image_contents')
								->set(array(
									'title' => $data[$lang[$q]['id'].'_reciepe_image_content_title'][$i],
									'description' => $data[$lang[$q]['id'].'_reciepe_image_content_description'][$i],
									'user_id' => $this->user_id,
									'datetime' => DB::expr('NOW()')))
								->where('reciepe_image_contents.id', '=', $data[$lang[$q]['id'].'_reciepe_image_content_id'][$i])
								->execute();					
						} else {
							// INSERT 
							$db_data = $this->db->insert('reciepe_image_contents', array(
									'reciepe_image_id',
									'language_id',
									'title',
									'description',
									'user_id',
									'datetime',
									'creation_user_id',
									'creation_datetime'))
								->values(array(
									$reciepe_image_id,
									$lang[$q]['id'],
									$data[$lang[$q]['id'].'_reciepe_image_content_title'][$i],
									$data[$lang[$q]['id'].'_reciepe_image_content_description'][$i],
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
		$db_data = $this->db->select(array('reciepe_images.id', 'id'))
			->from('reciepe_images')
			->where('reciepe_images.id', 'NOT IN', $needed_images)
			->where('reciepe_images.reciepe_id', '=', $reciepe_id)
			->execute()
			->as_array();	
		
		for ($i=0; $i<count($db_data); $i++) {
			$this->deleteReciepeImage($db_data[$i]['id']);
		}	
		
		//
		// LINKER MATERIALS
		//		
		$needed_materials = array();
		
		if (isset($data['linked_product_recipe_material_id'])) {
			for ($i=0; $i < count($data['linked_product_recipe_material_id']); $i++) {
				if (!is_numeric($data['linked_product_recipe_material_id'][$i])) {
					$db_data = $this->db->insert('reciepe_materials', array(
							'reciepe_id',
							'product_id',
							'qty',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$reciepe_id,
							$data['linked_product_id'][$i],
							$data['linked_product_qty'][$i],
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
						
					$reciepe_material_id = $db_data[0];
				} else {
					$db_data = $this->db->update('reciepe_materials')
						->set(array(
							'qty' => $data['linked_product_qty'][$i],
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()') ))
						->where('reciepe_materials.id', '=', $data['linked_product_recipe_material_id'][$i])
						->execute();
					
					$reciepe_material_id = $data['linked_product_recipe_material_id'][$i];
				}
				
				// NEEDED MATERIALS
				$needed_materials[] = $reciepe_material_id;
			}
		}
		
		// DELETE MATERIALS
		if (empty($needed_materials)) $needed_materials[] = 'null';
		$db_data = $this->db->delete('reciepe_materials')
			->where('reciepe_materials.id', 'NOT IN', $needed_materials)
			->where('reciepe_materials.reciepe_id', '=', $reciepe_id)
			->execute();			

		return $reciepe_id;		
	}

	public function deleteReciepeImage($reciepe_image_id) {
		// REMOVE IMAGE
		$image_data = $this->getReciepeImages($reciepe_image_id);
		
		if (count($image_data) > 0) {
			$this->files->deleteFile($image_data[0]['image_src']);
			
			$sql = "DELETE FROM reciepe_image_contents
					WHERE reciepe_image_contents.reciepe_image_id = :reciepe_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':reciepe_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
			
			$sql = "DELETE FROM reciepe_images
					WHERE reciepe_images.id = :reciepe_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':reciepe_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
		}
	}

	public function delete($data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($data['reciepe_id'])) {
			$reciepes = $this->getReciepes($data['reciepe_id']);
			
			// DELETE IMAGE
			$this->files->deleteFile($reciepes[0]['image_src']);
			
			// DELETE CONTENT IMAGES
			foreach($reciepes[0]['lang'] as $l => $val) {
				$this->files->deleteFile($val['image_src']);
			}
			
			// DELETE CONTENTS
			$db_data = $this->db->delete('reciepe_contents')
				->where('reciepe_contents.reciepe_id', '=', $data['reciepe_id'])
				->execute();
				
			// DELETE MATERIALS
			$db_data = $this->db->delete('reciepe_materials')
				->where('reciepe_materials.reciepe_id', '=', $data['reciepe_id'])
				->execute();
			
			// DELETE RECIEPE
			$db_data = $this->db->delete('reciepes')
				->where('reciepes.id', '=', $data['reciepe_id'])
				->execute();
			
			$status = array(	'status' => '1',
								'error' => '',
								'response' => '');
		} 
		
		return $status;	
	}
}