<?php
$config = array('MeCms' => array(
	//Backend options
	'backend' => array(
		//ID of the default users group
		'default_group' => 3,
		//KCFinder
		'kcfinder' => array(
			//KCFinder types. See http://kcfinder.sunhater.com/install#_types
			'types' => array('images' => '*img')
		),
		//Number of photos to show per page. This must be a multiple of 4
		'photos_for_page' => 12,
		//Number of records to show per page
		'records_for_page' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Topbar menus
		'topbar' => 'posts, pages, photos, banners, users, systems'
	),
	//Frontend options
	'frontend' => array(
		//Google Analytics ID or FALSE
		'analytics' => FALSE,
		//Fancybox for photos
		'fancybox' => TRUE,
		//Site logo. Relative path to `APP/webroot/img/`
		'logo' => 'logo.png',
		//Sets the site offline
		'offline' => FALSE,
		//Text to display when the site is offline
		'offline_text' => FALSE,
		//Number of records to show per page
		'records_for_page' => 10,
		//Interval between searches, in seconds. Set to `0` or `FALSE` to disable
		'search_interval' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Number of characters to truncate a text. `0` or `FALSE` to disable
		'truncate_to' => 1000,
		//Widgets. You can use the plugin notation (eg., `PluginName.widgetName`)
		'widgets' => 'MeCms.search, MeCms.categories, MeCms.latest_posts, MeCms.random_photo, pages',
		//Specific widgets for the homepage. If this is set to "FALSE", will be used the default widget
		'widgets_homepage' => FALSE
	),
	//General options
	'general' => array(
		//Cache (enabled or disabled)
		'cache' => TRUE,
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
		//It forces debugging for the localhost (enabled or disabled)
		'debug_on_localhost' => TRUE,
		//Configuration to use for emails, located into `APP/Config/email.php`
		'email_config' => 'default',
		//Address from which to send emails
		'email_from' => 'email@example.com',
		//Session (login) timeout, in minutes
		'timeout' => 30,
		//Site title
		'title' => 'MeCms',
		//Users can signup
		'users_can_signup' => TRUE,
		//Default users group (ID)
		'users_default_group' => 3,
		//How to activating accounts
		//	`0` - No activation required, the account is immediately active
		//	`1` - The account will be enabled by the user via email (default)
		//	`2`	- The account will be enabled by an administrator
		'users_need_to_be_enabled' => 1
	)
));