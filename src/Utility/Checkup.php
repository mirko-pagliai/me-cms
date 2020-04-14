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

namespace MeCms\Utility;

/**
 * Checkup utility.
 *
 * This class provides quick and logical access to all checkup classes.
 * @property \MeCms\Utility\Checkups\Apache $Apache
 * @property \MeCms\Utility\Checkups\Directories $Directories
 * @property \MeCms\Utility\Checkups\ElFinder $ElFinder
 * @property \MeCms\Utility\Checkups\PHP $PHP
 * @property \MeCms\Utility\Checkups\Plugin $Plugin
 */
class Checkup
{
    /**
     * Magic method that allows access to all properties
     * @param string $name Class name
     * @return object
     */
    public function __get(string $name): object
    {
        if (!isset($this->$name)) {
            $className = sprintf('\MeCms\Utility\Checkups\%s', $name);
            $this->$name = new $className();
        }

        return $this->$name;
    }
}
