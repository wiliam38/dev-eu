<?php
function smarty_function_setting ($params, &$smarty) {
    // Check if required "name" parameter is set.
    if (!isset($params['name'])) {
    	if (Kohana::$environment == Kohana::DEVELOPMENT) trigger_error("SETTINGS: missing 'name' parameter");
		else return '';
	}
	
	// GET LANGUAGE
	$lang = isset($params['lang'])?'settings-'.$params['lang']:null;

    // Return the string in currently used language.
    $val = CMS::getSettings($params['name'], $lang);
	
	return $val;
}

?>