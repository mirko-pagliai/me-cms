<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

use Cake\Collection\Collection;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Laminas\Diactoros\UploadedFile;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\Admin\ControllerTestCase;
use Tools\Filesystem;

/**
 * UsersControllerTest class
 * @property \MeCms\Model\Table\UsersTable $Table
 * @group admin-controller
 */
class UsersControllerTest extends ControllerTestCase
{
    /**
     * @var array
     */
    protected static array $example = [
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
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::beforeFilter()
     */
    public function testBeforeFilter(): void
    {
        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [2]);
            $this->assertResponseOkAndNotEmpty();
            $this->assertNotEmpty($this->viewVariable('groups'));
        }

        //Other actions (for example `changePassword`) still work
        $this->get($this->url + ['action' => 'changePassword']);
        $this->assertEmpty($this->viewVariable('groups'));

        //Deletes all categories
        $this->Table->Groups->deleteAll(['id IS NOT' => null]);

        //`add` and `edit` actions don't work
        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'UsersGroups', 'action' => 'index']);
            $this->assertFlashMessage('You must first create an user group');
        }

        //Other actions (for example `changePassword`) still work
        $this->get($this->url + ['action' => 'changePassword']);
        $this->assertEmpty($this->viewVariable('groups'));
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::isAuthorized()
     */
    public function testIsAuthorized(): void
    {
        foreach (['changePassword', 'changePicture'] as $action) {
            $this->assertAllGroupsAreAuthorized($action);
        }

        foreach (['activate', 'delete'] as $action) {
            $this->assertOnlyAdminIsAuthorized($action);
        }

        parent::testIsAuthorized();
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::index()
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(User::class, $this->viewVariable('users'));
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::view()
     */
    public function testView(): void
    {
        $url = $this->url + ['action' => 'view', 1];

        Configure::write('MeCms.users.login_log', 0);
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'view.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));
        $this->assertEmpty($this->viewVariable('loginLog'));

        Configure::write('MeCms.users.login_log', 1);
        $this->get($url);
        $this->assertContainsOnlyInstancesOf(Entity::class, $this->viewVariable('loginLog'));
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::add()
     */
    public function testAdd(): void
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'add.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. Data are valid
        $this->post($url, self::$example);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['username' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::edit()
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 2];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'edit.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. Data are valid
        $this->post($url, ['first_name' => 'Gamma']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['first_name' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        $url = $this->url + ['action' => 'edit', 1];

        //An admin cannot edit other admin users
        $this->setAuthData('admin', 2);
        $this->get($url);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Only the admin founder can do this');

        //The admin founder can edit others admin users
        $this->setAuthData('admin');
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::delete()
     */
    public function testDelete(): void
    {
        $url = $this->url + ['action' => 'delete'];

        $this->post($url + [2]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->all()->isEmpty());

        //Cannot delete the admin founder
        $this->post($url + [1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('You cannot delete the admin founder');
        $this->assertFalse($this->Table->findById(1)->all()->isEmpty());

        //Cannot delete a user with posts
        $this->post($url + [4]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
        $this->assertFalse($this->Table->findById(4)->all()->isEmpty());

        //Only the admin founder can delete others admin users
        $this->setAuthData('admin', 2);
        $this->post($url + [5]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('Only the admin founder can do this');
        $this->assertFalse($this->Table->findById(5)->all()->isEmpty());
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::activate()
     */
    public function testActivate(): void
    {
        $this->get($this->url + ['action' => 'activate', 2]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->all()->extract('active')->first());
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::changePassword()
     */
    public function testChangePassword(): void
    {
        $oldPassword = 'OldPassword1"';
        $url = $this->url + ['action' => 'changePassword'];

        //Saves the password for the first user
        $user = $this->Table->get(1);
        $this->Table->save($user->set('password', $oldPassword));

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'change_password.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. Data are valid
        $this->post($url, [
            'password_old' => $oldPassword,
            'password' => 'newPassword!1',
            'password_repeat' => 'newPassword!1',
        ]);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //The password has changed
        $this->assertNotEquals($user->get('password'), $this->Table->findById(1)->all()->extract('password')->first());

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
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //The password has not changed
        $this->assertEquals($user->get('password'), $this->Table->findById(1)->all()->extract('password')->first());
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::changePicture()
     */
    public function testChangePicture(): void
    {
        $expectedPicture = USER_PICTURES . '1.jpg';
        $url = $this->url + ['action' => 'changePicture'];

        //GET request
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'change_picture.php');
        $this->assertSessionEmpty('Auth.User.picture');

        //Creates some files that simulate previous user pictures. These files will be deleted before upload
        array_map([new Filesystem(), 'createFile'], [$expectedPicture, USER_PICTURES . '1.jpeg', USER_PICTURES . '1.png']);

        //POST request. This works
        $file = $this->createImageToUpload();
        $this->post($url + ['_ext' => 'json'], compact('file'));
        $this->assertResponseOk();
        $this->assertSession(basename(USER_PICTURES) . DS . '1.jpg', 'Auth.picture');
        $this->assertFileExists($expectedPicture);
        array_map([$this, 'assertFileDoesNotExist'], [USER_PICTURES . '1.jpeg', USER_PICTURES . '1.png']);

        unlink($expectedPicture);
    }

    /**
     * Tests for `changePicture()` method, error during the upload
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::changePicture()
     */
    public function testChangePictureErrorDuringUpload(): void
    {
        $file = new UploadedFile('', 0, UPLOAD_ERR_NO_FILE);
        $this->post($this->url + ['action' => 'changePicture', '_ext' => 'json'], compact('file'));
        $this->assertResponseFailure();
        $this->assertResponseEquals('{"error":"No file was uploaded"}');
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'json' . DS . 'change_picture.php');
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\UsersController::lastLogin()
     */
    public function testLastLogin(): void
    {
        /** @var \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $LoginRecorder */
        $LoginRecorder = $this->createPartialMock(LoginRecorderComponent::class, ['getController', 'getUserAgent']);
        $LoginRecorder->method('getController')->willReturn($this->Controller);
        $LoginRecorder->method('getUserAgent')->willReturn(['platform' => 'Linux', 'browser' => 'Chrome', 'version' => '55.0.2883.87']);
        $LoginRecorder->setConfig('user', 1);

        //Writes a login log
        $this->assertTrue($LoginRecorder->write());

        $url = $this->url + ['action' => 'lastLogin'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Users' . DS . 'last_login.php');
        $this->assertNotEmpty($this->viewVariable('loginLog'));
        $this->assertInstanceOf(Collection::class, $this->viewVariable('loginLog'));

        //Disabled
        Configure::write('MeCms.users.login_log', false);

        $this->get($url);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage('Disabled');
    }
}
