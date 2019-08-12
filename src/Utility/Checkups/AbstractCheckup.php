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
 * Checkup abstract class.
 *
 * This class provides methods common to all checkup classes.
 */
abstract class AbstractCheckup
{
    /**
     * Checks if each path is writeable
     * @param array $paths Paths to check
     * @return array Array with paths as keys and boolean as value
     */
    protected function isWriteable(array $paths = []): array
    {
        foreach ($paths as $path) {
            $result[$path] = is_writable_resursive($path);
        }

        return $result ?? [];
    }
}
