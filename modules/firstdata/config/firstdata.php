<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'ecomm_server_url'		=> 'https://secureshop-test.firstdata.lv:8443/ecomm/MerchantHandler',
	'ecomm_client_url' 		=> 'https://secureshop-test.firstdata.lv/ecomm/ClientHandler',
	'cert_url'				=> 'application/config/keystore.pem', //full path to keystore file
	'cert_pass'				=> 'testpass', //keystore password
	'currency'				=> '428', //428=LVL 978=EUR 840=USD 941=RSD 703=SKK 440=LTL 233=EEK 643=RUB 891=YUM

	/*UNCOMMENT THIS WHEN YOU GO TO PRODUCTION SYSTEM, ALSO CHANGE KEYSTORE AND PASSWORD

	'ecomm_server_url'		=> 'https://secureshop.firstdata.lv:8443/ecomm/MerchantHandler',
	'ecomm_client_url'		=> 'https://secureshop.firstdata.lv/ecomm/ClientHandler',
	
	*/
	
	//MYSQL config
	//!!!!! DO NOT CREATE DATABASE OR TABLE YOURSELF, IT WILL BE DONE AUTOMATICALY. CHANGE ONLY USER, PASS, HOST. !!!!!	
	'db_table_transaction'	=> 'fd_transaction',
	'db_table_batch'		=> 'fd_batch',
	'db_table_error'		=> 'fd_error',
);

?>