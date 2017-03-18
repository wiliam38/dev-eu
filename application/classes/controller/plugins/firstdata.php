<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Plugins_Firstdata extends Controller_Main {
	public $template = 'site/template/tmp';
	
	public function before() {
		parent::before();		
		
		// PARAMS
		$this->auto_render = FALSE;
		
		$this->fd = new Firstdata();
	}
	
		
	public function action_payment() {
		// ORDER ID
		$order_id = $this->request->param('data');
		
		if(!empty($order_id)) {
			$this->orders = Model::factory('manager_orders_orders');
			$order_data = $this->orders->getOrders($order_id);
			if ($order_data[0]['pay_status_id'] < 10) {
				if (empty($order_data[0]['number']) || $order_data[0]['number'] == '000') {
					// SET ORDER NR
					$sql = "SELECT IFNULL(MAX(orders.number),0) AS nr
							FROM orders";
					$db_data = $this->db->query(Database::SELECT, $sql)->execute()->as_array();
					$number = str_pad($db_data[0]['nr']+1, 3, '0', STR_PAD_LEFT);;
					
					$sql = "UPDATE
								orders
							SET
								number = :number
							WHERE
								orders.id = :order_id ";
					$res = $this->db->query(Database::UPDATE, $sql);	
					$res->bind(':number', $number);	
					$res->bind(':order_id', $order_id);
					$db_data = $res->execute();	
					
					$order_data[0]['number'] = $number;
				}
				
				// TOTALS
				$total =  0;
				$total_data =  $this->orders->getOrderTotal($order_id);	
				$total = round($total_data['price'],2);
				$total += round($order_data[0]['shipping_total'] * (1 + $order_data[0]['shipping_vat'] / 100),2);
				
				$amount = round($total,2) * 100;
				$description = '19BAR ORDER 19B'.$order_data[0]['number'];
				
				// PAYMENT
				echo $trans_id = $this->fd->payWithCard($amount, $description);
				
				if ($trans_id) {
					// INSERT TRANS
					$sql = "INSERT INTO order_payments 
							SET
								order_id = :order_id,
								user_id = :user_id,
								sum = :sum,
								trans_id = :trans_id ";
					$res = $this->db->query(Database::INSERT, $sql);		
						  
				    $res->bind(':order_id', $order_id);
					$res->bind(':user_id', $this->user_id);
					$sum = $amount / 100;
					$res->bind(':sum', $sum);
					$res->bind(':trans_id', $trans_id);
					
					$db_data = $res->execute();
					
					$url = $this->fd->ecomm_client_url . '?trans_id=' . urlencode($trans_id);
					$this->request->redirect($url);
				} else {
					echo "error";
				}
			} else {
				echo "NO ORDER SET";
			}
		} else {
			echo "NO ORDER SET";
		}
	}

	public function action_success() {
    	$trans_id = isset($_POST['trans_id']) ? $_POST['trans_id'] : '';

		// CHECKE SUCCESS
		$result = $this->fd->success($trans_id);
	
		if ($result != 'OK') {
			echo "
			    <script type='text/javascript'>
					window.opener.fd_pay_error();
					self.close();
			    </script> ";
		} else {
			// GET IP
			$sql = "SELECT order_id
					FROM order_payments
					WHERE trans_id = :trans_id ";
			$res = $this->db->query(Database::SELECT, $sql);
			$res->bind(':trans_id', $trans_id);			
			$db_data = $res->execute()->as_array();
			$order_id = $db_data[0]['order_id'];
			
			$this->orders = Model::factory('manager_orders_orders');
			$order_data = $this->orders->getOrders($order_id);
			
			if (empty($order_data[0]['number']) || $order_data[0]['number'] == '000') {			
				$sql = "SELECT IFNULL(MAX(orders.number),0) AS nr
						FROM orders";
				$db_data = $this->db->query(Database::SELECT, $sql)->execute()->as_array();
				$number = str_pad($db_data[0]['nr']+1, 3, '0', STR_PAD_LEFT);;
				
				$sql = "UPDATE
							orders
						SET
							number = :number
						WHERE
							orders.id = :order_id ";
				$res = $this->db->query(Database::UPDATE, $sql);	
				$res->bind(':number', $number);	
				$res->bind(':order_id', $order_id);
				$db_data = $res->execute();	
			}
			
			// SET STATUS
			$this->orders->setStatus($order_id, 10);
			$this->orders->setPayStatus($order_id, 10, date('d-M-Y', time()));
			
			// CREATE PDF AND SEND MAIL
			$this->orders->createSendPDF($order_id);			
			
			$this->session->set('current_order_id_done', '1');
			
			
		  	echo "
			    <script type='text/javascript'>
					window.opener.fd_pay_ok('".$order_id."');
					self.close();
			    </script>";
		}
	}
	
	public function action_error() {		
	    $trans_id = $_POST['trans_id'];
	    $error_msg = $_POST['error'];
		
		$result = $this->fd->error($trans_id, $error_msg);
			    
	    echo '<html style="background-color: #C6E1ED;"><body><center>
			  	<br/>
			  	<br/>
			  	Technical error! Please contact the merchant ! <br><br>' . $error_msg.'
			   </center></body></html>';
	}
}