<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Site_Cron extends Model {
	//
	// CHECK MANUAL CRON
	//
	public function chk_manual_cron() {
		$cron_time = 10 * 60; // 10 min 
		
		// GET LAST CRON TIME
		$last_cron = $this->db->select(array('settings.value', 'value'))
			->from('settings')
			->where('settings.name', '=', 'cron.last_datetime')
			->execute()
			->as_array();
		
		if (count($last_cron) == 0) {
			// CRON SETTING IS NOT SET
			$this->db->insert('settings', array('name', 'value', 'description'))
				->values(array('cron.last_datetime', time(), 'Manual cron job last run datetime'))
				->execute();
		}
		
		if (empty($last_cron[0]['value']) || !is_numeric($last_cron[0]['value']) || $last_cron[0]['value'] < (time() - $cron_time)) {
			$this->db->update('settings')
				->set(array('value' => time()))
				->where('settings.name', '=', 'cron.last_datetime')
				->execute();
			return true;
		}		
		
		return false;
	}

 	//
	// UPDATE PAGES STATUS
	//
	public function updatePageStatus($types_array = array('1')) {
		// DISCONTINUE PAGES		
		$sql = "UPDATE
					page_contents
					JOIN pages ON
						page_contents.page_id = pages.id AND
						pages.type_id IN :types_array 
				SET
					page_contents.status_id = 5
				WHERE
					page_contents.status_id = 10 AND
					page_contents.unpub_date IS NOT NULL AND
					page_contents.unpub_date != '0000-00-00 00:00:00' AND
					page_contents.unpub_date < NOW() ";
		$result = $this->db->query(Database::UPDATE, $sql);
		$result->bind(':types_array', $types_array);
		$result->execute();		

		// PUBLISH PAGES
		$sql = "UPDATE
					page_contents
					JOIN pages ON
						page_contents.page_id = pages.id AND
						pages.type_id IN :types_array 
				SET
					page_contents.status_id = 10
				WHERE
					page_contents.status_id = 1 AND
					page_contents.pub_date IS NOT NULL AND
					page_contents.pub_date != '0000-00-00 00:00:00' AND
					page_contents.pub_date < NOW() ";
		$result = $this->db->query(Database::UPDATE, $sql);
		$result->bind(':types_array', $types_array);
		$result->execute();	

		// DRAFT PAGES
		$sql = "UPDATE
					page_contents
					JOIN pages ON
						page_contents.page_id = pages.id AND
						pages.type_id IN :types_array 
				SET
					page_contents.status_id = 1
				WHERE
					page_contents.status_id = 10 AND
					page_contents.pub_date IS NOT NULL AND
					page_contents.pub_date != '0000-00-00 00:00:00' AND
					page_contents.pub_date > NOW() ";
		$result = $this->db->query(Database::UPDATE, $sql);
		$result->bind(':types_array', $types_array);
		$result->execute();
	} 
	
	//
	// CLEAR SESSION DATA
	//
	public function clearSessionData() {
		$this->db->delete('sessions')
			->where('sessions.last_active', '<', DB::expr('UNIX_TIMESTAMP(CURRENT_DATE - INTERVAL 2 DAY)'))
			->execute();
	}
	
	//
	// CLEAR TMP DIR
	//
	public function clearTmpDir() {
		if (is_dir($this->base_path."files/tmp")) {
			$files = glob($this->base_path."files/tmp/*");
			if (!empty($files)) {
				foreach($files as $file) {
					if(is_file($file) && time() - filemtime($file) >= 2*24*60*60) {
						unlink($file);
					}
				}
			}
		}
	}
}