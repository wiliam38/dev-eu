<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Types extends Model {
	
	//
	// GET SETTINGS
	//
	public function getTypes($id = null, $lang_id = null, $category_name = null, $filter_data = array()) {
		$sql = $this->db->select(
				array('types.id', 'id'),
				array('types.table_type_name', 'table_type_name'),
				array('types.type_id', 'type_id'),
				array('types.name', 'name'),
				array('types.description', 'description'),
				array('types.value', 'value'),
				array('types.image_src', 'image_src'),
				array('types.order_index', 'order_index'),
				array('types.user_id', 'user_id'),
				
				array('types.user_datetime', 'user_datetime'),
				array('CONCAT("users.first_name",\' \',"users.last_name")', 'user_full_name'),
				
				array('IFNULL("type_contents.id", \'new\')', 'l_id'),
				array('languages.id', 'l_language_id'),
				array('type_contents.name', 'l_name') )
				
			->from('types')
			->join('languages', 'left')
				->on('languages.status_id', '>=', DB::expr('5'))
			->join('type_contents', 'left')
				->on('types.id', '=', 'type_contents.type_id')
				->on('type_contents.language_id', '=', 'languages.id')
			->join('users', 'left')
				->on('types.user_id', '=', 'users.id')
			->order_by('types.order_index')
			->order_by('types.name');				
		
		// FILTER
		if (!is_null($id)) $sql->where('types.id', '=', $id);
		else $sql->where('types.table_type_name', '=', $category_name);
		if (!empty($filter_data['type_id'])) $sql->where('types.type_id', '=', $filter_data['type_id']);
	
		return CMS::langArray($sql->execute()->as_array());
	}

	public function getTableTypeName() {
		return $this->db->select(array('DISTINCT "types.table_type_name"', 'value'))
					->from('types')
					->order_by('types.table_type_name')
					->execute()
					->as_array();
	}
	
	//
	//
	//
	// JOBS
	//
	//
	//
	
	//
	// SAVE
	//
	public function save($id, $data) {
		if (!is_numeric($id)) {				
			// CHECK UNIQUE
			$tmp_data = $this->getTypes(null, null, $data['table_type_name'], array('type_id' => $data['type_id']));
			if (count($tmp_data) > 0) return __('Type already exists!');
				
			// INSERT
			$db_data = $this->db->insert('types', array(
					'table_type_name',
					'type_id',
					'name',
					'description',
					'value',
					'order_index',
					'user_id',
					'user_datetime'))
				->values(array(
					$data['table_type_name'],
					$data['type_id'],
					$data['name'],
					$data['description'],
					$data['value'],
					$data['order_index'],
					$this->user_id,
					DB::expr('NOW()')))
				->execute();
			$id = $db_data[0];
		} else {
			// UPDATE
			$this->db->update('types')
				->set(array(
					'description' => $data['description'],
					'value' => $data['value'],
					'order_index' => $data['order_index'],				
					'user_id' => $this->user_id,
					'user_datetime' => DB::expr('NOW()') ))
				->where('types.id', '=', $id)
				->execute();
		}
		
		// IMAGE
		$this->files = Model::factory('manager_files');
		$types = $this->getTypes($id);
		$image_src = $this->files->update_image2('files/types/', $data['image_src'], $types[0]['image_src']);
		
		$db_data = $this->db->update('types')
			->set(array('image_src' => $image_src))
			->where('types.id', '=', $id)
			->execute();

		// UPDATE LANGS
		if (!empty($data['content_id'])) {
			for ($i=0; $i<count($data['content_id']); $i++) {
				if (!is_numeric($data['content_id'][$i])) {
					// INSERT
					$this->db->insert('type_contents', array(
							'type_id',
							'language_id',
							'name' ))
						->values(array(
							$id,
							$data['language_id'][$i],
							$data['content_name'][$i] ))
						->execute();
				} else {
					// UPDATE
					$this->db->update('type_contents')
						->set(array('name' => $data['content_name'][$i]))
						->where('type_contents.id', '=', $data['content_id'][$i])
						->where('type_contents.language_id', '=', $data['language_id'][$i])
						->where('type_contents.type_id', '=', $id)
						->execute();
				}
			}
		}
			
		return $id;
	}
	
	//
	// DELETE
	//
	public function delete($id) {
		// DELETE IMAGE
		$this->files = Model::factory('manager_files');
		$types = $this->getTypes($id);
		$image_src = $this->files->update_image2('files/types/', '', $types[0]['image_src']);
		
		// DELETE CONTENTS
		$this->db->delete('type_contents')
			->where('type_contents.type_id', '=', $id)
			->execute();
			
		// DELETE TYPE 
		$this->db->delete('types')
			->where('types.id', '=', $id)
			->execute();
	}
}