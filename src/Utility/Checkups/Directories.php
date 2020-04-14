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
 * @since       2.29.0
 */

namespace MeCms\Utility\Checkups;

use Cake\Core\Configure;

/**
 * Checkup for directories
 */
class Directories
{
    /**
     * Checks if each path is writeable
     * @return array Array with paths as keys and boolean as value
     */
    public static function isWriteable(): array
    {
        foreach (Configure::read('WRITABLE_DIRS') as $path) {
            $result[$path] = is_writable_resursive($path);
        }

        ksort($result);

        return $result;
    }
}
