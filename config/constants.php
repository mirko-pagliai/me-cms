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

//Sets the admin prefix
if (!defined('ADMIN_PREFIX')) {
    define('ADMIN_PREFIX', 'admin');
}

//Sets the default banners directory
if (!defined('BANNERS')) {
    define('BANNERS', WWW_ROOT . 'img' . DS . 'banners');
}

//Sets the default banners web address
if (!defined('BANNERS_WWW')) {
    define('BANNERS_WWW', 'banners');
}

//Sets the datetime format for MySql
if (!defined('FORMAT_FOR_MYSQL')) {
    define('FORMAT_FOR_MYSQL', 'YYYY-MM-dd HH:mm');
}

//Sets the default login log directory
if (!defined('LOGIN_LOGS')) {
    define('LOGIN_LOGS', TMP . 'login' . DS);
}

//Sets the default MeCms name
if (!defined('MECMS')) {
    define('MECMS', 'MeCms');
}

//Sets the default photos directory
if (!defined('PHOTOS')) {
    define('PHOTOS', WWW_ROOT . 'img' . DS . 'photos');
}

//Sets the default sitemap path
if (!defined('SITEMAP')) {
    define('SITEMAP', TMP . 'sitemap.xml.gz');
}

//Sets the default directory for uploaded files
if (!defined('UPLOADED')) {
    define('UPLOADED', WWW_ROOT . 'files');
}
