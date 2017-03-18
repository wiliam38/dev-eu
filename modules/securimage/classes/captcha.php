<?php defined('SYSPATH') OR die('No direct access.');
/**
 * Captcha abstract class.
 *
 * @package		Securimage
 * @author		Janis Dauksts
 * @copyright	(c) 2014 
 */
abstract class Captcha {	
	public static $config = array ();
	
	public static function show() {
		// LOAD CLASS
		require_once Kohana::find_file('vendor', 'securimage/securimage');
		$img = new Securimage();
		
		// LOAD CONFIG
		$config = Kohana::$config->load('securimage')->get('default');
		foreach ($config as $key => $val) {
			$img->$key = $val;
		}
		
		// NAMESPACE
		$namespace = CMS::getGET('namespace');
		if (!empty($namespace)) $img->setNamespace($namespace);

		// SHOW IMAGE
		$img->show();
	}
	
	public static function check($captcha_code) {
		// LOAD CLASS
		require_once Kohana::find_file('vendor', 'securimage/securimage');
		$securimage = new Securimage();
		
		// SAVE SECURITY PARAMS
		$securimage_code_disp = $_SESSION['securimage_code_disp'] [$securimage->namespace];
		$securimage_code_value = $_SESSION['securimage_code_value'][$securimage->namespace];
		$securimage_code_ctime = $_SESSION['securimage_code_ctime'][$securimage->namespace];
		
		$result = $securimage->check($captcha_code);
		
		// SAVE SECURITY PARAMS
		$_SESSION['securimage_code_disp'] [$securimage->namespace] = $securimage_code_disp;
		$_SESSION['securimage_code_value'][$securimage->namespace] = $securimage_code_value;
		$_SESSION['securimage_code_ctime'][$securimage->namespace] = $securimage_code_ctime;
		
		return $result;
	}
	
	public static function clear() {
		// LOAD CLASS
		require_once Kohana::find_file('vendor', 'securimage/securimage');
		$securimage = new Securimage();
		
		// CLEAR SECURITY PARAMS
		$_SESSION['securimage_code_disp'] [$securimage->namespace] = '';
		$_SESSION['securimage_code_value'][$securimage->namespace] = '';
		$_SESSION['securimage_code_ctime'][$securimage->namespace] = '';
	}

} // End Captcha Class
