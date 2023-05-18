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
 * @since       2.31.0
 */

namespace MeCms\View\Helper;

use Authentication\View\Helper\IdentityHelper as CakeIdentityHelper;
use Tools\Exceptionist;

/**
 * Identity Helper.
 *
 * A convenience helper to access the identity data.
 */
class IdentityHelper extends CakeIdentityHelper
{
    /**
     * Checks whether the logged user belongs to a user group.
     *
     * If you compare with several user groups, it will check that at least one matches.
     * @param string ...$group User group
     * @return bool
     * @throws \ErrorException
     */
    public function isGroup(string ...$group): bool
    {
        return in_array(Exceptionist::isTrue($this->get('group.name'), '`group.name` path is missing'), $group);
    }
}
