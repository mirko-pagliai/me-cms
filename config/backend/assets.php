<?php
return ['Assets' => [
	'backend-css' => [
		'input' => [
			'plugins/MeTools/webroot/css/font-awesome.min',
			'plugins/MeCms/webroot/css/backend/bootstrap.min',
			'plugins/MeTools/webroot/css/default',
			'plugins/MeTools/webroot/css/forms',
			'plugins/MeCms/webroot/css/backend/layout',
			'plugins/MeCms/webroot/css/backend/photos'
		],
		'output' => 'plugins/MeCms/webroot/assets/backend.min',
		'type' => 'css'
	],
	'backend-js' => [
		'input' => [
			'plugins/MeTools/webroot/js/jquery.min',
			'plugins/MeCms/webroot/js/backend/bootstrap.min',
			'plugins/MeCms/webroot/js/jquery.cookie',
			'plugins/MeCms/webroot/js/backend/layout'
		],
		'output' => 'plugins/MeCms/webroot/assets/backend.min',
		'type' => 'js'		
	]
]];