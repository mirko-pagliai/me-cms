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
 * @since       2.22.8
 */

namespace MeCms\Utility\Checkups;

/**
 * Checkup for KCFinder
 */
class KCFinder
{
    /**
     * Checks if the `.htaccess` is readable
     * @return bool
     */
    public function htaccess(): bool
    {
        return is_readable(KCFINDER . '.htaccess');
    }

    /**
     * Checks if KCFinder is available
     * @return bool
     */
    public function isAvailable(): bool
    {
        return is_readable(KCFINDER . 'browse.php');
    }

    /**
     * Gets the version for KCFinder
     * @return string|null Version or `null`
     * @uses isAvailable()
     */
    public function version(): ?string
    {
        return $this->isAvailable() &&
            preg_match('/@version\s+([\d\.]+)/', file_get_contents(KCFINDER . 'browse.php'), $matches)
            ? $matches[1] : null;
    }
}
