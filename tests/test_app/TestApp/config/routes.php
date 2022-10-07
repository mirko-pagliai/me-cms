<?php
declare(strict_types=1);

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->scope('/', function ($routes): void {
    $routes->loadPlugin('RecaptchaMailhide');
    $routes->loadPlugin('Thumber/Cake');
    $routes->loadPlugin('MeCms');
});
