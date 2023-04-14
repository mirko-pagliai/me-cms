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
 * @since       2.31.9
 */

namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * `AbstractMenuHelper` for all `MenuHelper` classes
 * @see \MeCms\View\Helper\MenuBuilderHelper::generate() for more information
 * @property \MeCms\View\Helper\IdentityHelper $Identity
 */
abstract class AbstractMenuHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = ['MeCms.Identity'];
}
