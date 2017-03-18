<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'cookie' => array(
		'name' => 'iss_w_box_session_cookie',
		'encrypted' => TRUE,
		'lifetime' => 43200,
	),
	'native' => array(
		'name' => 'iss_w_box_session_native',
		'encrypted' => TRUE,
		'lifetime' => 43200,
	),
	'database' => array(
		'name' => 'iss_w_box_session_database',
		'group' => 'default',
		'table' => 'sessions',
	),
);
