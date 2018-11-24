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
if (!$routes->nameExists('pagesCategories')) {
    $routes->connect(
        '/pages/categories',
        ['controller' => 'PagesCategories', 'action' => 'index'],
        ['_name' => 'pagesCategories']
    );
}

//Category
if (!$routes->nameExists('pagesCategory')) {
    $routes->connect(
        '/pages/category/:slug',
        ['controller' => 'PagesCategories', 'action' => 'view'],
        ['_name' => 'pagesCategory']
    )->setPatterns(['slug' => '[\d\w\-]+'])->setPass(['slug']);
}

//Page
if (!$routes->nameExists('page')) {
    $routes->connect('/page/:slug', ['controller' => 'Pages', 'action' => 'view'], ['_name' => 'page'])
        ->setPatterns(['slug' => '[\d\w\-]+'])
        ->setPass(['slug']);
}

//Page preview
if (!$routes->nameExists('pagesPreview')) {
    $routes->connect('/page/preview/:slug', ['controller' => 'Pages', 'action' => 'preview'], ['_name' => 'pagesPreview'])
        ->setPatterns(['slug' => '[\d\w\-]+'])
        ->setPass(['slug']);
}
