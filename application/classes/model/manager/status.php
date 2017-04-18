<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Status extends Model {
	
	//
	// GET SETTINGS
	//
	public function getStatus($id = null, $lang_id = null, $category_name = null, $filter_data = array()) {
		$sql = $this->db->select(
				array('status.id', 'id'),
				array('status.table_status_name', 'table_status_name'),
				array('status.status_id', 'status_id'),
				array('status.name', 'name'),
				array('status.description', 'description'),
				array('status.value', 'value'),
				array('status.order_index', 'order_index'),
				array('status.user_id', 'user_id'),
				
				array('status.user_datetime', 'user_datetime'),
				array('CONCAT("users.first_name",\' \',"users.last_name")', 'user_full_name'),
				
				array('IFNULL("status_contents.id", \'new\')', 'l_id'),
				array('languages.id', 'l_language_id'),
				array('status_contents.name', 'l_name') )
				
			->from('status')
			->join('languages', 'left')
				->on('languages.status_id', '>=', DB::expr('5'))
			->join('status_contents', 'left')
				->on('status.id', '=', 'status_contents.status_id')
				->on('status_contents.language_id', '=', 'languages.id')
			->join('users', 'left')
				->on('status.user_id', '=', 'users.id')
			->order_by('status.order_index')
			->order_by('status.name');				
		
		// FILTER
		if (!is_null($id)) $sql->where('status.id', '=', $id);
		else $sql->where('status.table_status_name', '=', $category_name);
		if (!empty($filter_data['status_id'])) $sql->where('status.status_id', '=', $filter_data['status_id']);
	
		return CMS::langArray($sql->execute()->as_array());
	}

	public function getTableStatusName() {
		return $this->db->select(array('DISTINCT "status.table_status_name"', 'value'))
					->from('status')
					->order_by('status.table_status_name')
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
			$tmp_data = $this->getStatus(null, null, $data['table_status_name'], array('status_id' => $data['status_id']));
			if (count($tmp_data) > 0) return __('Status already exists!');
				
			// INSERT
			$db_data = $this->db->insert('status', array(
					'table_status_name',
					'status_id',
					'name',
					'description',
					'value',
					'order_index',
					'user_id',
					'user_datetime'))
				->values(array(
					$data['table_status_name'],
					$data['status_id'],
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
			$this->db->update('status')
				->set(array(
					'description' => $data['description'],
					'value' => $data['value'],
					'order_index' => $data['order_index'],				
					'user_id' => $this->user_id,
					'user_datetime' => DB::expr('NOW()') ))
				->where('status.id', '=', $id)
				->execute();
		}

		// UPDATE LANGS
		if (!empty($data['content_id'])) {
			for ($i=0; $i<count($data['content_id']); $i++) {
				if (!is_numeric($data['content_id'][$i])) {
					// INSERT
					$this->db->insert('status_contents', array(
							'status_id',
							'language_id',
							'name' ))
						->values(array(
							$id,
							$data['language_id'][$i],
							$data['content_name'][$i] ))
						->execute();
				} else {
					// UPDATE
					$this->db->update('status_contents')
						->set(array('name' => $data['content_name'][$i]))
						->where('status_contents.id', '=', $data['content_id'][$i])
						->where('status_contents.language_id', '=', $data['language_id'][$i])
						->where('status_contents.status_id', '=', $id)
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
		// DELETE CONTENTS
		$this->db->delete('status_contents')
			->where('status_contents.status_id', '=', $id)
			->execute();
			
		// DELETE TYPE 
		$this->db->delete('status')
			->where('status.id', '=', $id)
			->execute();
	}
}