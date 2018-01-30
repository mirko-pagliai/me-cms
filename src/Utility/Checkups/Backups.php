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

use DatabaseBackup\Utility\BackupManager;
use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for backups
 */
class Backups extends AbstractCheckup
{
    /**
     * Checks if the path is writeable
     * @return array Array with paths as keys and boolean as value
     * @uses _isWriteable()
     * @uses path()
     */
    public function isWriteable()
    {
        return $this->_isWriteable((array)(new BackupManager)->getTarget());
    }
}
