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
     */
    public function modules(): array
    {
        $modules = [];

        foreach ($this->modulesToCheck as $module) {
            $modules[$module] = in_array('mod_' . $module, apache_get_modules());
        }

        return $modules;
    }

    /**
     * Returns the version of Apache
     * @return string
     */
    public function version(): string
    {
        $version = apache_get_version();

        return preg_match('/Apache\/(\d+\.\d+\.\d+)/i', $version, $matches) ? $matches[1] : $version;
    }
}
