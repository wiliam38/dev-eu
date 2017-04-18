<?php defined('SYSPATH') or die('No direct script access.');

class FILES  {
	static function upload_file($resize_image = true, $md5 = false, $allowedExtensions = array(), $sizeLimit = 62914560) {
		$base_path = DOCROOT;
		
		require_once $base_path.'assets/libs/jquery-plugins/qq.FileUploader/upload.php';
		
		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
		$result = $uploader->handleUpload($base_path.'files/tmp/');
		
		if (isset($result['success']) && $result['success'] == true) {
			$result['files'] = $uploader->getUploadName();
		}
		
		/*
		 * MD5 FILE NAME
		 */
		if ($md5) {
			$file_info = pathinfo($base_path.'files/tmp/'.$result['files']);
			$new_filename = md5_file($base_path.'files/tmp/'.$result['files']);
			rename($base_path.'files/tmp/'.$file_info['filename'].'.'.$file_info['extension'], $base_path.'files/tmp/'.$new_filename.'.'.$file_info['extension']);
			$result['files'] = $new_filename.'.'.$file_info['extension'];
		}
		
		//
		// CREATE NEW SIZE
		//
		if ($resize_image) {
			$targetFile = $base_path.'files/tmp/'.$result['files'];
				
			// NEW SIXE
			$w = 1000;
			$h = 1000;
	
			// open the directory
			$info = pathinfo($targetFile);	
	
			// GET IMAGE SIZE
			$imgInfo = getimagesize($targetFile);
	
			// continue only if this is a JPEG, GIF OR PNG image
			if ($imgInfo AND $imgInfo[2] <= 3 AND $imgInfo[2] >= 1)
			{	   	
				switch ($imgInfo[2]) {
					case 1: 
						// GIF
						$im = imagecreatefromgif($targetFile); 
						break;
					case 2: 
						// JPEG
						$im = imagecreatefromjpeg($targetFile);  
						break;
					case 3: 
						// PNG
						$im = imagecreatefrompng($targetFile); 
						break;
				}
	
				//If image dimension is smaller, do not resize
				if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
					$nHeight = $imgInfo[1];
					$nWidth = $imgInfo[0];
				} else {
					//yeah, resize it, but keep it proportional
					if ($w/$imgInfo[0] < $h/$imgInfo[1]) {
						$nWidth = $w;
						$nHeight = $imgInfo[1]*($w/$imgInfo[0]);
					} else {
						$nWidth = $imgInfo[0]*($h/$imgInfo[1]);
						$nHeight = $h;
					}
				}
	
				$nWidth = round($nWidth);
				$nHeight = round($nHeight);
	
				$newImg = imagecreatetruecolor($nWidth, $nHeight);
	
				/* Check if this image is PNG or GIF, then set if Transparent*/
				if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)) {
					imagealphablending($newImg, false);
					imagesavealpha($newImg,true);
					$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
					imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
				}
				imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);
	
				//Generate the file, and rename it to $newfilename
				unlink($targetFile);
				switch ($imgInfo[2]) {
					case 1: 
						imagegif($newImg,$targetFile); 
						break;
					case 2: 
						imagejpeg($newImg,$targetFile);  
						break;
					case 3: 
						imagepng($newImg,$targetFile); 
						break;
				}
	
			}	
	
	
	
	
			//
			// CREATE TUMB
			//
	
			// NEW SIXE
			$w = 300;
			$h = 300;
	
			// open the directory
			$info = pathinfo($targetFile);	
	
			// GET IMAGE SIZE
			$imgInfo = getimagesize($targetFile);
			
			// continue only if this is a JPEG, GIF OR PNG image
			if ($imgInfo AND $imgInfo[2] <= 3 AND $imgInfo[2] >= 1)
			{	   	
				switch ($imgInfo[2]) {
					case 1: 
						// GIF
						$im = imagecreatefromgif($targetFile); 
						break;
					case 2: 
						// JPEG
						$im = imagecreatefromjpeg($targetFile);  
						break;
					case 3: 
						// PNG
						$im = imagecreatefrompng($targetFile); 
						break;
				}
	
				//If image dimension is smaller, do not resize
				if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
					$nHeight = $imgInfo[1];
					$nWidth = $imgInfo[0];
				} else {
					//yeah, resize it, but keep it proportional
					if ($w/$imgInfo[0] < $h/$imgInfo[1]) {
						$nWidth = $w;
						$nHeight = $imgInfo[1]*($w/$imgInfo[0]);
					} else {
						$nWidth = $imgInfo[0]*($h/$imgInfo[1]);
						$nHeight = $h;
					}
				}
	
				$nWidth = round($nWidth);
				$nHeight = round($nHeight);
	
				$newImg = imagecreatetruecolor($nWidth, $nHeight);
	
				/* Check if this image is PNG or GIF, then set if Transparent*/
				if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)) {
					imagealphablending($newImg, false);
					imagesavealpha($newImg,true);
					$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
					imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
				}
				imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);
	
				//Generate the file, and rename it to $newfilename
				switch ($imgInfo[2]) {
					case 1: 
						imagegif($newImg,$info['dirname'].'/thumb_'.$info['basename']); 
						break;
					case 2: 
						imagejpeg($newImg,$info['dirname'].'/thumb_'.$info['basename']);  
						break;
					case 3: 
						imagepng($newImg,$info['dirname'].'/thumb_'.$info['basename']); 
						break;
				}
	
			}
		}
		
		return htmlspecialchars(json_encode($result), ENT_NOQUOTES);
	}

	static function update_file($folder_path, $file_src, $old_file_src = '', $md5 = false) {
		$base_path = DOCROOT;
		$base_url = URL::base(FALSE, FALSE);		
		
		if (substr($folder_path, 0, 1) == '/') $folder_path = substr($folder_path, 1);
		$files_path = $base_path.$folder_path;
		if (substr($files_path, -1) != '/') $files_path = $files_path.'/';
		
		// REMPVE thumb
		$file_src = preg_replace('/\/thumb_([^\/]*)$/i', '/$1', $file_src);

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
		$thumb_file_name = $info['dirname'].'/thumb_'.$info['basename'];

		// REMOVE DOC IMAGE
		if (trim($old_file_src) != '' AND (trim($file_src) == '' OR trim($file_src) != trim($old_file_src))) {
			if (substr($old_file_src, 0, 5) == 'files') {
				$cur_file_name = $base_path.$old_file_src;
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
			
			if (!$md5) {
				$i = 0;
				$tail = (strlen($info['extension'])+1) * (-1);
				while (file_exists($targetFile)) {
					$i++;
					$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
				}	
			}	
			// UPDATE LINK
			$file_src_upd = $targetFile;
				
			// MOVE FILE FROM TMP TO RESOURCE		
			if ($md5 && file_exists($targetFile)) {
				if (file_exists($file_name)) unlink($file_name);
			} else {
				if (file_exists($file_name)) rename($file_name, $targetFile);
			}
			
			// MOVE IMAGE TUMBNAIL
			$targetFile = $files_path.'thumb_'.$info['basename'];
			$orginal_file_name = $targetFile;
			
			if (!$md5) {
				$i = 0;
				$tail = (strlen($info['extension'])+1) * (-1);
				while (file_exists($targetFile)) {
					$i++;
					$targetFile = substr($orginal_file_name, 0, $tail) . "-".$i."." . $info['extension'];
				}	
			}	
			
			if ($md5 && file_exists($targetFile)) {
				if (file_exists($thumb_file_name)) unlink($thumb_file_name);
			} else {
				if (file_exists($thumb_file_name)) rename($thumb_file_name, $targetFile);
			}
		} else {
			$file_src_upd = $file_name;
		}
		
		return preg_replace('/^'.str_replace('/', '\/', str_replace('\\', '\\\\', $base_path)).'/i', '', $file_src_upd);		
	}

	// DESTROY DIR
	static function destroy_dir($dir) {
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

	// DELETE FILE
	static function deleteFile($file_path) {
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
	
	// COPY FILE
	static function copyFile($from_file_path, $to_folder) {
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
	
	// REMOVE TMP FILE
	static function removeTmpFile($file_name) {
		$base_path = DOCROOT;
		
		if (!empty($file_name)) {
			$file_name = $base_path.$file_name;
			$info = pathinfo($file_name);
			
			$thumb_file_name = $info['dirname'].'/thumb_'.$info['basename'];
			
			if (substr($info['dirname'], -9) == 'files/tmp') {
				if (file_exists($file_name)) unlink($file_name);
				if (file_exists($thumb_file_name)) unlink($thumb_file_name);
			}
		}
	}
 
 	// COPY TO TMP
 	static function copyToTmp($from_file_path) {
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