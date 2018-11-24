<?php
use Cake\Routing\Router;

Router::scope('/', function ($routes) {
    $routes->loadPlugin(RECAPTCHA_MAILHIDE);
    $routes->loadPlugin('Thumber');
    $routes->loadPlugin('MeCms');
});
