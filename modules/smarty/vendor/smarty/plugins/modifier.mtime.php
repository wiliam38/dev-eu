<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty mtime modifier plugin
 */
function smarty_modifier_mtime($file)
{
	$tmp_file = $file;
	if (substr($tmp_file,0,1) == '/') $tmp_file = $_SERVER['DOCUMENT_ROOT'].$tmp_file;	
	if (file_exists($tmp_file)) {
		$mtime = substr(date('ymdHis', filemtime($tmp_file)), 0, -1);
		return $file."?d=".(empty($mtime)?'1':$mtime);
	} else {
		return $file;
	}
} 

?>