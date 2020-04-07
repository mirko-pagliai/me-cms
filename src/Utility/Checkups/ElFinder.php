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

/**
 * Checkup for ElFinder
 */
class ElFinder
{
    /**
     * Checks if ElFinder is available
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return is_readable(ELFINDER . 'php' . DS . 'connector.minimal.php');
    }

    /**
     * Gets the ElFinder version
     * @return string|null Version
     * @uses isAvailable()
     */
    public static function getVersion(): ?string
    {
        return self::isAvailable() &&
            preg_match('/elFinder \(([\d\.]+)\)/', file_get_contents(ELFINDER . 'Changelog'), $matches) &&
            isset($matches[1]) ? $matches[1] : null;
    }
}
