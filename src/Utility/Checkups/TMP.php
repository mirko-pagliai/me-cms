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
 */
namespace MeCms\Utility\Checkups;

use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for temporary directories
 */
class TMP extends AbstractCheckup
{
    /**
     * Paths to check
     * @var array
     */
    protected $pathsToCheck = [];

    /**
     * Construct
     * @uses $pathsToCheck
     */
    public function __construct()
    {
        $this->pathsToCheck = [
            LOGS,
            TMP,
            getConfigOrFail(ASSETS . '.target'),
            CACHE,
            LOGIN_RECORDS,
            getConfigOrFail(THUMBER . '.target'),
        ];
    }

    /**
     * Checks if each path is writeable
     * @return array Array with paths as keys and boolean as value
     * @uses $pathsToCheck
     * @uses _isWriteable()
     */
    public function isWriteable()
    {
        return $this->_isWriteable($this->pathsToCheck);
    }
}
