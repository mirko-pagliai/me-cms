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
 * @since       2.20.0
 */
namespace MeCms\TestSuite;

use MeTools\TestSuite\IntegrationTestCase as BaseIntegrationTestCase;

/**
 * A test case class intended to make integration tests of your controllers
 *  easier.
 *
 * This test class provides a number of helper methods and features that make
 *  dispatching requests and checking their responses simpler. It favours full
 *  integration tests over mock objects as you can test more of your code
 *  easily and avoid some of the maintenance pitfalls that mock objects create.
 */
abstract class IntegrationTestCase extends BaseIntegrationTestCase
{
    /**
     * A controller instance
     * @var object
     */
    protected $Controller;

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes all logs
        safe_unlink_recursive(LOGS);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        $this->_controller->viewBuilder()->setLayout('with_flash');

        //Sets key for cookies
        $this->_controller->loadComponent('Cookie');
        $this->_controller->Cookie->setConfig('key', 'somerandomhaskeysomerandomhaskey');
    }

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
     * @param int $userId User ID
     * @return void
     * @uses $Controller
     */
    protected function setUserId($userId)
    {
        if (!empty($this->Controller)) {
            $this->Controller->Auth->setUser(['id' => $userId]);
        }

        $this->session(['Auth.User.id' => $userId]);
    }

    /**
     * Internal method to set the user group
     * @param string $groupName Group name
     * @return void
     * @uses $Controller
     */
    protected function setUserGroup($groupName)
    {
        if (!empty($this->Controller)) {
            $this->Controller->Auth->setUser(['group' => ['name' => $groupName]]);
        }

        $this->session(['Auth.User.group.name' => $groupName]);
    }
}
