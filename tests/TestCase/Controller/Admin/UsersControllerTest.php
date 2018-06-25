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
use MeCms\Controller\Admin\UsersController;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends IntegrationTestCase
{
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
     * Internal method to create a file to upload.
     *
     * It returns an array, similar to the `$_FILE` array that is created after
     *  a upload
     * @return array
     */
    protected function createFileToUpload()
    {
        $file = TMP . 'file_to_upload.jpg';

        safe_copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);

        return [
            'tmp_name' => $file,
            'error' => UPLOAD_ERR_OK,
            'name' => basename($file),
            'type' => mime_content_type($file),
            'size' => filesize($file),
        ];
    }

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
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        //Mocks the `Uploader` component
        $this->_controller->Uploader = $this->getMockBuilder(get_class($this->_controller->Uploader))
            ->setConstructorArgs([new ComponentRegistry])
            ->setMethods(['move_uploaded_file'])
            ->getMock();

        $this->_controller->Uploader->method('move_uploaded_file')
            ->will($this->returnCallback(function ($filename, $destination) {
                return rename($filename, $destination);
            }));
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [2]);
            $this->assertNotEmpty($this->viewVariable('groups'));
        }

        //Other actions, for example `changePassword`, still work
        $this->setUserId(1);
        $this->get($this->url + ['action' => 'changePassword']);
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
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'UsersGroups', 'action' => 'index']);
            $this->assertFlashMessage('You must first create an user group');
        }

        //Other actions, for example `changePassword`, still work
        $this->setUserId(1);
        $this->get($this->url + ['action' => 'changePassword']);
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
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/index.ctp');

        $usersFromView = $this->viewVariable('users');
        $this->assertNotEmpty($usersFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $usersFromView);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        Configure::write(ME_CMS . '.users.login_log', 0);

        $url = $this->url + ['action' => 'view', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/view.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        $loginLogFromView = $this->viewVariable('loginLog');
        $this->assertEmpty($loginLogFromView);

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
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/add.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

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
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['username' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 2];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/edit.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        //POST request. Data are valid
        $this->post($url, ['first_name' => 'Gamma']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        $url = $this->url + ['action' => 'edit', $this->Users->findByGroupId(1)->extract('id')->first()];

        //An admin cannot edit other admin users
        $this->get($url);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Only the admin founder can do this');

        $this->setUserId(1);

        //The admin founder can edit others admin users
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $url = $this->url + ['action' => 'delete'];

        //Cannot delete the admin founder
        $this->post($url + [1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('You cannot delete the admin founder');

        $adminUser = $this->Users->find()
            ->where(['group_id' => 1, 'id !=' => 1])
            ->extract('id')
            ->first();

        //Only the admin founder can delete others admin users
        $this->post($url + [$adminUser]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Only the admin founder can do this');

        $userWithPosts = $this->Users->find()
            ->where(['group_id !=' => 1, 'post_count >=' => 1])
            ->extract('id')
            ->first();

        $this->post($url + [$userWithPosts]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);

        $userWithNoPosts = $this->Users->find()
            ->where(['group_id !=' => 1, 'post_count' => 0])
            ->extract('id')
            ->first();

        $this->post($url + [$userWithNoPosts]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
    }

    /**
     * Tests for `activate()` method
     * @test
     */
    public function testActivate()
    {
        $pendingUser = $this->Users->find('pending')->extract('id')->first();

        $this->get($this->url + ['action' => 'activate', $pendingUser]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

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
        $url = $this->url + ['action' => 'changePassword'];
        $this->setUserId(1);

        //Saves the password for the first user
        $user = $this->Users->get(1);
        $user->password = $oldPassword;
        $this->Users->save($user);

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/change_password.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        //POST request. Data are valid
        $this->post($url, [
            'password_old' => $oldPassword,
            'password' => 'newPassword!1',
            'password_repeat' => 'newPassword!1',
        ]);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage('The operation has been performed correctly');

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
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        //The password has not changed
        $this->assertEquals($user->password, $this->Users->findById(1)->extract('password')->first());

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);
    }

    /**
     * Tests for `changePicture()` method
     * @test
     */
    public function testChangePicture()
    {
        $expectedPicture = USER_PICTURES . '1.jpg';
        $file = $this->createFileToUpload();
        $url = $this->url + ['action' => 'changePicture'];
        $this->setUserId(1);

        //GET request
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/change_picture.ctp');

        //Creates some files that simulate previous user pictures. These files
        //  will be deleted before upload
        file_put_contents($expectedPicture, null);
        file_put_contents(USER_PICTURES . '1.jpeg', null);
        file_put_contents(USER_PICTURES . '1.png', null);

        $this->assertSession(null, 'Auth.User.picture');

        //POST request. This works
        $this->post($url + ['_ext' => 'json'], compact('file'));
        $this->assertResponseOkAndNotEmpty();
        $this->assertSession($expectedPicture, 'Auth.User.picture');
        $this->assertFileExists($expectedPicture);
        $this->assertFileNotExists(USER_PICTURES . '1.jpeg');
        $this->assertFileNotExists(USER_PICTURES . '1.png');

        safe_unlink($expectedPicture);
    }

    /**
     * Tests for `changePicture()` method, error during the upload
     * @test
     */
    public function testChangePictureErrorDuringUpload()
    {
        $file = ['error' => UPLOAD_ERR_NO_FILE] + $this->createFileToUpload();

        $this->post($this->url + ['action' => 'changePicture', '_ext' => 'json'], compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"No file was uploaded"}');
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/json/change_picture.ctp');
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

        $url = $this->url + ['action' => 'lastLogin'];

        $this->setUserId(1);

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Users/last_login.ctp');

        $loginLogFromView = $this->viewVariable('loginLog');
        $this->assertNotEmpty($loginLogFromView);
        $this->assertIsArray($loginLogFromView);

        //Disabled
        Configure::write(ME_CMS . '.users.login_log', false);

        $this->get($url);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage('Disabled');
    }
}
