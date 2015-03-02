<?php
$config = array('MeCms' => array(
	//Backend
	'backend' => array(
		//Number of photos to show per page. This must be a multiple of 4
		'photos' => 12,
		//Number of records to show per page
		'records' => 10,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Topbar menus.
		//They may to be indicated as an array or as a string (separated by commas)
		'topbar' => array('posts', 'pages', 'photos', 'banners', 'users', 'systems')
	),
	//Email
	'email' => array(
		//Configuration, located into `APP/Config/email.php`
		'config' => 'default',
		//Address from which to send emails
		'from' => 'email@example.com'
	),
	//Frontend
	'frontend' => array(
		//Google Analytics ID or FALSE
		'analytics' => FALSE,
		//Fancybox for photos
		'fancybox' => TRUE,
		//Site logo. Relative path to `APP/webroot/img/`
		'logo' => 'logo.png',
		//Site offline (enabled or disabled)
		'offline' => FALSE,
		//Text to display when the site is offline
		'offline_text' => FALSE,
		//Number of records to show per page
		'records' => 10,
		//Number of records to show on RSS
		'records_for_rss' => 20,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Number of characters to truncate a text. `0` or `FALSE` to disable
		'truncate_to' => 1000,
		//Widgets. You can use the plugin notation (eg., `PluginName.widgetName`).
		//See our wiki: http://github.com/mirko-pagliai/MeCms/wiki/Widgets
		'widgets' => array(
			'MeCms.search_posts', 
			'MeCms.categories', 
			'MeCms.latest_posts' => array('posts' => 10),
			'MeCms.random_photo',
			'MeCms.pages'
		),
		//Specific widgets for the homepage. If this is set to "FALSE", will be used the default widget
		'widgets_homepage' => FALSE
	),
	//KCFinder
	'kcfinder' => array(
		//KCFinder types. See http://kcfinder.sunhater.com/install#_types
		'types' => array('images' => '*img')
	),
	//Main
	'main' => array(
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
		//Site title
		'title' => 'MeCms',
	),
	//Security
	'security' => array(
		//reCAPTCHA (enabled or disabled).
		//It will be used for some actions, such as signup or reset the password
		'recaptcha' => TRUE,
		//Interval between searches, in seconds. Set to `0` or `FALSE` to disable
		'search_interval' => 10,
	),
	//Users
	'users' => array(
		//How to activating accounts
		//	`0` - No activation required, the account is immediately active
		//	`1` - The account will be enabled by the user via email (default)
		//	`2`	- The account will be enabled by an administrator
		'activation' => 1,
		//Login with cookies ("remember me" function)
		'cookies_login' => TRUE,
		//Default users group (ID)
		'default_group' => 3,
		//Reset password (enabled or disabled)
		'reset_password' => TRUE,
		//Signup (enabled or disabled)
		'signup' => TRUE
	)
));