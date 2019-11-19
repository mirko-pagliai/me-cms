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

use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for webroot directories
 */
class Webroot extends AbstractCheckup
{
    /**
     * Checks if each path is writeable
     * @param array $paths Paths to check
     * @return array Array with paths as keys and boolean as value
     * @uses isWriteable()
     */
    public function isWriteable(array $paths = []): array
    {
        $paths = $paths ?: [
            BANNERS,
            PHOTOS,
            USER_PICTURES,
            WWW_ROOT . 'files' . DS,
        ];

        return parent::isWriteable($paths);
    }
}
