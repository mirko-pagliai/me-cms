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
 */

namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\ControllerTestCase;
use Tools\Filesystem;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends ControllerTestCase
{
    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Table;

    /**
     * @var array
     */
    protected static $example = [
        'group_id' => 1,
        'username' => 'new-username',
        'email' => 'new-test-email@example.com',
        'email_repeat' => 'new-test-email@example.com',
        'password' => 'Password1!',
        'password_repeat' => 'Password1!',
        'first_name' => 'Alfa',
        'last_name' => 'Beta',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter(): void
    {
        parent::testBeforeFilter();

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
    public function testBeforeFilterNoGroups(): void
    {
        //Deletes all categories
        $this->Table->Groups->deleteAll(['id IS NOT' => null]);

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
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized(): void
    {
        parent::testIsAuthorized();

        //With `changePassword` action
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => true,
        ], 'changePassword');

        //With `activate` and `delete` actions
        foreach (['activate', 'delete'] as $action) {
            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => false,
                'user' => false,
            ], $action);
        }
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(User::class, $this->viewVariable('users'));
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView(): void
    {
        $url = $this->url + ['action' => 'view', 1];

        Configure::write('MeCms.users.login_log', 0);
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'view.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));
        $this->assertEmpty($this->viewVariable('loginLog'));

        Configure::write('MeCms.users.login_log', 1);
        $this->get($url);
        $this->assertContainsOnlyInstancesOf(Entity::class, $this->viewVariable('loginLog'));
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd(): void
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'add.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. Data are valid
        $this->post($url, self::$example);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['username' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(User::class, $this->viewVariable('user'));
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 2];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'edit.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. Data are valid
        $this->post($url, ['first_name' => 'Gamma']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        $url = $this->url + ['action' => 'edit', 1];

        //An admin cannot edit other admin users
        $this->get($url);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Only the admin founder can do this');

        //The admin founder can edit others admin users
        $this->setUserId(1);
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete(): void
    {
        $url = $this->url + ['action' => 'delete'];

        $this->post($url + [2]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->isEmpty());

        //Cannot delete the admin founder
        $this->post($url + [1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('You cannot delete the admin founder');
        $this->assertFalse($this->Table->findById(1)->isEmpty());

        //Cannot delete an user with posts
        $this->post($url + [4]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
        $this->assertFalse($this->Table->findById(4)->isEmpty());

        //Only the admin founder can delete others admin users
        $this->post($url + [5]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Only the admin founder can do this');
        $this->assertFalse($this->Table->findById(5)->isEmpty());
    }

    /**
     * Tests for `activate()` method
     * @test
     */
    public function testActivate(): void
    {
        $this->get($this->url + ['action' => 'activate', 2]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->extract('active')->first());
    }

    /**
     * Tests for `changePassword()` method
     * @test
     */
    public function testChangePassword(): void
    {
        $oldPassword = 'OldPassword1"';
        $url = $this->url + ['action' => 'changePassword'];
        $this->setUserId(1);

        //Saves the password for the first user
        $user = $this->Table->get(1);
        $this->Table->save($user->set('password', $oldPassword));

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'change_password.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. Data are valid
        $this->post($url, [
            'password_old' => $oldPassword,
            'password' => 'newPassword!1',
            'password_repeat' => 'newPassword!1',
        ]);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //The password has changed
        $this->assertNotEquals($user->get('password'), $this->Table->findById(1)->extract('password')->first());

        //Saves the password for the first user
        $user = $this->Table->get(1);
        $this->Table->save($user->set('password', $oldPassword));

        //POST request. Data are invalid (the old password is wrong)
        $this->post($url, [
            'password_old' => 'wrongOldPassword!1',
            'password' => 'newPassword!1',
            'password_repeat' => 'newPassword!1',
        ]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //The password has not changed
        $this->assertEquals($user->get('password'), $this->Table->findById(1)->extract('password')->first());
    }

    /**
     * Tests for `changePicture()` method
     * @test
     */
    public function testChangePicture(): void
    {
        $expectedPicture = USER_PICTURES . '1.jpg';
        $file = $this->createImageToUpload();
        $url = $this->url + ['action' => 'changePicture'];
        $this->setUserId(1);

        //GET request
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'change_picture.php');
        $this->assertSessionEmpty('Auth.User.picture');

        //Creates some files that simulate previous user pictures. These files
        //  will be deleted before upload
        array_map([new Filesystem(), 'createFile'], [$expectedPicture, USER_PICTURES . '1.jpeg', USER_PICTURES . '1.png']);

        //POST request. This works
        $this->post($url + ['_ext' => 'json'], compact('file'));
        $this->assertResponseOk();
        $this->assertSession($expectedPicture, 'Auth.User.picture');
        $this->assertFileExists($expectedPicture);
        array_map([$this, 'assertFileDoesNotExist'], [USER_PICTURES . '1.jpeg', USER_PICTURES . '1.png']);

        @unlink($expectedPicture);
    }

    /**
     * Tests for `changePicture()` method, error during the upload
     * @test
     */
    public function testChangePictureErrorDuringUpload(): void
    {
        $file = ['error' => UPLOAD_ERR_NO_FILE] + $this->createImageToUpload();
        $this->post($this->url + ['action' => 'changePicture', '_ext' => 'json'], compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"No file was uploaded"}');
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'json' . DS . 'change_picture.php');
    }

    /**
     * Tests for `lastLogin()` method
     * @test
     */
    public function testLastLogin(): void
    {
        /** @var \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $LoginRecorder */
        $LoginRecorder = $this->getMockForComponent(LoginRecorderComponent::class, ['getController', 'getUserAgent']);
        $LoginRecorder->method('getController')->will($this->returnValue($this->Controller));
        $LoginRecorder->method('getUserAgent')
            ->will($this->returnValue([
                'platform' => 'Linux',
                'browser' => 'Chrome',
                'version' => '55.0.2883.87',
            ]));
        $LoginRecorder->setConfig('user', 1);

        //Writes a login log
        $this->assertTrue($LoginRecorder->write());

        $this->setUserId(1);
        $url = $this->url + ['action' => 'lastLogin'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'last_login.php');
        $this->assertNotEmpty($this->viewVariable('loginLog'));
        $this->assertIsArray($this->viewVariable('loginLog'));

        //Disabled
        Configure::write('MeCms.users.login_log', false);

        $this->get($url);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage('Disabled');
    }
}
