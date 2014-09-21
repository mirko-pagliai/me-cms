<?php
$config = array(
	//Backend options
	'backend' => array(
		//ID of the default users group
		'default_group' => 3,
		//Number of photos to show per page. This must be a multiple of 4
		'photos_for_page' => 12,
		//Number of records to show per page
		'records_for_page' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE
	),
	//Frontend options
	'frontend' => array(
		//Site logo. Relative path to `APP/webroot/img/`
		'logo' => 'logo.png',
		//Number of records to show per page
		'records_for_page' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Number of characters to truncate a text
		'truncate_to' => 1500,
		//Widgets
		'widgets' => array('search', 'categories', 'latest_posts', 'random_photo', 'pages')
	),
	//General options
	'general' => array(
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
		//Session (login) timeout, in minutes
		'timeout' => 30,
		//Site title
		'title' => 'MeCms'
	)
);