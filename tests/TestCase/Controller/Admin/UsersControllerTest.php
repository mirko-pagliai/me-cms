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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\UsersController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\UsersController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * @var array
     */
    protected $url;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUserGroup('admin');

        $this->Controller = new UsersController;

        $this->Users = TableRegistry::get('MeCms.Users');

        Cache::clear(false, $this->Users->cache);

        $this->url = ['controller' => 'Users', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->Users);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['index', 'add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [2]));
            $this->assertResponseOk();
            $this->assertNotEmpty($this->viewVariable('groups'));
        }

        //Other actions, for example `changePassword`, still work
        $this->setUserId(1);
        $this->get(array_merge($this->url, ['action' => 'changePassword']));
        $this->assertResponseOk();
        $this->assertEmpty($this->viewVariable('groups'));
    }

    /**
     * Tests for `beforeFilter()` method, with no groups
     * @test
     */
    public function testBeforeFilterNoGroups()
    {
        //Deletes all categories
        $this->Users->Groups->deleteAll(['id IS NOT' => null]);

        //`add` and `edit` actions don't work
        foreach (['index', 'add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
            $this->assertRedirect(['controller' => 'UsersGroups', 'action' => 'index']);
            $this->assertSession('You must first create an user group', 'Flash.flash.0.message');
        }

        //Other actions, for example `changePassword`, still work
        $this->setUserId(1);
        $this->get(array_merge($this->url, ['action' => 'changePassword']));
        $this->assertResponseOk();
        $this->assertEmpty($this->viewVariable('groups'));
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->Controller = new UsersController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'index');
        $this->Controller->initialize();

        $this->assertContains('LoginRecorder', $this->Controller->components()->loaded());
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

        //`changePassword` action
        $this->Controller = new UsersController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'changePassword');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => true,
        ]);

        //`activate` and `delete` actions
        foreach (['activate', 'delete'] as $action) {
            $this->Controller = new UsersController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);

            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => false,
                'user' => false,
            ]);
        }
    }
}
