<?php
return ['MeCms' => [
	//Backend
	'backend' => [
		//Menus. You can use the plugin notation (eg., `PluginName.widgetName`)
		'menu' => ['MeCms.posts', 'MeCms.pages', 'MeCms.photos', 'MeCms.banners', 'MeCms.users', 'MeCms.systems'],
		//Number of photos to show per page. This must be a multiple of 4
		'photos' => 12,
		//Number of records to show per page
		'records' => 10
	 ],
	//Email
	'email' => [
		//EmailTransport configuration
		'config' => 'default',
		//Address used as the sender for emails sent to users and as a 
		//recipient for the email sent by users
		'webmaster' => 'email@example.com'
	],
	//Frontend
	'frontend' => [
		//Google Analytics ID or FALSE
		'analytics' => FALSE,
		//Contact form (enabled or disabled).
		'contact_form' => TRUE,
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
		//Number of records to show for searches
		'records_for_searches' => 20,
		//Theme. Must be located in `APP/View/Themed/`
		'theme' => FALSE,
		//Number of characters to truncate a text. `0` or `FALSE` to disable
		'truncate_to' => 1000
	],
	//KCFinder
	'kcfinder' => [
		//KCFinder types. See http://kcfinder.sunhater.com/install#_types
		'types' => ['images' => '*img']
	],
	//Main
	'main' => [
		//Date formats
		//See; http://php.net/manual/it/datetime.formats.php
		'date' => [
			//Long format
			'long'	=> 'YYYY/MM/dd',
			//Short format
			'short'	=> 'yy/MM/dd',
		],
		//Datetime formats
		//See; http://php.net/manual/it/datetime.formats.php
		'datetime' => [
			//Long format
			'long'	=> 'YYYY/MM/dd, HH:MM',
			//Short format
			'short'	=> 'yy/MM/dd, HH:MM'
		],
		//Forces debug on localhost (enabled or disabled)
		'debug_on_localhost' => TRUE,
		//Site title
		'title' => 'MeCms',
	],
	//Security
	'security' => [
		//Key used to crypt
		'crypt_key' => 'at1UsdACWJFTXGgf4oZoiLwQGrLgf2SA',
		//reCAPTCHA (enabled or disabled).
		//It will be used for some actions, such as signup or reset the password
		'recaptcha' => TRUE,
		//Interval between searches, in seconds. Set to `0` or `FALSE` to disable
		'search_interval' => 10,
	],
	//Users
	'users' => [
		//How to activating accounts
		//	`0` - No activation required, the account is immediately active
		//	`1` - The account will be enabled by the user via email (default)
		//	`2`	- The account will be enabled by an administrator
		'activation' => 1,
		//Login with cookies ("remember me" function)
		//Before using it, you should change the value of "security.crypt_key"
		'cookies_login' => TRUE,
		//Default users group (ID)
		'default_group' => 3,
		//Reset password (enabled or disabled)
		'reset_password' => TRUE,
		//Signup (enabled or disabled)
		'signup' => TRUE
	]
]];