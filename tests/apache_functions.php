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

if (!function_exists('apache_get_modules')) {
    function apache_get_modules()
    {
        return ['core', 'http_core', 'mod_so', 'sapi_apache2', 'mod_mime', 'mod_rewrite'];
    }
}

if (!function_exists('apache_get_version')) {
    function apache_get_version()
    {
        return 'Apache/1.3.29 (Unix) PHP/4.3.4';
    }
}
