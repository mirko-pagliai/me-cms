<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
use Cake\Routing\Router;

Router::defaultRouteClass('DashedRoute');
Router::extensions('rss');

//Gets existing routes name
$GLOBALS['existingRoutesNames'] = array_filter(
    array_map(function ($route) {
        return empty($route->options['_name']) ? false : $route->options['_name'];
    }, Router::routes())
);

/**
 * Checks whether the name of a route already exists
 * @param string $name Name
 * @return bool
 */
function routeNameExists($name)
{
    return in_array($name, $GLOBALS['existingRoutesNames']);
}

Router::scope('/', ['plugin' => MECMS], function ($routes) {
    //Includes routes
    include_once 'routes/banners.php';
    include_once 'routes/pages.php';
    include_once 'routes/photos.php';
    include_once 'routes/posts.php';
    include_once 'routes/systems.php';
    include_once 'routes/users.php';

    //Default home page
    //To avoid conflicts with `/posts`, this route has to be at the bottom
    if (!routeNameExists('homepage')) {
        $routes->connect(
            '/',
            ['controller' => 'Posts', 'action' => 'index'],
            ['_name' => 'homepage']
        );
    }
    
    $routes->connect(
        '/homepage',
        ['controller' => 'Posts', 'action' => 'index']
    );

    //Admin routes
    $routes->prefix('admin', function ($routes) {
        //Admin home page
        if (!routeNameExists('dashboard')) {
            $routes->connect(
                '/',
                ['controller' => 'Posts', 'action' => 'index'],
                ['_name' => 'dashboard']
            );
        }
    });
});

Router::plugin(MECMS, ['path' => '/me-cms'], function ($routes) {
    //Admin routes
    $routes->prefix('admin', function ($routes) {
        //Route `/me-cms/admin`
        $routes->connect(
            '/',
            ['controller' => 'Posts', 'action' => 'index']
        );
        
        //All others admin routes
        $routes->fallbacks('DashedRoute');
    });
    
    //All others admin routes
    $routes->fallbacks('DashedRoute');
});
