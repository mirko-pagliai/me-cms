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
        ['_name' => 'postsCategory']
    )->setPatterns(['slug' => '[\d\w\-]+'])->setPass(['slug']);
}

//Posts
if (!$routes->nameExists('posts')) {
    $routes->connect('/posts', ['controller' => 'Posts', 'action' => 'index'], ['_name' => 'posts']);
}

//Posts by date
if (!$routes->nameExists('postsByDate')) {
    $routes->connect('/posts/:date', ['controller' => 'Posts', 'action' => 'indexByDate'], ['_name' => 'postsByDate'])
        ->setPatterns(['date' => '(today|yesterday|\d{4}(\/\d{2}(\/\d{2})?)?)'])
        ->setPass(['date']);
}

//Posts as RSS
if (!$routes->nameExists('postsRss')) {
    $routes->connect('/posts/rss', ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss'], ['_name' => 'postsRss']);
}

//Posts search
if (!$routes->nameExists('postsSearch')) {
    $routes->connect('/posts/search', ['controller' => 'Posts', 'action' => 'search'], ['_name' => 'postsSearch']);
}

//Post
if (!$routes->nameExists('post')) {
    $routes->connect('/post/:slug', ['controller' => 'Posts', 'action' => 'view'], ['_name' => 'post'])
        ->setPatterns(['slug' => '[\d\w\-]+'])
        ->setPass(['slug']);
}

//Post preview
if (!$routes->nameExists('postsPreview')) {
    $routes->connect(
        '/post/preview/:slug',
        ['controller' => 'Posts', 'action' => 'preview'],
        ['_name' => 'postsPreview']
    )->setPatterns(['slug' => '[\d\w\-]+'])->setPass(['slug']);
}

//Tags
if (!$routes->nameExists('postsTags')) {
    $routes->connect('/posts/tags', ['controller' => 'PostsTags', 'action' => 'index'], ['_name' => 'postsTags']);
}

//Tag
if (!$routes->nameExists('postsTag')) {
    $routes->connect('/posts/tag/:tag', ['controller' => 'PostsTags', 'action' => 'view'], ['_name' => 'postsTag'])
        ->setPatterns(['tag' => '[\d\w\-]+'])
        ->setPass(['tag']);
}

/**
 * Fallbacks
 */
$routes->connect('/rss', ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss']);
$routes->connect('/search', ['controller' => 'Posts', 'action' => 'search']);
$routes->connect('/tags', ['controller' => 'PostsTags', 'action' => 'index']);
