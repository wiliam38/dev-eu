<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Plugins extends Model {	
	
	public function getPlugins($id = null) {
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND plugins.id = :id ";
		
		$sql = "SELECT
					plugins.id				AS id,
					plugins.name			AS name,
					plugins.model			AS model,
					plugins.template		AS template,
					plugins.parameters		AS parameters,
					plugins.type_id			AS type_id,
					types.name				AS type_name,
					CONCAT(users.first_name,' ',users.last_name)			AS user_full_name				
				FROM
					plugins
					LEFT JOIN types ON
						types.type_id = plugins.type_id AND
						types.table_type_name = 'plugins_type_id'
					LEFT JOIN users ON
						plugins.user_id = users.id
				WHERE
					1 = 1
					".$filter."
				ORDER BY
					plugins.name";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		
		return $result->execute()->as_array();
	}
	
	public function getNewPlugins() {
		$sql = "SELECT
					'new'							AS id,
					''								AS name,
					''								AS model,
					'plugins/'						AS template,
					'parent_list=0|id_list=9'		AS parameters,
					1								AS type_id,
					''								AS type_name,
					''								AS user_full_name				";
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
		if (!is_numeric($id)) {
			// INSERT
			$db_data = $this->db->insert('plugins', array(
					'name',
					'model',
					'template',
					'parameters',
					'user_id',
					'user_datetime'))
				->values(array(
					$data['name'],
					$data['model'],
					$data['template'],
					preg_replace('/[\n\r\t]/', '|', $data['parameters']),
					$this->user_id,
					DB::expr('NOW()')))
				->execute();				
			$id = $db_data[0];
		} else {
			$db_data = $this->db->update('plugins')
				->set(array(
					'name' => $data['name'],
					'model' => $data['model'],
					'template' => $data['template'],
					'parameters' => preg_replace('/[\n\r\t]/', '|', $data['parameters']),
					'user_id' => $this->user_id,
					'user_datetime' => DB::expr('NOW()')))
				->where('plugins.id', '=', $id)
				->execute();
		}
		
		// GENERATE FILES
		$this->generate_files();
			
		return $id;
	}
	
	public function delete($id) {
		$sql = "DELETE FROM plugins
				WHERE plugins.id = :id ";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':id', $id);
		$data = $result->execute();

		// GENERATE FILES
		$this->generate_files();
		
		if ($data > 0) {
			return "deleted";
		}
	}
	
	public function generate_files() {
		// LANG FILES
		$plugins_file = "<?php defined('SYSPATH') or die('No direct script access.'); \r\nreturn array \r\n( \r\n";
		
		// GET LEXICONS
		$data = $this->getPlugins();
		
		for ($i=0; $i<count($data); $i++) {
			$tmp_param = explode('|', $data[$i]['parameters']);
			$param =  'array ( ';
			if (count($tmp_param) > 0) {
				for ($j=0; $j < count($tmp_param); $j++) {
					$tmp_param_val = explode('=', $tmp_param[$j]);
					if (!empty($tmp_param_val[0])) {
						if (!isset($tmp_param_val[1])) $tmp_param_val[1] = '';
						$param .= "
						'".str_replace("'", "\'", $tmp_param_val[0])."' => '".str_replace("'", "\'", $tmp_param_val[1])."', ";
					}
				}
			}
			$param .= ') ';
			
			$plugins_file .= "    '".str_replace("'", "\'", $data[$i]['name'])."' => array(
				'name' => '".str_replace("'", "\'", $data[$i]['name'])."',
				'model' => '".str_replace("'", "\'", $data[$i]['model'])."',
				'template' => '".str_replace("'", "\'", $data[$i]['template'])."',
				'parameters' => ".$param." ), \r\n";
		}		
		
		// LANG FILES
		$plugins_file .= "); ";
		
		if (!file_exists(APPPATH.'i18n/plugins/')) mkdir(APPPATH.'i18n/plugins/');
		$filename = APPPATH.'i18n/plugins/site.php';
			
		// CREATE FILES
		if (file_exists($filename)) unlink($filename);
		file_put_contents($filename, $plugins_file);
	}
}