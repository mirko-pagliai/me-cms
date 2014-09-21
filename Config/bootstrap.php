<?php

//Adds the admin routes prefix
Configure::write('Routing.prefixes', array('admin'));

//Default options
$options = array(
	'duration'	=> '+999 days',
	'engine'	=> 'File',
	'mask'		=> 0666,
	'path'		=> CACHE.'mecms'.DS,
	'prefix'	=> NULL
);

Cache::config('pages', am($options, array('groups' => array('pages'))));
Cache::config('photos', am($options, array('groups' => array('photos'))));
Cache::config('posts', am($options, array('groups' => array('posts'))));

//Default cache configuration for MeCms
Cache::config('default', $options);