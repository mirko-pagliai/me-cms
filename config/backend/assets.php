<?php

use MeTools\Core\Plugin;

return ['Assets' => [
	'backend-css' => [
		'input' => [
			'webroot/vendor/font-awesome/css/font-awesome.min',
			Plugin::path('MeCms').'webroot/css/backend/bootstrap.min',
			Plugin::path('MeTools').'webroot/css/default',
			Plugin::path('MeTools').'webroot/css/forms',
			Plugin::path('MeCms').'webroot/css/backend/layout',
			Plugin::path('MeCms').'webroot/css/backend/photos'
		],
		'output' => Plugin::path('MeCms').'webroot/assets/backend.min',
		'type' => 'css'
	],
	'backend-js' => [
		'input' => [
			'webroot/vendor/jquery/jquery.min',
			Plugin::path('MeCms').'webroot/js/backend/bootstrap.min',
			Plugin::path('MeCms').'webroot/js/jquery.cookie',
			Plugin::path('MeTools').'webroot/js/default',
			Plugin::path('MeCms').'webroot/js/backend/layout'
		],
		'output' => Plugin::path('MeCms').'webroot/assets/backend.min',
		'type' => 'js'		
	]
]];