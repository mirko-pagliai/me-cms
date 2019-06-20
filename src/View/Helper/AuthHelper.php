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
     * Default configuration
     * @var array
     */
    protected $_defaultConfig = ['user' => []];

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this helper
     * @return void
     * @see http://api.cakephp.org/3.4/class-Cake.View.Helper.html#_initialize
     */
    public function initialize(array $config)
    {
        $this->setConfig($config);
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
        return in_array($this->user('id'), (array)$id);
    }

    /**
     * Checks whether the logged user is the admin founder (ID 1)
     * @return bool
     * @uses user()
     */
    public function isFounder()
    {
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
        return in_array($this->user('group.name'), (array)$group);
    }

    /**
     * Checks whether the user is logged in
     * @return bool
     * @uses user()
     */
    public function isLogged()
    {
        return (bool)$this->user('id');
    }

    /**
     * Get the current user from storage
     * @param string|null $key Field to retrieve. Leave null to get entire User
     *  record
     * @return mixed|null Either User record or null if no user is logged in,
     *  or retrieved field if key is specified
     */
    public function user($key = null)
    {
        return $key ? Hash::get($this->getConfig('user'), $key) : $this->getConfig('user');
    }
}
