<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Templates extends Model {	
	
	//
	// GET TEMPLATES
	//
	public function getTemplates($id = null) {		
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND templates.id = :id ";
		
		$sql = "SELECT
					templates.id					AS id,
					templates.name					AS name,
					templates.tpl_name				AS tpl_name,
					templates.conf_title_image		AS conf_title_image,
					templates.conf_introtext		AS conf_introtext,
					templates.conf_image			AS conf_image,
					templates.conf_menu_image		AS conf_menu_image,
					templates.conf_seo				AS conf_seo,
					templates.conf_target			AS conf_target,
					templates.conf_gallery			AS conf_gallery,
					templates.type_id				AS type_id,
					types.name						AS type_name,
					CONCAT(users.first_name,' ',users.last_name)			AS user_full_name					
				FROM
					templates
					LEFT JOIN types ON
						types.type_id = templates.type_id AND
						types.table_type_name = 'templates_type_id'
					LEFT JOIN users ON
						templates.user_id = users.id
				WHERE
					1 = 1
					".$filter."
				ORDER BY
					templates.name";			
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		
		return $result->execute()->as_array();
	}
	
	public function getNewTemplates() {
		$sql = "SELECT
					'new'					AS id,
					''						AS name,
					'site/template/'		AS tpl_name,
					1						AS type_id,
					''						AS type_name,
					''						AS user_full_name				";
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
	
	public function delete($id) {
		$sql = "DELETE FROM templates
				WHERE templates.id = :id ";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':id', $id);
		$data = $result->execute();

		if ($data > 0) {
			return "deleted";
		}
	}
	
	public function save($id, $data) {
		if (!is_numeric($id)) {
			// INSERT
			$db_data = $this->db->insert('templates', array(
					'name',
					'tpl_name',
					'conf_title_image',
					'conf_introtext',
					'conf_image',
					'conf_menu_image',
					'conf_seo',
					'conf_target',
					'conf_gallery',
					'user_id',
					'user_datetime' ))
				->values(array(
					$data['name'],
					$data['tpl_name'],
					!empty($data['conf_title_image'])?1:0,
					!empty($data['conf_introtext'])?1:0,
					!empty($data['conf_image'])?1:0,
					!empty($data['conf_menu_image'])?1:0,
					!empty($data['conf_seo'])?1:0,
					!empty($data['conf_target'])?1:0,
					!empty($data['conf_gallery'])?1:0,
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
			$id = $db_data[0];
		} else {
			// UPDATE
			$this->db->update('templates')
				->set(array(
					'name' => $data['name'],
					'tpl_name' => $data['tpl_name'],
					'conf_title_image' => !empty($data['conf_title_image'])?1:0,
					'conf_introtext' => !empty($data['conf_introtext'])?1:0,
					'conf_image' => !empty($data['conf_image'])?1:0,
					'conf_menu_image' => !empty($data['conf_menu_image'])?1:0,
					'conf_seo' => !empty($data['conf_seo'])?1:0,
					'conf_target' => !empty($data['conf_target'])?1:0,
					'conf_gallery' => !empty($data['conf_gallery'])?1:0,
					'user_id' => $this->user_id,
					'user_datetime' => DB::expr('NOW()')))
				->where('templates.id', '=', $id)
				->execute();
		}
		
		return $id;
	}
}