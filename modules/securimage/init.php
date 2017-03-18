<?php defined('SYSPATH') or die('No direct script access.');

// Catch-all route for Securimage classes to run
Route::set('securimage', 'securimage(/<action>)')
	->defaults(array(
		'controller' => 'securimage',
		'action' => 'show' ));
