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

/**
 * Users controller
 */
$routes->connect('/activation/resend',
    ['controller' => 'Users', 'action' => 'resend_activation'],
    ['_name' => 'resend_activation']
);
$routes->connect('/activation/:id/:token',
    ['controller' => 'Users', 'action' => 'activate_account'],
    ['_name' => 'activate_account', 'id' => '\d+', 'token' => '[\d\w]+', 'pass' => ['id', 'token']]
);
$routes->connect('/login',
    ['controller' => 'Users', 'action' => 'login'],
    ['_name' => 'login']
);
$routes->connect('/logout',
    ['controller' => 'Users', 'action' => 'logout'],
    ['_name' => 'logout']
);
$routes->connect('/password/forgot',
    ['controller' => 'Users', 'action' => 'forgot_password'],
    ['_name' => 'forgot_password']
);
$routes->connect('/password/reset/:id/:token',
    ['controller' => 'Users', 'action' => 'reset_password'],
    ['_name' => 'reset_password', 'id' => '\d+', 'token' => '[\d\w]+', 'pass' => ['id', 'token']]
);
$routes->connect('/signup',
    ['controller' => 'Users', 'action' => 'signup'],
    ['_name' => 'signup']
);