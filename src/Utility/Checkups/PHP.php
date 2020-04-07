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
 * Checkup for PHP
 */
class PHP
{
    /**
     * Extensions to check
     */
    protected const EXT_TO_CHECK = ['exif', 'imagick', 'zip'];

    /**
     * Checks if some extensions are loaded
     * @return array Array with extension name as key and boolean as value
     */
    public static function extensions(): array
    {
        foreach (self::EXT_TO_CHECK as $extension) {
            $extensions[$extension] = extension_loaded($extension);
        }

        return $extensions;
    }

    /**
     * Gets the version number
     * @return string
     * @since 2.28.1
     */
    public static function getVersion(): string
    {
        return preg_match('/^([\d\.]+)/', PHP_VERSION, $matches) ? $matches[1] : PHP_VERSION;
    }
}
