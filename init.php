<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Do some bootstrapping for the module
 */
Route::set('register_config', 'v_config/registerconfig')
	->defaults(array(
		'controller' 	=> 'registerconfig',
		'action'		=> 'index'
));