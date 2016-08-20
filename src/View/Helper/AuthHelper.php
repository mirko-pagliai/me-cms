<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * Auth Helper.
 *
 * This helper allows you to check the user data.
 */
class AuthHelper extends Helper
{
    /**
     * User data.
     * You should use the `user()` method to access user data.
     * @var array
     * @see user()
     */
    protected $user;

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this helper
     * @return void
     * @uses $user
     * @see http://api.cakephp.org/3.3/class-Cake.View.Helper.html#_initialize
     */
    public function initialize(array $config)
    {
        $this->user = $config;
    }

    /**
     * Checks whether the logged user has a specific ID.
     *
     * You can pass the ID as string or array of IDs.
     * In the last case, it will be sufficient that the user has one of the IDs.
     * @param string|array $id User ID as string or array
     * @return bool
     * @uses user()
     */
    public function hasId($id)
    {
        if (!$this->user('id')) {
            return false;
        }

        return in_array($this->user('id'), (array)$id);
    }

    /**
     * Checks whether the logged user is the admin founder (ID 1)
     * @return bool
     * @uses user()
     */
    public function isFounder()
    {
        if (!$this->user('id')) {
            return false;
        }

        return $this->user('id') === 1;
    }

    /**
     * Checks whether the logged user belongs to a group.
     *
     * You can pass the group as string or array of groups.
     * In the last case, it will be sufficient that the user belongs to one of
     *  the groups.
     * @param string|array $group User group as string or array
     * @return bool
     * @uses user()
     */
    public function isGroup($group)
    {
        if (!$this->user('group.name')) {
            return false;
        }

        return in_array($this->user('group.name'), (array)$group);
    }

    /**
     * Checks whether the user is logged in
     * @return bool
     * @uses user()
     */
    public function isLogged()
    {
        return !empty($this->user('id'));
    }

    /**
     * Get the current user from storage
     * @param string|null $key Field to retrieve. Leave null to get entire User
     *  record
     * @return mixed|null Either User record or null if no user is logged in,
     *  or retrieved field if key is specified
     * @uses $user
     */
    public function user($key = null)
    {
        if (empty($this->user)) {
            return null;
        }

        if ($key === null) {
            return $this->user;
        }

        return Hash::get($this->user, $key);
    }
}
