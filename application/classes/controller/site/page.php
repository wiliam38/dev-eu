<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Site_Page extends Controller_Main {
	public $template = 'site/template/tmp';
	
	public function before() {
		if (Kohana::$environment == Kohana::DEVELOPMENT) {
			// Start a new benchmark
			$this->benchmark = Profiler::start('Your Category', __FUNCTION__);
		}
		
		parent::before();		
		
		// CSS AND JS
		$this->tpl->css_file = array();
		$this->tpl->js_file = array();
	}
	
	function after() {
		parent::after();
		
		if (Kohana::$environment == Kohana::DEVELOPMENT) {
			// Stop the benchmark
			Profiler::stop($this->benchmark);
			echo '	<a href="#stat" onclick="$(\'#sys_stat_data\').toggle(); return false;" style="position: absolute; color: #000000; background-color: #FFFFFF; border: 1px solid #777777; padding: 5px; opacity: 0.5; text-decoration: none; margin: -30px 0px 0px 10px;">Statistics</a>
					<div style="display: none;" id="sys_stat_data">';
			echo 		View::factory('profiler/stats');
			echo '	</div>';
		}
	}
	
		
	public function action_load() {		
		// PARAMS
		$this->auto_render = FALSE;
		
		// REDIRECT TO WWW
		if (substr_count($_SERVER['HTTP_HOST'], '.') == 1) {
			$this->request->redirect("http" . (empty($_SERVER["HTTPS"])?'':($_SERVER["HTTPS"]=="on")?"s":"") . '://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
		}
			
		// GET PAGE	ALIAS	
		$page_alias = $this->request->param('alias');

		$page_alias = preg_replace('/\.html$/i','',$page_alias);

		if (empty($page_alias)) {
			$lang_id = null;
			$lang_tag = null;
		}		
		
		// GET LANGUAGE
		$lang_tag = substr($page_alias, 0, strpos($page_alias, '/'));
		if (empty($lang_tag) AND !empty($page_alias)) $lang_tag = $page_alias;
		$lang_data = CMS::getLanguages(null, $lang_tag, 10);
		
		// REMOVE LANG TAG FROM LINK
		if (isset($lang_data[0]['id'])) {
			$page_alias = preg_replace('/^'.$lang_tag.'\/?/', '', $page_alias);
		}
	
		if (isset($lang_data[0]['id']) AND $lang_data[0]['status_id'] >= 10) {
			$lang_tag = $lang_data[0]['tag'];
			$lang_id = $lang_data[0]['id'];			
		} else {
			// GET LANGUAGE FROM DOMAIN
			$domain = $_SERVER['SERVER_NAME'];
			$tmp_lang_tag = substr($domain, strrpos($domain, '.')+1);
			if (!empty($tmp_lang_tag)) {
				$lang_data = CMS::getLanguages(null, $tmp_lang_tag, 10);
				if (isset($lang_data[0]['id']) AND $lang_data[0]['status_id'] >= 10) {
					$lang_tag = $lang_data[0]['tag'];
					$lang_id = $lang_data[0]['id'];			
				} else {
					$lang_data = CMS::getLanguages(CMS::getSettings('default.lang_id'), null, 10);
			
					$lang_tag = $lang_data[0]['tag'];
					$lang_id = $lang_data[0]['id'];
				}
			} else {
				$lang_data = CMS::getLanguages(CMS::getSettings('default.lang_id'), null, 10);
		
				$lang_tag = $lang_data[0]['tag'];
				$lang_id = $lang_data[0]['id'];
			}
		}
		
		// GET RESOURCE TYPES
		$resource_types_list = CMS::getSettings('default.resource_type_list', $lang_tag);
		if (empty ($resource_types_list)) $resource_types_list = array('1');
		else $resource_types_list = explode(',', $resource_types_list);
		
		// PAGE PARAMS
		$page_id = null;
		$paginate = null;
		if (preg_match('/\/([a-z]?[0-9]+)*$/i', '/'.$page_alias, $page_params)) {
			$page_id = CMS::getPageAliasParam('i', $page_alias);
			$paginate = CMS::getPageAliasParam('p', $page_alias);
		}
		if (count($page_params)==0) $page_params[0] = array();

		// FILTER_DATA
		$filter_data = array('status_id' => '10');

		if (empty($page_alias) AND empty($page_id)) {
			// GET START ID
			$data = CMS::getDocuments(CMS::getSettings('default.site_start_id', $lang_id),null, null, $lang_id, $filter_data, $resource_types_list);
			
			// REDIRECT TO HOME PAGE
			if (count($data) > 0) {
				$this->request->redirect($this->base_url.$data[0]['full_alias'], 301);
			}
		} else {
			// GET BY ALIAS
			$data = CMS::getDocuments($page_id, null, $page_alias, $lang_id, $filter_data, $resource_types_list);
			
			// REDIRECT LEVEL UP IF PAGE ID SET
			if (!empty($page_id) AND count($data) == 0) {
				$data = CMS::getDocuments(null, null, $page_alias, $lang_id, $filter_data, $resource_types_list);
				
				// REDIRECT IF NO PLUGIN CONTROLLER
				if (count($data) > 0 AND $data[0]['plugin_controller'] != '1') {
					$this->request->redirect($this->base_url.$data[0]['full_alias'], 301);
				}
			}
		} 

		if (!isset($data[0]['admin_id'])) {
			// GET ERROR ID
			$data = CMS::getDocuments(CMS::getSettings('default.site_error_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
			if (!isset($data[0]['admin_id'])) {
				// GET START ID
				$data = CMS::getDocuments(CMS::getSettings('default.site_start_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
				if (!isset($data[0]['admin_id'])) {
					// GET DEFAULT LANGUAGE
					$lang_data = CMS::getLanguages(CMS::getSettings('default.lang_id'), null, 10);
					
					$lang_tag = $lang_data[0]['tag'];
					$lang_id = $lang_data[0]['id'];
					
					// GET ERROR ID
					$data = CMS::getDocuments(CMS::getSettings('default.site_error_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
					if (!isset($data[0]['admin_id'])) {
						// GET START ID
						$data = CMS::getDocuments(CMS::getSettings('default.site_start_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
						
						// REDIRECT TO HOME PAGE
						if (count($data) > 0) {
							$this->request->redirect($this->base_url.$data[0]['full_alias'], 301);
						}	
					} else {
						// REDIRECT TO HOME PAGE
						if (count($data) > 0) {
							$this->request->redirect($this->base_url.$data[0]['full_alias'], 301);
						}
					}
				} else {
					// REDIRECT TO HOME PAGE
					if (count($data) > 0) {
						$this->request->redirect($this->base_url.$data[0]['full_alias'], 301);
					}
				}					
			} else {
				// REDIRECT TO ERROR PAGE
				if (count($data) > 0) {
					$this->request->redirect($this->base_url.$data[0]['full_alias'], 301);
				}
			}
		}		
		
		if (count($data) > 0) {
			$this->tpl->page = $data[0];
		} else {
			// GET DEFAULT PAGE
			$this->tpl->page = array();
		}	
		
		// RETURN URL		
		$this->return_url = $this->session->get('return_url');
		$this->current_lang_id = $this->session->get('current_lang_id');

		if (($lang_id == $this->current_lang_id OR empty($this->return_url)) AND $this->request->referrer() != $this->base_url.$this->request->param('alias')) {
			$this->return_url = $this->request->referrer();			
			$this->session->set('return_url', $this->return_url); 
		}
		$this->session->set('current_lang_id', $lang_id); 
		
		// SETTINGS
		$settings_val['site_name'] = CMS::getSettings('default.site_name', $lang_tag);
		$settings_val['keywords'] = CMS::getSettings('seo.keywords', $lang_tag);
		$settings_val['description'] = CMS::getSettings('seo.description', $lang_tag);
		$this->tpl->settings = $settings_val;	
		
		// RESET PRODUCT FILTER
		if ($data[0]['id'] != CMS::$products_page_id) $this->session->delete('product_filter');
		
		if (isset($data[0]['content_type_id']) && $data[0]['content_type_id'] == '1') {
			//
			// SESSION LANG DATA
			//

			$this->session->set('lang_id',$lang_id);
			$this->session->set('lang_tag',$lang_tag);
			
			$this->lang_id = $lang_id;
			I18n::lang('site-'.$lang_tag);
			
			// SET LOCALE
			SYSTEM::setLocale($lang_tag);
		
			
			//
			// PARAMS
			//
			$this->tpl->lang_tag = $lang_tag;
			
			// GET PARENT LIST			
			$parents_data = CMS::getPageParents($data[0]['id']);			
			$this->tpl->page['root_id'] = isset($parents_data[0])?$parents_data[0]:null;
			$this->tpl->page['root_root_id'] = isset($parents_data[1])?$parents_data[1]:null;
			
			//
			// PLUGIN PARAMETERS
			//
			$this->tpl->plugin_data = array();
			$this->tpl->plugin_data['data'] = $data;
			$this->tpl->plugin_data['parents_data'] = $parents_data;
			$this->tpl->plugin_data['page_params'] = $page_params[0];
			$this->tpl->plugin_data['class'] = $this;	
			
			// CART NR
			$this->orders = Model::factory('manager_orders_orders');
			$order = $this->orders->getOrders(null, $this->user_id, array('status_id' => '1'));
			$total_data = $this->orders->getOrderTotal($order[0]['id']);
			$this->tpl->cart_qty = $total_data['item_qty'];
					
			
			//
			// PARSE PAGE
			//
			$page = $this->tpl->render($data[0]['tpl_name']);
			$this->tpl->set_filename($data[0]['tpl_name']);
			
			//
			// REPLACE ALL PAGE WITH TEMPLATE FROM PLUGIN
			//
			if (isset($this->new_template)) $page = $this->new_template;

			/*
			 * REPLACE META DATA
			 */
			if (isset($this->meta['title'])) $page = preg_replace('~<title>[^::]*(::|</title>)~', '<title>'.htmlspecialchars($this->meta['title']).' $1', $page, 1);
			if (isset($this->meta['description'])) $page = preg_replace('~<meta name="description" content=".*" />~', '<meta name="description" content="'.htmlspecialchars($this->meta['description']).'" />', $page, 1);
			
			if (isset($this->meta['og_url'])) $page = preg_replace('~<meta property="og:url" content=".*" />~', '<meta property="og:url" content="'.htmlspecialchars($this->meta['og_url']).'" />', $page, 1);
			if (isset($this->meta['og_title'])) $page = preg_replace('~<meta property="og:title" content=".*" />~', '<meta property="og:title" content="'.htmlspecialchars($this->meta['og_title']).'" />', $page, 1);
			if (isset($this->meta['og_description'])) $page = preg_replace('~<meta property="og:description" content=".*" />~', '<meta property="og:description" content="'.htmlspecialchars($this->meta['og_description']).'" />', $page, 1);
			if (isset($this->meta['og_image'])) $page = preg_replace('~<meta property="og:image" content=".*" />~', '<meta property="og:image" content="'.htmlspecialchars($this->meta['og_image']).'" />', $page, 1);
			
			//
			// DISABEL IE6
			//
			/*
			$ie_js = '
				<!--[if lt IE 9]>
					<script type="text/javascript">
						document.body.innerHTML = "<div style=\"width: 200px; height: 150px; margin: auto; border: 1px;\">".CMS::getLexicons('default.update_browser')."</div>";
					</script>
				<![endif]-->';
			$page = str_replace('<body>', '<body>'.$ie_js, $page);
			*/
			
			//
			// ADD CSS FILES
			//
			$added_array = array();
			if(strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox')) $this->tpl->css_file[] = 'assets/templates/global/firefox.css';
			for($i=0; $i<count($this->tpl->css_file); $i++) {
				if (!in_array($this->tpl->css_file[$i], $added_array)) {
					$added_array[] = $this->tpl->css_file[$i];
					if (substr(trim($this->tpl->css_file[$i]), 0, 5) != '<link')
						$page = str_replace('</head>', '<link rel="stylesheet" type="text/css" media="" href="'.CMS::mtime($this->tpl->css_file[$i]).'"/></head>', $page);
					else
						$page = str_replace('</head>', $this->tpl->css_file[$i].'</head>', $page);
				}
			}
			
			//
			// ADD JS FILES
			//
			
			// JS FILE ADDONS
			$this->tpl->js_file = $this->getJSAddons($this->tpl->js_file, $lang_id, $lang_tag);
			
			$added_array = array();
			for($i=0; $i<count($this->tpl->js_file); $i++) {
				if (!in_array($this->tpl->js_file[$i], $added_array)) {
					$added_array[] = $this->tpl->js_file[$i];
					if (substr(trim($this->tpl->js_file[$i]), 0, 7) != '<script')
						$page = str_replace('</head>', "<script type='text/javascript' src='". CMS::mtime($this->tpl->js_file[$i])."'></script></head>", $page);
					else 
						$page = str_replace('</head>', $this->tpl->js_file[$i].'</head>', $page);
				}				
			}			
			
			echo $page;	
						
		} elseif (isset($data[0]['content_type_id']) && $data[0]['content_type_id'] == '2') {
			// REDIRECT
			if ((int)$data[0]['redirect_link'] && (int)$data[0]['redirect_link']!=0) {
				// LOCAL REDIRECT
				$data = CMS::getDocuments((int)$data[0]['redirect_link'], null, null, $lang_id, $filter_data, $resource_types_list);
				
				if (!isset($data[0]['admin_id'])) {
					// GET ERROR ID
					$data = CMS::getDocuments ($this->getSettings('default.site_error_id', $lang_id),null, null, $lang_id, $filter_data, $resource_types_list);
					if (!isset($data[0]['admin_id'])) {
						// GET START ID
						$data = CMS::getDocuments ($this->getSettings('default.site_start_id', $lang_id),null, null, $lang_id, $filter_data, $resource_types_list);
						if (!isset($data[0]['admin_id'])) {
							// GET DEFAULT LANGUAGE
							$lang_data = CMS::getLanguages($this->getSettings('default.lang_id', null), null, 10);
							
							$lang_tag = $lang_data[0]['tag'];
							$lang_id = $lang_data[0]['id'];
							
							// GET ERROR ID
							$data = CMS::getDocuments ($this->getSettings('default.site_error_id', $lang_id),null, null, $lang_id, $filter_data, $resource_types_list);
							if (!isset($data[0]['admin_id'])) {
								// GET START ID
								$data = CMS::getDocuments ($this->getSettings('default.site_start_id', $lang_id),null, null, $lang_id, $filter_data, $resource_types_list);
							}
						}						
					}
				}	

				$this->request->redirect($lang_tag."/".$data[0]['alias'], 301);
			} else {
				$link = $data[0]['redirect_link'];
				if (!(strtolower(substr($link, 0, 7)) == 'http://' || strtolower(substr($link, 0, 8)) == 'https://')) $link = $this->base_url_protocol.$data[0]['redirect_link'];
				$this->request->redirect($link, 301);
			}
		} elseif (isset($data[0]['content_type_id']) && $data[0]['content_type_id'] == '3') {
			// REDIRECT TO FIRST SUB PAGE
			$data = CMS::getDocuments(null, $data[0]['id'], null, $lang_id, $filter_data, $resource_types_list, " pages.order_index ", $limit = ' LIMIT 1 ');
			
			if (!isset($data[0]['admin_id'])) {
				// GET ERROR ID
				$data = CMS::getDocuments (CMS::getSettings('default.site_error_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
				if (!isset($data[0]['admin_id'])) {
					// GET START ID
					$data = CMS::getDocuments (CMS::getSettings('default.site_start_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
					if (!isset($data[0]['admin_id'])) {
						// GET DEFAULT LANGUAGE
						$lang_data = CMS::getLanguages(CMS::getSettings('default.lang_id'), null, 10);
						
						$lang_tag = $lang_data[0]['tag'];
						$lang_id = $lang_data[0]['id'];
						
						// GET ERROR ID
						$data = CMS::getDocuments (CMS::getSettings('default.site_error_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
						if (!isset($data[0]['admin_id'])) {
							// GET START ID
							$data = CMS::getDocuments (CMS::getSettings('default.site_start_id', $lang_tag),null, null, $lang_id, $filter_data, $resource_types_list);
						}
					}						
				}
			}	

			$this->request->redirect($data[0]['full_alias'], 301);
		}			
	}
	
	private function getJSAddons($js_file, $lang_id, $lang_tag) {
		// SETTINGS JS
		$js_file[] = '
			<script type="text/javascript">
				var lang_id = '.$lang_id.';
				var base_url = "'.URL::base(TRUE, FALSE).'";
				var base_path = "'.URL::base(FALSE, FALSE).'";
			</script>
		';
			
		// GOOGLE ANALYSTIC JS
		if (CMS::getSettings('google.analystic_enabled', $lang_tag) != '0') {
			$google_analystic_key = CMS::getSettings('google.analystic_key', $lang_tag);
			if (!empty($google_analystic_key) AND $google_analystic_key != 'UA-00000000-00') {
				$js_file[] = '
					<script type="text/javascript">
						var _gaq = _gaq || [];
						_gaq.push(["_setAccount", "'.$google_analystic_key.'"]);
						_gaq.push(["_trackPageview"]);
					
						(function() {
							var ga = document.createElement("script"); 
							ga.type = "text/javascript"; 
							ga.async = true;
							ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
							var s = document.getElementsByTagName("script")[0]; 
							s.parentNode.insertBefore(ga, s);
						})();					
					</script>';
			}
		}

		// CRON JOBS
		$this->cron = Model::factory('site_cron');
		if ($this->cron->chk_manual_cron()) {
			$js_file[] = ' <script type="text/javascript"> $.post(base_url+"plugins/cron/jobs"); </script> ';
		}
		
		return $js_file;
	}
}