<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_List extends Model {	
	
	public function load($parameters, $template, $page_data, $page_class) {				
		// PARAMS
		$id_list = null; // 1,2,3
		$parent_list = null; // 1,2,3
		$type_list = array('1'); // array('1', '2')
		$limit = null; // 10
		$order_by = " pages.order_index ";
		$filter_type = 'menu';  // menu, published, all
		$show_levels = '1'; // 1,2,3
		$show_levels_type = 'active'; // all, active
		
		
		// GET PARAMETERS
		$parents = explode('|',$page_data['parents_id_list']);
		foreach ($parameters as $key => $val) {
			if ($val == 'id') $val = $parents[count($parents)-1];	
			if ($val == 'parent_id') $val = $parents[count($parents)-2];	
			if ($val == 'root_id') $val = $parents[0];
			
			if ($key == 'limit') $$key = " LIMIT ".(int)$val;
			else $$key = $val;
		}
		
		// FILTER TYPE
		$filter = array();
		switch ($filter_type) {
			case 'menu':
				$filter = array('hide_menu' => '0', 'status_id' => '10');
				break;
			case 'published':
				$filter = array('status_id' => '10');
				break;
			case 'all':
				$filter = array();
				break;
		}
		
		$docs_data = CMS::getDocuments($id_list,$parent_list, null, $this->lang_id, $filter, $type_list, $order_by, $limit);
		$data['docs'] = $docs_data;
		
		// LOOP MENU LEVELS 
		if ($show_levels > 1) {
			$parents_array = explode('|', $page_data['parents_id_list']);
			for ($i=1; $i<$show_levels AND count($docs_data)>0; $i++) {
				if ($show_levels_type == 'active') {
					$sub_data = array();
					foreach($docs_data as $key => $value) {
						if (in_array($value['id'], $parents_array)) $sub_data[] = $value['id'];
					}
				} else {
					$sub_data = array();
					foreach($docs_data as $key => $value) $sub_data[] = $value['id'];
				}			
				$docs_data = CMS::getDocuments(null, implode(',', $sub_data), null, $this->lang_id, $filter, $type_list, $order_by, $limit);
				$data['docs'] = array_merge($data['docs'],$docs_data);		
			}
		}
		
		$data['action'] = 'load';
		
		$data['user_data'] = $this->user_data;
				
		$data['lang_tag'] = $this->session->get('lang_tag');
		$data['page_id'] = $page_data['page_id'];
		$data['page_data'] = $page_data;
		$data['parents'] = explode('|',$page_data['parents_id_list']);
		$data['parent_list'] = $parent_list;
		
		$result = $this->tpl->factory($template, $data);
				
		return $result;
	}
}