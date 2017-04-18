<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package		Securimage
 * @author		Janis Dauksts
 * @copyright	(c) 2014
 */
class Controller_Securimage extends Controller {
	public $auto_render = FALSE;

	public function action_show() {
		Captcha::show();
	}
}
