<?php
return ['Assets' => [
	'frontend-css' => [
		'input' => [
			'plugins/MeTools/webroot/css/font-awesome.min',
			'plugins/MeCms/webroot/css/frontend/bootstrap.min',
			'plugins/MeTools/webroot/css/default',
			'plugins/MeTools/webroot/css/forms',
			'plugins/MeCms/webroot/css/frontend/layout',
			'plugins/MeCms/webroot/css/frontend/contents',
			'plugins/MeCms/webroot/css/frontend/photos'
		],
		'output' => 'plugins/MeCms/webroot/assets/frontend.min',
		'type' => 'css'
	],
	'frontend-js' => [
		'input' => [
			'plugins/MeTools/webroot/js/jquery.min',
			'plugins/MeCms/webroot/js/frontend/bootstrap.min',
			'plugins/MeTools/webroot/js/default'
		],
		'output' => 'plugins/MeCms/webroot/assets/frontend.min',
		'type' => 'js'		
	]
]];