<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Lexicons extends Model {	
	
	//
	// GET LEXICONS
	//
	public function getLexicons($id = null, $filter_data = array(), $limit = null, $offset = null, $order_by = '"lexicons.name" ASC') {
		$sql = $this->db->select()
			->from('lexicons')
			->where('IFNULL("lexicons.parent_id",0)', '=', '0');
			
		// FILTER
		if(!is_null($id)) $sql->where('lexicons.id', '=', $id);
		if(!empty($filter_data['category_name'])) $sql->where('lexicons.name', 'LIKE', $filter_data['category_name'].'%');
		if(!empty($filter_data['search'])) {
			$filter_sql = $this->db->select('lexicons.id')
				->from('lexicons')
				->join('languages', 'LEFT')
					->on('languages.status_id', '>=', DB::expr('5'))
				->join(array('lexicons', 'lang_lexicons'), 'LEFT')	
					->on('lexicons.id', '=', 'lang_lexicons.parent_id')
					->on('lang_lexicons.language_id', '=', 'languages.id')
				->where('IFNULL("lexicons.parent_id",0)', '=', '0');
				
				$search_data = explode(' ', $filter_data['search']);
				for ($i=0; $i<count($search_data); $i++) $filter_sql->where('CONCAT(IFNULL("lexicons.name",\'\'), \' \', IFNULL("lang_lexicons.value",\'\'))', 'LIKE', '%'.$search_data[$i].'%');
			
			$sql->where('lexicons.id', 'IN', $filter_sql);
		}
		
		if (isset($filter_data['count'])) {
			$sql->select(
					array(DB::expr('COUNT(*)'), 'count'));
			$db_data = $sql->execute()->as_array();
			$db_data = $db_data[0]['count'];
		} else {
			// LIMIT
			if(!is_null($limit)) {
				// SELECT
				$sql->select(array('lexicons.id', 'id'));
									
				// LIMIT
				$sql->limit($limit);
				if (!is_null($offset) && $offset > 0) $sql->offset($offset);
				
				// DATA
				$db_data = $sql->execute()->as_array();		
							
				$id_list = array();
				foreach($db_data as $key => $val) $id_list[] = $val['id'];
				$sql->where('lexicons.id', 'IN', !empty($id_list)?$id_list:array(-1));
				
				$sql->limit(NULL);
				$sql->offset(NULL);			 
			}		
			
			$sql->select(
					array('lexicons.id', 'id'),
					array('lexicons.name', 'name'),
					array('lexicons.type_id', 'type_id'),
					array('types.name', 'type_name'),
					array('lexicons.user_datetime', 'user_datetime'),
					
					array('IFNULL("lang_lexicons.id", \'new\')', 'l_id'),
					array('languages.id', 'l_language_id'),
					array('lang_lexicons.value', 'l_name') )
				->join('languages', 'LEFT')
					->on('languages.status_id', '>=', DB::expr('5'))
				->join(array('lexicons', 'lang_lexicons'), 'LEFT')	
					->on('lexicons.id', '=', 'lang_lexicons.parent_id')
					->on('lang_lexicons.language_id', '=', 'languages.id')
				->join('types', 'LEFT')		
					->on('lexicons.type_id', '=', 'types.type_id')
					->on('types.table_type_name', '=', DB::expr("'lexicons_type_id'"))
				->order_by($order_by);
			
			$db_data = CMS::langArray($sql->execute()->as_array());
		}
		
		return $db_data;
	}
	public function getNewLexicons() {
		$sql = $this->db->select(
				array(DB::expr('\'new\''), 'id'),
				array(DB::expr('\'\''), 'name'),
				array('types.type_id', 'type_id'),
				array('types.name', 'type_name'),
				array(DB::expr('NOW()'), 'user_datetime') )
			->from('types')
			->where('types.type_id', '=', '1')
			->where('types.table_type_name', '=', DB::expr('\'lexicons_type_id\''));		
		$data = $sql->execute()->as_array();
		
		// GET LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		
		for ($j=0; $j<count($data); $j++) {		
			for ($i=0; $i<count($lang); $i++) {	
				$data[$j]['lang'][$lang[$i]['id']]['id'] = 'new';
				$data[$j]['lang'][$lang[$i]['id']]['name'] = '';
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
	
	public function save($post_data) {
		if ($post_data['id'] == 'new') {
			// INSERT
			$sql = "INSERT INTO lexicons (
						name,
						parent_id,
						type_id,
						user_id,
						user_datetime )
					VALUES (
						:name,
						0,
						1,
						:user_id,
						NOW() )";
			$result = $this->db->query(Database::INSERT, $sql);
		} else {
			$sql = "UPDATE
						lexicons
					SET
						name = :name,
						user_id = :user_id,
						user_datetime = NOW()
					WHERE
						lexicons.type_id = 1 AND
						lexicons.id = :id ";
			$result = $this->db->query(Database::UPDATE, $sql);
		}
		
		$result->bind(':id', $post_data['id']);
		$result->bind(':name', $post_data['system_name']);
		$result->bind(':user_id', $this->user_id);
		
		
		$data = $result->execute();	
		if ($post_data['id'] == 'new') {
			$parent_id = $data[0];
		} else {
			$parent_id = $post_data['id'];
		}
		
		// UPDATE LANGS
		for($i=0; $i<count($post_data['lexicon_id']); $i++) {
			if (empty($post_data['lexicon_id'][$i]) || $post_data['lexicon_id'][$i] == 'new') {
				// INSERT
				$sql = "INSERT INTO lexicons (
							value,
							parent_id,
							language_id,
							user_id,
							user_datetime )
						VALUES (
							:name,
							:parent_id,
							:language_id,
							:user_id,
							NOW() ) ";
				$result = $this->db->query(Database::INSERT, $sql);
			} else {
				// UPDATE
				$sql = "UPDATE
							lexicons
						SET
							value = :name,
							user_id = :user_id,
							user_datetime = NOW()
						WHERE
							lexicons.id = :id ";
				$result = $this->db->query(Database::UPDATE, $sql);
			}
			$result->bind(':id', $post_data['lexicon_id'][$i]);
			$result->bind(':name', $post_data['name'][$i]);
			$result->bind(':parent_id', $parent_id);
			$result->bind(':language_id', $post_data['lang_id'][$i]);
			$result->bind(':user_id', $this->user_id);
			
			$r_data = $result->execute();	
		}
		
		// GENERATE FILES
		$this->generate_files();
		
		return $parent_id;
	}

	public function delete($id) {
		$sql = "DELETE FROM lexicons
				WHERE 
					lexicons.type_id = 1 AND
					lexicons.id = :id ";
		$result = $this->db->query(Database::DELETE, $sql);
		$result->bind(':id', $id);
		$data = $result->execute();

		if ($data > 0) {
			// DELETE SUB DATA
			$sql = "DELETE FROM lexicons
					WHERE lexicons.parent_id = :id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':id', $id);
			$data = $result->execute();
			
			// GENERATE FILES
			$this->generate_files();
			
			return "deleted";
		}
	}
	
	public function generate_files() {
		// GET LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		
		// LANG FILES
		$lang_files = array();
		for ($i=0; $i<count($lang); $i++) {
			$lang_files[$lang[$i]['tag']] = "<?php defined('SYSPATH') or die('No direct script access.'); \r\nreturn array \r\n( \r\n";
		}
		
		// GET LEXICONS
		$data = $this->getLexicons();
		
		for ($i=0; $i<count($data); $i++) {
			for ($j=0; $j<count($lang); $j++) {
				$lang_files[$lang[$j]['tag']] .= "    '".str_replace("'", "\'", $data[$i]['name'])."' => '".str_replace("'", "\'", $data[$i]['lang'][ $lang[$j]['id'] ]['name'])."', \r\n";
			}
		}		
		
		// LANG FILES
		for ($i=0; $i<count($lang); $i++) {
			$lang_files[$lang[$i]['tag']] .= "); ";
		}
		
		for ($i=0; $i<count($lang); $i++) {
			if (!file_exists(APPPATH.'i18n/site/')) mkdir(APPPATH.'i18n/site/');
			$filename = APPPATH.'i18n/site/'.$lang[$i]['tag'].'.php';
			
			// CREATE FILES
			if (file_exists($filename)) unlink($filename);
			file_put_contents($filename, $lang_files[$lang[$i]['tag']]);
		}
	}

	// CATEGORIES
	public function getCategories() {
		$sql = $this->db->select(
				array('DISTINCT SUBSTRING("lexicons.name",1,LOCATE(\'.\',"lexicons.name")-1)', 'value') )
			->from('lexicons')
			->where('IFNULL("lexicons.parent_id",0)', '=', 0)
			->order_by('SUBSTRING("lexicons.name",1,LOCATE(\'.\',"lexicons.name")-1)');
		return $sql->execute()->as_array();
	}
}