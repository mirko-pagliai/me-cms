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

/** @var \Cake\Routing\RouteBuilder $routes */

//"Contact us" form
if (!$routes->nameExists('contactUs')) {
    $routes->connect(
        '/contact/us',
        ['controller' => 'Systems', 'action' => 'contactUs'],
        ['_name' => 'contactUs']
    );
}

//Redirect from the old address of the "contact us" form
$routes->redirect(
    '/contact/form',
    ['controller' => 'Systems', 'action' => 'contactUs'],
    ['status' => 301]
);

//"IP not allowed" page
if (!$routes->nameExists('ipNotAllowed')) {
    $routes->connect('/unallowed', ['controller' => 'Systems', 'action' => 'ipNotAllowed'], ['_name' => 'ipNotAllowed']);
}

//Offline page
if (!$routes->nameExists('offline')) {
    $routes->connect('/offline', ['controller' => 'Systems', 'action' => 'offline'], ['_name' => 'offline']);
}

//Sitemap
if (!$routes->nameExists('sitemap')) {
    $routes->connect('/sitemap{ext}', ['controller' => 'Systems', 'action' => 'sitemap'], ['_name' => 'sitemap', ])
        ->setPatterns(['ext' => '\.xml(\.gz)?']);
}
