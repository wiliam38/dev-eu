<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Products_Mfrs extends Model {
	public function __construct() {
		parent::__construct();
		
		if (empty($content_type_id)) Kohana::error_handler(true, 'NOT SET content_type_id');
		else $this->content_type_id = $content_type_id;
		
		$this->files = Model::factory('manager_files');
	}
	
	public function getMfrs($id = null, $lang_id = null, $filter_data = array()) {
		// FILTER
		$lang_filter = ' ';
		if (!is_null($lang_id)) $lang_filter = ' AND mfr_contents.language_id = :lang_id '; 
		
		$filter = " ";
		if (!is_null($id)) $filter .= " AND mfrs.id = :id ";
		
		if (isset($filter_data['from_status_id'])) $filter .= " AND mfrs.status_id >= :from_status_id ";
		
		if (!is_null($id) AND ($id == 'new' OR $id == '')) {
			// NEW PRODUCT
			$sql = "SELECT
						'new'												AS id,
						''													AS name,
						1													AS status_id ";
		} else {
			// GET DATA	
			$sql = "SELECT
						mfrs.id							AS id,
						mfrs.name						AS name,
						mfrs.vat						AS vat,
						mfrs.address					AS address,
						mfrs.city_id					AS city_id,
						cities.city						AS city_name,
						mfrs.web						AS web,
						mfrs.email						AS email,
						mfrs.phone						AS phone,
						mfrs.fax						AS fax,
						mfrs.logo_src					AS logo_src,
						mfrs.image_src					AS image_src,

						mfrs.status_id					AS status_id,
						status.name						AS status_name,
						status.description				AS status_description,
						
						mfr_contents.id					AS l_id,
						mfr_contents.language_id		AS l_language_id,
						mfr_contents.title				AS l_title,
						mfr_contents.description		AS l_description,
						mfr_contents.content			AS l_content
					FROM
						mfrs
						LEFT JOIN mfr_contents ON
							mfrs.id = mfr_contents.mfr_id
							".$lang_filter."
						LEFT JOIN status ON
							mfrs.status_id = status.status_id AND
							status.table_status_name = 'mfrs_status_id'
						LEFT JOIN cities ON
							mfrs.city_id = cities.id
					WHERE
						1 = 1
						".$filter."
					ORDER BY
						mfrs.name ";
		}
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':id', $id);
		$res->bind(':lang_id', $lang_id);
		
		if (isset($filter_data['from_status_id'])) $res->bind(':from_status_id', $filter_data['from_status_id']);
		
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) {
			$this->resources = Model::factory('manager_resources');
			$db_data = $this->resources->langArray($db_data);
		}	
		
		return $db_data;
	}
	
	public function save($data) {
		$this->resources = Model::factory('manager_resources');
		
		if ($data['mfr_id'] == 'new') {
			// INSERT
			$sql = "INSERT INTO mfrs (
						name,
						vat,
						address,
						city_id,
						web,
						email,
						phone,
						fax,
						status_id,
						user_id,
						datetime,
						creation_user_id,
						creation_datetime	)
					VALUES (
						:name,
						:vat,
						:address,
						:city_id,
						:web,
						:email,
						:phone,
						:fax,
						:status_id,
						:user_id,
						NOW(),
						:user_id,
						NOW() )";
			$result = $this->db->query(Database::INSERT, $sql);
		} else {
			$sql = "UPDATE
						mfrs
					SET
						name = :name,
						vat = :vat,
						address = :address,
						city_id = :city_id,
						web = :web,
						email = :email,
						phone = :phone,
						fax = :fax,
						status_id = :status_id,
						user_id = :user_id,
						datetime = NOW()
					WHERE
						mfrs.id = :mfr_id ";
			$result = $this->db->query(Database::UPDATE, $sql);
		}
		$result->bind(':mfr_id', $data['mfr_id']);
		$result->bind(':name', $data['name']);
		$result->bind(':vat', $data['vat']);
		$result->bind(':address', $data['address']);
		$result->bind(':city_id', $data['city_id']);
		$result->bind(':web', $data['web']);
		$result->bind(':email', $data['email']);
		$result->bind(':phone', $data['phone']);
		$result->bind(':fax', $data['fax']);
		$result->bind(':status_id', $data['status_id']);
		$result->bind(':user_id', $this->user_id);
		
		$db_data = $result->execute();	
		$mfr_id = $data['mfr_id'];
		if ($mfr_id == 'new') {
			$mfr_id = $db_data[0];
		}
		
		//
		// UPDATE IMAGES
		//
		$mfr = $this->getMfrs($mfr_id);
		$logo_src = $this->files->update_image2('files/mfrs/'.$mfr_id.'/', $data['logo_src'], $mfr[0]['logo_src']);
		$image_src = $this->files->update_image2('files/mfrs/'.$mfr_id.'/', $data['image_src'], $mfr[0]['image_src']);
		
		$sql = "UPDATE
					mfrs
				SET
					logo_src = :logo_src,
					image_src = :image_src
				WHERE
					mfrs.id = :mfr_id ";
		$result = $this->db->query(Database::UPDATE, $sql);		
		$result->bind(':logo_src', $logo_src);		
		$result->bind(':image_src', $image_src);
		$result->bind(':mfr_id', $mfr_id);
		$db_data = $result->execute();					
			
		//
		// LOOP MFR CONTENTS
		//
		
		// UPDATE LANGUAGES
		$lang = CMS::getLanguages(null, null, 5);
		
		for ($i=0; $i<count($lang); $i++) {
			if (isset($data[$lang[$i]['id'].'_mfr_content_id']) AND $data[$lang[$i]['id'].'_mfr_content_id'] != 'none') {
				if ($data[$lang[$i]['id'].'_mfr_content_id'] == 'new') {
					// INSERT
					$sql = "INSERT INTO mfr_contents (
								mfr_id,
								language_id,
								title,
								description,
								content )
							VALUES (
								:mfr_id,
								:language_id,
								:title,
								:description,
								:content ) ";
					$result = $this->db->query(Database::INSERT, $sql);
				} else {
					// UPDATE
					$sql = "UPDATE
								mfr_contents
							SET
								title = :title,
								description = :description,
								content = :content
							WHERE
								mfr_contents.id = :mfr_content_id ";
					$result = $this->db->query(Database::UPDATE, $sql);
				}					
				$result->bind(':mfr_content_id', $data[$lang[$i]['id'].'_mfr_content_id']);
				$result->bind(':mfr_id', $mfr_id);
				$result->bind(':language_id', $lang[$i]['id']);
				$result->bind(':title', $data[$lang[$i]['id'].'_title']);			
				$result->bind(':description', $data[$lang[$i]['id'].'_description']);
				$result->bind(':content', $data[$lang[$i]['id'].'_content']);
					
				$db_data = $result->execute();
			}
		}
	}

	public function delete($data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($data['mfr_id'])) {
			// CHECK FOR ACTIVE ORDERS
			
			// DELETE IMAGES
			$mfrs = $this->getMfrs($data['mfr_id']);
			if (!empty($mfrs[0]['logo_src'])) $this->files->deleteFile($mfrs[0]['logo_src']);
			if (!empty($mfrs[0]['image_src'])) $this->files->deleteFile($mfrs[0]['image_src']);	
			
			// DELETE CONTENTS
			$sql = "DELETE FROM mfr_contents
					WHERE mfr_contents.mfr_id = :mfr_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':mfr_id', $data['mfr_id']);
			$db_data = $result->execute();
			
			$sql = "DELETE FROM mfrs
					WHERE mfrs.id = :mfr_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':mfr_id', $data['mfr_id']);
			$db_data = $result->execute();
			
			$status = array(	'status' => '1',
								'error' => '',
								'response' => '');
		} 
		
		return $status;	
	}
}