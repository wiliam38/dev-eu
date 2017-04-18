<?php defined('SYSPATH') or die('No direct access allowed.');

return array(

	'driver'       => 'access',
	'hash_method'  => 'sha256',
	'hash_key'     => 'iss.cms',
	'lifetime'     => 3600,
	'session_type' => 'database',
	'session_key'  => 'iss_cms_w_box_key',
	'users' => array(),

);
