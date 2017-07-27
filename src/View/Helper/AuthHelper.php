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
     * @see http://api.cakephp.org/3.4/class-Cake.View.Helper.html#_initialize
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
