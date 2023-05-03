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

use MeTools\Core\Configure;

if (!extension_loaded('imagick') && !extension_loaded('gd')) {
    trigger_error('You must enable the imagick or the gd extension', E_USER_ERROR);
}

if (!extension_loaded('zip')) {
    trigger_error('You must enable the zip extension', E_USER_ERROR);
}

foreach (Configure::readFromPlugins('WritableDirs') as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0777, true);
    }
    if (!is_writeable($dir)) {
        trigger_error(sprintf('Directory %s not writeable', $dir), E_USER_ERROR);
    }
}

if (PHP_SAPI === 'cli') {
    return;
}

if (!in_array('mod_expires', apache_get_modules())) {
    trigger_error('You must enable the expires module', E_USER_ERROR);
}

if (!in_array('mod_rewrite', apache_get_modules())) {
    trigger_error('You must enable the rewrite module', E_USER_ERROR);
}
