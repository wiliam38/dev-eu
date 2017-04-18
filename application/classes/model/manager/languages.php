<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Languages extends Model {	
	
	//
	// GET LANGUAGES
	//
	public function getLanguages($id = null, $tag = null, $from_status_id = null) {
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND languages.id = :id ";
		if (!is_null($tag)) $filter .= " AND languages.tag = :tag ";
		
		$sql = "SELECT
					languages.id				AS id,
					languages.name				AS name,
					languages.ticker			AS ticker,
					languages.tag				AS tag,
					languages.img_src			AS img_src,
					languages.order_index		AS order_index,
					
					languages.user_id			AS user_id,
					CONCAT(users.first_name,' ',users.last_name)				AS user_full_name,
					languages.user_datetime		AS user_datetime,	

					languages.status_id			AS status_id,
					status.name					AS status_name,
					status.description			AS status_description,
					
					IF (languages.id = settings.value, 1, 0)	AS `default`
				FROM
					languages
					LEFT JOIN users ON
						languages.user_id = users.id
					LEFT JOIN status ON
						languages.status_id = status.status_id AND
						status.table_status_name = 'languages_status_id'
					LEFT JOIN settings ON
						settings.name = 'default.lang_id'
				WHERE
					1 = 1
					".$filter."
				ORDER BY
					languages.order_index";			
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		$result->bind(':tag', $tag);
		
		return $result->execute()->as_array();
	}
	
	//
	// NEW LANGUAGE
	//
	public function getNewLanguages() {
		$sql = "SELECT
					'new'						AS id,
					''							AS name,
					''							AS ticker,
					''							AS tag,
					''							AS img_src,
					''							AS order_index,
					
					''							AS user_id,
					''							AS user_full_name,
					''							AS user_datetime,	

					10							AS status_id,
					''							AS status_name,
					
					0							AS `default`";
		$result = $this->db->query(Database::SELECT, $sql);
		
		return $result->execute()->as_array();
	}
	
	//
	//
	//
	// JOBS
	//
	//
	//
	
	public function save($id, $data) {
	    // VALIDATE
	    if ($data['order_index'] == '') $data['order_index'] = '0';
	    
		if (!is_numeric($id)) {
			// INSERT
			$db_data = $this->db->insert('languages', array(
					'name',
					'ticker',
					'tag',
					'order_index',
					'status_id',
					'img_src',
					'user_id',
					'user_datetime'))
				->values(array(
					$data['name'],
					$data['ticker'],
					$data['tag'],
					$data['order_index'],
					$data['status_id'],
					$data['img_src'],
					$this->user_id,
					DB::expr('NOW()')))
				->execute();
			$id = $db_data[0];
		} else {
			$db_data = $this->db->update('languages')
				->set(array(
					'name' => $data['name'],
					'ticker' => $data['ticker'],
					'tag' => $data['tag'],
					'order_index' => $data['order_index'],
					'status_id' => $data['status_id'],
					'img_src' => $data['img_src'],
					'user_id' => $this->user_id,
					'user_datetime' => DB::expr('NOW()') ))
				->where('languages.id', '=', $id)
				->execute();
		}		
		
		return $id;
	}
	public function delete($id) {
		$sql = "DELETE FROM languages 
				WHERE 
					languages.id = :id AND
					languages.id NOT IN (	SELECT settings.value
										 	FROM settings
										 	WHERE settings.name = 'default.lang_id' )";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':id', $id);
		$data = $result->execute();	
		
		if ($data > 0) {
			$sql = "DELETE FROM page_contents 
					WHERE page_contents.language_id = :id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':id', $id);
			$data = $result->execute();	
			
			return "deleted";
		}
	}
 
}