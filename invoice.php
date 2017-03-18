<?php
/*
 * GET BASE URL
 */
$url_data = explode('/', $_SERVER['REQUEST_URI']);
$base_url = '';
$id_num = 0;
for ($i=0; $i<count($url_data); $i++) {
	if (mb_strtolower($url_data[$i]) == 'files' && mb_strtolower($url_data[$i+1]) == 'orders') {
		$id_num = $i+2;
		break;
	} else {
		if (!empty($url_data[$i])) $base_url .= '/'.$url_data[$i];
	}
}

/*
 * GET REDIRECT URL
 */
if (isset($url_data[$id_num])) {
	$file_data = explode('-', $url_data[$id_num]);
	$type = mb_strtolower(isset($file_data[0])?$file_data[0]:null);
	$order_id = mb_substr(isset($file_data[1])?$file_data[1]:'', 0, strpos(isset($file_data[1])?$file_data[1]:'', '.'));
 
	if (!empty($type) && is_numeric($order_id)) {
		if ($type == 'invoice') $type = 'bill';
		if ($type == 'pavadzime') $type = 'invoice';
		$redirect_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'].$base_url.'/plugins/orders/'.$type.'/'.$order_id;
	} else {
		$redirect_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'].$base_url;
	}
}

/*
 * REDIRECT
 */
header('Location: '.$redirect_url);
