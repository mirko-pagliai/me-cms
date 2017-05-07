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
//Activation
if (!$routes->nameExists('activation')) {
    $routes->connect(
        '/activation/:id/:token',
        ['controller' => 'Users', 'action' => 'activation'],
        [
            '_name' => 'activation',
            'id' => '\d+',
            'token' => '[\d\w]+',
            'pass' => ['id', 'token'],
        ]
    );
}

//Activation resend
if (!$routes->nameExists('activationResend')) {
    $routes->connect(
        '/activation/resend',
        ['controller' => 'Users', 'action' => 'activationResend'],
        ['_name' => 'activationResend']
    );
}

//Login
if (!$routes->nameExists('login')) {
    $routes->connect(
        '/login',
        ['controller' => 'Users', 'action' => 'login'],
        ['_name' => 'login']
    );
}

//Logout
if (!$routes->nameExists('logout')) {
    $routes->connect(
        '/logout',
        ['controller' => 'Users', 'action' => 'logout'],
        ['_name' => 'logout']
    );
}

//Password forgot
if (!$routes->nameExists('passwordForgot')) {
    $routes->connect(
        '/password/forgot',
        ['controller' => 'Users', 'action' => 'passwordForgot'],
        ['_name' => 'passwordForgot']
    );
}

//Password reset
if (!$routes->nameExists('passwordReset')) {
    $routes->connect(
        '/password/reset/:id/:token',
        ['controller' => 'Users', 'action' => 'passwordReset'],
        [
            '_name' => 'passwordReset',
            'id' => '\d+',
            'token' => '[\d\w]+',
            'pass' => ['id', 'token'],
        ]
    );
}

//Signup
if (!$routes->nameExists('signup')) {
    $routes->connect(
        '/signup',
        ['controller' => 'Users', 'action' => 'signup'],
        ['_name' => 'signup']
    );
}
