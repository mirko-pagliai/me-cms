<?php
//Widgets. You can use the plugin notation (eg., `PluginName.widgetName`)
return ['Widgets' => [
	//General widgets
	'general' => [
		'MeCms.Posts::search', 
		'MeCms.Posts::categories', 
		'MeCms.Posts::latest' => ['limit' => 10],
		'MeCms.Photos::random' => ['limit' => 1],
		'MeCms.Pages::pages'
	],
	//Specific widgets for the homepage. 
	//If empty, will be used the default widget
	'homepage' => []
]];