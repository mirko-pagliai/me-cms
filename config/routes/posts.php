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

//Categories
if (!$routes->nameExists('postsCategories')) {
    $routes->connect(
        '/posts/categories',
        ['controller' => 'PostsCategories', 'action' => 'index'],
        ['_name' => 'postsCategories']
    );
}

//Category
if (!$routes->nameExists('postsCategory')) {
    $routes->connect(
        '/posts/category/:slug',
        ['controller' => 'PostsCategories', 'action' => 'view'],
        [
            '_name' => 'postsCategory',
            'slug' => '[a-z0-9\-]+',
            'pass' => ['slug'],
        ]
    );
}

//Posts
if (!$routes->nameExists('posts')) {
    $routes->connect(
        '/posts',
        ['controller' => 'Posts', 'action' => 'index'],
        ['_name' => 'posts']
    );
}

//Posts by date
if (!$routes->nameExists('postsByDate')) {
    //Posts by date
    $routes->connect(
        '/posts/:date',
        ['controller' => 'Posts', 'action' => 'indexByDate'],
        [
            '_name' => 'postsByDate',
            'date' => '(today|yesterday|\d{4}(\/\d{2}(\/\d{2})?)?)',
            'pass' => ['date'],
        ]
    );
}

//Posts as RSS
if (!$routes->nameExists('postsRss')) {
    $routes->connect(
        '/posts/rss',
        ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss'],
        ['_name' => 'postsRss']
    );
}

//Posts search
if (!$routes->nameExists('postsSearch')) {
    $routes->connect(
        '/posts/search',
        ['controller' => 'Posts', 'action' => 'search'],
        ['_name' => 'postsSearch']
    );
}

//Post
if (!$routes->nameExists('post')) {
    $routes->connect(
        '/post/:slug',
        ['controller' => 'Posts', 'action' => 'view'],
        ['_name' => 'post', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
    );
}

//Post preview
if (!$routes->nameExists('postsPreview')) {
    $routes->connect(
        '/post/preview/:slug',
        ['controller' => 'Posts', 'action' => 'preview'],
        ['_name' => 'postsPreview', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
    );
}

//Tags
if (!$routes->nameExists('postsTags')) {
    $routes->connect(
        '/posts/tags',
        ['controller' => 'PostsTags', 'action' => 'index'],
        ['_name' => 'postsTags']
    );
}

//Tag
if (!$routes->nameExists('postsTag')) {
    $routes->connect(
        '/posts/tag/:tag',
        ['controller' => 'PostsTags', 'action' => 'view'],
        ['_name' => 'postsTag', 'tag' => '[a-z0-9\-]+', 'pass' => ['tag']]
    );
}

/**
 * Fallbacks
 */
$routes->connect('/rss', ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss']);
$routes->connect('/search', ['controller' => 'Posts', 'action' => 'search']);
$routes->connect('/tags', ['controller' => 'PostsTags', 'action' => 'index']);
