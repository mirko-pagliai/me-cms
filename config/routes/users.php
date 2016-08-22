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

//Resend activation
if (!routeNameExists('resendActivation')) {
    $routes->connect(
        '/activation/resend',
        ['controller' => 'Users', 'action' => 'resendActivation'],
        ['_name' => 'resendActivation']
    );
}

//Activate account
if (!routeNameExists('activateAccount')) {
    $routes->connect(
        '/activation/:id/:token',
        ['controller' => 'Users', 'action' => 'activateAccount'],
        [
            '_name' => 'activateAccount',
            'id' => '\d+',
            'token' => '[\d\w]+',
            'pass' => ['id', 'token'],
        ]
    );
}

//Change password
if (!routeNameExists('changePassword')) {
    $routes->connect(
        '/change/password',
        ['controller' => 'Users', 'action' => 'changePassword'],
        ['_name' => 'changePassword']
    );
}

//Login
if (!routeNameExists('login')) {
    $routes->connect(
        '/login',
        ['controller' => 'Users', 'action' => 'login'],
        ['_name' => 'login']
    );
}

//Logout
if (!routeNameExists('logout')) {
    $routes->connect(
        '/logout',
        ['controller' => 'Users', 'action' => 'logout'],
        ['_name' => 'logout']
    );
}

//Forgot password
if (!routeNameExists('forgotPassword')) {
    $routes->connect(
        '/password/forgot',
        ['controller' => 'Users', 'action' => 'forgotPassword'],
        ['_name' => 'forgotPassword']
    );
}

//Reset password
if (!routeNameExists('resetPassword')) {
    $routes->connect(
        '/password/reset/:id/:token',
        ['controller' => 'Users', 'action' => 'resetPassword'],
        [
            '_name' => 'resetPassword',
            'id' => '\d+',
            'token' => '[\d\w]+',
            'pass' => ['id', 'token'],
        ]
    );
}

//Signup
if (!routeNameExists('signup')) {
    $routes->connect(
        '/signup',
        ['controller' => 'Users', 'action' => 'signup'],
        ['_name' => 'signup']
    );
}
