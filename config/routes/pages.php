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

//Categories
$routes->connect('/pages/categories',
    ['controller' => 'PagesCategories', 'action' => 'index'],
    ['_name' => 'pages_categories']
);
//Category
$routes->connect('/pages/category/:slug',
    ['controller' => 'PagesCategories', 'action' => 'view'],
    ['_name' => 'pages_category', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
);

//Pages
$routes->connect('/pages',
    ['controller' => 'Pages', 'action' => 'index'],
    ['_name' => 'pages']
);
//Page
$routes->connect('/page/:slug',
    ['controller' => 'Pages', 'action' => 'view'],
    ['_name' => 'page', 'slug' => '[a-z0-9\-\/]+', 'pass' => ['slug']]
);
//Page preview
$routes->connect('/page/preview/:slug',
    ['controller' => 'Pages', 'action' => 'preview'],
    ['_name' => 'pages_preview', 'slug' => '[a-z0-9\-\/]+', 'pass' => ['slug']]
);