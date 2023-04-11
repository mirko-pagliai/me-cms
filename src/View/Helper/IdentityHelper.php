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

/**
 * Identity Helper.
 *
 * A convenience helper to access the identity data.
 */
class IdentityHelper extends CakeIdentityHelper
{
    /**
     * Checks whether the logged user belongs to a group.
     *
     * If you compare with several groups, it will check that at least one matches.
     * @param string ...$group User group
     * @return bool
     */
    public function isGroup(string ...$group): bool
    {
        return in_array($this->get('group.name'), $group);
    }
}
