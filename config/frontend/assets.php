<?php

use MeTools\Core\Plugin;

return ['Assets' => [
	'frontend-css' => [
		'input' => [
			Plugin::path('MeCms').'webroot/css/frontend/bootstrap.min',
			Plugin::path('MeTools').'webroot/css/default',
			Plugin::path('MeTools').'webroot/css/forms',
			Plugin::path('MeCms').'webroot/css/frontend/layout',
			Plugin::path('MeCms').'webroot/css/frontend/contents',
			Plugin::path('MeCms').'webroot/css/frontend/photos'
		],
		'output' => Plugin::path('MeCms').'webroot/assets/frontend.min',
		'type' => 'css'
	],
	'frontend-js' => [
		'input' => [
			'webroot/vendor/jquery/jquery.min',
			Plugin::path('MeCms').'webroot/js/frontend/bootstrap.min',
			Plugin::path('MeTools').'webroot/js/default'
		],
		'output' => Plugin::path('MeCms').'webroot/assets/frontend.min',
		'type' => 'js'		
	]
]];