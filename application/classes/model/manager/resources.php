<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Resources extends Model {	
	//
	// GET DOCUMENTS
	//
	public function getDocuments($id = null, $parent_id = null, $page_alias = null, $lang_id = null, $filters = array(), $type_list = array('1'), $order_by = " pages.order_index ", $limit = " ", $where = " ") {
		// FILTER
		$filter = " ";
		$pc_filter = " ";
		if (!is_null($parent_id)) $filter .= " AND pages.parent_id IN :parent_id ";
		if (!is_null($id)) $filter .= " AND pages.id = :id ";
		if (!is_null($lang_id)) $pc_filter .= " AND page_contents.language_id = :lang_id ";
		if (isset($filters['not_page_contnet_id'])) {
			if (empty($filters['not_page_contnet_id'])) $filters['not_page_contnet_id'] = array(0);
			$filter .= " AND page_contents.id NOT IN :not_page_contnet_id ";
		}
		
		if (isset($filters['sitemap'])) {
			$filter .= " AND 
				page_contents.status_id >= 10 AND
				page_contents.searchable = 1 AND 
				page_contents.content_type_id = 1 ";
		}
		
		
		$sql = "SELECT
					pages.id							AS id,
					pages.template_id					AS template_id,
					pages.parent_id						AS parent_id,
					pages.date							AS date,
					pages.order_index					AS order_index,
					pages.main_image_id					AS main_image_id,
					pages.image_src						AS image_src,
					pages.admin_title					AS admin_title,
					pages.status_id						AS status_id,
					pages.type_id						AS type_id,
					IF(pages.id IN :parent_id,'1','0')	AS opened,
					
					templates.conf_title_image			AS conf_title_image,
					templates.conf_introtext			AS conf_introtext,
					templates.conf_image				AS conf_image,
					templates.conf_menu_image			AS conf_menu_image,
					templates.conf_seo					AS conf_seo,
					templates.conf_target				AS conf_target,
					templates.conf_gallery				AS conf_gallery,
					templates.type_id					AS conf_type_id,
					
					page_contents.id					AS l_id,
					page_contents.language_id			AS l_language_id,
					page_contents.title					AS l_title,
					page_contents.title_image_src		AS l_title_image_src,
					page_contents.description			AS l_description,
					page_contents.intro					AS l_intro,
					page_contents.image_src				AS l_image_src,
					page_contents.pub_date				AS l_pub_date,
					page_contents.unpub_date			AS l_unpub_date,
					page_contents.alias					AS l_alias,
					page_contents.full_alias			AS l_full_alias,
					CONCAT(languages.tag,'/',page_contents.full_alias)		AS l_full_page_alias,
					page_contents.target_type_id		AS l_target_type_id,
					page_contents.keywords				AS l_keywords,
					page_contents.menu_title			AS l_menu_title,
					page_contents.menu_image_src		AS l_menu_image_src,
					page_contents.hide_menu				AS l_menu_hide,
					page_contents.searchable			AS l_searchable,
					page_contents.content				AS l_content,
					page_contents.content_type_id		AS l_content_type_id,
					page_contents.redirect_link			AS l_redirect_link,
					page_contents.status_id				AS l_status_id,
					page_contents.user_datetime			AS l_datetime
				FROM
					pages
					LEFT JOIN page_contents ON
						page_contents.page_id = pages.id
						".$pc_filter."
					LEFT JOIN languages ON
						page_contents.language_id = languages.id
					LEFT JOIN templates ON
						pages.template_id = templates.id
				WHERE
					pages.status_id > 0 AND
					pages.type_id IN :type_list
					".$filter."
					".$where."
				ORDER BY
					".$order_by."
				".$limit." ";		
					
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		if (!is_array($parent_id)) $parent_id = array($parent_id);
		$result->bind(':parent_id', $parent_id);		
		if (!empty($filters['not_page_contnet_id'])) $result->bind(':not_page_contnet_id', $filters['not_page_contnet_id']);
		if (!is_array($type_list)) $type_list = explode(',',$type_list);
		$result->bind(':type_list', $type_list);
		$result->bind(':lang_id', $lang_id);

		$data = $result->execute()->as_array();
		
		// LANG ARRAY
		$data = CMS::langArray($data);		

		return $data;
	}	
	
	//
	// GET NEW DOCUMENT
	//
	public function getNewDocuments($from_id = null) {
		if (!empty($from_id)) {
			// GET DATA FROM RESOURCE
			$tmp_data = $this->getDocuments($from_id);
			
			$data[0] = array(
				'id' => 'new',
				'template_id' => $tmp_data[0]['template_id'],
				'parent_id' => $tmp_data[0]['id'],
				'date' => time(),
				'order_index' => '0',
				'admin_title' => '',
				'content_type_id' => '1',
				'target_type_id' => 1);
		} else {
			$data[0] = array(
				'id' => 'new',
				'template_id' => '1',
				'parent_id' => '0',
				'date' => time(),
				'order_index' => '0',
				'admin_title' => '',
				'content_type_id' => '1',
				'target_type_id' => 1);
		}
		
		// UPDATE INDEX
		$tmp_data[0]['type_id'] = !empty($tmp_data[0]['type_id'])?$tmp_data[0]['type_id']:'1';
		
		$sql = "SELECT
					IFNULL(MAX(pages.order_index),0)+10	AS max_index
				FROM
					pages
				WHERE
					pages.status_id > 0 AND
					pages.type_id = :type_id AND
					pages.parent_id = :parent_id ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':parent_id', $data[0]['parent_id']);
		$result->bind(':type_id', $tmp_data[0]['type_id']);
		$max_data = $result->execute()->as_array();
		if (isset($max_data[0]['max_index'])) $data[0]['order_index'] = $max_data[0]['max_index'];
		else $data[0]['order_index'] = '10';

		return $data;		
	}
	
	//
	// GET PAGE FULL ALIAS
	//
	public function updateFullAlias($page_content_id, $type_id = '1') {
		$unique = false;	
			
		while (!$unique) {			
			// GET FULL ALIAS
			$full_alias = $this->getFullPageAlias($page_content_id);
			$sql = "SELECT 
						page_contents.alias					AS alias,
						page_contents.language_id			AS lang_id
					FROM
						page_contents
					WHERE
						page_contents.id = :page_content_id ";
			$result = $this->db->query(Database::SELECT, $sql);
			$result->bind(':page_content_id', $page_content_id);
			$full_alias_data = $result->execute()->as_array();
			
			$sql = "SELECT 
						page_contents.id					AS page_content_id
					FROM
						page_contents
					WHERE
						page_contents.full_alias = :full_alias AND
						page_contents.language_id = :lang_id ";
			$result = $this->db->query(Database::SELECT, $sql);
			$result->bind(':full_alias', $full_alias);
			$result->bind(':lang_id', $full_alias_data[0]['lang_id']);
			$doc = $result->execute()->as_array();

			// LOOP DOCS
			$unique = true;
			for ($i=0; $i<count($doc); $i++) {
				if ($doc[$i]['page_content_id'] != $page_content_id) $unique = false;
			}
			
			if (!$unique) {					
				$clean_alias = preg_replace('/-[0-9]+$/', '', $full_alias_data[0]['alias']);
				$nr = substr(str_replace($clean_alias, '', $full_alias_data[0]['alias']),1);
				
				if (empty($nr)) $nr = '1';
				else $nr++;
				
				// UPDATE ALIAS
				$sql = "UPDATE
							page_contents
						SET
							alias = :alias
						WHERE
							page_contents.id = :page_content_id";
				$result = $this->db->query(Database::UPDATE, $sql);
				$result->bind(':page_content_id', $page_content_id);
				$new_alias = $clean_alias."-".$nr;
				$result->bind(':alias', $new_alias);
				$result->execute();						
			}
		}	
		
		// UPDATE THIS
		$sql = "UPDATE
					page_contents
				SET
					full_alias = :full_alias
				WHERE
					page_contents.id = :page_content_id";
		$result = $this->db->query(Database::UPDATE, $sql);
		$full_alias = $this->getFullPageAlias($page_content_id);
		$result->bind(':full_alias', $full_alias);
		$result->bind(':page_content_id', $page_content_id);
		$result->execute();	
		
		// UPDATE CHILD
		$sql = "SELECT
					child_page_contents.id			AS id
				FROM
					page_contents
					JOIN pages ON
						page_contents.page_id = pages.id
					JOIN pages child_pages ON
						pages.id = child_pages.parent_id
					JOIN page_contents child_page_contents ON
						child_pages.id = child_page_contents.page_id AND
						child_page_contents.language_id = page_contents.language_id
				WHERE
					page_contents.id = :page_content_id ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':page_content_id', $page_content_id);
		$data = $result->execute()->as_array();
		
		for ($i=0; $i<count($data); $i++) {
			$this->updateFullAlias($data[$i]['id'], $type_id);
		}
	}
	function getFullPageAlias($page_content_id) {
		$sql = "SELECT
					IFNULL(pages.parent_id,0)				AS parent_id,
					page_contents.language_id				AS language_id,
					IFNULL(page_contents.alias,'')			AS alias
				FROM
					page_contents
					JOIN pages ON
						page_contents.page_id = pages.id
				WHERE
					page_contents.id = :page_content_id";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':page_content_id', $page_content_id);
		$page_data = $result->execute()->as_array();
		
		$parent_id = $page_data[0]['parent_id'];
		$lang_id = $page_data[0]['language_id'];
		$alias = $page_data[0]['alias'];

		while (!empty($parent_id)) {
			$sql = "SELECT
						IFNULL(pages.parent_id,0)								AS parent_id,
						CONCAT(IFNULL(page_contents.alias,''),'/',:alias)		AS alias
					FROM
						pages
						LEFT JOIN page_contents ON
							page_contents.page_id = pages.id AND
							page_contents.language_id = :lang_id
					WHERE
						pages.id = :parent_id";
			$result = $this->db->query(Database::SELECT, $sql);
			$result->bind(':alias', $alias);
			$result->bind(':lang_id', $lang_id);
			$result->bind(':parent_id', $parent_id);
			$page_data = $result->execute()->as_array();
			
			$parent_id = $page_data[0]['parent_id'];
			$alias = $page_data[0]['alias'];
		}
	
		return $alias;
	}
	
	public function getDocumentImages($id = null, $page_id = null, $lang_id = null) {
		 if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		 
		$res = $this->db->select(
			array('page_images.id', 'id'),
			array('page_images.image_src', 'image_src'),
			
			array('page_image_contents.id', 'l_id'),
			array('page_image_contents.language_id', 'l_language_id'),
			array('page_image_contents.title', 'l_title'),
			array('page_image_contents.description', 'l_description') );
			
		$res->from('page_images');
		$res->join('page_image_contents', 'LEFT')
			->on('page_images.id', '=', 'page_image_contents.page_image_id');
			if (!is_null($lang_id)) $res->on('page_image_contents.language_id', '=', DB::expr($lang_id)); 
		
		if (!is_null($id)) $res->where('page_images.id', '=', $id);
		else $res->where('page_images.page_id', '=', $page_id);
		
		$res->order_by('page_images.order_index', 'ASC');
		
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}
	
	//
	//
	// 
	// JOB FUNCTIONS
	//
	//
	//
	
	// 
	// SAVE
	//
	public function save($page_id, $data) {
		// MODEL
		$this->files = Model::factory('manager_files');
        
        // VALIDATE
        if ($data['order_index'] == '') $data['order_index'] = 0;
		
		if (!is_numeric($page_id)) {
			// INSERT
			$db_data = $this->db->insert('pages', array(
					'template_id',
					'parent_id',
					'order_index',
					'admin_title',
					'type_id',
					'status_id'))
				->values(array(
					$data['template_id'],
					$data['parent_id'],
					$data['order_index'],
					$data['admin_title'],
					DB::expr('1'),
					DB::expr('10')))
				->execute();
			$page_id = $db_data[0];
		} else {
			// UPDATE
			$db_data = $this->db->update('pages')
				->set(array(
					'template_id' => $data['template_id'],
					'parent_id' => $data['parent_id'],
					'order_index' => $data['order_index'],
					'admin_title' => $data['admin_title']))
				->where('pages.id', '=', $page_id)
				->execute();
		}
		
		//
		// LOOP SUB PAGES
		//
		
		// UPDATE LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		
		$needed_page_content_id = array();
		for ($i=0; $i<count($lang); $i++) {
			$page_content_id = isset($data[$lang[$i]['id'].'_page_content_id'])?$data[$lang[$i]['id'].'_page_content_id']:'none';
			if (!empty($page_content_id) && $page_content_id != 'none') {
				if (!is_numeric($page_content_id)) {
					// INSERT
					$db_data = $this->db->insert('page_contents', array(
							'page_id',
							'language_id',
							'title',
							'description',
							'intro',
							'content',
							'content_type_id',
							'redirect_link',
							'status_id',
							'pub_date',
							'unpub_date',
							'alias',
							'target_type_id',
							'keywords',
							'menu_title',											
							'hide_menu',
							'searchable',
							'user_id',
							'user_datetime'))
						->values(array(
							$page_id,
							$data[$lang[$i]['id'].'_language_id'],
							$data[$lang[$i]['id'].'_title'],
							$data[$lang[$i]['id'].'_description'],
							$data[$lang[$i]['id'].'_intro'],
							$data[$lang[$i]['id'].'_content'],
							$data[$lang[$i]['id'].'_content_type_id'],
							$data[$lang[$i]['id'].'_redirect_link'],
							$data[$lang[$i]['id'].'_status_id'],
							CMS::date($data[$lang[$i]['id'].'_pub_date']),
							CMS::date($data[$lang[$i]['id'].'_unpub_date']),
							$data[$lang[$i]['id'].'_alias'],
							$data[$lang[$i]['id'].'_target_type_id'],
							$data[$lang[$i]['id'].'_keywords'],
							$data[$lang[$i]['id'].'_menu_title'],
							isset($data[$lang[$i]['id'].'_menu_hide'])?1:0,
							isset($data[$lang[$i]['id'].'_searchable'])?1:0,
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
					$page_content_id = $db_data[0];					
				} else {
					// UPDATE
					$db_data = $this->db->update('page_contents')
						->set(array(
							'title' => $data[$lang[$i]['id'].'_title'],
							'description' => $data[$lang[$i]['id'].'_description'],
							'intro' => $data[$lang[$i]['id'].'_intro'],
							'content' => $data[$lang[$i]['id'].'_content'],
							'content_type_id' => $data[$lang[$i]['id'].'_content_type_id'],
							'redirect_link' => $data[$lang[$i]['id'].'_redirect_link'],
							'status_id' => $data[$lang[$i]['id'].'_status_id'],
							'pub_date' => CMS::date($data[$lang[$i]['id'].'_pub_date']),
							'unpub_date' => CMS::date($data[$lang[$i]['id'].'_unpub_date']),
							'alias' => $data[$lang[$i]['id'].'_alias'],
							'target_type_id' => $data[$lang[$i]['id'].'_target_type_id'],
							'keywords' => $data[$lang[$i]['id'].'_keywords'],
							'menu_title' => $data[$lang[$i]['id'].'_menu_title'],
							'hide_menu' => isset($data[$lang[$i]['id'].'_menu_hide'])?1:0,
							'searchable' => isset($data[$lang[$i]['id'].'_searchable'])?1:0,
							'user_id' => $this->user_id,
							'user_datetime' => DB::expr('NOW()')))
						->where('page_contents.id', '=', $page_content_id)
						->execute();
				}
					
				// UPDATE FULL PATH
				$this->updateFullAlias($page_content_id);
					
				// UPDATE TITLE IMAGES
				$img_src = preg_replace('/\/thumb_([^\/]*)$/i', '/$1', $data[$lang[$i]['id'].'_title_image_src']);
				$title_image_src_upd = $this->files->update_image($page_id, $page_id, $img_src, array('1'), 'title_image_src',$data[$lang[$i]['id'].'_language_id']);
									
				// IMAGES
				$img_src = preg_replace('/\/thumb_([^\/]*)$/i', '/$1', $data[$lang[$i]['id'].'_image_src']);
				$image_src_upd = $this->files->update_image($page_id, $page_id, $img_src, array('1'), 'image_src',$data[$lang[$i]['id'].'_language_id']);
									
				// UPDATE MENU IMAGES
				$img_src = preg_replace('/\/thumb_([^\/]*)$/i', '/$1', $data[$lang[$i]['id'].'_menu_image_src']);
				$menu_image_src_upd = $this->files->update_image($page_id, $page_id, $img_src, array('1'), 'menu_image_src',$data[$lang[$i]['id'].'_language_id']);
				
				$this->db->update('page_contents')
					->set(array(
						'title_image_src' => $title_image_src_upd,
						'image_src' => $image_src_upd,
						'menu_image_src' => $menu_image_src_upd ))
					->where('page_contents.id', '=', $page_content_id)
					->execute();
					
				$needed_page_content_id[] = $page_content_id;
			}
		}

		// DELETE REMOVED TRANSLATIONS
		$del_page_data = $this->getDocuments($page_id, null, null, null, array('not_page_contnet_id' => $needed_page_content_id));
		if (!empty($del_page_data[0]['lang'])) {
			foreach ($del_page_data[0]['lang'] as $key => $val) {
				FILES::deleteFile($val['title_image_src']);
				FILES::deleteFile($val['image_src']);
				FILES::deleteFile($val['menu_image_src']);
				
				$this->db->delete('page_contents')
					->where('page_contents.page_id', '=', $page_id)
					->where('page_contents.id', '=', $val['id'])
					->execute();
			}
		}
				
		//
		// LOOP GALLERY
		//		
		$needed_images = array();
		
		if (isset($data['page_image_id'])) {
			for ($i=0; $i < count($data['page_image_id']); $i++) {
				// IMAGE ID
				if (!is_numeric($data['page_image_id'][$i])) {
					// IMAGES
					$image_src = $this->files->update_image2('files/resource_images/'.$page_id.'/', $data['page_image_src'][$i], '');							
					
					$db_data = $this->db->insert('page_images', array(
							'page_id',
							'image_src',
							'order_index',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$page_id,
							$image_src,
							$i,
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
						
					$page_image_id = $db_data[0];
				} else {
					$page_image_id = $data['page_image_id'][$i];
				}
				
				// NEEDED IMAGE
				$needed_images[] = $page_image_id;
				
				// UPDATE MAIN IMAGE
				if ($data['page_main_image'][$i] == "1") {
					$db_data = $this->db->update('pages')
						->set(array(
							'main_image_id' => $page_image_id))
						->where('pages.id', '=', $page_id)
						->execute();
				}

				for ($q=0; $q<count($lang); $q++) {
					if (isset($data[$lang[$q]['id'].'_page_image_content_id'][$i])) {
						if (!empty($data[$lang[$q]['id'].'_page_image_content_id']) && preg_match('/^[0-9]*$/', $data[$lang[$q]['id'].'_page_image_content_id'][$i]) == 1) {
							// UPDATE
							$this->db->update('page_image_contents')
								->set(array(
									'title' => $data[$lang[$q]['id'].'_page_image_content_title'][$i],
									'description' => $data[$lang[$q]['id'].'_page_image_content_description'][$i],
									'user_id' => $this->user_id,
									'datetime' => DB::expr('NOW()')))
								->where('page_image_contents.id', '=', $data[$lang[$q]['id'].'_page_image_content_id'][$i])
								->execute();					
						} else {
							// INSERT 
							$db_data = $this->db->insert('page_image_contents', array(
									'page_image_id',
									'language_id',
									'title',
									'description',
									'user_id',
									'datetime',
									'creation_user_id',
									'creation_datetime'))
								->values(array(
									$page_image_id,
									$lang[$q]['id'],
									$data[$lang[$q]['id'].'_page_image_content_title'][$i],
									$data[$lang[$q]['id'].'_page_image_content_description'][$i],
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
		$db_data = $this->db->select(array('page_images.id', 'id'))
			->from('page_images')
			->where('page_images.id', 'NOT IN', $needed_images)
			->where('page_images.page_id', '=', $page_id)
			->execute()
			->as_array();	
		
		for ($i=0; $i<count($db_data); $i++) {
			$this->deleteDocumentImage($db_data[$i]['id']);
		}
		
		return $page_id;
	}

	public function deleteDocumentImage($page_image_id) {
		// REMOVE IMAGE
		$image_data = $this->getDocumentImages($page_image_id);
		
		if (count($image_data) > 0) {
			$this->files->deleteFile($image_data[0]['image_src']);
			
			$sql = "DELETE FROM page_image_contents
					WHERE page_image_contents.page_image_id = :page_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':page_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
			
			$sql = "DELETE FROM page_images
					WHERE page_images.id = :page_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':page_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
		}
	}
	
	//
	// DELETE
	//
	public function delete($id) {
		$sql = "UPDATE pages
				SET status_id = 0
				WHERE 
					pages.id = :id ";
		$result = $this->db->query(Database::UPDATE, $sql);
		$result->bind(':id', $id);
		$data = $result->execute();
		
		$sql = "SELECT
					pages.id		AS id
				FROM
					pages
				WHERE
					pages.parent_id = :id ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		$data = $result->execute()->as_array();
		
		for ($i=0; $i<count($data); $i++) {
			$this->delete($data[$i]['id']);
		}
	}			
	public function delete_permanently($id, $type_id) {
		$data = $this->getDocuments($id, null, null, $type_id);
		
		// DELETE IMAGES
		$dir = DOCROOT . "files/resources/".$id."/";
		if (file_exists($dir)) {
			$files = Model::factory('manager_files');
			$files->destroy_dir($dir);	
			
			// REMOVE DIR
			rmdir($dir);
		}
		
		// DELETE PAGE
		$sql = "DELETE FROM page_contents
				WHERE page_contents.page_id = :page_id ";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':page_id', $id);
		$data = $result->execute();
		$sql = "DELETE FROM pages
				WHERE pages.id = :page_id ";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':page_id', $id);
		$data = $result->execute();
		
		// DELET SUB PAGES
		$data = $this->getDocuments(null, $id, null, null);
		for ($i=0; $i<count($data); $i++) {
			$this->delete_permanently($data[$i]['id']);
		}		
	}
	
	/*
	 * CLEAR CACHE
	 */
	public function clear_cache() {
		// GENERATE LEXICONS
		$lexicon_class = Model::factory('manager_lexicons');
		$lexicon_class->generate_files();
		
		// GENERATE SETTINGS
		$setting_class = Model::factory('manager_settings');
		$setting_class->generate_files();
		
		// GENERATE PLUGINS
		$plugin_class = Model::factory('manager_plugins');
		$plugin_class->generate_files();
		
		// REMOVE CACHE FILES
		$scan = glob($this->base_path.'application/cache/smarty/compile/*');
		if (!empty($scan)) {
			foreach($scan as $index=>$path) {
				if (is_file($path) AND file_exists($path)) unlink($path);
			}
		}
		
		// GENERATE ROBOTS
		$this->generate_robots();
	}
	public function generate_robots() {		
		$sql = "SELECT
					CONCAT(languages.tag, '/',page_contents.full_alias)	AS alias
				FROM
					pages
					JOIN page_contents ON
						pages.id = page_contents.page_id 
					LEFT JOIN languages ON
						page_contents.language_id = languages.id
				WHERE
					pages.status_id > 0 AND
					page_contents.status_id >= 10 AND
					page_contents.searchable = 0
				ORDER BY
					page_contents.title ";
		$result = $this->db->query(Database::SELECT, $sql);	
		$data = $result->execute()->as_array();
				
		// PUT CONTENT
		$robots_file = 	"User-agent: *\r\n".
						"Disallow: /plugins/\r\n".
						"Disallow: /manager/\r\n";
		
		for ($i=0; $i<count($data); $i++) {
			$robots_file .= 'Disallow: /'.$data[$i]['alias']."/\r\n";
		}		
		$robots_file .= "Allow: /";
		
		// CREATE FILE
		$filename = $this->base_path.'robots.txt';
		//if (file_exists($filename)) unlink($filename);
		file_put_contents($filename, $robots_file);
	}

	//
	// ALIAS
	//
	public function title_to_alias($title) {
		$title = preg_replace('/Ā|ā/','a',$title);
		$title = preg_replace('/Č|č/','c',$title);
		$title = preg_replace('/Ē|ē/','e',$title);
		$title = preg_replace('/Ģ|ģ/','g',$title);
		$title = preg_replace('/Ī|ī/','i',$title);
		$title = preg_replace('/Ķ|ķ/','k',$title);
		$title = preg_replace('/Ļ|ļ/','l',$title);
		$title = preg_replace('/Ņ|ņ/','n',$title);
		$title = preg_replace('/Ō|ō/','o',$title);
		$title = preg_replace('/Š|š/','s',$title);
		$title = preg_replace('/Ū|ū/','u',$title);
		$title = preg_replace('/Ž|ž/','z',$title);	

		$title = preg_replace('/а|А/','a',$title);
		$title = preg_replace('/б|Б/','b',$title);
		$title = preg_replace('/в|В/','v',$title);
		$title = preg_replace('/г|Г/','g',$title);
		$title = preg_replace('/д|Д/','d',$title);
		$title = preg_replace('/е|Е|ё|Ё|э|Э/','e',$title);
		$title = preg_replace('/ж|Ж|з|З/','z',$title);
		$title = preg_replace('/и|И/','i',$title);
		$title = preg_replace('/й|Й/','j',$title);
		$title = preg_replace('/к|К/','k',$title);
		$title = preg_replace('/л|Л/','l',$title);
		$title = preg_replace('/м|М/','m',$title);
		$title = preg_replace('/н|Н/','n',$title);
		$title = preg_replace('/о|О/','o',$title);
		$title = preg_replace('/п|П/','p',$title);
		$title = preg_replace('/р|Р/','r',$title);
		$title = preg_replace('/с|С|ш|Ш|щ|Щ/','s',$title);
		$title = preg_replace('/т|Т/','t',$title);
		$title = preg_replace('/у|У/','u',$title);
		$title = preg_replace('/ф|Ф/','f',$title);
		$title = preg_replace('/х|Х/','h',$title);
		$title = preg_replace('/ц|Ц|ч|Ч/','c',$title);
		$title = preg_replace('/ъ|Ъ|ь|Ь/','',$title);
		$title = preg_replace('/ы|Ы/','y',$title);
		$title = preg_replace('/ю|Ю/','ju',$title);
		$title = preg_replace('/я|Я/','ja',$title);
		
		$title = preg_replace('/[^a-zA-Z0-9-]/','-',$title);
		$title = preg_replace('/[-]+/','-',$title);
		
		return strtolower($title);
	} 
 
}