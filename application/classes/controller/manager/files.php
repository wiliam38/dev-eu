<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager_Files extends Controller {
	
	public function before() {
		parent::before();
		
		$this->doc_root = DOCROOT;
		$this->base_path = DOCROOT;
		$this->base_url = URL::base(FALSE, FALSE);		
		
		if ($_SERVER['HTTP_USER_AGENT']==='Shockwave Flash')
		{
			$sesion_type = Kohana::$config->load('auth.session_type');
			//session_set_cookie_params(Kohana::$config->load('session.'.$sesion_type.'.lifetime'), Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
			//session_cache_limiter(FALSE);
			$session_name = Kohana::$config->load('session.'.$sesion_type.'.name');
			
			//session_id($this->request->post('dbsid'));
			
			$dbsid = $this->request->post('dbsid');				
			if (!empty($dbsid)) $_COOKIE[$session_name] = $dbsid;
		} else {
			$this->session = Session::instance();
			$this->user = Auth::instance();
		}
		
		//echo $this->session->id();
		
		
		//if (Auth::instance()->logged_in() || Auth::instance()->auto_login())
		//{
		//	$this->user = Auth::instance()->get_user();
		//}
		
		// resize: 'no'	
	}
	
	public function action_rm_tmp() {
		if ($this->user->logged_in('manager')) {
			$file_name = $this->request->post('file_name');			
			
			if (!empty($file_name)) {
				$file_name = $this->doc_root.$file_name;
				$info = pathinfo($file_name);
				
				$thumb_file_name = $info['dirname'].'/thumb_'.$info['basename'];
				
				if (substr($info['dirname'], -9) == 'files/tmp') {
					if (file_exists($file_name)) unlink($file_name);
					if (file_exists($thumb_file_name)) unlink($thumb_file_name);
				}			
			}
		}
	}
	
	function action_upload_tmp_files() {					
		if (!empty($_FILES)) {			
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $this->doc_root . 'files/tmp/';	
			$targetFileName = $_FILES['Filedata']["name"];
			if (mb_substr(mb_strtolower($targetFileName), 0, 6) == 'thumb_') $targetFileName = mb_substr($targetFileName, 6);
			$targetFile = str_replace('//','/',$targetPath) . $targetFileName;

			$fileParts  = pathinfo($_FILES['Filedata']['name']);

			$orginal_file = $targetFile;

			$i = 0;
			$tail = (strlen($fileParts['extension'])+1) * (-1);
			while (file_exists($targetFile)) {
				$i++;
				$targetFile = substr($orginal_file, 0, $tail) . "-".$i."." . $fileParts['extension'];
			}

			// if (in_array($fileParts['extension'],$typesArray)) {
			// Uncomment the following line if you want to make the directory if it doesn't exist
			// mkdir(str_replace('//','/',$targetPath), 0755, true);

			move_uploaded_file($tempFile,$targetFile);

			//
			// CREATE NEW SIZE
			//

			// NEW SIXE
			$resize = $this->request->post('resize');
			$w = 1000;
			$h = 1000;

			// open the directory
			$info = pathinfo($targetFile);	

			// GET IMAGE SIZE
			$imgInfo = getimagesize($targetFile);

			if (empty($resize)) {
				// continue only if this is a JPEG, GIF OR PNG image
				if ($imgInfo AND $imgInfo[2] <= 3 AND $imgInfo[2] >= 1) {	   	
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

			echo str_replace($this->doc_root,'',$targetFile);		
			
		}
	}	
}