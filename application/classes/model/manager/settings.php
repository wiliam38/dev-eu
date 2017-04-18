<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Settings extends Model {	
	
	//
	// GET SETTINGS
	//
	public function getSettings($name = null, $lang_id = null, $id = null, $category_name = null) {
		$sql = $this->db->select(
				array('settings.id', 'id'),
				array('settings.name', 'name'),
				array('settings.description', 'description'),
				array('settings.value', 'value'),
				array('settings.user_id', 'user_id'),
				
				array('settings.user_datetime', 'user_datetime'),
				array('CONCAT("users.first_name",\' \',"users.last_name")', 'user_full_name'),
				
				array('settings.type_id', 'type_id'),
				array('types.name', 'type_name'),
				
				array('IFNULL("lang_settings.id", \'new\')', 'l_id'),
				array('languages.id', 'l_language_id'),
				array('lang_settings.value', 'l_value') )
				
			->from('settings')
			->join('languages', 'left')
				->on('languages.status_id', '>=', DB::expr('5'))
			->join(array('settings', 'lang_settings'), 'left')
				->on('settings.id', '=', 'lang_settings.parent_id')
				->on('lang_settings.language_id', '=', 'languages.id')
			->join('users', 'left')
				->on('settings.user_id', '=', 'users.id')
			->join('types', 'left')
				 ->on('settings.type_id', '=', 'types.type_id')
				 ->on('types.table_type_name', '=', DB::expr('\'settings_type_id\''))
				 
			->where('IFNULL("settings.parent_id",0)', '=', 0)
			
			->order_by('settings.name');				
		
		// FILTER
		if (!is_null($id)) $sql->where('settings.id', '=', $id);
		if(!is_null($category_name) AND trim($category_name) != '') $sql->where('settings.name', 'LIKE', $category_name.'%');
	
		return CMS::langArray($sql->execute()->as_array());
	}
	
	public function getNewSettings() {
		$sql = "SELECT
					'new'		 				AS id,
					''							AS name,
					''							AS description,
					''							AS value,
					''							AS user_id,
					
					''							AS user_datetime,
					''							AS user_full_name,

					types.type_id				AS type_id,
					types.name					AS type_name
				FROM
					types
				WHERE
					types.type_id = 2 AND
					types.table_type_name = 'settings_type_id'";
		$result = $this->db->query(Database::SELECT, $sql);
		
		$data = $result->execute()->as_array();
		
		// GET LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		
		for ($j=0; $j<count($data); $j++) {
			for ($i=0; $i<count($lang); $i++) {
				$data[$j]['lang'][$lang[$i]['id']]['id'] = 'new';
				$data[$j]['lang'][$lang[$i]['id']]['value'] = '';
			}
		}
		
		return $data;
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
			// INSERT
			$db_data = $this->db->insert('settings', array(
					'parent_id',
					'name',
					'description',
					'value',
					'type_id',
					'user_id',
					'user_datetime'))
				->values(array(
					DB::expr('0'),
					$data['def_name'],
					$data['def_description'],
					$data['def_value'],
					DB::expr('2'),
					$this->user_id,
					DB::expr('NOW()')))
				->execute();
			$id = $db_data[0];
		} else {
			// UPDATE
			$update_data = array(
				'value' => $data['def_value'],
				'user_id' => $this->user_id,
				'user_datetime' => DB::expr('NOW()') );
			if (isset($data['def_name']) && isset($data['def_description'])) {
				$update_data['name'] = $data['def_name'];
				$update_data['description'] = $data['def_description'];
			}
			$this->db->update('settings')
				->set($update_data)
				->where('settings.id', '=', $id)
				->execute();
		}

		// UPDATE LANGS
		$lang_data = isset($data['lang_data'])?$data['lang_data']:null;
			
		if (!empty($lang_data)) {
			foreach($lang_data as $key => $l_data) {
				if (empty($l_data[1]) || $l_data[1] == 'new') {
					// INSERT
					$this->db->insert('settings', array(
							'value',
							'parent_id',
							'language_id',
							'user_id',
							'user_datetime' ))
						->values(array(
							$l_data[2],
							$id,
							$l_data[0],
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
				} else {
					// UPDATE
					$this->db->update('settings')
						->set(array(
							'value' => $l_data[2],
							'user_id' => $this->user_id,
							'user_datetime' => DB::expr('NOW()')))
						->where('settings.id', '=', $l_data[1])
						->execute();
				}
			}
		}
		
		// GENERATE FILES
		$this->generate_files();
			
		return $id;
	}
	
	//
	// DELETE
	//
	public function delete($id) {
		$sql = "DELETE FROM settings 
				WHERE 
					settings.id = :id AND
					settings.type_id = 2 ";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':id', $id);
		$data = $result->execute();
		
		if ($data > 0) {
			// DELETE SUB DATA
			$sql = "DELETE FROM settings 
					WHERE settings.parent_id = :id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':id', $id);
			$sub_data = $result->execute();
		}
		
		// GENERATE FILES
		$this->generate_files();
			
		if ($data > 0) return "deleted";
	}
	
	//
	// SET SETTINGS
	//
	public function set($name, $value, $lang_id = null) {
		$sql = "SELECT
					settings.id					AS id,
					lang_settings.id			AS lang_id
				FROM
					settings
					LEFT JOIN settings lang_settings ON
						settings.id = lang_settings.parent_id AND
						lang_settings.language_id = :lang_id
				WHERE
					settings.name = :name
				LIMIT 1";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':lang_id', $lang_id);
		$result->bind(':name', $name);
		$data = $result->execute()->as_array();
		
		// CREATE SETTING
		if (count($data) == 0) {
			// INSERT
			$sql = "INSERT INTO settings (
						parent_id,
						name,
						description,
						value,
						type_id,
						user_id,
						user_datetime )
					VALUES (
						0,
						:name,
						:name,
						'',
						2,
						:user_id,
						NOW() )";
			$result = $this->db->query(Database::INSERT, $sql);
			$result->bind(':name', $name);
			$result->bind(':user_id', $this->user_id);
			$sub_data = $result->execute();
			
			$parent_id = $sub_data[0];
		} else {
			$parent_id = $data[0]['id'];
		}
		
		if (empty($lang_id)) {
			// UPDATE PARENT
			$sql = "UPDATE
						settings
					SET
						value = :value,
						user_id = :user_id,
						user_datetime = NOW()
					WHERE
						settings.id = :id ";
			$result = $this->db->query(Database::UPDATE, $sql);
			$result->bind(':value', $value);
			$result->bind(':id', $parent_id);
			$result->bind(':user_id', $this->user_id);
			$sub_data = $result->execute();
		} else {
			// UPDATE LANG 
			if (empty($data[0]['lang_id'])) {
				// INSERT
				$sql = "INSERT INTO settings (
							value,
							parent_id,
							language_id,
							user_id,
							user_datetime )
						VALUES (
							:value,
							:parent_id,
							:language_id,
							:user_id,
							NOW() ) ";
				$result = $this->db->query(Database::INSERT, $sql);
			} else {
				// UPDATE
				$sql = "UPDATE
							settings
						SET
							value = :value,
							user_id = :user_id,
							user_datetime = NOW()
						WHERE
							settings.id = :id ";
				$result = $this->db->query(Database::UPDATE, $sql);
			}
			$result->bind(':id', $data[0]['lang_id']);
			$result->bind(':value', $value);
			$result->bind(':parent_id', $parent_id);
			$result->bind(':language_id', $lang_id);
			$result->bind(':user_id', $this->user_id);
		
			$r_data = $result->execute();
		}
		
		// GENERATE FILES
		$this->generate_files();
	}

	public function generate_files() {
		// GET LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		
		// LANG FILES
		$lang_files = array();
		$lang_files['default'] = "<?php defined('SYSPATH') or die('No direct script access.'); \r\nreturn array \r\n( \r\n";
		for ($i=0; $i<count($lang); $i++) {
			$lang_files[$lang[$i]['tag']] = "<?php defined('SYSPATH') or die('No direct script access.'); \r\nreturn array \r\n( \r\n";
		}
		
		// GET LEXICONS
		$data = $this->getSettings();
		
		for ($i=0; $i<count($data); $i++) {
			$lang_files['default'] .= "    '".str_replace("'", "\'", $data[$i]['name'])."' => '".str_replace("'", "\'", $data[$i]['value'])."', \r\n";
			for ($j=0; $j<count($lang); $j++) {
				$lang_files[$lang[$j]['tag']] .= "    '".str_replace("'", "\'", $data[$i]['name'])."' => '".str_replace("'", "\'", $data[$i]['lang'][ $lang[$j]['id'] ]['value'])."', \r\n";
			}
		}		
		
		// LANG FILES
		$lang_files['default'] .= "); ";
		for ($i=0; $i<count($lang); $i++) {
			$lang_files[$lang[$i]['tag']] .= "); ";
		}
		
		foreach ($lang_files as $key => $file_content) {
			if (!file_exists(APPPATH.'i18n/settings/')) mkdir(APPPATH.'i18n/settings/');
			$filename = APPPATH.'i18n/settings/'.$key.'.php';
			
			// CREATE FILES
			if (file_exists($filename)) unlink($filename);
			file_put_contents($filename, $file_content);
		}
	}
 
}