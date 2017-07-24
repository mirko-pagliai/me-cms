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

//Albums
if (!$routes->nameExists('albums')) {
    $routes->connect(
        '/albums',
        ['controller' => 'PhotosAlbums', 'action' => 'index'],
        ['_name' => 'albums']
    );
}

//Album
if (!$routes->nameExists('album')) {
    $routes->connect(
        '/album/:slug',
        ['controller' => 'PhotosAlbums', 'action' => 'view'],
        ['_name' => 'album', 'slug' => '[a-z0-9\-]+', 'pass' => ['slug']]
    );
}

//Photo
if (!$routes->nameExists('photo')) {
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
if (!$routes->nameExists('photosPreview')) {
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
