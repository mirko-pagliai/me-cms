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
namespace MeCms\TestSuite\Traits;

/**
 * This trait provides some useful methods to test authentication within the
 *  controllers.
 *
 * It is necessary that the current controller is contained in the `$Controller`
 *  property of the test class.
 */
trait AuthMethodsTrait
{
    /**
     * Asserts that groups are authorized
     * @param array $values Group name as key and boolean as value
     * @return void
     * @uses $Controller
     * @uses setUserGroup()
     */
    public function assertGroupsAreAuthorized($values)
    {
        if (empty($this->Controller)) {
            $this->fail('The property `$this->Controller` has not been set');
        }

        foreach ($values as $group => $isAllowed) {
            $this->setUserGroup($group);
            $this->assertEquals($isAllowed, $this->Controller->isAuthorized());
        }
    }

    /**
     * Asserts that users are authorized
     * @param array $values UserID as key and boolean as value
     * @return void
     * @uses $Controller
     * @uses setUserId()
     */
    public function assertUsersAreAuthorized($values)
    {
        if (empty($this->Controller)) {
            $this->fail('The property `$this->Controller` has not been set');
        }

        foreach ($values as $id => $isAllowed) {
            $this->setUserId($id);
            $this->assertEquals($isAllowed, $this->Controller->isAuthorized());
        }
    }

    /**
     * Internal method to set the user ID
     * @param int $id User ID
     * @return void
     * @uses $Controller
     */
    protected function setUserId($id)
    {
        if (!empty($this->Controller)) {
            $this->Controller->Auth->setUser(['id' => $id]);
        }

        if (method_exists($this, 'session')) {
            $this->session(['Auth.User.id' => $id]);
        }
    }

    /**
     * Internal method to set the user group
     * @param string $group Group name
     * @return void
     * @uses $Controller
     */
    protected function setUserGroup($group)
    {
        if (!empty($this->Controller)) {
            $this->Controller->Auth->setUser(['group' => ['name' => $group]]);
        }

        if (method_exists($this, 'session')) {
            $this->session(['Auth.User.group.name' => $group]);
        }
    }
}
