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

namespace MeCms\Utility;

/**
 * Checkup utility.
 *
 * This class provides quick and logical access to all checkup classes.
 */
class Checkup
{
    /**
     * @var \MeCms\Utility\Checkups\Apache
     */
    public $Apache;

    /**
     * @var \MeCms\Utility\Checkups\Backups
     */
    public $Backups;

    /**
     * @var \MeCms\Utility\Checkups\KCFinder
     */
    public $KCFinder;

    /**
     * @var \MeCms\Utility\Checkups\PHP
     */
    public $PHP;

    /**
     * @var \MeCms\Utility\Checkups\Plugin
     */
    public $Plugin;

    /**
     * @var \MeCms\Utility\Checkups\TMP
     */
    public $TMP;

    /**
     * @var \MeCms\Utility\Checkups\Webroot
     */
    public $Webroot;

    /**
     * Construct
     */
    public function __construct()
    {
        foreach (array_keys(get_object_vars($this)) as $class) {
            $className = sprintf('\MeCms\Utility\Checkups\%s', $class);
            class_exists($className) ?: trigger_error(sprintf('Class `%s` does not exist', $className), E_USER_ERROR);
            $this->$class = new $className();
        }
    }
}
