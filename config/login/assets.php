<?php

use MeTools\Core\Plugin;

return ['Assets' => [
	'login-css' => [
		'input' => [
			'webroot/vendor/font-awesome/css/font-awesome.min',
			Plugin::path('MeCms').'webroot/css/login/bootstrap.min',
			Plugin::path('MeTools').'webroot/css/default',
			Plugin::path('MeTools').'webroot/css/forms',
			Plugin::path('MeCms').'webroot/css/login/layout'
		],
		'output' => Plugin::path('MeCms').'webroot/assets/login.min',
		'type' => 'css'
	]
]];