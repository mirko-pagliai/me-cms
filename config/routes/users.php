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

//Activation
if (!$routes->nameExists('activation')) {
    $routes->connect(
        '/activation/:id/:token',
        ['controller' => 'Users', 'action' => 'activation'],
        ['_name' => 'activation']
    )->setPatterns(['id' => '\d+', 'token' => '[\d\w]+'])->setPass(['id', 'token']);
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
    $routes->connect('/login', ['controller' => 'Users', 'action' => 'login'], ['_name' => 'login']);
}

//Logout
if (!$routes->nameExists('logout')) {
    $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout'], ['_name' => 'logout']);
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
        ['_name' => 'passwordReset']
    )->setPatterns(['id' => '\d+', 'token' => '[\d\w]+'])->setPass(['id', 'token']);
}

//Signup
if (!$routes->nameExists('signup')) {
    $routes->connect('/signup', ['controller' => 'Users', 'action' => 'signup'], ['_name' => 'signup']);
}
