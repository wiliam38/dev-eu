<?php

function smarty_function_plugin ($params, &$smarty) {
	if (!isset($params['name'])) {
        if (Kohana::$environment == Kohana::DEVELOPMENT) trigger_error("PAGE DATA: missing 'name' parameter");
        return '';
	}
	
	// GET PLUGIN DATA
	$plugin_data = $smarty->getTemplateVars('plugin_data');
	$class = $plugin_data['class'];
	$data = $plugin_data['data'];
	$page_params = $plugin_data['page_params'];
	$parents_data = $plugin_data['parents_data'];
	
	// GET PLUGIN DATA
	$plugin_data = CMS::getPlugin($params['name']);
				
	$plugin_params = array(	'page_id' => $data[0]['id'], 
							'page_data' => $data[0],
							'page_params' => $page_params,
							'parent_id' => $data[0]['parent_id'],
							'root_id' => isset($parents_data[0])?$parents_data[0]:null,
							'root_root_id' => isset($parents_data[1])?$parents_data[1]:null,
							'parents_id_list' => implode('|', $parents_data));
		
	try {
		if (count($plugin_data) == 0) throw new  Exception('Plugin not found!');					
		if (!file_exists(APPPATH.'classes/model/plugins/'.strtolower($plugin_data['model']).EXT) AND Kohana::$environment == Kohana::PRODUCTION)
			throw new  Exception('Plugin not found!'); 
		
		// GET VALUES FROM PLUGIN CALL
		foreach ($params as $key => $value)
	        if ($key != 'name') $plugin_data['parameters'][$key] = $value;		
					
		/* - kā loop un rezultātā masīvs nevis strings
		// REPLACE PAGE PARAMS DATA		
		preg_match_all('/\(\([a-zA-Z0-9_]*\)\)/', $plugin_data[0]['parameters'], $param_matches);
		for ($j=0; $j<count($param_matches[0]); $j++) {
			switch ($param_matches[0][$j]) {
				case '((id))':
					$plugin_data[0]['parameters'] = str_replace($param_matches[0][$j], $data[0]['id'], $plugin_data[0]['parameters']);
					break;
				case '((parent_id))':
					$plugin_data[0]['parameters'] = str_replace($param_matches[0][$j], $data[0]['parent_id'], $plugin_data[0]['parameters']);
					break;
				case '((root_id))':
					$plugin_data[0]['parameters'] = str_replace($param_matches[0][$j], isset($parents_data[0])?$parents_data[0]:null, $plugin_data[0]['parameters']);
					break;
				case '((root_root_id))':
					$plugin_data[0]['parameters'] = str_replace($param_matches[0][$j], isset($parents_data[1])?$parents_data[1]:null, $plugin_data[0]['parameters']);
					break;
				default:
					$plugin_data[0]['parameters'] = str_replace($param_matches[0][$j], '', $plugin_data[0]['parameters']);
				break;
			}
		}
		 
			
		// REPLACE SETTING VALUES
		preg_match_all('/\[\[#([a-zA-Z0-9_.]*)\]\]/', $plugin_data[0]['parameters'], $settings_matches);
		for ($j=0; $j<count($settings_matches[0]); $j++) {
			try {
				$setting_val = CMS::getSettings($settings_matches[1][$j]);
			} catch (Exception $e) {
				$setting_val = '';
			}
		
			$plugin_data[0]['parameters'] = str_replace($settings_matches[0][$j], $setting_val, $plugin_data[0]['parameters']);
		}
		 */
		
		$plugin = Model::factory('plugins_'.$plugin_data['model']);
		$text = $plugin->load($plugin_data['parameters'], $plugin_data['template'], $plugin_params, $class);				
	} catch (Exception $e) {	
		if (Kohana::$environment == Kohana::DEVELOPMENT) {
			echo $e;
			$text = "{{".$params['name']."}}";
		} else {
			$text = "";
		}
	}
	
	return $text;
}

?>