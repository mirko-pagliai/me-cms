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
namespace MeCms\Test\TestCase\TestSuite;

use Cake\Controller\Controller as CakeController;
use Cake\Event\Event;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * Controller class
 */
class Controller extends CakeController
{
    /**
     * Checks if the user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized($user = null)
    {
        if ($this->Auth->user('id') === 2) {
            return false;
        }

        if ($this->Auth->user('group.name') === 'moderator') {
            return false;
        }

        return true;
    }
}

/**
 * IntegrationTestCase class
 */
class IntegrationTestCaseTest extends IntegrationTestCase
{
    /**
     * @var \Controller
     */
    protected $Controller;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Controller = new Controller;
        $this->Controller->loadComponent('Auth');
        $this->Controller->loadComponent('Cookie');
    }

    /**
     * Test for `controllerSpy()` method
     * @test
     */
    public function testControllerSpy()
    {
        $this->controllerSpy(new Event(null), $this->Controller);
        $this->assertEquals('somerandomhaskeysomerandomhaskey', $this->_controller->Cookie->config('key'));
    }

    /**
     * Test for `assertGroupsAreAuthorized()` method
     * @test
     */
    public function testAssertGroupsAreAuthorized()
    {
        $this->assertGroupsAreAuthorized(['admin' => true, 'moderator' => false]);
    }

    /**
     * Test for `assertGroupsAreAuthorized()` method on failure
     * @expectedException \PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessage The property `$this->Controller` has not been set
     * @test
     */
    public function testAssertGroupsAreAuthorizedFailure()
    {
        $this->Controller = false;
        $this->assertGroupsAreAuthorized(null);
    }

    /**
     * Test for `assertUsersAreAuthorized()` method
     * @test
     */
    public function testAssertUsersAreAuthorized()
    {
        $this->assertUsersAreAuthorized([1 => true, 2 => false]);
    }

    /**
     * Test for `assertUsersAreAuthorized()` method on failure
     * @expectedException \PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessage The property `$this->Controller` has not been set
     * @test
     */
    public function testAssertUsersAreAuthorizedFailure()
    {
        $this->Controller = false;
        $this->assertUsersAreAuthorized(null);
    }

    /**
     * Test for `setUserId()` method
     * @test
     */
    public function testSetUserId()
    {
        $this->setUserId(1);
        $this->assertEquals(1, $this->Controller->Auth->user('id'));
        $this->assertEquals(1, $this->_session['Auth.User.id']);
    }

    /**
     * Test for `setUserGroup()` method
     * @test
     */
    public function testSetUserGroup()
    {
        $this->setUserGroup('admin');
        $this->assertEquals('admin', $this->Controller->Auth->user('group.name'));
        $this->assertEquals('admin', $this->_session['Auth.User.group.name']);
    }
}
