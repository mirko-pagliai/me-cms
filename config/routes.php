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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */

use Cake\Routing\Router;

Router::defaultRouteClass('InflectedRoute');

Router::extensions('rss');

Router::scope('/', ['plugin' => 'MeCms'], function ($routes) {
	/**
	 * Home page
	 */
	Router::connect('/', ['controller' => 'Posts', 'action' => 'index', 'plugin' => MECMS], ['_name' => 'homepage']);
	
	/**
	 * Banners controller
	 */
	Router::connect('/banner/:id',
		['controller' => 'Banners', 'action' => 'open', 'plugin' => MECMS],
		['_name' => 'banner', 'id' => '\d+', 'pass' => ['id']]
	);
	
	/**
	 * Pages controller
	 */
	Router::connect('/pages',
		['controller' => 'Pages', 'action' => 'index', 'plugin' => MECMS],
		['_name' => 'pages']
	);
	Router::connect('/page/:slug',
		['controller' => 'Pages', 'action' => 'view', 'plugin' => MECMS],
		['_name' => 'page', 'slug' => '[a-z0-9\-\/]+', 'pass' => ['slug']]
	);

	/**
	 * PhotosAlbums controller
	 */
	Router::connect('/albums', ['controller' => 'PhotosAlbums', 'action' => 'index', 'plugin' => MECMS], ['_name' => 'albums']);
	Router::connect('/album/:slug',
		['controller' => 'PhotosAlbums', 'action' => 'view', 'plugin' => MECMS],
		['_name' => 'album', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);

	/**
	 * Photos controller
	 */
	Router::connect('/photo/:id',
		['controller' => 'Photos', 'action' => 'view', 'plugin' => MECMS],
		['_name' => 'photo', 'id' => '\d+', 'pass' => ['id']]
	);
	
	/**
	 * PostsCategories controller
	 */
	Router::connect('/categories', ['controller' => 'PostsCategories', 'action' => 'index', 'plugin' => MECMS], ['_name' => 'categories']);
	
	/**
	 * Posts controller
	 */
	Router::connect('/category/:slug',
		['controller' => 'Posts', 'action' => 'index', 'plugin' => MECMS],
		['_name' => 'category', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);
	Router::connect('/post/:slug',
		['controller' => 'Posts', 'action' => 'view', 'plugin' => MECMS],
		['_name' => 'post', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);
	Router::connect('/posts', ['controller' => 'Posts', 'action' => 'index', 'plugin' => MECMS], ['_name' => 'posts']);
	Router::connect('/posts/rss', ['controller' => 'Posts', 'action' => 'rss', 'plugin' => MECMS, '_ext' => 'rss']);
	Router::connect('/posts/search',	 ['controller' => 'Posts', 'action' => 'search', 'plugin' => MECMS], ['_name' => 'search_posts']);
	
	/**
	 * Systems controller
	 */
	Router::connect('/unallowed', ['controller' => 'Systems', 'action' => 'ip_not_allowed', 'plugin' => MECMS], ['_name' => 'ip_not_allowed']);
	Router::connect('/offline', ['controller' => 'Systems', 'action' => 'offline', 'plugin' => MECMS], ['_name' => 'offline']);
	Router::connect('/contact/form',	 ['controller' => 'Systems', 'action' => 'contact_form', 'plugin' => MECMS], ['_name' => 'contact_form']);
	
	/**
	 * Users controller
	 */
	Router::connect('/activation/resend', ['controller' => 'Users', 'action' => 'resend_activation', 'plugin' => MECMS], ['_name' => 'resend_activation']);
	Router::connect('/activation/:id/:token',
		['controller' => 'Users', 'action' => 'activate_account', 'plugin' => MECMS],
		['_name' => 'activate_account', 'id' => '\d+', 'token' => '[\d\w]+', 'pass' => ['id', 'token']]
	);
	Router::connect('/login', ['controller' => 'Users', 'action' => 'login', 'plugin' => MECMS], ['_name' => 'login']);
	Router::connect('/logout', ['controller' => 'Users', 'action' => 'logout', 'plugin' => MECMS], ['_name' => 'logout']);
	Router::connect('/password/forgot', ['controller' => 'Users', 'action' => 'forgot_password', 'plugin' => MECMS], ['_name' => 'forgot_password']);
	Router::connect(	'/password/reset/:id/:token',
		['controller' => 'Users', 'action' => 'reset_password', 'plugin' => MECMS],
		['_name' => 'reset_password', 'id' => '\d+', 'token' => '[\d\w]+', 'pass' => ['id', 'token']]
	);
	Router::connect('/signup', ['controller' => 'Users', 'action' => 'signup', 'plugin' => MECMS], ['_name' => 'signup']);
	
	/**
	 * Admin routes
	 */
    $routes->prefix('admin', function ($routes) {
		/**
		 * Admin home page
		 */
        $routes->connect('/', ['controller' => 'Posts', 'action' => 'index', 'prefix' => ADMIN, 'plugin' => MECMS], ['_name' => 'dashboard']);
		
        $routes->fallbacks();
    });
	
    $routes->fallbacks();
});