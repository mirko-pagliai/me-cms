<?php
use Cake\Routing\Router;

Router::scope('/', function ($routes) {
    $routes->loadPlugin(RECAPTCHA_MAILHIDE);
    $routes->loadPlugin(THUMBER);
    $routes->loadPlugin(ME_CMS);
});
