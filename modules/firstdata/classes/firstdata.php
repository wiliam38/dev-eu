<?php
class Firstdata extends Model {
	
	public function __construct() {
		parent::__construct();
		
		// CONFIG
		$db_config = Kohana::$config->load('database.default');
		$fd_config = Kohana::$config->load('firstdata');
		
		$this->ecomm_server_url = $ecomm_server_url = $fd_config['ecomm_server_url'];
		$this->ecomm_client_url = $ecomm_client_url = $fd_config['ecomm_client_url'];
		$this->cert_url = $cert_url = $fd_config['cert_url'];
		$this->cert_pass = $cert_pass = $fd_config['cert_pass'];
		$this->currency = $currency = $fd_config['currency'];		
		$db_user = $db_config['connection']['username'];
		$db_pass = $db_config['connection']['password'];
		$db_host = $db_config['connection']['hostname'];
		$db_database = $db_config['connection']['database'];
		$this->db_table_transaction = $db_table_transaction = $fd_config['db_table_transaction'];
		$this->db_table_batch = $db_table_batch = $fd_config['db_table_batch'];
		$this->db_table_error = $db_table_error = $fd_config['db_table_error'];
		
		require_once Kohana::find_file('vendor', 'firstdata/connect');
		require_once Kohana::find_file('vendor', 'firstdata/Merchant');   
		
		// MERCHANT CLASS
		$this->merchant = new Merchant($ecomm_server_url, $cert_url, $cert_pass, 1);      
	}

	public function payWithCard($amount, $description) {
		// PAYMENT DATA	
		$ip           = $_SERVER['REMOTE_ADDR'];
		$language     = '';
		
		// DO PAYMENT
		echo $resp = $this->merchant->startSMSTrans($amount, $this->currency, $ip, $description, $language);
		
		if (substr($resp, 0, 14) == "TRANSACTION_ID") {
			$trans_id = substr($resp, 16, 28);
			$url = $ecomm_client_url . '?trans_id=' . urlencode($trans_id);
		
			// INSERT TRANSACTION
			$sql = "INSERT INTO ".$this->db_table_transaction." 
					SET
						trans_id = :trans_id,
						amount = :amount,
						currency = :currency,
						client_ip_addr = :client_ip_addr,
						description = :description,
						language = :language,
						dms_ok = '---',
						result = '???',
						result_code = '???',
						result_3dsecure = '???',
						card_number = '???',
						t_date = now(),
						response = :response ";
			$res = $this->db->query(Database::INSERT, $sql);		
				  
		    $res->bind(':trans_id', $trans_id);
		    $res->bind(':amount', $amount);
		    $res->bind(':currency', $currency);
		    $res->bind(':client_ip_addr', $ip);
		    $res->bind(':description', $description);
		    $res->bind(':language', $language);
		    $res->bind(':response', $resp);
			
			$db_data = $res->execute();			
		} else {
			$trans_id = false;			
			$resp = htmlentities($resp, ENT_QUOTES);
			
			$sql = "INSERT INTO ".$this->db_table_error."
					SET 
						error_time = now(), 
						action = 'startsmstrans', 
						response = :response ";			
			$res = $this->db->query(Database::INSERT, $sql);

			$res->bind(':response', $resp);
			
			$db_data = $res->execute();
		}
		
		return $trans_id;
	}
	
	public function success($trans_id) {
		// GET IP
		$sql = "SELECT client_ip_addr 
				FROM ".$this->db_table_transaction."
				WHERE trans_id = :trans_id ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':trans_id', $trans_id);			
		$db_data = $res->execute();		

		$resp = $this->merchant->getTransResult(urlencode($trans_id), $db_data[0]['client_ip_addr']);

		$result = '';
		if (strstr($resp, 'RESULT:')) {
			if (strstr($resp, 'RESULT:')) {
				$result = explode('RESULT: ', $resp);
				$result = preg_split( '/\r\n|\r|\n/', $result[1]);
				$result = $result[0];
			} else {
				$result = '';
			}
	
			if (strstr($resp, 'RESULT_CODE:')) {
				$result_code = explode('RESULT_CODE: ', $resp);
				$result_code = preg_split( '/\r\n|\r|\n/', $result_code[1]);
				$result_code = $result_code[0];
			} else {
				$result_code = '';
			}
	
			if (strstr($resp, '3DSECURE:')) {
				$result_3dsecure = explode('3DSECURE: ', $resp);
				$result_3dsecure = preg_split( '/\r\n|\r|\n/', $result_3dsecure[1] );
				$result_3dsecure = $result_3dsecure[0];
			} else {
				$result_3dsecure = '';
			}
	
			if (strstr($resp, 'CARD_NUMBER:')) {
				$card_number = explode('CARD_NUMBER: ', $resp);
				$card_number = preg_split( '/\r\n|\r|\n/', $card_number[1] );
				$card_number = $card_number[0];
			} else {
				$card_number = '';
			}
	
			// UPDATE DATA
			$sql = "UPDATE
						".$this->db_table_transaction."
					SET
						result = :result,
						result_code = :result_code,
						result_3dsecure = :result_3dsecure,
						card_number = :card_number,
						response = :response
					WHERE
						trans_id = :trans_id ";
			$res = $this->db->query(Database::UPDATE, $sql);			
			$res->bind(':result', $result);
			$res->bind(':result_code', $result_code);
			$res->bind(':result_3dsecure', $result_3dsecure);
			$res->bind(':card_number', $card_number);
			$res->bind(':response', $resp);
			$res->bind(':trans_id', $trans_id);		
			$db_data = $res->execute();
		} else {
			// INSERT ERROR
			$sql = "INSERT INTO ".$this->db_table_error." 
					SET 
						error_time = now(), 
						action = 'ReturnOkURL', 
						response = :response ";
			$res = $this->db->query(Database::INSERT, $sql);
			$response = htmlentities($resp, ENT_QUOTES);
			$res->bind(':response', $response);		
			$db_data = $res->execute();
		}
				
		return $result;
	}
	
	public function error($trans_id, $error) {
		// GET IP
		$sql = "SELECT client_ip_addr 
				FROM ".$this->db_table_transaction."
				WHERE trans_id = :trans_id ";
		$res = $this->db->query(Database::SELECT, $sql);
		$res->bind(':trans_id', $trans_id);			
		$db_data = $res->execute();		

	    $resp = $merchant->getTransResult(urlencode($trans_id), $db_data[0]['client_ip_addr']);
	    $resp = htmlentities($resp, ENT_QUOTES);
	    $resp = $error_msg.' + '.$resp;
		
		// INSERT ERROR
		$sql = "INSERT INTO ".$this->db_table_error." 
				SET 
					error_time = now(), 
					action = 'ReturnFailURL', 
					response = :response ";
		$res = $this->db->query(Database::INSERT, $sql);
		$response = htmlentities($resp, ENT_QUOTES);
		$res->bind(':response', $response);		
		$db_data = $res->execute();
	}
	
	
	
}