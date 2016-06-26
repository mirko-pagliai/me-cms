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
$routes->connect('/posts/categories',
    ['controller' => 'PostsCategories', 'action' => 'index'],
    ['_name' => 'posts_categories']
);
//Category
$routes->connect('/posts/category/:slug',
    ['controller' => 'PostsCategories', 'action' => 'view'],
    ['_name' => 'posts_category', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
);

//Tags
$routes->connect('/posts/tags',
    ['controller' => 'PostsTags', 'action' => 'index'],
    ['_name' => 'posts_tags']
);
//Tag
$routes->connect('/posts/tag/:tag',
    ['controller' => 'PostsTags', 'action' => 'view'],
    ['_name' => 'posts_tag', 'tag' => '[a-z0-9\-]+', 'pass' => ['tag']]
);

//Posts
$routes->connect('/posts',
    ['controller' => 'Posts', 'action' => 'index'],
    ['_name' => 'posts']
);
//Posts (RSS)
$routes->connect('/posts/rss',
    ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss'],
    ['_name' => 'posts_rss']
);
//Posts search
$routes->connect('/posts/search',
    ['controller' => 'Posts', 'action' => 'search'],
    ['_name' => 'posts_search']
);
//Posts by date
$routes->connect('/posts/:date',
    ['controller' => 'Posts', 'action' => 'index_by_date'], 
    [
        '_name'	=> 'posts_by_date',
        'date' => '(today|yesterday|\d{4}(\/\d{2}(\/\d{2})?)?)',
        'pass' => ['date']
    ]
);
//Post
$routes->connect('/post/:slug',
    ['controller' => 'Posts', 'action' => 'view'],
    ['_name' => 'post', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
);
//Post preview
$routes->connect('/post/preview/:slug',
    ['controller' => 'Posts', 'action' => 'preview'],
    ['_name' => 'posts_preview', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
);

/**
 * This allows backward compatibility for URLs like:
 * /posts/page:3
 * /posts/page:3/sort:Post.created/direction:desc
 * These URLs will become:
 * /posts?page=3
 */
$routes->connect('/posts/page::page/*',
    ['controller' => 'Posts', 'action' => 'index_compatibility'],
    ['page' => '\d+', 'pass' => ['page']]
);

/**
 * Fallback for RSS
 */
$routes->connect('/rss',
    ['controller' => 'Posts', 'action' => 'rss', '_ext' => 'rss']
);