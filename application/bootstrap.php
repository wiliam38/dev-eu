<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

// PRODUCTION/DEVELOPMENT
if(isset($_SERVER['SERVER_NAME']) AND in_array($_SERVER['SERVER_NAME'], array('19bar.dev', 'www.19bar.dev')))
    Kohana::$environment = Kohana::DEVELOPMENT;
else
    Kohana::$environment = Kohana::PRODUCTION;

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
ini_set('date.timezone', 'Europe/Riga');
date_default_timezone_set('Europe/Riga');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
//setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}

/**
 * SESSION CONFIG
 */ 
Session::$default = 'database';
Cookie::$salt = 'iss.wbox.cookie.2011';

/**
 * GET BASE URL
 */
$base_url = isset($_SERVER['HTTP_HOST'])?((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')?'https://':'http://').$_SERVER['HTTP_HOST'].substr($_SERVER['SCRIPT_NAME'],0,strpos($_SERVER['SCRIPT_NAME'],'index.php')):'';
if (empty($base_url)) $base_url = '/';

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'   => $base_url,
	'errors' 	=> TRUE,
	'caching'	=> TRUE,
	'index_file' => '',
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
		'auth'       => MODPATH.'auth',       // Basic authentication
	//	'cache'      => MODPATH.'cache',      // Caching with multiple backends
		'database'   => MODPATH.'database',   // Database access
		'mysqli'     => MODPATH.'mysqli',     // MySQLi
	//	'image'      => MODPATH.'image',      // Image manipulation
		'orm'        => MODPATH.'orm',        // Object Relationship Mapping
		'smarty'	 => MODPATH.'smarty',  	  // Smarty
		'email'  	 => MODPATH.'email',  	  // Email
	//	'recaptcha'  => MODPATH.'recaptcha',  // reCaptcha
	//	'captcha'	 => MODPATH.'captcha',	  // Captcha
	//	'securimage' => MODPATH.'securimage', // SecurImage
		'oauth'  	 => MODPATH.'oauth',  	  // OAuth
		'firstdata'  => MODPATH.'firstdata',  // mPDF
		'mpdf'  	 => MODPATH.'mpdf',  	  // mPDF
		'phpexcel'   => MODPATH.'phpexcel',   // PHPExcel
	));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */	
	
Route::set('manager_modules', 'manager/modules(/<controller>(/<action>(/<id>(/<opt>))))')
	->defaults(array(
		'directory'  => 'manager/modules',
		'controller' => 'home',
		'action'     => 'load',
	));	
Route::set('manager', 'manager(/<controller>(/<action>(/<id>(/<opt>))))')
	->defaults(array(
		'directory'  => 'manager',
		'controller' => 'home',
		'action'     => 'load',
	));

Route::set('export', 'export(/<controller>(/<action>(/<lang>)(.<ext>)))')
	->defaults(array(
		'directory'  => 'export',
		'controller' => 'class',
		'action'     => 'load',
		'lang'		 => '',
		'ext'		 => ''
	));

Route::set('plugins', 'plugins(/<controller>(/<action>(/<data>)))')
	->defaults(array(
		'directory'  => 'plugins',
		'controller' => 'class',
		'action'     => 'load',
	));
	
Route::set('page', '(<alias>)',
  array(
    'alias' => '.*',
  ))
  ->defaults(array(
  	'alias' => '',
  	'directory' => 'site',
    'controller' => 'page',
    'action' => 'load',
  ));	
