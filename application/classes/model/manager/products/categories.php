<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Products_Categories extends Model {
	public function __construct($content_type_id) {
		parent::__construct();
		
		if (empty($content_type_id)) Kohana::error_handler(true, 'NOT SET content_type_id');
		else $this->content_type_id = $content_type_id;
		
		$this->files = Model::factory('manager_files');
	}
	
	public function getCategories($id = null, $lang_id = null, $filter_data = array()) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		if (!is_null($id) AND ($id == 'new' OR $id == '')) {
			// NEW PRODUCT
			$res = $this->db->select(
				array(DB::expr("'new'"), 'id'),
				array(DB::expr("''"), 'title'),
				array(DB::expr("1"), 'status_id'),
				array(DB::expr("10"), 'type_id'),
				array(DB::expr($filter_data['parent_id']), 'parent_id'),
				array(DB::query(Database::SELECT, "	IFNULL((SELECT MAX(categories.order_index)
													FROM categories
													WHERE IFNULL(categories.parent_id,0) = :parent_id AND categories.content_type_id = :content_type_id
													GROUP BY categories.parent_id ),0) + 10")
						->bind(':parent_id', $filter_data['parent_id'])
						->bind(':content_type_id', $this->content_type_id), 'order_index') );
			$db_data = $res->execute()->as_array();
		} else {
			// SELECT
			$res = $this->db->select(
				array('categories.id', 'id'),
				array('categories.title', 'title'),
				array('categories.image_src', 'image_src'),
				array('categories.order_index', 'order_index'),
				array('IFNULL("categories.parent_id",0)', 'parent_id'),
						
				array('categories.main_image_id', 'main_image_id'),
				array('category_images.image_src', 'main_image_src'),
						
				array(DB::query(Database::SELECT, '	SELECT small_images.image_src
													FROM category_images small_images
													WHERE small_images.category_id = categories.id
													ORDER BY small_images.order_index							
													LIMIT 1
													OFFSET 1 '), 'small_image_src'),
						
				array('categories.status_id', 'status_id'),
				array('status.name', 'status_name'),
				array('status.description', 'status_description'),

				array('categories.type_id', 'type_id'),
				array('types.name', 'type_name'),
				array('types.description', 'type_description'),
						
				array('category_contents.id', 'l_id'),
				array('category_contents.language_id', 'l_language_id'),
				array('category_contents.title', 'l_title'),
				array('category_contents.description', 'l_description'),
				array('category_contents.content', 'l_content'),
				array('category_contents.alias', 'l_alias'),
				array('category_contents.full_alias', 'l_full_alias'),
				
				array('parent_category_contents.title', 'l_parent_title'),
				array('parent_category_contents.alias', 'l_parent_alias'),
				
				array('CONCAT(
						IFNULL("parent_categories_10.id",\'\'), \'---\',
						IFNULL("parent_categories_9.id",\'\'), \'---\',
						IFNULL("parent_categories_8.id",\'\'), \'---\',
						IFNULL("parent_categories_7.id",\'\'), \'---\',
						IFNULL("parent_categories_6.id",\'\'), \'---\',
						IFNULL("parent_categories_5.id",\'\'), \'---\',
						IFNULL("parent_categories_4.id",\'\'), \'---\',
						IFNULL("parent_categories_3.id",\'\'), \'---\',
						IFNULL("parent_categories_2.id",\'\'), \'---\',
						IFNULL("parent_categories_1.id",\'\'), \'---\',
						"categories.id" )', 'root_id_list'),
				array('CONCAT(
						IFNULL("parent_category_contents_10.title",\'\'), \'---\',
						IFNULL("parent_category_contents_9.title",\'\'), \'---\',
						IFNULL("parent_category_contents_8.title",\'\'), \'---\',
						IFNULL("parent_category_contents_7.title",\'\'), \'---\',
						IFNULL("parent_category_contents_6.title",\'\'), \'---\',
						IFNULL("parent_category_contents_5.title",\'\'), \'---\',
						IFNULL("parent_category_contents_4.title",\'\'), \'---\',
						IFNULL("parent_category_contents_3.title",\'\'), \'---\',
						IFNULL("parent_category_contents_2.title",\'\'), \'---\',
						IFNULL("parent_category_contents_1.title",\'\'), \'---\',
						"category_contents.title" )', 'root_title_list') );
			
			// FROM
			$res->from('categories');
			$res->join('category_contents', 'LEFT')->on('categories.id', '=', 'category_contents.category_id');
				if (!is_null($lang_id)) $res->on('category_contents.language_id', '=', DB::expr($lang_id)); 
				
			$res->join(array('categories', 'parent_categories_1'), 'LEFT')
				->on('categories.parent_id', '=', 'parent_categories_1.id');
			$res->join(array('category_contents', 'parent_category_contents_1'), 'LEFT')
				->on('parent_categories_1.id', '=', 'parent_category_contents_1.category_id')
				->on('parent_category_contents_1.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_2'), 'LEFT')
				->on('parent_categories_1.parent_id', '=', 'parent_categories_2.id');
			$res->join(array('category_contents', 'parent_category_contents_2'), 'LEFT')
				->on('parent_categories_2.id', '=', 'parent_category_contents_2.category_id')
				->on('parent_category_contents_2.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_3'), 'LEFT')
				->on('parent_categories_2.parent_id', '=', 'parent_categories_3.id');
			$res->join(array('category_contents', 'parent_category_contents_3'), 'LEFT')
				->on('parent_categories_3.id', '=', 'parent_category_contents_3.category_id')
				->on('parent_category_contents_3.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_4'), 'LEFT')
				->on('parent_categories_3.parent_id', '=', 'parent_categories_4.id');
			$res->join(array('category_contents', 'parent_category_contents_4'), 'LEFT')
				->on('parent_categories_4.id', '=', 'parent_category_contents_4.category_id')
				->on('parent_category_contents_4.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_5'), 'LEFT')
				->on('parent_categories_4.parent_id', '=', 'parent_categories_5.id');
			$res->join(array('category_contents', 'parent_category_contents_5'), 'LEFT')
				->on('parent_categories_5.id', '=', 'parent_category_contents_5.category_id')
				->on('parent_category_contents_5.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_6'), 'LEFT')
				->on('parent_categories_5.parent_id', '=', 'parent_categories_6.id');
			$res->join(array('category_contents', 'parent_category_contents_6'), 'LEFT')
				->on('parent_categories_6.id', '=', 'parent_category_contents_6.category_id')
				->on('parent_category_contents_6.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_7'), 'LEFT')
				->on('parent_categories_6.parent_id', '=', 'parent_categories_7.id');
			$res->join(array('category_contents', 'parent_category_contents_7'), 'LEFT')
				->on('parent_categories_7.id', '=', 'parent_category_contents_7.category_id')
				->on('parent_category_contents_7.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_8'), 'LEFT')
				->on('parent_categories_7.parent_id', '=', 'parent_categories_8.id');
			$res->join(array('category_contents', 'parent_category_contents_8'), 'LEFT')
				->on('parent_categories_8.id', '=', 'parent_category_contents_8.category_id')
				->on('parent_category_contents_8.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_9'), 'LEFT')
				->on('parent_categories_8.parent_id', '=', 'parent_categories_9.id');
			$res->join(array('category_contents', 'parent_category_contents_9'), 'LEFT')
				->on('parent_categories_9.id', '=', 'parent_category_contents_9.category_id')
				->on('parent_category_contents_9.language_id', '=', 'category_contents.language_id'); 
			$res->join(array('categories', 'parent_categories_10'), 'LEFT')
				->on('parent_categories_9.parent_id', '=', 'parent_categories_10.id');
			$res->join(array('category_contents', 'parent_category_contents_10'), 'LEFT')
				->on('parent_categories_10.id', '=', 'parent_category_contents_10.category_id')
				->on('parent_category_contents_10.language_id', '=', 'category_contents.language_id'); 
				
			$res->join(array('categories', 'parent_categories'), 'LEFT')->on('categories.parent_id', '=', 'parent_categories.id');
			$res->join(array('category_contents', 'parent_category_contents'), 'LEFT')
				->on('parent_categories.id', '=', 'parent_category_contents.category_id')
				->on('parent_category_contents.language_id', '=', 'category_contents.language_id'); 
				
			$res->join('status', 'LEFT')
				->on('categories.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr("'categories_status_id'"));
			$res->join('types', 'LEFT')
				->on('categories.type_id', '=', 'types.type_id')
				->on('types.table_type_name', '=', DB::expr("'categories_type_id'"));
			$res->join('category_images', 'LEFT')->on('categories.main_image_id', '=', 'category_images.id');
			
			// WHERE
			$res->where('categories.content_type_id', '=', $this->content_type_id);
			if (!is_null($id)) $res->where('categories.id', '=', $id);
			if (isset($filter_data['parent_id']) && !is_null($filter_data['parent_id'])) $res->where('categories.parent_id', 'IN', (is_array($filter_data['parent_id'])?$filter_data['parent_id']:array($filter_data['parent_id'])));
			if (isset($filter_data['from_status_id'])) $res->where('categories.status_id', '>=', $filter_data['from_status_id']);
		
			// ORDER BY
			$res->order_by('categories.order_index', 'ASC');
			$res->order_by('category_contents.title', 'ASC');
			
			$db_data = $res->execute()->as_array();
			
			// LANGS
			if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		}

		return $db_data;
	}
	
	public function getCategoryImages($id = null, $category_id = null, $lang_id = null) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		// SELECT
		$res = $this->db->select(
			array('category_images.id', 'id'),
			array('category_images.image_src', 'image_src'),
			
			array('category_image_contents.id', 'l_id'),
			array('category_image_contents.language_id', 'l_language_id'),
			array('category_image_contents.title', 'l_title'),
			array('category_image_contents.description', 'l_description') );
			
		// FROM
		$res->from('category_images');
		$res->join('category_image_contents', 'LEFT')
			->on('category_images.id', '=', 'category_image_contents.category_image_id');
			if (!is_null($lang_id)) $res->on('category_image_contents.language_id', '=', DB::expr($lang_id));
		$res->join('categories')->on('category_images.category_id', '=', 'categories.id'); 
		
		// WHERE
		$res->where('categories.content_type_id', '=', $this->content_type_id);
		if (!is_null($id)) $res->where('category_images.id', '=', $id);
		else $res->where('category_images.category_id', '=', $category_id);
		
		// ORDER BY
		$res->order_by('category_images.order_index', 'ASC');
		
		// DATA
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);
		
		return $db_data;
	}
	
	public function save($data) {
		$this->resources = Model::factory('manager_resources');
		
		if ($data['category_id'] == 'new') {
			// INSERT
			$res = $this->db->insert('categories', array(
				'parent_id',
				'title',
				'order_index',
				'status_id',
				'type_id',
				'content_type_id',
				'user_id',
				'datetime',
				'creation_user_id',
				'creation_datetime' ));
			$res->values(array(
				$data['parent_id'],
				$data['title'],	
				$data['order_index'],
				$data['status_id'],
				$data['type_id'],
				$this->content_type_id,
				$this->user_id,
				DB::expr('NOW()'),
				$this->user_id,
				DB::expr('NOW()') ));
			$db_data = $res->execute();
				
			$category_id = $db_data[0];
		} else {
			// UPDATE
			$res = $this->db->update('categories');
			$res->set(array(
				'parent_id' => $data['parent_id'],
				'title' => $data['title'],
				'order_index' => $data['order_index'],
				'status_id' => $data['status_id'],
				'type_id' => $data['type_id'],
				'user_id' => $this->user_id,
				'datetime' => DB::expr('NOW()') ));
			$res->where('categories.id', '=', $data['category_id']);
			$res->where('categories.content_type_id', '=', $this->content_type_id);
			$db_data = $res->execute();
			
			$category_id = $data['category_id'];
		}
		
		// IMAGES
		$category = $this->getCategories($category_id);
		$image_src = $this->files->update_image2('files/product_categories/'.$category_id.'/', $data['image_src'], $category[0]['image_src']);
		$db_data = $this->db->update('categories')
			->set(array('image_src' => $image_src))
			->where('categories.id', '=', $category_id)
			->execute();
			
		//
		// LOOP PRODUCT CATEGORY CONTENTS
		//
		
		// UPDATE LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		$needed_category_content_id = array();
		for ($i=0; $i<count($lang); $i++) {
			if (isset($data[$lang[$i]['id'].'_category_content_id']) AND $data[$lang[$i]['id'].'_category_content_id'] != 'none') {
				if ($data[$lang[$i]['id'].'_category_content_id'] == 'new') {
					// INSERT
					$res = $this->db->insert('category_contents', array(
						'category_id',
						'language_id',
						'title',
						'description',
						'content',
						'alias',
						'user_id',
						'datetime',
						'creation_user_id',
						'creation_datetime' ));
					$res->values(array(
						$category_id,
						$lang[$i]['id'],
						$data[$lang[$i]['id'].'_title'],		
						$data[$lang[$i]['id'].'_description'],
						$data[$lang[$i]['id'].'_content'],
						$this->resources->title_to_alias($data[$lang[$i]['id'].'_title']),
						$this->user_id,
						DB::expr('NOW()'),
						$this->user_id,
						DB::expr('NOW()') ));
					$db_data = $res->execute();
					
					$category_content_id = $db_data[0];	
				} else {
					// UPDATE
					$res = $this->db->update('category_contents');
					$res->set(array(
						'title' => $data[$lang[$i]['id'].'_title'],
						'description' => $data[$lang[$i]['id'].'_description'],
						'content' => $data[$lang[$i]['id'].'_content'],
						'alias' => $this->resources->title_to_alias($data[$lang[$i]['id'].'_title']),
						'user_id' => $this->user_id,
						'datetime' => DB::expr('NOW()') ));
					$res->where('category_contents.id', '=', $data[$lang[$i]['id'].'_category_content_id']);
					$result = $res->execute();
					
					$category_content_id = $data[$lang[$i]['id'].'_category_content_id'];	
				}
				$needed_category_content_id[] = $category_content_id;
				
				// UPDATE FULL PATH
				$this->updateFullAlias($category_content_id);					
			}
		}	
		
		// DELETE REMOVED TRANSLATIONS
		if (empty($needed_category_content_id)) $needed_category_content_id[] = 0;
		$db_data = $this->db->delete('category_contents')
			->where('category_contents.category_id', '=', $category_id)
			->where('category_contents.id', 'NOT IN', $needed_category_content_id)
			->execute();
		
		//
		// LOOP GALLERY
		//		
		$needed_images = array();
		
		if (isset($data['category_image_id'])) {
			for ($i=0; $i < count($data['category_image_id']); $i++) {
				// IMAGE ID
				if ($data['category_image_id'][$i] == "new" OR $data['category_image_id'][$i] == "") {
					// IMAGES
					$image_src = $this->files->update_image2('files/product_categories/'.$category_id.'/', $data['category_image_src'][$i], '');							
					
					$res = $this->db->insert('category_images', array(
						'category_id',
						'image_src',
						'order_index',
						'user_id',
						'datetime',
						'creation_user_id',
						'creation_datetime') );
					$res->values(array(
						$category_id,
						$image_src,
						$i,
						$this->user_id,
						DB::expr('NOW()'),
						$this->user_id,
						DB::expr('NOW()') ));
					$db_data = $res->execute();

					$category_image_id = $db_data[0];
				} else {
					$category_image_id = $data['category_image_id'][$i];
				}
				
				// NEEDED IMAGE
				$needed_images[] = $category_image_id;
				
				// UPDATE MAIN IMAGE
				if ($data['category_main_image'][$i] == "1") {
					$db_data = $this->db->update('categories')
						->set(array('main_image_id' => $category_image_id))
						->where('categories.id', '=', $category_id)
						->execute();
				}

				for ($q=0; $q<count($lang); $q++) {
					if (isset($data[$lang[$q]['id'].'_category_image_content_id'][$i])) {
						if (!empty($data[$lang[$q]['id'].'_category_image_content_id']) && preg_match('/^[0-9]*$/', $data[$lang[$q]['id'].'_category_image_content_id'][$i]) == 1) {
							// UPDATE
							$res = $this->db->update('category_image_contents');
							$res->set(array(
								'title' => $data[$lang[$q]['id'].'_category_image_content_title'][$i],
								'user_id' => $this->user_id,
								'datetime' => DB::expr('NOW()') ));
							$res->where('category_image_contents.id', '=', $data[$lang[$q]['id'].'_category_image_content_id'][$i]);
							$result = $res->execute();						
						} else {
							// INSERT 
							$res = $this->db->insert('category_image_contents', array(
								'category_image_id',
								'language_id',
								'title',
								'user_id',
								'datetime',
								'creation_user_id',
								'creation_datetime') );
							$res->values(array(
								$category_image_id,
								$lang[$q]['id'],
								$data[$lang[$q]['id'].'_category_image_content_title'][$i],
								$this->user_id,
								DB::expr('NOW()'),
								$this->user_id,
								DB::expr('NOW()') ));
							$result = $res->execute();
						}						
					}
				}
			}
		}
		
		// DELETE IMAGES
		if (empty($needed_images)) $needed_images[] = '-1';
		$db_data = $this->db->select(array('category_images.id', 'id'))
			->from('category_images')
			->where('category_images.category_id', '=', $category_id)
			->where('category_images.id', 'NOT IN', $needed_images)
			->execute()->as_array();	
		
		for ($i=0; $i<count($db_data); $i++) {
			$this->deleteImage($db_data[$i]['id']);
		}		
	}

	public function delete($data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($data['category_id'])) {
			// CHECK ADDED SETTINGS
			$settings = $this->getCategorySettings(null, $data['category_id']);
			
			if (count($settings) == 0) {			
				// DELETE IMAGES
				$db_data = $this->db
					->select(array('category_images.id', 'id'))
					->from('category_images')
					->where('category_images.category_id', '=', $data['category_id'])
					->execute()->as_array();		
				for ($i=0; $i<count($db_data); $i++) {
					$this->deleteImage($db_data[$i]['id']);
				}	
				
				// DELETE CONTENTS
				$db_data = $this->db	
					->delete('category_contents')
					->where('category_contents.category_id', '=', $data['category_id'])
					->execute();
					
				$db_data = $this->db	
					->delete('categories')
					->where('categories.id', '=', $data['category_id'])
					->execute();
				
				$status = array(	'status' => '1',
									'error' => '',
									'response' => '');
			} else {
				$status = array(	'status' => '0',
									'error' => __('This category has added parameters!'),
									'response' => '');
			} 
		} 
		
		return $status;	
	}


	public function deleteImage($category_image_id) {
		// REMOVE IMAGE
		$image_data = $this->getCategoryImages($category_image_id);
		
		if (count($image_data) > 0) {
			$this->files->deleteFile($image_data[0]['image_src']);
			
			$db_data = $this->db	
				->delete('category_image_contents')
				->where('category_image_contents.category_image_id', '=', $image_data[0]['id'])
				->execute();
			
			$db_data = $this->db	
				->delete('category_images')
				->where('category_images.id', '=', $image_data[0]['id'])
				->execute();
		}
	}
	
	//
	// GET PAGE FULL ALIAS
	//
	public function updateFullAlias($category_content_id) {
		$unique = false;	
			
		while (!$unique) {			
			// GET FULL ALIAS
			$full_alias = $this->getFullCategoryAlias($category_content_id);
			$full_alias_data = $this->db->select(
					array('category_contents.alias', 'alias'),
					array('category_contents.language_id', 'lang_id'))
				->from('category_contents')
				->where('category_contents.id', '=', $category_content_id)
				->execute()->as_array();
			
			$categories = $this->db->select(
					array('category_contents.id', 'category_content_id'))
				->from('category_contents')
				->where('category_contents.full_alias', '=', $full_alias)
				->where('category_contents.language_id', '=', $full_alias_data[0]['lang_id'])
				->execute()->as_array();

			// LOOP DOCS
			$unique = true;
			for ($i=0; $i<count($categories); $i++) {
				if ($categories[$i]['category_content_id'] != $category_content_id) $unique = false;
			}
			
			if (!$unique) {					
				$clean_alias = preg_replace('/-[0-9]+$/', '', $full_alias_data[0]['alias']);
				$nr = substr(str_replace($clean_alias, '', $full_alias_data[0]['alias']),1);
				
				if (empty($nr)) $nr = '1';
				else $nr++;
				
				// UPDATE ALIAS
				$db_data = $this->db->update('category_contents')
					->set(array('alias' => $clean_alias."-".$nr))
					->where('category_contents.id', '=', $category_content_id)
					->execute();					
			}
		}	
		
		// UPDATE THIS
		$db_data = $this->db->update('category_contents')
			->set(array('full_alias' => $full_alias))
			->where('category_contents.id', '=', $category_content_id)
			->execute();
		
		// UPDATE CHILD
		$data = $this->db->select(
				array('child_category_contents.id', 'id'))
			->from('category_contents')
			->join('categories')
				->on('category_contents.category_id', '=', 'categories.id')
			->join(array('categories', 'child_categories'))
				->on('categories.id', '=', 'child_categories.parent_id')
			->join(array('category_contents', 'child_category_contents'))
				->on('child_categories.id', '=', 'child_category_contents.category_id')
				->on('child_category_contents.language_id', '=', 'category_contents.language_id')
			->where('category_contents.id', '=', $category_content_id)
			->execute()->as_array();
		
		for ($i=0; $i<count($data); $i++) {
			$this->updateFullAlias($data[$i]['id']);
		}
	}
	function getFullCategoryAlias($category_content_id) {
		$category_data = $this->db->select(
				array('IFNULL("categories.parent_id",0)', 'parent_id'),
				array('category_contents.language_id', 'language_id'),
				array('IFNULL("category_contents.alias",\'\')', 'alias'))
			->from('category_contents')
			->join('categories')
				->on('category_contents.category_id', '=', 'categories.id')
			->where('category_contents.id', '=', $category_content_id)
			->execute()->as_array();
		
		$parent_id = $category_data[0]['parent_id'];
		$lang_id = $category_data[0]['language_id'];
		$alias = $category_data[0]['alias'];

		while (!empty($parent_id)) {
			$category_data = $this->db->select(
					array('IFNULL("categories.parent_id",0)', 'parent_id'),
					array('category_contents.alias', 'alias'))
				->from('categories')
				->join('category_contents', 'LEFT')
					->on('category_contents.category_id', '=', 'categories.id')
					->on('category_contents.language_id', '=', DB::expr($lang_id))
				->where('categories.id', '=', $parent_id)
				->execute()->as_array();
			
			$parent_id = $category_data[0]['parent_id'];
			$alias = $category_data[0]['alias'].'/'.$alias;
		}
	
		return $alias;
	}

	/*
	 * SETTINGS
	 */
	function getCategorySettings($id = null, $category_id = null, $lang_id = null, $filter_data = array()) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		$sql = $this->db->select(
				array('category_settings.id', 'id'),
				array('category_settings.category_id', 'category_id'),
				array('category_settings.image_src', 'image_src'),
				array('category_settings.order_index', 'order_index'),
				array('category_settings.type_id', 'type_id'),
				array('types.name', 'type_name'),
				array('category_settings.status_id', 'status_id'),
				array('status.name', 'status_name'),
				
				array('category_setting_contents.id', 'l_id'),
				array('category_setting_contents.language_id', 'l_language_id'),
				array('category_setting_contents.title', 'l_title') )
			->from('category_settings')
			->join('category_setting_contents', 'LEFT')
				->on('category_settings.id', '=', 'category_setting_contents.category_setting_id');
				if (!is_null($lang_id)) $sql->on('category_setting_contents.language_id', '=', DB::expr($lang_id));
		$sql->join('status')
				->on('status.table_status_name', '=', DB::expr("'category_settings_status_id'"))
				->on('status.status_id', '=', 'category_settings.status_id')
			->join('types')
				->on('types.table_type_name', '=', DB::expr("'category_settings_type_id'"))
				->on('types.type_id', '=', 'category_settings.type_id');
		
		// WHERE
		if (!is_null($id)) {
			if (is_array($id)) {
				$sql->where('category_settings.id', 'IN', $id);
				$sql->where('category_settings.category_id', '=', $category_id);
			} else {
				$sql->where('category_settings.id', '=', $id);
			}
		} else $sql->where('category_settings.category_id', '=', $category_id);
		if (isset($filter_data['active'])) $sql->where('category_settings.status_id', '>=', '10');
		
		// ORDER BY
		$sql->order_by('category_settings.order_index', 'ASC');
		
		// DATA
		$db_data = $sql->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);
		
		return $db_data;		
	}
	function saveCategorySettings($post_data) {		
		if (isset($post_data['id']) && is_numeric($post_data['id'])) {
			// UPDATE
			$db_data = $this->db->update('category_settings')
				->set(array(
					'order_index' => $post_data['order_index'],
					'status_id' => $post_data['status_id'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('category_settings.id', '=', $post_data['id'])
				->where('category_settings.category_id', '=', $post_data['category_id'])
				->execute();
			$id = $post_data['id'];
		} else {
			// INSERT			
			$db_data = $this->db->insert('category_settings', array(
					'category_id',
					'type_id',
					'order_index',
					'status_id',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$post_data['category_id'],
					$post_data['type_id'],
					$post_data['order_index'],
					$post_data['status_id'],
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
			$id = $db_data[0];
		}
		
		// IMAGES
		$category_settings = $this->getCategorySettings($id);
		$image_src = $this->files->update_image2('files/category_settings/'.$category_settings[0]['id'].'/', $post_data['image_src'], $category_settings[0]['image_src']);
		$db_data = $this->db->update('category_settings')
			->set(array('image_src' => $image_src))
			->where('category_settings.id', '=', $category_settings[0]['id'])
			->execute();
		
		for ($i=0; $i<count($post_data['content_id']); $i++) {
			if (is_numeric($post_data['content_id'][$i])) {
				// UPDATE
				$db_data = $this->db->update('category_setting_contents')
					->set(array('title' => $post_data['title'][$i]))
					->where('category_setting_contents.id', '=', $post_data['content_id'][$i])
					->where('category_setting_contents.language_id', '=', $post_data['language_id'][$i])
					->execute();
			} else {
				// INSERT
				$db_data = $this->db->insert('category_setting_contents', array(
						'category_setting_id',
						'language_id',
						'title' ))
					->values(array(
						$id,
						$post_data['language_id'][$i],
						$post_data['title'][$i] ))
					->execute();
			}
		}
		
		return $id;
	}
	function deleteCategorySettings($post_data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($post_data['setting_id'])) {
			// CHECK ADDED VALUES
			$setting_values = $this->getCategorySettingValues(null, $post_data['setting_id']);
			
			if (count($setting_values) == 0) {
				// DELETE IMAGES
				$settings = $this->getCategorySettings($post_data['setting_id']);
				$this->files->deleteFile($settings[0]['image_src']);	
				
				// DELETE CONTENTS
				$db_data = $this->db	
					->delete('category_setting_contents')
					->where('category_setting_contents.category_setting_id', '=', $post_data['setting_id'])
					->execute();
					
				$db_data = $this->db	
					->delete('category_settings')
					->where('category_settings.id', '=', $post_data['setting_id'])
					->execute();
				
				$status = array(	'status' => '1',
									'error' => '',
									'response' => '');
			} else {
				$status = array(	'status' => '0',
									'error' => __('This parameter has added values!'),
									'response' => '');
			} 
		} 
		
		return $status;	
	}


	function getCategorySettingValues($id = null, $category_setting_id = null, $lang_id = null, $filter_data = array()) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		$sql = $this->db->select(
				array('category_setting_values.id', 'id'),
				array('category_setting_values.category_setting_id', 'category_setting_id'),
				array('category_settings.type_id', 'category_setting_type_id'),
				array('category_setting_values.color', 'color'),
				array('category_setting_values.image_src', 'image_src'),
				array('category_setting_values.order_index', 'order_index'),
				array('category_setting_values.status_id', 'status_id'),
				array('status.name', 'status_name'),
				
				array('category_setting_value_contents.id', 'l_id'),
				array('category_setting_value_contents.language_id', 'l_language_id'),
				array('category_setting_value_contents.title', 'l_title') )
			->from('category_setting_values')
			->join('category_settings')
				->on('category_setting_values.category_setting_id', '=', 'category_settings.id')
			->join('category_setting_value_contents', 'LEFT')
				->on('category_setting_values.id', '=', 'category_setting_value_contents.category_setting_value_id');
				if (!is_null($lang_id)) $sql->on('category_setting_value_contents.language_id', '=', DB::expr($lang_id));
		$sql->join('status')
				->on('status.table_status_name', '=', DB::expr("'category_setting_values_status_id'"))
				->on('status.status_id', '=', 'category_setting_values.status_id');
		
		// WHERE
		if (!is_null($id)) $sql->where('category_setting_values.id', '=', $id);
		else {
			if (!is_array($category_setting_id)) $category_setting_id = array($category_setting_id);
			$sql->where('category_setting_values.category_setting_id', 'IN', $category_setting_id);
		}
		if (isset($filter_data['active'])) $sql->where('category_setting_values.status_id', '>=', '10');
		
		// ORDER BY
		$sql->order_by('category_setting_values.order_index', 'ASC');
		
		// DATA
		$db_data = $sql->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);
		
		return $db_data;		
	}
	function saveCategorySettingValues($post_data) {
		if (!isset($post_data['color'])) $post_data['color'] = '';
		
		if (isset($post_data['id']) && is_numeric($post_data['id'])) {
			// UPDATE
			$db_data = $this->db->update('category_setting_values')
				->set(array(
					'color' => $post_data['color'],
					'order_index' => $post_data['order_index'],
					'status_id' => $post_data['status_id'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('category_setting_values.id', '=', $post_data['id'])
				->where('category_setting_values.category_setting_id', '=', $post_data['category_setting_id'])
				->execute();
			$id = $post_data['id'];
		} else {
			// INSERT
			$db_data = $this->db->insert('category_setting_values', array(
					'category_setting_id',
					'color',
					'order_index',
					'status_id',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$post_data['category_setting_id'],
					$post_data['color'],
					$post_data['order_index'],
					$post_data['status_id'],
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
			$id = $db_data[0];
		}
		
		// IMAGES
		$category_setting_values = $this->getCategorySettingValues($id);
		$image_src = $this->files->update_image2('files/category_settings/'.$category_setting_values[0]['category_setting_id'].'/', $post_data['image_src'], $category_setting_values[0]['image_src']);
		$db_data = $this->db->update('category_setting_values')
			->set(array('image_src' => $image_src))
			->where('category_setting_values.id', '=', $id)
			->execute();
		
		for ($i=0; $i<count($post_data['content_id']); $i++) {
			if (is_numeric($post_data['content_id'][$i])) {
				// UPDATE
				$db_data = $this->db->update('category_setting_value_contents')
					->set(array('title' => $post_data['title'][$i]))
					->where('category_setting_value_contents.id', '=', $post_data['content_id'][$i])
					->where('category_setting_value_contents.language_id', '=', $post_data['language_id'][$i])
					->execute();
			} else {
				// INSERT
				$db_data = $this->db->insert('category_setting_value_contents', array(
						'category_setting_value_id',
						'language_id',
						'title' ))
					->values(array(
						$id,
						$post_data['language_id'][$i],
						$post_data['title'][$i] ))
					->execute();
			}
		}
		
		return $id;
	}
	public function deleteCategorySettingValues($post_data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($post_data['setting_value_id'])) {
			// CHECK ADDED SETTINGS
			$product_data = $this->db->select('product_category_settings.category_setting_value_id')
				->from('product_category_settings')
				->where('product_category_settings.category_setting_value_id', '=', $post_data['setting_value_id'])
				->execute()
				->as_array();
			
			if (count($product_data) == 0) {
				// DELETE IMAGES
				$setting_values = $this->getCategorySettingValues($post_data['setting_value_id']);
				$this->files->deleteFile($setting_values[0]['image_src']);	
				
				// DELETE CONTENTS
				$db_data = $this->db	
					->delete('category_setting_value_contents')
					->where('category_setting_value_contents.category_setting_value_id', '=', $post_data['setting_value_id'])
					->execute();
					
				$db_data = $this->db	
					->delete('category_setting_values')
					->where('category_setting_values.id', '=', $post_data['setting_value_id'])
					->execute();
				
				$status = array(	'status' => '1',
									'error' => '',
									'response' => '');
			} else {
				$status = array(	'status' => '0',
									'error' => __('This parameter is added to product!'),
									'response' => '');
			} 
		} 
		
		return $status;	
	}
}