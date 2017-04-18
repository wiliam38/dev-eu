<?php defined('SYSPATH') or die('No direct script access.');

class CMS  {
	public static $products_page_id = 3;
	public static $recipes_page_id = 4;
	public static $news_page_id = 2;
	public static $lang_id = 1;
		
	//
	// GET DOCUMENTS
	//
	public static function getDocuments($id_list = null, $parent_list = null, $page_alias = null, $lang_id = null, $filters = array(), $type_list = null, $order_by = " pages.order_index ", $limit = " ", $where = " ") {
		// DB
		$db = new DB;
		
		// PARAMS
		if (is_null($lang_id)) $lang_id = Session::instance()->get('lang_id');		
		
		// FILTER
		$filter = " ";
		if (!is_null($id_list)) $filter .= " AND pages.id IN :id_list ";
		if (!is_null($parent_list)) $filter .= " AND IFNULL(pages.parent_id,0) IN :parent_list ";
		if (!is_null($page_alias)) {
			$filter .= " AND IF (pages.last_alias = 1,	( 	:page_alias REGEXP CONCAT('^',page_contents.full_alias) OR 
															( 	:page_alias REGEXP CONCAT('^',pages.alias) AND 
																IFNULL(pages.alias,'') != '' )),
														( 	page_contents.full_alias LIKE :page_alias OR 
															( 	pages.alias LIKE :page_alias AND 
																IFNULL(pages.alias,'') != '' )) )
							AND page_contents.full_alias != '' 
							AND RIGHT(page_contents.full_alias,1) != '/' ";
		}															
		if (!is_null($lang_id)) $filter .= " AND page_contents.language_id = :lang_id ";
		if (!is_null($type_list)) $filter .= " AND pages.type_id IN :type_list ";
		
		$filter_data = array();
		if (!is_null($filters)) {
			foreach ($filters as $key => $value) {
				$filter_data[] = array(	'key' => $key,
										'value' => $value);
			}		
			for ($i=0; $i<count($filter_data); $i++) {
				$filter .= " AND page_contents.".$filter_data[$i]['key']." = :".$filter_data[$i]['key']." ";
			}
		}
		
		// ORDER BY
		if ($order_by == 'id_list') {
			$tmp_order_data = explode(',', $id_list);			
			$order_by = ' CASE ';
			for ($i=0; $i<count($tmp_order_data); $i++) {
				if (is_numeric($tmp_order_data[$i])) $order_by .= ' WHEN pages.id = '.$tmp_order_data[$i].' THEN '.$i.' ';
			}
			$order_by .= ' ELSE '.count($tmp_order_data).' END ';
		}
		
		$sql = "SELECT
					pages.id						AS id,
					pages.id						AS admin_id,
					pages.admin_title				AS admin_title,
					pages.image_src					AS page_image_src,
					pages.main_image_id				AS main_image_id,
					page_images.image_src			AS main_image_src,
					pages.parent_id					AS parent_id,
					pages.status_id					AS status_id,
					pages.type_id					AS type_id,
					pages.plugin_controller			AS plugin_controller,
					
					templates.tpl_name				AS tpl_name,
	
					page_contents.id				AS page_content_id,
					page_contents.image_src			AS image_src,
					page_contents.title_image_src	AS title_image_src,
					page_contents.title				AS title,
					page_contents.image_src			AS image_src,
					page_contents.description		AS description,
					page_contents.intro				AS intro,
					page_contents.full_alias		AS alias,
					page_contents.language_id		AS language_id,
					CONCAT(languages.tag,'/',page_contents.full_alias)							AS full_alias,
					CONCAT(languages.tag,'/',pages.alias,'/',page_contents.full_alias)			AS global_alias,
					page_contents.keywords			AS keywords,
					page_contents.menu_title		AS menu_title,
					page_contents.menu_image_src	AS menu_image_src,
					page_contents.content			AS content,
					page_contents.content_type_id	AS content_type_id,
					page_contents.redirect_link		AS redirect_link,
					page_contents.user_datetime		AS datetime
				FROM
					pages
					JOIN page_contents ON
						pages.id = page_contents.page_id
					LEFT JOIN languages ON
						page_contents.language_id = languages.id
					LEFT JOIN templates ON
						pages.template_id = templates.id
					LEFT JOIN page_images ON
						page_images.id = pages.main_image_id
				WHERE
					pages.status_id >= 10 AND
					page_contents.status_id >= 10 AND
					(	languages.id IS NULL OR
						languages.status_id >= 10 ) 
					".$filter."
				ORDER BY
					".$order_by." 
				".$limit." ";			
		$result = $db->query(Database::SELECT, $sql);
		if (!is_array($type_list)) $type_list = explode(',',$type_list);
		$result->bind(':type_list', $type_list);
		if (!is_array($id_list)) $id_list = explode(',',$id_list);
		$result->bind(':id_list', $id_list);
		$parent_array = explode(',',$parent_list);
		$result->bind(':parent_list', $parent_array);
		$result->bind(':page_alias', $page_alias);	
		$result->bind(':lang_id', $lang_id);
		
		for ($i=0; $i<count($filter_data); $i++) {
			$result->bind(':'.$filter_data[$i]['key'], $filter_data[$i]['value']);
		}
		
		return $result->execute()->as_array();
	}
	
	//
	// SHOW ERROR PAGE
	//
	static function errorPage() {
		$page_data = CMS::getDocuments(CMS::getSettings('default.site_error_id'));
		header('Location: '.URL::base(TRUE, FALSE).$page_data[0]['full_alias']);
		exit();
	}
	static function introPage() {
		$page_data = CMS::getDocuments(CMS::getSettings('default.site_intro_id'));
		header('Location: '.URL::base(TRUE, FALSE).$page_data[0]['full_alias']);
		exit();
	}
	
	//
	// VALIDATE INPUT
	//
	static function validate($data, $config, &$error = array(), &$error_text = array(), $error_text_prefix = false) {
		// array('name' => '', 'new_name' => '', 'type' => '', 'must_fill' => false, 'related' => array())	
		$ret_data = array();
		
		for ($i=0; $i<count($config); $i++) {
			$value = (isset($data[$config[$i]['name']]))?(!is_array($data[$config[$i]['name']])?array($data[$config[$i]['name']]):$data[$config[$i]['name']]):'';
			
			for ($v=0; $v<count($value); $v++) {
				$value[$v] = trim($value[$v]);
				
				if ($config[$i]['must_fill'] && ($value[$v] == '' || (is_array($value[$v]) && count($value[$v]) == 0))) {
					// VALUE MUST BE FILLED
					$error[$config[$i]['name']] = 'error';
					if ($config[$i]['type'] == 'array') $error[$config[$i]['name'].'[]'] = 'error';
					if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['name']);
				} else {
					if ($value[$v] != '') {
						switch($config[$i]['type']) {
							case 'text':
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'numeric':
								$value[$v] = str_replace(',', '.', $value[$v]);
								if (!Valid::numeric($value[$v])) {
									$error[$config[$i]['name']] = 'error';
									if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['name']);
								}
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'phone':
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'email':
								if (!Valid::email($value[$v])) {
									$error[$config[$i]['name']] = 'error';
									if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['name']);
								}
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'date':
								if (!Valid::date($value[$v])) {
									$error[$config[$i]['name']] = 'error';
									if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['name']);
								}
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'url':
								if (!Valid::url($value[$v])) {
									$error[$config[$i]['name']] = 'error';
									if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['name']);
								}
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'reg_nr':
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'pk':
								if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9][0-9]-[0-9][0-9][0-9][0-9][0-9]$/', $value[$v])) {
									$error[$config[$i]['name']] = 'error';
									if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['name']);
								}
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							case 'array':
								if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
								break;
							default: 
								echo 'No validation type "'.$config[$i]['type'].'" defined!';
								exit();
								break;
						}
	
						// RELATED FIELDS
						for ($j=0; $j<count($config[$i]['related']); $j++) {
							if (!isset($data[$config[$i]['related'][$j]]) || trim($data[$config[$i]['related'][$j]]) == '') {
								$error[$config[$i]['related'][$j]] = 'error';
								if ($error_text_prefix) $error_text[] = __($error_text_prefix.$config[$i]['related'][$j]);
							}
						}
					} else {
						// RETURN EMPTY VALUE
						if ($config[$i]['new_name'] != '') $ret_data[$config[$i]['new_name']] = $value[$v];
					}
				}
			}
		}	
		
		if (count($error) == 0 && count($error_text) == 0) {
			return $ret_data;	
		} else {
			return false;	
		}
	}
	
	//
	// GET PAGE PARENTS
	//
	public static function getPageParents($page_id) {
		// DB
		$db = new DB;
		
		$tmp_parents = array();
		
		while (!empty($page_id)) {
			$sql = "SELECT
						pages.id							AS id,
						IFNULL(pages.parent_id,0)			AS parent_id
					FROM
						pages
					WHERE
						pages.id = :page_id ";
			$result = $db->query(Database::SELECT, $sql);
			$result->bind(':page_id', $page_id);
			$res = $result->execute()->as_array();
			
			if (count($res) > 0) {
				$page_id = $res[0]['parent_id'];
				$tmp_parents[] = $res[0]['id'];
			} else {
				$page_id = null;
			}
		}

		return array_reverse($tmp_parents);
	}
	
    //
	// GET LANGUAGES
	//
	public static function getLanguages($id = null, $tag = null, $from_status_id = null) {
		// DB
		$db = new DB;	
		
		if (!is_null($id) && !is_array($id)) $id = array($id); 
		
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND languages.id IN :id ";
		if (!is_null($tag)) $filter .= " AND languages.tag = :tag ";
		if (!is_null($from_status_id)) $filter .= " AND languages.status_id >= :from_status_id ";
		
		$sql = "SELECT
					languages.id				AS id,
					languages.name				AS name,
					languages.ticker			AS ticker,
					languages.tag				AS tag,
					languages.img_src			AS img_src,
					languages.order_index		AS order_index,
					
					languages.user_id			AS user_id,
					CONCAT(users.first_name,' ',users.last_name)	AS user_full_name,
					languages.user_datetime		AS user_datetime,	

					languages.status_id			AS status_id,
					status.name					AS status_name,
					
					IF (languages.id = settings.value, 1, 0)	AS `default`
				FROM
					languages
					LEFT JOIN users ON
						languages.user_id = users.id
					LEFT JOIN status ON
						languages.status_id = status.status_id AND
						status.table_status_name = 'languages_status_id'
					LEFT JOIN settings ON
						settings.name = 'default.lang_id'
				WHERE
					1 = 1
					".$filter."
				ORDER BY
					languages.order_index";			
		$result = $db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		$result->bind(':tag', $tag);
		$result->bind(':from_status_id', $from_status_id);	
		
		return $result->execute()->as_array();
	}
	
	//
	// GET LEXICONS
	//
	public static function getLexicons($name, $params = array(), $lang_tag = null) {		
		if (empty($lang_tag)) {
			// SESSION
			$session = Session::instance();	
			$lang_tag = $session->get('lang_tag');
		}
		
		$val = I18n::get($name, 'site-'.$lang_tag);
		
		if ($val == $name) {
			// CLEAR CACHE
			$resources = Model::factory('manager_resources');		
			$resources->clear_cache();
			
			$val = I18n::get($name, 'site-'.$lang_tag);
			
			if ($val == $name) {
				if (Kohana::$environment == Kohana::DEVELOPMENT) trigger_error("I18n: missing '".$name."' translation in language '".$lang_tag."'");
				else $val = '';
			} 
		}
		
		// Parse additional parameters.
	    if (is_array($params)) foreach ($params as $key => $value) $val = str_replace(':'.$key, $value, $val);
		
		return $val;
	}
	
		//
	// GET PLUGIN
	//
	public static function getPlugin($name) {
		if (!file_exists(APPPATH.'i18n/plugins/site.php')) {
			// CLEAR CACHE
			$resources = Model::factory('manager_resources');		
			$resources->clear_cache();	
		}
		if (file_exists(APPPATH.'i18n/plugins/site.php')) $plugins = require APPPATH.'i18n/plugins/site.php';
		else {
			if (Kohana::$environment == Kohana::DEVELOPMENT) trigger_error("PLUGINS: missing plugin '".$name."'");
			else return array();	
		}
		
		if (isset($plugins[$name])) {
			return $plugins[$name];
		} else {
			// CLEAR CACHE
			$resources = Model::factory('manager_resources');		
			$resources->clear_cache();	
			
			$plugins = require APPPATH.'i18n/plugins/site.php';
			
			if (isset($plugins[$name])) return $plugins[$name];
			else {
				if (Kohana::$environment == Kohana::DEVELOPMENT) trigger_error("PLUGINS: missing plugin '".$name."'");
				else return array();
			}
		}
	}
		
	//
	// GET SETTINGS
	//
	public static function getSettings($name = null, $lang_tag = null) {
		if (empty($lang_tag)) {
			// SESSION
			$session = Session::instance();	
			$lang_tag = $session->get('lang_tag');
		}
		
		$val = I18n::get($name, 'settings-'.$lang_tag);		
		if ($val == $name || trim($val) == '') $val = I18n::get($name, 'settings-default');
		
		if ($val == $name) {
			// CLEAR CACHE
			$resources = Model::factory('manager_resources');		
			$resources->clear_cache();
			
			$val = I18n::get($name, 'settings-'.$lang_tag);		
			if ($val == $name || trim($val) == '') $val = I18n::get($name, 'settings-default');
			
			if ($val == $name) {
				if (Kohana::$environment == Kohana::DEVELOPMENT) trigger_error("SETTINGS: missing setting '".$name."' value");
				else $val = '';
			} 
		}
		
		return $val;
	}
	
	//
	// GET TEMPLATES
	//
	public static function getTemplates($id = null) {
		// DB
		$db = new DB;	
		
		// FILTER
		$filter = " ";
		if (!is_null($id)) $filter .= " AND templates.id = :id ";
		
		$sql = "SELECT
					templates.id			AS id,
					templates.name			AS name,
					templates.tpl_name		AS tpl_name,
					templates.type_id		AS type_id,
					types.name				AS type_name,
					CONCAT(users.first_name,' ',users.last_name)			AS user_full_name					
				FROM
					templates
					LEFT JOIN types ON
						types.type_id = templates.type_id AND
						types.table_type_name = 'templates_type_id'
					LEFT JOIN users ON
						templates.user_id = users.id
				WHERE
					1 = 1
					".$filter."
				ORDER BY
					templates.name";			
		$result = $db->query(Database::SELECT, $sql);
		$result->bind(':id', $id);
		
		return $result->execute()->as_array();
	}
	
	//
	// GET STATUS
	//
	public static function getStatus($table_status_name, $status_id = null, $selected_id = null, $selected_text = 'checked', $from_status_id = null) {
		// DB
		$db = new DB;	
		
		// LANG
		$lang_id = Session::instance()->get('lang_id');	
		if (!is_numeric($lang_id)) $lang_id = CMS::getSettings('default.lang_id');
		if (!is_numeric($lang_id)) $lang_id = "'NULLL'";
		
		$sql = $db->select(
				array('status.status_id', 'id'),
				array('status.name', 'name'),
				array('status.description', 'description'),
                array('status.value', 'value'),
				array('IF("status.status_id" = '.(is_numeric($selected_id)?$selected_id:'NULL').', \''.addslashes($selected_text).'\', \'\')', 'selected') )
			->from('status')
			->where_open()
			->where_open()
			->where('status.table_status_name', '=', $table_status_name)
			->order_by('status.order_index')
			->order_by('status.status_id');
			
		// GET TYPE CONTENTS
		$tmp_data = $db->query(Database::SELECT, "SHOW TABLES LIKE 'status_contents'")->execute()->as_array();
		$tableExists = count($tmp_data) > 0;
		if ($tableExists) {
			$sql->join('status_contents', 'LEFT')
				->on('status.id', '=', 'status_contents.status_id')
				->on('status_contents.language_id', '=', DB::expr($lang_id));
			$sql->select(array('status_contents.name', 'l_name'));
		}
		$sql->where_close();	
		if (is_numeric($selected_id)) {
			$sql->or_where_open()
				->where('status.table_status_name', '=', $table_status_name)
				->where('status.status_id', '=', $selected_id)				
				->or_where_close();	
		}
		$sql->where_close();
		
		// FILTER
		if (!is_null($status_id)) $sql->where('status.status_id', '=', $status_id);
		if (!is_null($from_status_id)) $sql->where('status.status_id', '>', $from_status_id);
		
		return $sql->execute()->as_array();
	}
	
	//
	// GET TYPES
	//
	public static function getTypes($table_type_name, $type_id = null, $selected_id = null, $selected_text = 'checked') {
		// DB
		$db = new DB;	
		
		// LANG
		$lang_id = Session::instance()->get('lang_id');	
		if (!is_numeric($lang_id)) $lang_id = CMS::getSettings('default.lang_id');
		if (!is_numeric($lang_id)) $lang_id = "'NULLL'";
		
		$sql = $db->select(
				array('types.type_id', 'id'),
				array('types.name', 'name'),
				array('types.description', 'description'),
                array('types.value', 'value'),
				array('IF("types.type_id" = '.(is_numeric($selected_id)?$selected_id:'NULL').', \''.addslashes($selected_text).'\', \'\')', 'selected') )
			->from('types')
			->where_open()
			->where_open()
			->where('types.table_type_name', '=', $table_type_name)
			->order_by('types.order_index')
			->order_by('types.type_id');
			
		// GET TYPE CONTENTS
		$tmp_data = $db->query(Database::SELECT, "SHOW TABLES LIKE 'type_contents'")->execute()->as_array();
		$tableExists = count($tmp_data) > 0;
		if ($tableExists) {
			$sql->join('type_contents', 'LEFT')
				->on('types.id', '=', 'type_contents.type_id')
				->on('type_contents.language_id', '=', DB::expr($lang_id));
			$sql->select(array('type_contents.name', 'l_name'));
		}
		$sql->where_close();	
		if (is_numeric($selected_id)) {
			$sql->or_where_open()
				->where('types.table_type_name', '=', $table_type_name)
				->where('types.type_id', '=', $selected_id)				
				->or_where_close();	
		}
		$sql->where_close();
		
		// FILTER
		if (!is_null($type_id)) $sql->where('types.type_id', '=', $type_id);
		
		return $sql->execute()->as_array();
	}
	
	//
	// GET PARAMETER VALUE
	// 
	public static function getGET($key = null) {
		// GET PARAMS
		$params = array();
		$url = parse_url($_SERVER['REQUEST_URI']);
		if (!empty($url['query'])) parse_str($url['query'], $params);
		
		return !empty($key)?(isset($params[$key])?$params[$key]:null):$params;
	}
	
	// 
	// DB date
	//
	public static function date($date) {
		if (preg_match('/^[0-9]*$/', $date)) {
			return !empty($date)?date('Y-m-d H:i:s',$date):null;
		} else {
			$date = strtotime($date);
			if ($date == false OR $date == -1) $date = null;
			return !empty($date)?date('Y-m-d H:i:s',$date):null;
		}
	}
	
	//
	// GET MTIME
	//
	public static function mtime($file) {
		$tmp_file = $file;
		if (substr($tmp_file,0,1) == '/') $tmp_file = $_SERVER['DOCUMENT_ROOT'].$tmp_file;	
		if (file_exists($tmp_file)) {
			$mtime = substr(date('ymdHis', filemtime($tmp_file)), 0, -1);
			return $file."?d=".(empty($mtime)?'1':$mtime);
		} else {
			return $file;
		}
	}
	
	//
	// GET PLUGIN PAGE ALIAS
	//
	public static function getPluginPageAlias($page_data) {
		// SUB PAGE ALIAS		
		$base_url_path = str_replace('http://'.$_SERVER['HTTP_HOST'],'',URL::base());	
		$base_url_path = str_replace('https://'.$_SERVER['HTTP_HOST'],'',$base_url_path);	
		
		$page_alias_array = explode('?', $_SERVER['REQUEST_URI']);		
		$page_alias = $page_alias_array[0];
		
		$page_alias = preg_replace('/^'.str_replace('/','\/',$base_url_path).'/i','', $page_alias);
		if (substr($page_data['page_data']['full_alias'],0,strlen($page_alias)) == $page_alias) $page_alias = '';
		$page_alias = preg_replace('/'.str_replace('/','\/',$page_data['page_data']['full_alias']).'/i', '', $page_alias);
		$page_alias = preg_replace('/^\/'.str_replace('/', '\/', $page_data['page_data']['full_alias']).'[\/]?/', '', $page_alias);
		
		if (substr($page_alias,0,1) == '/') $page_alias = substr($page_alias, 1);
		if (substr($page_alias,-1,1) == '/') $page_alias = substr($page_alias, 0, strlen($page_alias)-1);	
		$page_alias = preg_replace('/\.html$/i','',$page_alias);	
		if (empty($page_alias)) $page_alias = null;
		
		return $page_alias;
	}
	
	// 
	// GET CODED PARAM VALUE
	//
	public static function getPageAliasParam($param_name, &$page_alias) {
		$val = null;
		
		if ($param_name == 'p') {
			// GET PAGE
			$val = CMS::getGET('p');
		} else {
			if (preg_match('/(-'.$param_name.'([0-9]+|new))[\.\/]/i', $page_alias, $m)) {
				$val = $m[2];
				$page_alias = preg_replace('/(-'.$param_name.'[0-9]+|new)(\.\/)/i', '\2', $page_alias);
			} elseif (preg_match('/(-'.$param_name.'([0-9]+|new))$/i', $page_alias, $m)) {
				$val = $m[2];
				$page_alias = preg_replace('/(-'.$param_name.'[0-9]+|new)$/i', '\2', $page_alias);
			}
		}
		
		return $val;
	}
	
	//
	// LANG ARRAY
	//
	static function langArray($sub_data) {
		$tmp_data = array();
		for($i=0; $i<count($sub_data); $i++) {
			if (!isset($tmp_data[$sub_data[$i]['id']])) {
				foreach($sub_data[$i] as $key => $value) {
					if (substr($key, 0, 2) != 'l_')
						$tmp_data[$sub_data[$i]['id']][$key] = $value;
				}
			}
			if (!empty($sub_data[$i]['l_language_id'])) {
				foreach($sub_data[$i] as $key => $value) {
					if (substr($key, 0, 2) == 'l_')
						$tmp_data[$sub_data[$i]['id']]['lang'][$sub_data[$i]['l_language_id']][substr($key, 2)] = $value;
				}		
			}	
		}
		
		$data = array();
		foreach($tmp_data as $key => $value) {
			$data[] = $value;
		}
		
		return $data;
	}
}