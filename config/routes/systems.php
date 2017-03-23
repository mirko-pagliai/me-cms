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

//Accept cookies
if (!$routes->nameExists('acceptCookies')) {
    $routes->connect(
        '/accept/cookies',
        ['controller' => 'Systems', 'action' => 'acceptCookies'],
        ['_name' => 'acceptCookies']
    );
}

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

//Offline page
if (!$routes->nameExists('offline')) {
    $routes->connect(
        '/offline',
        ['controller' => 'Systems', 'action' => 'offline'],
        ['_name' => 'offline']
    );
}

//Sitemap
if (!$routes->nameExists('sitemap')) {
    $routes->connect(
        '/sitemap:ext',
        ['controller' => 'Systems', 'action' => 'sitemap'],
        ['_name' => 'sitemap', 'ext' => '\.xml(\.gz)?']
    );
}

//Unallowed page
if (!$routes->nameExists('ipNotAllowed')) {
    $routes->connect(
        '/unallowed',
        ['controller' => 'Systems', 'action' => 'ipNotAllowed'],
        ['_name' => 'ipNotAllowed']
    );
}
