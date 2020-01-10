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

use Cake\Core\Configure;
use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for backups
 */
class Backups extends AbstractCheckup
{
    /**
     * Checks if the path is writeable
     * @param array $paths Paths to check
     * @return array Array with paths as keys and boolean as value
     * @uses isWriteable()
     * @uses path()
     */
    public function isWriteable(array $paths = [])
    {
        return parent::isWriteable($paths ?: [Configure::read('DatabaseBackup.target')]);
    }
}
