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

foreach (array_filter(Configure::readFromPlugins('WritableDirs'), fn(string $dir): bool => !file_exists($dir)) as $dir) {
    @mkdir($dir, 0777, true);
    if (!is_writeable($dir)) {
        trigger_error('Directory `' . $dir . '` not writeable', E_USER_ERROR);
    }
}

if (PHP_SAPI === 'cli') {
    return;
}

foreach (['mod_expires', 'mod_rewrite'] as $module) {
    if (!in_array($module, apache_get_modules())) {
        trigger_error('You must enable the `' . $module . '` module', E_USER_ERROR);
    }
}
