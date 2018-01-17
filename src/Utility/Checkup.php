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
namespace MeCms\Utility;

use MeCms\Utility\Checkups\Apache;
use MeCms\Utility\Checkups\Backups;
use MeCms\Utility\Checkups\KCFinder;
use MeCms\Utility\Checkups\PHP;
use MeCms\Utility\Checkups\Plugin;
use MeCms\Utility\Checkups\TMP;
use MeCms\Utility\Checkups\Webroot;

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
     * @uses $Apache
     * @uses $Backups
     * @uses $KCFinder
     * @uses $PHP
     * @uses $Plugin
     * @uses $TMP
     * @uses $Webroot
     */
    public function __construct()
    {
        $this->Apache = new Apache;
        $this->Backups = new Backups;
        $this->KCFinder = new KCFinder;
        $this->PHP = new PHP;
        $this->Plugin = new Plugin;
        $this->TMP = new TMP;
        $this->Webroot = new Webroot;
    }
}
