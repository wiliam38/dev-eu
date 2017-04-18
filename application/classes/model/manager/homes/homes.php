<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Homes_Homes extends Model {
	public function __construct() {
		parent::__construct();
		
		$this->files = Model::factory('manager_files');
	}
	
	public function getHomes($id = null, $lang_id = null, $filter_data = array(),  $limit = null, $offset = null, $count_all = false) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		// SELECT
		$res = $this->db->select(); 
		
		// FROM
		$res->from('homes'); 
		$res->join('home_contents', 'LEFT')
			->on('homes.id', '=', 'home_contents.home_id');
			if (!is_null($lang_id)) $res->on('home_contents.language_id', '=', DB::expr($lang_id)); 
		           
		// WHERE
		if (!is_null($id)) $res->where('homes.id', '=', $id);
		if (isset($filter_data['from_status_id'])) $res->where('home_contents.status_id', '>=', $filter_data['from_status_id']);	
		     
		// ORDER BY
		$res->order_by('homes.order_index', 'ASC');
		$res->order_by('homes.id', 'DESC');
		
		// ONLY FOR COUNT ROWS
		if ($count_all) {
			$res->select(array('COUNT("homes.id")', 'cnt'));
			$res->group_by('homes.id');
			$data = $db_data = $res->execute()->as_array();
                  
			return count($data);
		}
		
		// LIMIT
		if(!is_null($limit)) {
			$tmp_res = $res;
			// SELECT
			$tmp_res->select(array('homes.id', 'id'));
                                                
			// LIMIT
			$tmp_res->limit($limit);
			if (!is_null($offset) && $offset > 0) $tmp_res->offset($offset);
			
			// GROUP BY			
			//$tmp_res->group_by('homes.id');
                  
			// DATA
			$db_data = $tmp_res->execute()->as_array();         
                                    
			$id_list = array();
			foreach($db_data as $key => $val) $id_list[] = $val['id'];
			$res->where('homes.id', 'IN', !empty($id_list)?$id_list:array(-1));      
			$res->limit(null);
			$res->offset(null);                   
		} 
		
		// SELECT
		$res->select(
			array('homes.id', 'id'),
			array('homes.admin_title', 'admin_title'),
			array('homes.order_index', 'order_index'),
			array('homes.image_src', 'image_src'),
			
			array('homes.color_type_id', 'color_type_id'),
			array('color_types.name', 'color_type_name'),
			array('color_types.value', 'color_type_value'),
			
			array('home_contents.id', 'l_id'),
			array('home_contents.language_id', 'l_language_id'),
			array('home_contents.status_id', 'l_status_id'),
			array('status.name', 'l_status_name'),
			array('status.description', 'l_status_description'),
			array('home_contents.title', 'l_title'),
			array('home_contents.intro', 'l_intro'),
			array('home_contents.link', 'l_link') );
		
		// FROM
		$res->join('status', 'LEFT')
				->on('home_contents.status_id', '=', 'status.status_id')
				->on('status.table_status_name', '=', DB::expr("'home_contents_status_id'"))
			->join(array('types', 'color_types'), 'LEFT')
				->on('homes.color_type_id', '=', 'color_types.type_id')
				->on('color_types.table_type_name', '=', DB::expr("'homes_color_type_id'"));
			
		// ORDER BY
		$res->order_by('homes.order_index', 'ASC');
		
		// DATA
		$db_data = $res->execute()->as_array();		
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}
		
	public function save($data) {
		$this->resources = Model::factory('manager_resources');
		
		if ($data['home_id'] == 'new') {
			// INSERT
			$db_data = $this->db->insert('homes', array(
					'admin_title',
					'order_index',
					'color_type_id',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$data['admin_title'],
					$data['order_index'],
					$data['color_type_id'],
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
				
			$home_id = $db_data[0];
		} else {
			$db_data = $this->db->update('homes')
				->set(array(
					'admin_title' => $data['admin_title'],
					'order_index' => $data['order_index'],
					'color_type_id' => $data['color_type_id'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('homes.id', '=', $data['home_id'])
				->execute();
				
			$home_id = $data['home_id'];
		}
		
		// IMAGE
		$home = $this->getHomes($home_id);
		$image_src = $this->files->update_image2('files/homes/'.$home_id.'/', $data['image_src'], $home[0]['image_src']);
		
		$db_data = $this->db->update('homes')
			->set(array('image_src' => $image_src))
			->where('homes.id', '=', $home_id)
			->execute();
		
		//
		// LOOP PRODUCT CONTENTS
		//
		
		// UPDATE LANGUAGES		
		if (empty($data['language_id'])) $data['language_id'] = array();
		$needed_home_content_id = array();
		for ($i=0; $i<count($data['language_id']); $i++) {
			if (isset($data[$data['language_id'][$i].'_home_content_id']) AND $data[$data['language_id'][$i].'_home_content_id'] != 'none') {
				if (empty($data[$data['language_id'][$i].'_home_content_id']) OR $data[$data['language_id'][$i].'_home_content_id'] == 'new') {
					// INSERT
					$db_data = $this->db->insert('home_contents', array(
							'home_id',
							'language_id',
							'title',
							'intro',
							'link',
							'status_id',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$home_id,
							$data['language_id'][$i],
							$data[$data['language_id'][$i].'_title'],
							$data[$data['language_id'][$i].'_intro'],
							$data[$data['language_id'][$i].'_link'],
							$data[$data['language_id'][$i].'_status_id'],
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()') ))
						->execute();
					$home_content_id = $db_data[0];	
				} else {
					// UPDATE
					$db_data = $this->db->update('home_contents')
						->set(array(
							'title' => $data[$data['language_id'][$i].'_title'],
							'intro' => $data[$data['language_id'][$i].'_intro'],
							'link' => $data[$data['language_id'][$i].'_link'],
							'status_id' => $data[$data['language_id'][$i].'_status_id'],
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()') ))
						->where('home_contents.id', '=', $data[$data['language_id'][$i].'_home_content_id'])
						->execute();
					$home_content_id = $data[$data['language_id'][$i].'_home_content_id'];	
				}
				$needed_home_content_id[] = $home_content_id;
			}
		}	

		// DELETE REMOVED TRANSLATIONS
		if (empty($needed_home_content_id)) $needed_home_content_id[] = 0;
		$db_data = $this->db->delete('home_contents')
			->where('home_contents.home_id', '=', $home_id)
			->where('home_contents.id', 'NOT IN', $needed_home_content_id)
			->execute();

		return $home_id;		
	}

	public function delete($data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($data['home_id'])) {
			$homes = $this->getHomes($data['home_id']);
			
			// DELETE IMAGE
			$this->files->deleteFile($homes[0]['image_src']);
			
			// DELETE CONTENTS
			$db_data = $this->db->delete('home_contents')
				->where('home_contents.home_id', '=', $data['home_id'])
				->execute();
			
			// DELETE HOME
			$db_data = $this->db->delete('homes')
				->where('homes.id', '=', $data['home_id'])
				->execute();
			
			$status = array(	'status' => '1',
								'error' => '',
								'response' => '');
		} 
		
		return $status;	
	}
}