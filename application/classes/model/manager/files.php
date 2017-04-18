<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_files extends Model {	
	public function update_image($id, $folder_id, $image_src, $type_id = array('1'), $filed_name = 'image_src', $lang_id = null) {
		$base_path = DOCROOT;
		$base_url = URL::base(FALSE, FALSE);		
		
		// MODEL
		$this->resources = Model::factory('manager_resources');
		
		$files_path = $base_path.'files/resources/'.$folder_id.'/';
		
		// REMOVE thumb
		$image_src = preg_replace('/\/thumb_([^\/]*)$/i', '/$1', $image_src);

		// GET FILE INFO
		if (preg_match('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', $image_src))
			$image_src = preg_replace('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', '', $image_src);
		else
			$image_src = preg_replace('/^'.str_replace('/', '\/', $base_url).'/i', '', $image_src);
		$file_name = $base_path.$image_src;
		$info = pathinfo($file_name);		
		$thumb_file_name = $info['dirname'].'/thumb_'.$info['basename'];
		
		// REMOVE DOC IMAGE
		if (trim($image_src) == '') {
			$doc = $this->resources->getDocuments($id, null, null, null, null, $type_id);
			$old_image_src = is_null($lang_id)?$doc[0][$filed_name]:$doc[0]['lang'][$lang_id][$filed_name];
			if (count($doc) > 0 AND preg_replace('/^('.str_replace('/', '\/', str_replace('\\', '\\\\', $base_path)).')/i', '', $old_image_src) != '') {
				if (substr($old_image_src, 0, 5) == 'files') {
					$cur_file_name = $base_path.$old_image_src;
					$cur_info = pathinfo($cur_file_name);
				
					$cur_thumb_file_name = $cur_info['dirname'].'/thumb_'.$cur_info['basename'];			
					
					if (file_exists($cur_file_name)) unlink($cur_file_name);
					if (file_exists($cur_thumb_file_name)) unlink($cur_thumb_file_name);
					
					// REMOVE EMPTY DIRECROY
					if (count(glob($cur_info['dirname'].'/*')) == 0) {
						if (file_exists($cur_info['dirname'])) rmdir($cur_info['dirname']);
					}
				}
			}
		}
		
		// REMOVE CURRENT IMAGE IF NEW IS TMP
		if (substr($info['dirname'], -3) == 'tmp') {			
			$doc = $this->resources->getDocuments($id, null, null, null, null, $type_id);
			$old_image_src = is_null($lang_id)?$doc[0][$filed_name]:$doc[0]['lang'][$lang_id][$filed_name];
			if (count($doc) > 0 AND preg_replace('/^('.str_replace('/', '\/', str_replace('\\', '\\\\', $base_path)).')/i', '', $old_image_src) != '') {
				if (substr($old_image_src, 0, 5) == 'files') {
					$cur_file_name = $base_path.$old_image_src;
					$cur_info = pathinfo($cur_file_name);
				
					$cur_thumb_file_name = $cur_info['dirname'].'/thumb_'.$cur_info['basename'];			
					
					if (file_exists($cur_file_name)) unlink($cur_file_name);
					if (file_exists($cur_thumb_file_name)) unlink($cur_thumb_file_name);
				}
			}
		
			// CREATE DIR
			if (!file_exists($files_path)) mkdir($files_path, 0777); 
		
			// MOVE IMAGE	
			$targetFile = $files_path.$info['basename'];
			$orginal_file_name = $targetFile;
			
			$i = 0;
			$tail = (strlen($info['extension'])+1) * (-1);
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
			}		
			// UPDATE LINK
			$image_src_upd = $targetFile;
						
			// MOVE FILE FROM TMP TO RESOURCE
			if (file_exists($file_name)) rename($file_name, $targetFile);
			
			// MOVE IMAGETUMBNAIL
			$targetFile = $files_path.'thumb_'.$info['basename'];
			$orginal_file_name = $targetFile;
			
			$i = 0;
			$tail = (strlen($info['extension'])+1) * (-1);
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
			}		
			if (file_exists($thumb_file_name)) rename($thumb_file_name, $targetFile);
		} else {
			$image_src_upd = $file_name;
		}
		
		return preg_replace('/^'.str_replace('/', '\/', str_replace('\\', '\\\\', $base_path)).'/i', '', $image_src_upd);		
	}

	public function update_image2($folder_path, $image_src, $old_image_src = '') {
		$base_path = DOCROOT;
		$base_url = URL::base(FALSE, FALSE);		
		
		if (substr($folder_path, 0, 1) == '/') $folder_path = substr($folder_path, 1);
		$files_path = $base_path.$folder_path;
		if (substr($files_path, -1) != '/') $files_path = $files_path.'/';
		
		// REMOVE thumb
		$image_src = preg_replace('/\/thumb_([^\/]*)$/i', '/$1', $image_src);

		// GET FILE INFO
		if (preg_match('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', $image_src))
			$image_src = preg_replace('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', '', $image_src);
		else
			$image_src = preg_replace('/^'.str_replace('/', '\/', $base_url).'/i', '', $image_src);
			
		if (preg_match('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', $old_image_src))
			$old_image_src = preg_replace('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', '', $old_image_src);
		else
			$old_image_src = preg_replace('/^'.str_replace('/', '\/', $base_url).'/i', '', $old_image_src);
		
		$file_name = $base_path.$image_src;
		$info = pathinfo($file_name);		
		$thumb_file_name = $info['dirname'].'/thumb_'.$info['basename'];

		// REMOVE DOC IMAGE
		if (trim($old_image_src) != '' AND (trim($image_src) == '' OR trim($image_src) != trim($old_image_src))) {
			if (substr($old_image_src, 0, 5) == 'files') {
				$cur_file_name = $base_path.$old_image_src;
				$cur_info = pathinfo($cur_file_name);
			
				$cur_thumb_file_name = $cur_info['dirname'].'/thumb_'.$cur_info['basename'];			
				
				if (file_exists($cur_file_name)) unlink($cur_file_name);
				if (file_exists($cur_thumb_file_name)) unlink($cur_thumb_file_name);
				
				// REMOVE EMPTY DIRECROY
				if (count(glob($cur_info['dirname'].'/*')) == 0) {
					if (file_exists($cur_info['dirname'])) rmdir($cur_info['dirname']);
				}
			}
		}
		
		// REMOVE CURRENT IMAGE IF NEW IS TMP
		if (substr($info['dirname'], -3) == 'tmp') {					
			// CREATE DIR
			if (!file_exists($files_path)) {
				$file_parent_path = substr($files_path, 0, strrpos($files_path, '/', -2));
				if (!file_exists($file_parent_path)) mkdir($file_parent_path, 0777); 
				mkdir($files_path, 0777); 
			}
		
			// MOVE IMAGE	
			$targetFile = $files_path.$info['basename'];
			$orginal_file_name = $targetFile;
			
			$i = 0;
			$tail = (strlen($info['extension'])+1) * (-1);
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
			}		
			// UPDATE LINK
			$image_src_upd = $targetFile;
						
			// MOVE FILE FROM TMP TO RESOURCE
			if (file_exists($file_name)) rename($file_name, $targetFile);
			
			// MOVE IMAGE TUMBNAIL
			$targetFile = $files_path.'thumb_'.$info['basename'];
			$orginal_file_name = $targetFile;
			
			$i = 0;
			$tail = (strlen($info['extension'])+1) * (-1);
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
			}		
			if (file_exists($thumb_file_name)) rename($thumb_file_name, $targetFile);
		} else {
			$image_src_upd = $file_name;
		}
		
		return preg_replace('/^'.str_replace('/', '\/', str_replace('\\', '\\\\', $base_path)).'/i', '', $image_src_upd);		
	}

	public function update_file($folder_path, $file_src, $old_file_src = '') {
		$base_path = DOCROOT;
		$base_url = URL::base(FALSE, FALSE);		
		
		if (substr($folder_path, 0, 1) == '/') $folder_path = substr($folder_path, 1);
		$files_path = $base_path.$folder_path;
		if (substr($files_path, -1) != '/') $files_path = $files_path.'/';
		
		// GET FILE INFO
		if (preg_match('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', $file_src))
			$file_src = preg_replace('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', '', $file_src);
		else
			$file_src = preg_replace('/^'.str_replace('/', '\/', $base_url).'/i', '', $file_src);
			
		if (preg_match('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', $old_file_src))
			$old_file_src = preg_replace('/^'.str_replace('/', '\/', URL::base(TRUE, FALSE)).'/i', '', $old_file_src);
		else
			$old_file_src = preg_replace('/^'.str_replace('/', '\/', $base_url).'/i', '', $old_file_src);
		
		$file_name = $base_path.$file_src;
		$info = pathinfo($file_name);		
		
		// REMOVE DOC IMAGE
		if (trim($old_file_src) != '' AND (trim($file_src) == '' OR trim($file_src) != trim($old_file_src))) {
			if (substr($old_file_src, 0, 5) == 'files') {
				$cur_file_name = $base_path.$old_file_src;
				$cur_info = pathinfo($cur_file_name);
			
				if (file_exists($cur_file_name)) unlink($cur_file_name);
				
				// REMOVE EMPTY DIRECROY
				if (count(glob($cur_info['dirname'].'/*')) == 0) {
					if (file_exists($cur_info['dirname'])) rmdir($cur_info['dirname']);
				}
			}
		}
		
		// REMOVE CURRENT IMAGE IF NEW IS TMP
		if (substr($info['dirname'], -3) == 'tmp') {					
			// CREATE DIR
			if (!file_exists($files_path)) {
				$file_parent_path = substr($files_path, 0, strrpos($files_path, '/', -2));
				if (!file_exists($file_parent_path)) mkdir($file_parent_path, 0777); 
				mkdir($files_path, 0777); 
			}
		
			// MOVE IMAGE	
			$targetFile = $files_path.$info['basename'];
			$orginal_file_name = $targetFile;
			
			$i = 0;
			$tail = (strlen($info['extension'])+1) * (-1);
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
			}		
			// UPDATE LINK
			$file_src_upd = $targetFile;
						
			// MOVE FILE FROM TMP TO RESOURCE
			if (file_exists($file_name)) rename($file_name, $targetFile);
		} else {
			$file_src_upd = $file_name;
		}
		
		return preg_replace('/^'.str_replace('/', '\/', str_replace('\\', '\\\\', $base_path)).'/i', '', $file_src_upd);		
	}

	public function destroy_dir($dir) {
		$mydir = opendir($dir);
		while(false !== ($file = readdir($mydir))) {
			if($file != "." && $file != "..") {
				if(is_dir($dir.$file)) {
					chdir('.');
					$this->destroy_dir($dir.$file.'/');
					rmdir($dir.$file) or DIE("couldn't delete $dir$file<br />");
				}
				else
					unlink($dir.$file) or DIE("couldn't delete $dir$file<br />");
			}
		}
		closedir($mydir);
	}
	
	public function deleteFile($file_path) {
		$base_path = DOCROOT;
		
		if (trim($file_path) != '') {
			$cur_file_name = $base_path.$file_path;
			$cur_info = pathinfo($file_path);
			
			if (substr($file_path, 0, 5) == 'files') {
				$cur_thumb_file_name = $cur_info['dirname'].'/thumb_'.$cur_info['basename'];			
				
				if (file_exists($cur_file_name)) unlink($cur_file_name);
				if (file_exists($cur_thumb_file_name)) unlink($cur_thumb_file_name);
				
				// REMOVE EMPTY DIRECROY
				if (count(glob($cur_info['dirname'].'/*')) == 0) {
					if (file_exists($cur_info['dirname'])) rmdir($cur_info['dirname']);
				}
			}
		}
	}
	
	public function copyFile($from_file_path, $to_folder) {
		$base_path = DOCROOT;
		
		if (trim($from_file_path) != '' AND trim($to_folder) != '') {
			$cur_file_name = $base_path.$from_file_path;
			$cur_info = pathinfo($from_file_path);
			
			if (substr($to_folder, -1, 1) != '/') $to_folder .= '/';
			
			// CREATE DIR IF NOT EXISTS
			if (!file_exists($base_path.$to_folder)) mkdir($base_path.$to_folder, 0777);
			
			if (substr($from_file_path, 0, 5) == 'files') {
				$cur_thumb_file_name = $cur_info['dirname'].'/thumb_'.$cur_info['basename'];			
				
				if (file_exists($cur_file_name)) copy($cur_file_name, $base_path.$to_folder.$cur_info['basename']);
				if (file_exists($cur_thumb_file_name)) copy($cur_thumb_file_name, $base_path.$to_folder.'thumb_'.$cur_info['basename']);
			}
			
			return $to_folder.$cur_info['basename'];
		} else {
			return false;
		}
	}
 
 	public function copyToTmp($from_file_path) {
 		$base_path = DOCROOT;
		
		if (trim($from_file_path) != '') {
			$cur_file_name = $base_path.$from_file_path;
			$cur_info = pathinfo($from_file_path);
			
			$to_folder = 'files/tmp/';
			// CREATE DIR IF NOT EXISTS
			if (!file_exists($base_path.$to_folder)) mkdir($base_path.$to_folder, 0777);
			
			if (substr($from_file_path, 0, 5) == 'files') {
				$cur_thumb_file_name = $cur_info['dirname'].'/thumb_'.$cur_info['basename'];			
				
				if (file_exists($cur_thumb_file_name)) {
					$orginal_file_name = $targetFile = $to_folder.'thumb_'.$cur_info['basename'];
					
					$i = 0;
					$tail = (strlen($cur_info['extension'])+1) * (-1);
					while (file_exists($base_path.$targetFile)) {
						$i++;
						$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $cur_info['extension'];
					}		
					
					copy($cur_thumb_file_name, $base_path.$targetFile);
				}
				
				if (file_exists($cur_file_name)) {
					$orginal_file_name = $targetFile = $to_folder.$cur_info['basename'];
					
					$i = 0;
					$tail = (strlen($cur_info['extension'])+1) * (-1);
					while (file_exists($base_path.$targetFile)) {
						$i++;
						$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $cur_info['extension'];
					}		
					
					copy($cur_file_name, $base_path.$targetFile);
				}				
			}
			
			return $targetFile;
		} else {
			return false;
		}
 	}
}