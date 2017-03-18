<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_Languages extends Model {	
	
	public function load($parameters, $template, $page_data, $page_class) {								
		// GET PARAMETERS			
		foreach ($parameters as $key => $val) {
			$$key = $val;
		}
		
		$lang_data = CMS::getLanguages(null,null,'10');
		$langs = array();
		
		// GET DOCUMENT DATA;
		$page_model = Model::factory('manager_resources');
		$doc_data = $page_model->getDocuments($page_data['page_id']);
				
		// SUB PAGE PLUGIN ALIAS
		$plugin_page_alias = CMS::getPluginPageAlias($page_data);
				
		for ($i=0; $i<count($lang_data); $i++) {
			$page_alias = $lang_data[$i]['tag']."/".(isset($doc_data[0]['lang'][$lang_data[$i]['id']]['full_alias'])?$doc_data[0]['lang'][$lang_data[$i]['id']]['full_alias']:'');
			if (!empty($plugin_page_alias)) $page_alias .= '/'.$plugin_page_alias;			
			
			$langs[] =  array(	'lang_tag' => $lang_data[$i]['tag'],
								'lang_name' => $lang_data[$i]['name'],
								'lang_id' => $lang_data[$i]['id'],
								'lang_ticker' => $lang_data[$i]['ticker'],
								'page_alias' => $page_alias);
		}
		
		$data['langs'] = $langs;
		$data['lang_tag'] = $this->session->get('lang_tag');
		$data['lang_id'] = $this->lang_id;
		$data['page_id'] = $page_data['page_id'];
		$data['action'] = 'lang_menu';
		
		
		$result = $this->tpl->factory($template, $data);
		
		return $result;
	}
}