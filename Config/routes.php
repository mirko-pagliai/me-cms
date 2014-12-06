<?php
/**
 * Routes.
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Config
 */

//Home page
Router::connect('/',			array('controller' => 'posts', 'action' => 'index', 'plugin' => 'me_cms'));

//Pages controller
Router::connect('/pages',	array('controller' => 'pages', 'action' => 'index', 'plugin' => 'me_cms'));
Router::connect('/page/*',	array('controller' => 'pages', 'action' => 'view',	'plugin' => 'me_cms'));

//Posts categories controller
Router::connect('/postsCategories', array('controller' => 'posts_categories',	'action' => 'index', 'plugin' => 'me_cms'));
Router::connect('/postsCategory/*',	array('controller' => 'posts',				'action' => 'index', 'plugin' => 'me_cms'));

//Posts controller
Router::connect('/posts',		array('controller' => 'posts', 'action' => 'index',		'plugin' => 'me_cms'));
Router::connect('/post/*',		array('controller' => 'posts', 'action' => 'view',		'plugin' => 'me_cms'));
Router::connect('/search/*',		array('controller' => 'posts', 'action' => 'search',	'plugin' => 'me_cms'));
Router::connect('/rss',			array('controller' => 'posts', 'action' => 'index',		'plugin' => 'me_cms', 'ext' => 'rss'));

//Photos albums controller
Router::connect('/albums',	array('controller' => 'photos_albums', 'action' => 'index', 'plugin' => 'me_cms'));
Router::connect('/photos',	array('controller' => 'photos_albums', 'action' => 'index', 'plugin' => 'me_cms'));
Router::connect('/album/*',	array('controller' => 'photos_albums', 'action' => 'view',	'plugin' => 'me_cms'));

//Photos controller
Router::connect('/photo/*',	array('controller' => 'photos', 'action' => 'view', 'plugin' => 'me_cms'));

//Admin home page
Router::connect('/admin',	array('controller' => 'posts', 'plugin' => 'me_cms', 'admin' => TRUE));

//Login and logout
Router::connect('/login',	array('controller' => 'users',	'action' => 'login',	'plugin' => 'me_cms'));
Router::connect('/logout',	array('controller' => 'users',	'action' => 'logout',	'plugin' => 'me_cms'));

$controllers = '(banners|banners_positions|pages|photos_albums|photos|posts_categories|posts|systems|users|users_groups)';

//Each "admin" request is directed to the plugin
Router::connect('/admin/:controller',			array('plugin' => 'me_cms', 'admin' => TRUE), array('controller' => $controllers));
Router::connect('/admin/:controller/:action/*',	array('plugin' => 'me_cms', 'admin' => TRUE), array('controller' => $controllers));

//Enables the 'rss' extension
Router::parseExtensions('rss');