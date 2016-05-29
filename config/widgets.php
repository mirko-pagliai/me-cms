<?php
//Widgets. You can use the plugin notation (eg., `PluginName.widgetName`)
return ['Widgets' => [
	//General widgets
	'general' => [
		'MeCms.Pages::pages',
		'MeCms.Photos::albums',
		'MeCms.Photos::latest' => ['limit' => 1],
		'MeCms.Photos::random' => ['limit' => 1],
		'MeCms.Posts::categories',
		'MeCms.Posts::latest' => ['limit' => 10],
		'MeCms.Posts::month',
		'MeCms.Posts::search',
		'MeCms.PostsTags::popular' => [
			'limit' => 10,
			'prefix' => '#',
			'shuffle' => TRUE,
			'style' => ['maxFont' => 40, 'minFont' => 12]
		],
	],
	//Specific widgets for the homepage. 
	//If empty, will be used the default widget
	'homepage' => []
]];