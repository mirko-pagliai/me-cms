<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->setRouteClass(DashedRoute::class);

$routes->scope('/', ['plugin' => 'MeCms'], function (RouteBuilder $routes) {
    $routes->setExtensions(['rss']);

    //Requires other routes
    foreach (glob(dirname(__FILE__) . DS . 'routes' . DS . '*.php') ?: [] as $filename) {
        require $filename;
    }

    //Default home page
    //To avoid conflicts with `/posts`, this route has to be at the bottom
    if (!$routes->nameExists('homepage')) {
        $routes->connect('/', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'homepage']);
    }

    $routes->connect('/homepage', ['controller' => 'Posts', 'action' => 'index']);

    //Admin home page
    if (!$routes->nameExists('dashboard')) {
        $routes->connect('/admin', ['controller' => 'Posts', 'action' => 'index', 'prefix' => ADMIN_PREFIX], ['_name' => 'dashboard']);
    }

    $routes->fallbacks('DashedRoute');
});

$routes->plugin('MeCms', function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);

    //Admin routes
    $routes->prefix(ADMIN_PREFIX, function (RouteBuilder $routes) {
        $routes->fallbacks('DashedRoute');
    });
});
