<?php
return ['MeCms' => [
	//Backend
	'backend' => [
		'layout' => 'MeCms.backend',
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
		//Layout you want to use for the backend
		//If you want to use a layout from your application (eg. `default.ctp`), change this value without extension
		'layout' => 'MeCms.frontend',
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
		//Automatically adds the meta tag for RSS resources
		'rss_meta' => TRUE,
		//Theme. Must be located in `APP/plugins/`
		'theme' => FALSE,
		//Number of characters to truncate a text. `0` or `FALSE` to disable
		'truncate_to' => 1000
	],
	//KCFinder
	'kcfinder' => [
		//Here you can rewrite the configuration for KCFinder.
		//See http://kcfinder.sunhater.com/install#_types
	],
	//Main
	'main' => [
		//Date formats
		//See; http://php.net/manual/it/datetime.formats.php
		'date' => [
			//Long format
			'long'	=> 'YYYY/MM/dd',
			//Short format
			'short'	=> 'yy/MM/dd'
		],
		//Datetime formats
		//See; http://php.net/manual/it/datetime.formats.php
		'datetime' => [
			//Long format
			'long'	=> 'YYYY/MM/dd, HH:mm',
			//Short format
			'short'	=> 'yy/MM/dd, HH:mm'
		],
		//Forces debug on localhost (enabled or disabled)
		'debug_on_localhost' => TRUE,
		//Interface language.
		//With "auto" value, it will try to use the browser language
		'language' => 'auto',
		//Time formats
		//See; http://php.net/manual/it/datetime.formats.php
		'time' => [
			//Long format
			'long'	=> 'HH:mm',
			//Short format
			'short'	=> 'HH:mm'
		],
		//Timezone. See the list of supported timezones:
		//http://php.net/manual/en/timezones.php
		'timezone' => 'UTC',
		//Site title
		'title' => 'MeCms'
	],
	//Pages
	'page' => [
		//Displays the page created datetime
		'created' => FALSE,
		//Displays the Shareaholic social buttons
		//Remember you have to set app and site IDs. See `shareaholic.app_id` and `shareaholic.site_id`
		'shareaholic' => FALSE	
	],
	//Posts
	'post' => [
		//Displays the post author
		'author' => TRUE,
		//Displays the post category
		'category' => TRUE,
		//Displays the post created datetime
		'created' => TRUE,
		//Max number of related posts to get for each post. Use `0` to disable
		'related' => 5,
		//Displays the Shareaholic social buttons
		//Remember you have to set app and site IDs. See `shareaholic.app_id` and `shareaholic.site_id`
		'shareaholic' => FALSE,
		//Displays the post tags
		'tags' => TRUE
	],
	//Security
	'security' => [
		//Array of banned IP addresses.
		//You can use the asterisk (*) as a wildcard.
		//With "false" or an empty value, access is granted to any ip addresses (no limitation).
		'banned_ip' => [],
		//reCAPTCHA (enabled or disabled).
		//It will be used for some actions, such as signup or reset the password
		'recaptcha' => FALSE,
		//Interval between searches, in seconds. Set to `0` or `FALSE` to disable
		'search_interval' => 10
	],
	//Shareaholic
	'shareaholic' => [
		//App ID. Used for render the "share buttons" of Shareaholic
		//You can found it on the "Sharing: Edit Share Button Location"
		'app_id' => '',
		//Site ID. Used for render the "setup code" of Shareaholic.
		//You can found it on the "Site Tools Dashboard"
		'site_id' => ''
	],
	//Users
	'users' => [
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
	]
]];