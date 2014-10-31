<?php
$config = array('MeCms' => array(
	//Backend options
	'backend' => array(
		//ID of the default users group
		'default_group' => 3,
		//Number of photos to show per page. This must be a multiple of 4
		'photos_for_page' => 12,
		//Number of records to show per page
		'records_for_page' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Topbar menus
		'topbar' => 'posts, pages, photos, users, banners, systems'
	),
	//Frontend options
	'frontend' => array(
		//Google Analytics ID or FALSE
		'analytics' => FALSE,
		//Fancybox for photos
		'fancybox' => TRUE,
		//Site logo. Relative path to `APP/webroot/img/`
		'logo' => 'logo.png',
		//Number of records to show per page
		'records_for_page' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Number of characters to truncate a text
		'truncate_to' => 1000,
		//Widgets
		'widgets' => 'search, categories, latest_posts, random_photo, pages'
	),
	//General options
	'general' => array(
		//Cache (enabled or disabled)
		'cache' => FALSE,
		//Date formats
		'date' => array(
			//Long format
			'long'	=> '%Y/%m/%d',
			//Short format
			'short'	=> '%y/%m/%d',
		),
		//Datetime formats
		'datetime' => array(
			//Long format
			'long'	=> '%Y/%m/%d, %H:%M',
			//Short format
			'short'	=> '%y/%m/%d, %H:%M'
		),
		//Debug (enabled or disabled)
		'debug' => FALSE,
		//Session (login) timeout, in minutes
		'timeout' => 30,
		//Site title
		'title' => 'MeCms'
	)
));