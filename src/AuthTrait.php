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
 * @since       2.26.6
 */

namespace MeCms;

/**
 * AuthTrait.
 *
 * Provides some methods for classes that need to verify the data of the logged
 *  in user
 */
trait AuthTrait
{
    /**
     * Checks whether the logged user has a specific ID.
     *
     * If you pass an array of IDs, it will check that at least one matches.
     * @param string|int|array<int|string> $id User ID as string or array
     * @return bool
     * @uses user()
     */
    public function hasId($id): bool
    {
        return in_array($this->user('id'), (array)$id);
    }

    /**
     * Checks whether the logged user is the admin founder (ID 1)
     * @return bool
     * @uses user()
     */
    public function isFounder(): bool
    {
        return $this->user('id') === 1;
    }

    /**
     * Checks whether the logged user belongs to a group.
     *
     * If you pass an array of groups, it will check that at least one matches.
     * @param string|array $group User group as string or array
     * @return bool
     * @uses user()
     */
    public function isGroup($group): bool
    {
        return in_array($this->user('group.name'), (array)$group);
    }

    /**
     * Checks whether the user is logged in
     * @return bool
     * @uses user()
     */
    public function isLogged(): bool
    {
        return (bool)$this->user('id');
    }
}
