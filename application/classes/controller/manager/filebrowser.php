<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Filebrowser extends Controller_Main {	
	public $template = 'site/template/tmp';
	
	public function before() {
		parent::before();
	
		// PARAMS
		$this->auto_render = FALSE;
		
		// I18N
		$this->manager_lang = Kohana::$config->load('manager.language');
	}
	
	public function action_open() {
		if ($this->user->logged_in('manager') || $this->user->logged_in('admin')) {
			$tpl_data['base_url'] = $this->base_url;
			$tpl_data['lang_tag'] = $this->manager_lang;		
			$tpl_data['type'] = $this->request->param('id');
			
			echo $this->tpl->factory('manager/filebrowser/filebrowser', $tpl_data)->render();
		}
	}
	
	public function action_load() {
		if ($this->user->logged_in('manager') || $this->user->logged_in('admin')) {
			error_reporting(0); // Set E_ALL for debuging
			
			include_once $this->base_path.'assets/libs/elfinder/php/elFinderConnector.class.php';
			include_once $this->base_path.'assets/libs/elfinder/php/elFinder.class.php';
			include_once $this->base_path.'assets/libs/elfinder/php/elFinderVolumeDriver.class.php';
			include_once $this->base_path.'assets/libs/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
			
			$base_url = !empty($_REQUEST['base_url'])?$_REQUEST['base_url']:'/';
			$type = !empty($_REQUEST['type'])?$_REQUEST['type'].'/':'';			
			
			/**
			 * Simple function to demonstrate how to control file access using "accessControl" callback.
			 * This method will disable accessing files/folders starting from  '.' (dot)
			 *
			 * @param  string  $attr  attribute name (read|write|locked|hidden)
			 * @param  string  $path  file path relative to volume root directory started with directory separator
			 * @return bool|null
			 **/
			function access($attr, $path, $data, $volume) {
				return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
				? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
				:  null;                                    // else elFinder decide it itself
			}
			
			$opts = array(
					'debug' => true,
					'roots' => array(
							array(
									'driver'       	  => 'LocalFileSystem',   							// driver for accessing file system (REQUIRED)
									'path'            => $this->base_path.'files/upload/images/',       // path to files (REQUIRED)
									'URL'             => $this->base_url.'files/upload/images/', 		// URL to files (REQUIRED)
									'uploadOverwrite' => false,
									'uploadAllow'     => array('image'),
									'accessControl'   => 'access'             							// disable and hide dot starting files (OPTIONAL)
							),
							array(
									'driver'        => 'LocalFileSystem',   							// driver for accessing file system (REQUIRED)
									'path'          => $this->base_path.'files/upload/files/',         	// path to files (REQUIRED)
									'URL'           => $this->base_url.'files/upload/files/', 			// URL to files (REQUIRED)
									'accessControl' => 'access'             							// disable and hide dot starting files (OPTIONAL)
							),
							array(
									'driver'        => 'LocalFileSystem',   							// driver for accessing file system (REQUIRED)
									'path'          => $this->base_path.'files/upload/flash/',         	// path to files (REQUIRED)
									'URL'           => $this->base_url.'files/upload/flash/', 			// URL to files (REQUIRED)
									'accessControl' => 'access'            	 							// disable and hide dot starting files (OPTIONAL)
							)
					)
			);
			
			if ($_SERVER["REQUEST_METHOD"] == 'POST') $params = $this->request->post();
			else $params = CMS::getGET();
					
			// run elFinder
			$connector = new elFinderConnector(new elFinder($opts));
			$connector->run($params);
		}
	}
	
	public function action_upload() {
		if ($this->user->logged_in('manager') || $this->user->logged_in('admin')) {
			$type = $this->request->param('id');
			
			if (!empty($_FILES)) {
				$tempFile = $_FILES['upload']['tmp_name'];
				$targetPath = $this->base_path . 'files/upload/'.$type.'/';
				$targetFile = str_replace('//','/',$targetPath) . $_FILES['upload']["name"];				
				if (!file_exists($targetPath)) $targetPath = $this->base_path . 'files/upload/files/';
			
				$fileParts  = pathinfo($_FILES['upload']['name']);
			
				$orginal_file = $targetFile;
			
				$i = 0;
				$tail = (strlen($fileParts['extension'])+1) * (-1);
				while (file_exists($targetFile)) {
					$i++;
					$targetFile = substr($orginal_file, 0, $tail) . "-".$i."." . $fileParts['extension'];
				}
			
				move_uploaded_file($tempFile,$targetFile);
				
				$tpl_data['new_file_name'] = str_replace($this->base_path,'',$targetFile);
				$tpl_data['CKEditorFuncNum'] = CMS::getGET('CKEditorFuncNum');
								
				echo $this->tpl->factory('manager/filebrowser/upload', $tpl_data)->render();
			}
		}
	}
}