<?php

/**
 * The smarty_function_i18n works for a Smarty instance fit into KohanaPHP framework with languages
 * support. In that case, this function lets us use the Kohana's built-in language method
 * Kohana::lang().
 *
 * The syntax for this function in views is:
 * {i18n name='file.variable' param1='abc' param2='def' ... paramN='???'}
 *
 * The "name" parameter is required and it's syntax is equal to Kohana::lang() syntax, so according
 * to the example above: Kohana::lang() will look for file application/i18n/en_US/file.php and for
 * $lang['variable'] variable in it. You can read more about it at:
 * http://doc.kohanaphp.com/core/kohana#lang
 *
 * You can also pass more parameters after that, because Kohana::lang() uses sprintf()-like arguments
 * (%s, %d, etc.). You just have to name them param1, param2, param3 etc. This function will pass them
 * all to $lang['variable'], where %s and other "vars" will be replaced by these parameters.
 *
 *
 * @param   string      $params
 * @param   object      $smarty
 * @return  string
 */
function smarty_function_lang ($params, &$smarty) {
    // Check if required "name" parameter is set.
    if (!isset($params['name'])) {
    	if (Kohana::$environment == Kohana::DEVELOPMENT) $trigger_error("i18n: missing 'name' parameter");
		else return '';
	}
	
	// GET LANGUAGE
	$lang = isset($params['lang'])?$params['lang']:null;

    $val = CMS::getLexicons($params['name'], array(), $lang);
	
	return $val;
}

?>