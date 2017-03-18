<?php defined('SYSPATH') or die('No direct access allowed.');

if (Kohana::$environment == Kohana::PRODUCTION) {
	return array(
		'draugiem' => array (
			'key' => '15017271',
			'secret' => '42ba7e0df5e0a3f9359cf07c4b1999aa'
		),
		'facebook' => array (
			'key' => '1839006499681738',
			'secret' => 'f6403f4b004b7628a3b285d531f04bed',
			'scope' => 'email',
		),
		// 'facebook' => array (
		// 	'key' => '408893922582206',
		// 	'secret' => 'd7a0e9202517e137962b81f72259464a',
		// 	'scope' => 'email',
		// ),
		'twitter' => array(
			'key' => 'hBlX9qKq2jpDT6aBztkjg',
			'secret' => 'hi2RSMHLSvitlBCTTNdftDsbmc72saOTBuOP7TCmbY',
		),
		'google' => array(
			'key' => '354219026296.apps.googleusercontent.com',
			'secret' => 'xGXNv7DZeN3g_rygxq24fdDj',
			'scope' => 'https://www.googleapis.com/auth/structuredcontent',
		)
	);
} else {
	return array(
		'draugiem' => array(
			'key' => '',
			'secret' => ''
		),
		'facebook' => array(
			'key' => '407792909558048',
			'secret' => 'bf97d6402511d5db50dc1cbc20d41f99',
			'scope' => 'email',
		),
		'twitter' => array(
			'key' => '',
			'secret' => '',
		),
		'google' => array(
			'key' => '',
			'secret' => '',
			'scope' => 'https://www.googleapis.com/auth/structuredcontent',
		)
	);
}
