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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\UsersController;
use MeCms\Controller\Component\LoginRecorderComponent;
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
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * @var array
     */
    protected $url;

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

        $this->Users = TableRegistry::get(ME_CMS . '.Users');

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

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/index.ctp');

        $usersFromView = $this->viewVariable('users');
        $this->assertInstanceof('Cake\ORM\ResultSet', $usersFromView);
        $this->assertNotEmpty($usersFromView);

        foreach ($usersFromView as $user) {
            $this->assertInstanceof('MeCms\Model\Entity\User', $user);
        }
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $url = array_merge($this->url, ['action' => 'view', 1]);

        Configure::write(ME_CMS . '.users.login_log', 0);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/view.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);

        $loginLogFromView = $this->viewVariable('loginLog');
        $this->assertNull($loginLogFromView);

        Configure::write(ME_CMS . '.users.login_log', 1);

        $this->get($url);
        $loginLogFromView = $this->viewVariable('loginLog');
        $this->assertNotNull($loginLogFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/add.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);

        //POST request. Data are valid
        $this->post($url, [
            'group_id' => 1,
            'username' => 'new-username',
            'email' => 'new-test-email@example.com',
            'email_repeat' => 'new-test-email@example.com',
            'password' => 'Password1!',
            'password_repeat' => 'Password1!',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['username' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 2]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/edit.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);

        //POST request. Data are valid
        $this->post($url, ['first_name' => 'Gamma']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);

        $adminUser = $this->Users->find()->where(['group_id' => 1])->first();
        $url = array_merge($this->url, ['action' => 'edit', $adminUser->id]);

        //An admin cannot edit other admin users
        $this->get($url);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('Only the admin founder can do this', 'Flash.flash.0.message');

        $this->setUserId(1);

        //The admin founder can edit others admin users
        $this->get($url);
        $this->assertResponseOk();
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $url = array_merge($this->url, ['action' => 'delete']);

        //Cannot delete the admin founder
        $this->post(array_merge($url, [1]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('You cannot delete the admin founder', 'Flash.flash.0.message');

        $adminUser = $this->Users->find()
            ->where(['group_id' => 1, 'id !=' => 1])
            ->extract('id')
            ->first();

        //Only the admin founder can delete others admin users
        $this->post(array_merge($url, [$adminUser]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('Only the admin founder can do this', 'Flash.flash.0.message');

        $userWithPosts = $this->Users->find()
            ->where(['group_id !=' => 1, 'post_count >=' => 1])
            ->extract('id')
            ->first();

        $this->post(array_merge($url, [$userWithPosts]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession(
            'Before deleting this, you must delete or reassign all items that belong to this element',
            'Flash.flash.0.message'
        );

        $userWithNoPosts = $this->Users->find()
            ->where(['group_id !=' => 1, 'post_count' => 0])
            ->extract('id')
            ->first();

        $this->post(array_merge($url, [$userWithNoPosts]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');
    }

    /**
     * Tests for `activate()` method
     * @test
     */
    public function testActivate()
    {
        $pendingUser = $this->Users->findByActive(false)->extract('id')->first();

        $this->get(array_merge($this->url, ['action' => 'activate', $pendingUser]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //The user is now active
        $this->assertTrue($this->Users->findById($pendingUser)->extract('active')->first());
    }

    /**
     * Tests for `changePassword()` method
     * @test
     */
    public function testChangePassword()
    {
        $oldPassword = 'OldPassword1"';
        $url = array_merge($this->url, ['action' => 'changePassword']);

        $this->setUserId(1);

        //Saves the password for the first user
        $user = $this->Users->get(1);
        $user->password = $oldPassword;
        $this->Users->save($user);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/change_password.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);

        //POST request. Data are valid
        $this->post($url, [
            'password_old' => $oldPassword,
            'password' => 'newPassword!1',
            'password_repeat' => 'newPassword!1',
        ]);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //The password has changed
        $this->assertNotEquals($user->password, $this->Users->findById(1)->extract('password')->first());

        //Saves the password for the first user
        $user = $this->Users->get(1);
        $user->password = $oldPassword;
        $this->Users->save($user);

        //POST request. Data are invalid (the old password is wrong)
        $this->post($url, [
            'password_old' => 'wrongOldPassword!1',
            'password' => 'newPassword!1',
            'password_repeat' => 'newPassword!1',
        ]);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        //The password has not changed
        $this->assertEquals($user->password, $this->Users->findById(1)->extract('password')->first());

        $userFromView = $this->viewVariable('user');
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
        $this->assertNotEmpty($userFromView);
    }

    /**
     * Tests for `lastLogin()` method
     * @test
     */
    public function testLastLogin()
    {
        $this->LoginRecorder = $this->getMockBuilder(LoginRecorderComponent::class)
            ->setMethods(['getController', 'getUserAgent'])
            ->setConstructorArgs([new ComponentRegistry])
            ->getMock();

        $this->LoginRecorder->method('getController')
            ->will($this->returnValue($this->Controller));

        $this->LoginRecorder->method('getUserAgent')
            ->will($this->returnValue([
                'platform' => 'Linux',
                'browser' => 'Chrome',
                'version' => '55.0.2883.87',
            ]));

        $this->LoginRecorder->config('user', 1);

        //Writes a login log
        $this->assertTrue($this->LoginRecorder->write());

        $url = array_merge($this->url, ['action' => 'lastLogin']);

        $this->setUserId(1);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/last_login.ctp');

        $loginLogFromView = $this->viewVariable('loginLog');
        $this->assertTrue(is_array($loginLogFromView));
        $this->assertNotEmpty($loginLogFromView);

        //Disabled
        Configure::write(ME_CMS . '.users.login_log', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertSession('Disabled', 'Flash.flash.0.message');
    }
}
