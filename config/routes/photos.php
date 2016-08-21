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

//Albums
if (!routeNameExists('albums')) {
    $routes->connect(
        '/albums',
        ['controller' => 'PhotosAlbums', 'action' => 'index'],
        ['_name' => 'albums']
    );
}

//Album
if (!routeNameExists('album')) {
    $routes->connect(
        '/album/:slug',
        ['controller' => 'PhotosAlbums', 'action' => 'view'],
        ['_name' => 'album', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
    );
}

//Album preview
if (!routeNameExists('albumsPreview')) {
    $routes->connect(
        '/album/preview/:slug',
        ['controller' => 'PhotosAlbums', 'action' => 'preview'],
        [
            '_name' => 'albumsPreview',
            'slug' => '[a-z0-9\-]+',
            'pass' => ['slug'],
        ]
    );
}

//Photo
if (!routeNameExists('photo')) {
    $routes->connect(
        '/photo/:slug/:id',
        ['controller' => 'Photos', 'action' => 'view'],
        [
            '_name' => 'photo',
            'slug' => '[a-z0-9\-]+',
            'id' => '\d+',
            'pass' => ['slug', 'id'],
        ]
    );
}

//Photo preview
if (!routeNameExists('photosPreview')) {
    $routes->connect(
        '/photo/preview/:id',
        ['controller' => 'Photos', 'action' => 'preview'],
        [
            '_name' => 'photosPreview',
            'slug' => '[a-z0-9\-]+',
            'pass' => ['id'],
        ]
    );
}

/**
 * This allows backward compatibility for URLs like:
 * <pre>/photo/11</pre>
 * These URLs will become:
 * <pre>/photo/album-name/1</pre>
 */
$routes->connect(
    '/photo/:id',
    ['controller' => 'Photos', 'action' => 'view', 'slug' => false],
    ['id' => '\d+', 'pass' => ['slug', 'id']]
);
