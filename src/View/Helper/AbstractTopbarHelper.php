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
 * @since       2.30.0
 */

namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * Abstract Topbar Helper.
 *
 * This helper returns an array with the links to put in the topbar.
 * @property \MeTools\View\Helper\HtmlHelper $Html
 */
abstract class AbstractTopbarHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = [
        'Html' => ['className' => 'MeTools.Html'],
    ];

    /**
     * Returns an array with the links to put in the topbar
     * @return array
     */
    abstract public function build(): array;
}
