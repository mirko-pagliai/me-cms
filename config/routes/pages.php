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
if (!routeNameExists('pagesCategories')) {
    $routes->connect(
        '/pages/categories',
        ['controller' => 'PagesCategories', 'action' => 'index'],
        ['_name' => 'pagesCategories']
    );
}

//Category
if (!routeNameExists('pagesCategory')) {
    $routes->connect(
        '/pages/category/:slug',
        ['controller' => 'PagesCategories', 'action' => 'view'],
        [
            '_name' => 'pagesCategory',
            'slug' => '[a-z0-9\-]+',
            'pass' => ['slug'],
        ]
    );
}

//Page
if (!routeNameExists('page')) {
    $routes->connect(
        '/page/:slug',
        ['controller' => 'Pages', 'action' => 'view'],
        [
            '_name' => 'page',
            'slug' => '[a-z0-9\-\/]+',
            'pass' => ['slug'],
        ]
    );
}

//Page preview
if (!routeNameExists('pagesPreview')) {
    $routes->connect(
        '/page/preview/:slug',
        ['controller' => 'Pages', 'action' => 'preview'],
        [
            '_name' => 'pagesPreview',
            'slug' => '[a-z0-9\-\/]+',
            'pass' => ['slug'],
        ]
    );
}
