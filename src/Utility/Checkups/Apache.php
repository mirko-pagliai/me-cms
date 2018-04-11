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
use Tools\Apache as BaseApache;

/**
 * Checkup for Apache
 */
class Apache extends AbstractCheckup
{
    /**
     * Modules to check
     * @var array
     */
    protected $modulesToCheck = ['expires', 'rewrite'];

    /**
     * Checks if some modules are loaded
     * @return array
     * @uses $modulesToCheck
     * @uses Tools\Utility\Apache::isEnabled()
     */
    public function modules()
    {
        $Apache = new BaseApache;

        foreach ($this->modulesToCheck as $module) {
            $modules[$module] = $Apache->isEnabled('mod_' . $module);
        }

        return $modules;
    }

    /**
     * Returns the version of Apache
     * @return string
     * @uses Tools\Utility\Apache::version()
     */
    public function version()
    {
        return (new BaseApache)->version();
    }
}
