<?php
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
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::defaultRouteClass('DashedRoute');

Router::scope('/', ['plugin' => 'MeCms'], function (RouteBuilder $routes) {
    $routes->setExtensions(['rss']);

    //Requires other routes
    require 'routes' . DS . 'banners.php';
    require 'routes' . DS . 'pages.php';
    require 'routes' . DS . 'photos.php';
    require 'routes' . DS . 'posts.php';
    require 'routes' . DS . 'systems.php';
    require 'routes' . DS . 'users.php';

    //Default home page
    //To avoid conflicts with `/posts`, this route has to be at the bottom
    if (!$routes->nameExists('homepage')) {
        $routes->connect('/', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'homepage']);
    }

    $routes->connect('/homepage', ['controller' => 'Posts', 'action' => 'index']);

    //Admin routes
    $routes->prefix(ADMIN_PREFIX, function (RouteBuilder $routes) {
        //Admin home page
        if (!$routes->nameExists('dashboard')) {
            $routes->connect('/', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'dashboard']);
        }
    });
});

Router::plugin('MeCms', ['path' => '/me-cms'], function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);

    //Admin routes
    $routes->prefix(ADMIN_PREFIX, function (RouteBuilder $routes) {
        //Route `/me-cms/admin`
        $routes->connect('/', ['controller' => 'Posts', 'action' => 'index']);

        //All others admin routes
        $routes->fallbacks('DashedRoute');
    });

    //All others routes
    $routes->fallbacks('DashedRoute');
});
