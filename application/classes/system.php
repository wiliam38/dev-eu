<?php defined('SYSPATH') or die('No direct script access.');

class SYSTEM  {	
	//
	// SET LOCALE
	//
	static function setLocale($lang_tag) {
		switch (strtolower($lang_tag)) {
			case 'lv':
				setlocale(LC_TIME, 'lv_LV.utf8', 'Latvian');
				break;
			case 'en':
				setlocale(LC_TIME, 'en_US.utf8', 'English');
				break;
			case 'ru':
				setlocale(LC_TIME, 'ru_RU.utf8', 'Russian');
				break;
			default:
				setlocale(LC_TIME, $lang_tag."_".strtoupper($lang_tag).'.utf8');
				break;
		}
	}
	
}