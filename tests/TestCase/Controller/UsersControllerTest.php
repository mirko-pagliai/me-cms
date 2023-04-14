<?php
/** @noinspection PhpDocMissingThrowsInspection */
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

namespace MeCms\Test\TestCase\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\ControllerTestCase;
use Tokens\Controller\Component\TokenComponent;

/**
 * UsersControllerTest class
 * @property \MeCms\Model\Table\UsersTable $Table
 */
class UsersControllerTest extends ControllerTestCase
{
    /**
     * @var \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected LoginRecorderComponent $LoginRecorder;

    /**
     * @var \Tokens\Controller\Component\TokenComponent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected TokenComponent $Token;

    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Tokens',
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($this->LoginRecorder)) {
            $this->LoginRecorder = $this->createPartialMock(LoginRecorderComponent::class, ['getController', 'getUserAgent']);
            $this->LoginRecorder->method('getController')->willReturn($this->Controller);
        }

        $this->Token ??= $this->createPartialMock(TokenComponent::class, []);

        $this->enableRetainFlashMessages();
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\EventInterface $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
        parent::controllerSpy($event, $controller);

        $this->_controller->LoginRecorder = $this->LoginRecorder;
    }

    /**
     * @uses \MeCms\Controller\UsersController::beforeFilter()
     * @test
     */
    public function testBeforeFilter(): void
    {
        $this->setAuthData();
        $this->get(['_name' => 'login']);
        $this->assertRedirect(['_name' => 'dashboard']);
    }

    /**
     * @uses \MeCms\Controller\UsersController::activation()
     * @test
     */
    public function testActivation(): void
    {
        //Creates a token for an active user
        $tokenOptions = ['type' => 'signup', 'user_id' => 1];
        $Token = $this->Token->create('alfa@test.com', $tokenOptions);
        $url = ['_name' => 'activation'];

        //GET request. This request is invalid, because the user is already active
        $this->get($url + ['id' => '1', 'token' => $Token]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);
        $this->assertFalse($this->Token->check($Token, $tokenOptions));

        //Creates a token for a pending user
        $tokenOptions = ['type' => 'signup', 'user_id' => 2];
        $Token = $this->Token->create('gamma@test.com', $tokenOptions);

        //GET request. This request is valid, because the user is pending
        $this->get($url + ['id' => '2', 'token' => $Token]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->all()->extract('active')->first());
        $this->assertFalse($this->Token->check($Token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
        $this->disableErrorHandlerMiddleware();
        $this->get($url + ['id' => '1'] + ['token' => 'invalidToken']);
    }

    /**
     * @uses \MeCms\Controller\UsersController::activationResend()
     * @test
     */
    public function testActivationResend(): void
    {
        $url = ['_name' => 'activationResend'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'activation_resend.php');
        $this->assertLayout('single-column.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $wrongEmail = 'wrongEmail@example.com';
        $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No valid account was found');
        $this->assertLogContains('Resend activation request: invalid email `' . $wrongEmail . '`', 'users');

        //POST request. Now, data are valid
        $this->post($url, ['email' => 'gamma@test.com', 'email_repeat' => 'gamma@test.com']);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('We send you an email to activate your account');

        //With reCAPTCHA
        Configure::write('MeCms.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write('MeCms.users', ['signup' => false, 'activation' => 1]);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }

    /**
     * @uses \MeCms\Controller\Component\AuthenticationComponent::afterIdentify()
     * @uses \MeCms\Controller\UsersController::login()
     * @test
     */
    public function testLogin(): void
    {
        $url = ['_name' => 'login'];

        //Gets a user and sets a new password. For now, the `LoginRecorder` component is empty
        /** @var \MeCms\Model\Entity\User $User */
        $User = $this->Table->find('active')->firstOrFail();
        $password = 'newPassword1!';
        $this->Table->save($User->set('password', $password));
        $this->LoginRecorder->setConfig('user', $User->get('id'));
        $this->assertTrue($this->LoginRecorder->read()->isEmpty());

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('login.php');
        $this->assertLayout('single-column.php');

        $this->post($url, ['username' => $User->get('username')] + compact('password'));
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertSession($User->get('id'), 'Auth.id');
        $this->assertCount(1, $this->LoginRecorder->read());
    }

    /**
     * Test for `login()` method with a banned and a not active user
     * @uses \MeCms\Controller\Component\AuthenticationComponent::afterIdentify()
     * @uses \MeCms\Controller\UsersController::login()
     * @test
     */
    public function testLoginWithBannedOrNotActiveUser(): void
    {
        $url = ['_name' => 'login'];

        //Gets a banned user and sets a new password. For now, the `LoginRecorder` component is empty
        /** @var \MeCms\Model\Entity\User $User */
        $User = $this->Table->find('banned')->firstOrFail();
        $password = 'newPassword1!';
        $this->Table->save($User->set(compact('password')));
        $this->LoginRecorder->setConfig('user', $User->get('id'));

        //The user is banned
        $this->post($url, ['username' => $User->get('username')] + compact('password'));
        $this->assertRedirect('/');
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Your account has been banned by an admin');

        //The user is no longer banned, but now is not active
        $this->Table->save($User->set(['active' => false, 'banned' => false]));
        $this->post($url, ['username' => $User->get('username')] + compact('password'));
        $this->assertRedirect('/');
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Your account has not been activated yet');

        //The `LoginRecorder` component is always empty
        $this->assertTrue($this->LoginRecorder->read()->isEmpty());
    }

    /**
     * Test for `login()` method with a banned and a pending user
     * @uses \MeCms\Controller\Component\AuthenticationComponent::afterIdentify()
     * @uses \MeCms\Controller\UsersController::login()
     * @test
     */
    public function testLoginWithInvalidData(): void
    {
        $this->post(['_name' => 'login'], ['username' => 'wrong', 'password' => 'wrong']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Invalid username or password');
        $this->assertLogContains('Failed login: username `wrong`, password `wrong`', 'users');
    }

    /**
     * @uses \MeCms\Controller\UsersController::logout()
     * @test
     */
    public function testLogout(): void
    {
        $this->setAuthData();

        //The user is currently logged in
        $this->get('/');
        $this->assertSession(1, 'Auth.id');

        $this->get(['_name' => 'logout']);
        $this->assertResponseCode(302);
        $this->assertSessionEmpty('Auth');
    }

    /**
     * @test
     * @uses \MeCms\Controller\UsersController::passwordForgot()
     */
    public function testPasswordForgot(): void
    {
        $url = ['_name' => 'passwordForgot'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'password_forgot.php');
        $this->assertLayout('single-column.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. No existing mail address and user pending email
        foreach (['wrongMail@example.it', 'gamma@test.com'] as $wrongEmail) {
            $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
            $this->assertResponseOkAndNotEmpty();
            $this->assertResponseContains('No account found');
            $this->assertLogContains('Forgot password request: invalid email `' . $wrongEmail . '`', 'users');
            $this->deleteLog('users');
        }

        //POST request. This request is valid
        $this->post($url, ['email' => 'alfa@test.com', 'email_repeat' => 'alfa@test.com']);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('We have sent you an email to reset your password');

        //With reCAPTCHA
        Configure::write('MeCms.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write('MeCms.users.reset_password', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }

    /**
     * @test
     * @uses \MeCms\Controller\UsersController::passwordReset()
     */
    public function testPasswordReset(): void
    {
        //Creates the token for an active user
        $tokenOptions = ['type' => 'password_forgot', 'user_id' => 1];
        $Token = $this->Token->create('alfa@test.com', $tokenOptions);
        $url = ['_name' => 'passwordReset', 'id' => '1', 'token' => $Token];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'password_reset.php');
        $this->assertLayout('single-column.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. Data are invalid
        $this->post($url, ['password' => '', 'password_repeat' => '']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The password has not been edited');

        //The password has not been changed and the token still exists
        $this->assertTrue($this->Token->check($Token, $tokenOptions));
        $this->assertEmpty($this->Table->findById(1)->all()->extract('password')->first());

        //POST request again. Now data are valid
        $this->post($url, ['password' => 'newPassword1!', 'password_repeat' => 'newPassword1!']);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('The password has been edited');

        //The password has changed and the token no longer exists
        $this->assertNotEmpty($this->Table->findById(1)->all()->extract('password')->first());
        $this->assertFalse($this->Token->check($Token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
        $this->disableErrorHandlerMiddleware();
        $this->get(['token' => 'invalidToken'] + $url);
    }

    /**
     * @test
     * @uses \MeCms\Controller\UsersController::signup()
     */
    public function testSignup(): void
    {
        $data = [
            'username' => 'example',
            'email' => 'example@example.it',
            'email_repeat' => 'example@example.it',
            'password' => 'Password1!',
            'password_repeat' => 'Password1!',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
        ];
        $url = ['_name' => 'signup'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'signup.php');
        $this->assertLayout('single-column.php');
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $this->post($url, ['password' => 'anotherPassword'] + $data);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The account has not been created');

        //POST request. Data are valid, the account needs to be activated by an admin
        Configure::write('MeCms.users.activation', 2);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created, but it needs to be activated by an admin');
        /** @var \MeCms\Model\Entity\User $User */
        $User = $this->Table->findByUsername($data['username'])->firstOrFail();
        $this->assertEquals(['group_id' => getConfigOrFail('users.default_group'), 'active' => false], $User->extract(['group_id', 'active']));

        //Deletes the user
        $this->Table->deleteAll(['username' => $data['username']]);

        //POST request. Data are valid, an email is sent to the user
        Configure::write('MeCms.users.activation', 1);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('We send you an email to activate your account');
        /** @var \MeCms\Model\Entity\User $User */
        $User = $this->Table->findByUsername($data['username'])->firstOrFail();
        $this->assertEquals(['group_id' => getConfigOrFail('users.default_group'), 'active' => false], $User->extract(['group_id', 'active']));

        //Deletes the user
        $this->Table->deleteAll(['username' => $data['username']]);

        //POST request. Data are valid
        Configure::write('MeCms.users.activation', 0);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created. Now you can login');
        /** @var \MeCms\Model\Entity\User $User */
        $User = $this->Table->findByUsername($data['username'])->firstOrFail();
        $this->assertEquals(['group_id' => getConfigOrFail('users.default_group'), 'active' => true], $User->extract(['group_id', 'active']));

        //With reCAPTCHA
        Configure::write('MeCms.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write('MeCms.users.signup', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }
}
