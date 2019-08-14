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
 * @since       2.22.8
 */
namespace MeCms\Utility\Checkups;

use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for PHP
 */
class PHP extends AbstractCheckup
{
    /**
     * Extensions to check
     */
    const EXT_TO_CHECK = ['exif', 'imagick', 'zip'];

    /**
     * Checks if some extensions are loaded
     * @return array Array with extension name as key and boolean as value
     */
    public function extensions()
    {
        foreach (self::EXT_TO_CHECK as $extension) {
            $extensions[$extension] = extension_loaded($extension);
        }

        return $extensions;
    }
}
