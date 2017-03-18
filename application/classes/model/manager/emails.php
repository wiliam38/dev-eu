<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Emails extends Model {	
	
	public function getEmails($id = null, $filter_data = array(), $limit = null, $offset = null, $order_by = ' mail_queue.creation_datetime DESC, mail_queue.to_email ') {
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND mail_queue.id = :id ";
		if (isset($filter_data['status_id_list'])) $filter .= " AND mail_queue.status_id IN :status_id_list ";
		
		// LIMIT FILTER
		$limit_filter = '';
		if (!is_null($limit)) {
			$limit_filter .= " LIMIT :limit ";
			if (!is_null($offset)) $limit_filter .= " OFFSET :offset ";
		}
		
		$sql = "SELECT
					mail_queue.id						AS id,
					mail_queue.from_email				AS from_email,
					mail_queue.from_name				AS from_name,
					mail_queue.to_email					AS to_email,
					mail_queue.reply_to_email			AS reply_to_email,
					mail_queue.subject					AS subject,
					mail_queue.body						AS body,
					mail_queue.body_type				AS body_type,
					mail_queue.attachments				AS attachments,
					mail_queue.creation_datetime		AS creation_datetime,
					mail_queue.sent_datetime			AS sent_datetime,
					mail_queue.status_id				AS status_id,
					mail_queue.status_msg				AS status_msg,
					status.name							AS status_name,
					status.description					AS status_description	
				FROM
					mail_queue
					LEFT JOIN status ON
						status.status_id = mail_queue.status_id AND
						status.table_status_name = 'mail_queue_status_id'
				WHERE
					1 = 1
					".$filter."
				ORDER BY
					".$order_by." ";
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		$result->bind(':limit', $limit);
		$result->bind(':offset', $offset);
		
		if (isset($filter_data['status_id_list'])) {
			if (!is_array($filter_data['status_id_list'])) $filter_data['status_id_list'] = explode(',', $filter_data['status_id_list']);
			$result->bind(':status_id_list', $filter_data['status_id_list']);
		}
		
		return $result->execute()->as_array();
	}
	
	//
	// ADD EMAIL
	//
	public function add_email($mail_data) {
		$ret_data = array(
			'status' => 0,
			'error' => null,
			'id' => null);
			
		// ERROR CHECK
		if (!isset($mail_data['from_email'])) $mail_data['from_email'] = CMS::getSettings('default.email');
		if (empty($mail_data['from_email']) || !Valid::email($mail_data['from_email'])) $ret_data['error'][] = 'From e-mail error!';
		if (!isset($mail_data['from_name'])) $mail_data['from_name'] = CMS::getSettings('default.site_name');
		if (empty($mail_data['to_email']) || !Valid::email($mail_data['to_email'])) $ret_data['error'][] = 'To e-mail error!';
		if (!empty($mail_data['reply_to_email']) && !Valid::email($mail_data['reply_to_email'])) $ret_data['error'][] = 'Reply to e-mail error!';
		else $mail_data['reply_to_email'] = empty($mail_data['reply_to_email'])?'':$mail_data['reply_to_email'];
		if (empty($mail_data['body'])) $ret_data['error'][] = 'Content empty!';
		if (!isset($mail_data['subject'])) $mail_data['subject'] = '';
		if (!isset($mail_data['body_type'])) $mail_data['body_type'] = 'text/html';
		if (!isset($mail_data['attachments'])) $mail_data['attachments'] = '';
		if (!isset($mail_data['status_id'])) $mail_data['status_id'] = '10';
		
		if (empty($mail_data['reply_to_email'])) $mail_data['reply_to_email'] = $mail_data['from_email'];
		
		if (count($ret_data['error']) == 0) {
			// ADD EMAIL
			$sql = "INSERT INTO mail_queue (
						from_email,
						from_name,
						to_email,
						reply_to_email,
						subject,
						body,
						body_type,
						attachments,
						creation_datetime,
						status_id )
					VALUES (
						:from_email,
						:from_name,
						:to_email,
						:reply_to_email,
						:subject,
						:body,
						:body_type,
						:attachments,
						NOW(),
						:status_id ) ";
			$res = $this->db->query(Database::INSERT, $sql);
			$res->bind(':from_email',$mail_data['from_email']);	
			$res->bind(':from_name',$mail_data['from_name']);
			$res->bind(':to_email',$mail_data['to_email']);
			$res->bind(':reply_to_email',$mail_data['reply_to_email']);
			$res->bind(':subject',$mail_data['subject']);
			$res->bind(':body',$mail_data['body']);
			$res->bind(':body_type',$mail_data['body_type']);
			$res->bind(':status_id',$mail_data['status_id']);
			
			if (is_array($mail_data['attachments'])) $mail_data['attachments'] = implode(',', $mail_data['attachments']);
			$res->bind(':attachments',$mail_data['attachments']);
			
			$db_data = $res->execute();
			
			$ret_data['status'] = '1';
			$ret_data['id'] = $db_data[0];
		} else {
			$ret_data['error'] = implode('<br/>', $ret_data['error']);
		}
		
		return $ret_data;		
	}
	
	// 
	// SEND EMAIL
	//
	public function send_email($id, $mail_data = null) {		
		$ret_data = array(
			'status' => 0,
			'error' => null );
		
		if (!empty($id) AND is_null($mail_data)) {
			// GET MAIL DATA
			$tmp_data = $this->getEmails($id, array('status_id_list' => array('10','20')));
			if (count($tmp_data) > 0) $mail_data = $tmp_data[0];
		}
		
		// ERROR CHECK
		if (empty($mail_data['id'])) $ret_data['error'][] = 'Email data error!';
		else {
			if (empty($mail_data['from_email']) || !Valid::email($mail_data['from_email'])) $ret_data['error'][] = 'From e-mail error!';
			if (empty($mail_data['to_email']) || !Valid::email($mail_data['to_email'])) $ret_data['error'][] = 'To e-mail error!';
			if (!empty($mail_data['reply_to_email']) && !Valid::email($mail_data['reply_to_email'])) $ret_data['error'][] = 'Reply to e-mail error!';
			if (!isset($mail_data['subject'])) $ret_data['error'][] = 'Subject error!';
			if (!isset($mail_data['body'])) $ret_data['error'][] = 'Body error!';
			if (!isset($mail_data['body_type'])) $ret_data['error'][] = 'Body type error!';
		}
		
		if (count($ret_data['error']) == 0) {
			// SEND EMAIL
			$email = Email::factory()
				->subject($mail_data['subject'])
				->message($mail_data['body'],$mail_data['body_type'])
		        ->to($mail_data['to_email'])
		        ->from($mail_data['from_email'], $mail_data['from_name']);			
			if (!empty($mail_data['reply_to_email'])) $email->reply_to($mail_data['reply_to_email']);
			
			// ATTACH FILE
			if (!empty($mail_data['attachments'])) {
				$attachments = explode(',', $mail_data['attachments']);
				for ($i=0; $i<count($attachments); $i++) $email->attach_file($this->base_path.$attachments[$i]);
			}
			
			try { $email->send(); }
			catch (exception $e) { $ret_data['error'][] = $e->getMessage(); }				
		} 
				
		if (count($ret_data['error']) > 0) {
			$ret_data['error'] = implode('<br/>', $ret_data['error']);
			
			$sql = "UPDATE 
						mail_queue 
					SET
						status_id = 20,
						status_msg = :error
					WHERE
						mail_queue.id = :id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':error', $ret_data['error']);
			$res->bind(':id', $mail_data['id']);
			$res->execute();
		} else {
			if (!empty($mail_data['id'])) {
				$ret_data['status'] = '1';
				
				$sql = "UPDATE 
							mail_queue 
						SET
							status_id = 30,
							status_msg = '',
							sent_datetime = NOW()
						WHERE
							mail_queue.id = :id ";
				$res = $this->db->query(Database::UPDATE, $sql);
				$res->bind(':id', $mail_data['id']);
				$res->execute();
			}
		}
		
		return $ret_data;
	}
	
	// 
	// SEND ALL EMAIL
	//
	public function send_all_emails($limit = 100) {
		$emails = $this->getEmails(null, array('status_id_list' => array('10','20')), $limit, null, ' mail_queue.creation_datetime ');
		
		for ($i=0; $i<count($emails); $i++) {
			$ret_data = $this->send_email(null, $emails[$i]);
		}
	}
	
	// 
	// CANCEL EMAIL
	//
	public function cancel_email($id) {
		if (!empty($id)) {
			$sql = "UPDATE 
						mail_queue 
					SET
						status_id = 0,
						status_msg = ''
					WHERE
						mail_queue.status_id IN (10,20) AND
						mail_queue.id = :id ";
			$res = $this->db->query(Database::UPDATE, $sql);
			$res->bind(':id', $id);
			$res->execute();
		}
	}
}