<?php
declare(strict_types=1);

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
//Sets the admin prefix
if (!defined('ADMIN_PREFIX')) {
    define('ADMIN_PREFIX', 'Admin');
}

//Sets the default banners directory
if (!defined('BANNERS')) {
    define('BANNERS', WWW_ROOT . 'img' . DS . 'banners' . DS);
}

//Sets the default banners web address
if (!defined('BANNERS_WWW')) {
    define('BANNERS_WWW', 'banners/');
}

//Sets the default ElFinder path
if (!defined('ELFINDER')) {
    define('ELFINDER', WWW_ROOT . 'vendor' . DS . 'elfinder' . DS);
}

//Sets the datetime format for MySql
if (!defined('FORMAT_FOR_MYSQL')) {
    define('FORMAT_FOR_MYSQL', 'yyyy-MM-dd HH:mm');
}

//Sets the default login log directory
if (!defined('LOGIN_RECORDS')) {
    define('LOGIN_RECORDS', TMP . 'login' . DS);
}

//Sets the default photos directory
if (!defined('PHOTOS')) {
    define('PHOTOS', WWW_ROOT . 'img' . DS . 'photos' . DS);
}

//Sets the default sitemap path
if (!defined('SITEMAP')) {
    define('SITEMAP', TMP . 'sitemap.xml.gz');
}

//Sets the default directory for uploaded files
if (!defined('UPLOADED')) {
    define('UPLOADED', WWW_ROOT . 'files' . DS);
}

//Sets the default directory for user pictures
if (!defined('USER_PICTURES')) {
    define('USER_PICTURES', WWW_ROOT . 'img' . DS . 'users' . DS);
}
