<?php

use Cake\Routing\Router;

Router::scope('/', function ($routes) {
    $routes->loadPlugin('RecaptchaMailhide');
    $routes->loadPlugin('Thumber/Cake');
    $routes->loadPlugin('MeCms');
});
