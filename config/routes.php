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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */

use Cake\I18n\Time;
use Cake\Routing\Router;

Router::defaultRouteClass('InflectedRoute');

Router::extensions('rss');

/**
 * MeCms routes
 */
Router::scope('/', ['plugin' => 'MeCms'], function ($routes) {
	/**
	 * Banners controller
	 */
	$routes->connect('/banner/:id',
		['controller' => 'Banners', 'action' => 'open'],
		['_name' => 'banner', 'id' => '\d+', 'pass' => ['id']]
	);
	
	/**
	 * Pages controller
	 */
	$routes->connect('/page/:slug',
		['controller' => 'Pages', 'action' => 'view'],
		['_name' => 'page', 'slug' => '[a-z0-9\-\/]+', 'pass' => ['slug']]
	);
	$routes->connect('/page/preview/:slug',
		['controller' => 'Pages', 'action' => 'preview'],
		['_name' => 'pages_preview', 'slug' => '[a-z0-9\-\/]+', 'pass' => ['slug']]
	);
	$routes->connect('/pages',
		['controller' => 'Pages', 'action' => 'index'],
		['_name' => 'pages']
	);

	/**
	 * PhotosAlbums controller
	 */
	$routes->connect('/albums', ['controller' => 'PhotosAlbums', 'action' => 'index'], ['_name' => 'albums']);
	$routes->connect('/album/:slug',
		['controller' => 'PhotosAlbums', 'action' => 'view'],
		['_name' => 'album', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);

	/**
	 * Photos controller
	 */
	$routes->connect('/photo/:slug/:id',
		['controller' => 'Photos', 'action' => 'view'],
		['_name' => 'photo', 'slug' => '[a-z0-9\-]+', 'id' => '\d+', 'pass' => ['id']]
	);
    
	/**
	 * This allows backward compatibility for URLs like:
	 * <pre>/photo/11</pre>
	 * These URLs will become:
	 * <pre>/photo/album-name/1</pre>
	 */
	$routes->connect('/photo/:id',
		['controller' => 'Photos', 'action' => 'view_compatibility'],
		['id' => '\d+', 'pass' => ['id']]
	);
	
	/**
	 * PostsCategories controller
	 */
	$routes->connect('/posts/categories', ['controller' => 'PostsCategories', 'action' => 'index'], ['_name' => 'posts_categories']);
	$routes->connect('/posts/category/:slug',
		['controller' => 'PostsCategories', 'action' => 'view'],
		['_name' => 'posts_category', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);
	
	/**
	 * PostsTags controller
	 */
	$routes->connect('/posts/tags',
		['controller' => 'PostsTags', 'action' => 'index'],
		['_name' => 'posts_tags']
	);
	$routes->connect('/posts/tag/:tag',
		['controller' => 'PostsTags', 'action' => 'view'],
		['_name' => 'posts_tag', 'tag' => '[a-z0-9\-]+', 'pass' => ['tag']]
	);
	
	//Shortcut
	$routes->connect('/tag/:tag',
		['controller' => 'PostsTags', 'action' => 'view'],
		['tag' => '[a-z0-9\-]+', 'pass' => ['tag']]
	);
	
	/**
	 * Posts controller
	 */
	$routes->connect('/post/:slug',
		['controller' => 'Posts', 'action' => 'view'],
		['_name' => 'post', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);
	$routes->connect('/post/preview/:slug',
		['controller' => 'Posts', 'action' => 'preview'],
		['_name' => 'posts_preview', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
	);
	$routes->connect('/posts', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'posts']);
	$routes->connect('/posts/rss', ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss'], ['_name' => 'posts_rss']);
	$routes->connect('/posts/search', ['controller' => 'Posts', 'action' => 'search'], ['_name' => 'posts_search']);
	$routes->connect('/posts/:year/:month/:day', ['controller' => 'Posts', 'action' => 'index_by_day'], [
		'_name'	=> 'posts_by_day',
		'year'	=> '[12][0-9]{3}',
		'month'	=> '0[1-9]|1[012]',
		'day'	=> '0[1-9]|[12][0-9]|3[01]',
		'pass'	=> ['year', 'month', 'day']
	]);
	$routes->connect('/posts/:year/:month', ['controller' => 'Posts', 'action' => 'index_by_month'], [
		'_name'	=> 'posts_by_month',
		'year'	=> '[12][0-9]{3}',
		'month'	=> '0[1-9]|1[012]',
		'pass'	=> ['year', 'month']
	]);
	$routes->connect('/posts/:year', ['controller' => 'Posts', 'action' => 'index_by_year'], [
		'_name'	=> 'posts_by_year',
		'year'	=> '[12][0-9]{3}',
		'pass'	=> ['year']
	]);
	$routes->connect('/posts/today', [
		'controller'	=> 'Posts', 
		'action'		=> 'index_by_day',
		'year'			=> date('Y'),
		'month'			=> date('m'),
		'day'			=> date('d'),
	], ['_name' => 'posts_today', 'pass' => ['year', 'month', 'day']]);
	$routes->connect('/posts/yesterday', [
		'controller'	=> 'Posts', 
		'action'		=> 'index_by_day',
		'year'			=> (new Time('1 days ago'))->i18nFormat('YYYY'),
		'month'			=> (new Time('1 days ago'))->i18nFormat('MM'),
		'day'			=> (new Time('1 days ago'))->i18nFormat('dd'),
	], ['_name' => 'posts_yesterday', 'pass' => ['year', 'month', 'day']]);
	
	/**
	 * This allows backward compatibility for URLs like:
	 * /posts/page:3
	 * /posts/page:3/sort:Post.created/direction:desc
	 * These URLs will become:
	 * /posts?page=3
	 */
	$routes->connect('/posts/page::page/*', ['controller' => 'Posts', 'action' => 'index_compatibility'], ['page' => '\d+', 'pass' => ['page']]);
	
	/**
	 * Fallback for RSS
	 */
	$routes->connect('/rss', ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss']);
	
	/**
	 * Systems controller
	 */
	$routes->connect('/accept/cookies', ['controller' => 'Systems', 'action' => 'accept_cookies'], ['_name' => 'accept_cookies']);
	$routes->connect('/contact/form', ['controller' => 'Systems', 'action' => 'contact_form'], ['_name' => 'contact_form']);
	$routes->connect('/offline', ['controller' => 'Systems', 'action' => 'offline'], ['_name' => 'offline']);
    $routes->connect('/sitemap:ext', ['controller' => 'Systems', 'action' => 'sitemap'], ['_name' => 'sitemap', 'ext' => '\.xml(\.gz)?']);
	$routes->connect('/unallowed', ['controller' => 'Systems', 'action' => 'ip_not_allowed'], ['_name' => 'ip_not_allowed']);
	
	/**
	 * Users controller
	 */
	$routes->connect('/activation/resend', ['controller' => 'Users', 'action' => 'resend_activation'], ['_name' => 'resend_activation']);
	$routes->connect('/activation/:id/:token',
		['controller' => 'Users', 'action' => 'activate_account'],
		['_name' => 'activate_account', 'id' => '\d+', 'token' => '[\d\w]+', 'pass' => ['id', 'token']]
	);
	$routes->connect('/login', ['controller' => 'Users', 'action' => 'login'], ['_name' => 'login']);
	$routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout'], ['_name' => 'logout']);
	$routes->connect('/password/forgot', ['controller' => 'Users', 'action' => 'forgot_password'], ['_name' => 'forgot_password']);
	$routes->connect('/password/reset/:id/:token',
		['controller' => 'Users', 'action' => 'reset_password'],
		['_name' => 'reset_password', 'id' => '\d+', 'token' => '[\d\w]+', 'pass' => ['id', 'token']]
	);
	$routes->connect('/signup', ['controller' => 'Users', 'action' => 'signup'], ['_name' => 'signup']);
	
	/**
	 * Default home page
	 * For not create incompatibility with `/posts`, this route has to be at the bottom
	 */
	$routes->connect('/', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'homepage']);
	$routes->connect('/homepage', ['controller' => 'Posts', 'action' => 'index']);
	
	/**
	 * Admin routes
	 */
    $routes->prefix('admin', function ($routes) {
		/**
		 * Admin home page
		 */
        $routes->connect('/', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'dashboard']);
		
		/**
		 * Other admin routes
		 */
		$controllers = ['backups', 'banners', 'banners_positions', 'logs', 'pages', 'photos_albums', 'photos', 'posts_categories', 'posts_tags', 'posts', 'systems', 'tags', 'users', 'users_groups'];
		$controllers = sprintf('(%s)', implode('|', $controllers));
		
		$routes->connect('/:controller', [], ['controller' => $controllers]);
		$routes->connect('/:controller/:action/*', [], ['controller' => $controllers]);
    });
});

Router::plugin('MeCms', ['path' => '/me-cms'], function ($routes) {
	$routes->fallbacks('InflectedRoute');
});