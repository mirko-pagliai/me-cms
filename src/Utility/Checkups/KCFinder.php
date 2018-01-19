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
 * @since       2.22.8-RC5
 */
namespace MeCms\Utility\Checkups;

use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for KCFinder
 */
class KCFinder extends AbstractCheckup
{
    /**
     * Checks if the `.htaccess` is readable
     * @return bool
     */
    public function htaccess()
    {
        return is_readable(KCFINDER . '.htaccess');
    }

    /**
     * Checks if KCFinder is available
     * @return bool
     */
    public function isAvailable()
    {
        return is_readable(KCFINDER . 'browse.php');
    }

    /**
     * Gets the version for KCFinder
     * @return string|bool Version or `false`
     * @uses isAvailable()
     */
    public function version()
    {
        $matches = null;

        if (!$this->isAvailable() || !preg_match('/@version\s+([\d\.]+)/', file_get_contents(KCFINDER . 'browse.php'), $matches)) {
            return false;
        }

        return $matches[1];
    }
}
