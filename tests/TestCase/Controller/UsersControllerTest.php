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

namespace MeCms\Test\TestCase\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\I18n\Time;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\ControllerTestCase;
use Tokens\Controller\Component\TokenComponent;

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
     * @var \Tokens\Controller\Component\TokenComponent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $Token;

    /**
     * @var \MeCms\Controller\UsersController
     */
    protected $_controller;

    /**
     * Fixtures
     * @var array
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
    public function setUp(): void
    {
        parent::setUp();

        $this->Token = $this->Token ?: $this->getMockForComponent(TokenComponent::class, null);
    }

    /**
     * Asserts cookie values which are encrypted by the CookieComponent
     * @param mixed $expected The expected contents
     * @param string $name The cookie name
     * @param string $encrypt Encryption mode to use
     * @param string|null $key Encryption key used
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public function assertCookieEncrypted($expected, string $name, string $encrypt = 'aes', ?string $key = null, string $message = ''): void
    {
        $key = $key ?: Configure::read('Security.cookieKey', md5(Configure::read('Security.salt', '')));

        parent::assertCookieEncrypted($expected, $name, $encrypt, $key, $message);
    }

    /**
     * Sets a encrypted request cookie for future requests
     * @param string $name The cookie name to use
     * @param mixed $value The value of the cookie
     * @param string|false $encrypt Encryption mode to use
     * @param string|null $key Encryption key used
     * @return void
     */
    public function cookieEncrypted(string $name, $value, $encrypt = 'aes', $key = null): void
    {
        $key = $key ?: Configure::read('Security.cookieKey', md5(Configure::read('Security.salt', '')));

        parent::cookieEncrypted($name, $value, $encrypt, $key);
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

        /** @var \MeCms\Controller\Component\LoginRecorderComponent&\PHPUnit\Framework\MockObject\MockObject $LoginRecorder */
        $LoginRecorder = $this->getMockForComponent(LoginRecorderComponent::class);
        $LoginRecorder->method('setConfig')->will($this->returnSelf());
        $this->_controller->LoginRecorder = $LoginRecorder;
    }

    /**
     * Test for `loginWithCookie()` method
     * @test
     */
    public function testLoginWithCookie(): void
    {
        $url = ['_name' => 'login'];

        //No user data on cookies
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertSessionEmpty('Auth');

        //Writes wrong data on cookies
        $this->cookie('login', ['username' => 'a', 'password' => 'b']);
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertSessionEmpty('Auth');

        //Gets an user and sets a password, then writes right data on cookies
        $password = 'mypassword1!';
        $user = $this->Table->findByActiveAndBanned(true, false)->first();
        $this->Table->save($user->set(compact('password') + ['password_repeat' => $password]));
        $this->cookieEncrypted('login', ['username' => $user->username] + compact('password'));
        $this->get($url);
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertSession($user->get('id'), 'Auth.User.id');

        //"pending" and "banned" users
        foreach ([
            ['active' => false],
            ['active' => true, 'banned' => true],
        ] as $userData) {
            $this->Table->save($user->set($userData));
            $this->cookieEncrypted('login', ['username' => $user->username] + compact('password'));
            $this->get($url);
            $this->assertRedirect(['_name' => 'homepage']);
            $this->assertSessionEmpty('Auth');
            $this->assertCookieNotSet('login');
        }
    }

    /**
     * Test for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter(): void
    {
        parent::testBeforeFilter();

        $this->setUserId(1);
        $this->get(['_name' => 'login']);
        $this->assertRedirect(['_name' => 'dashboard']);
    }

    /**
     * Test for `activation()` method
     * @test
     */
    public function testActivation(): void
    {
        //Creates a token for an active user
        $tokenOptions = ['type' => 'signup', 'user_id' => 1];
        $token = $this->Token->create('alfa@test.com', $tokenOptions);
        $url = ['_name' => 'activation'];

        //GET request. This request is invalid, because the user is already active
        $this->get($url + ['id' => '1'] + compact('token'));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);
        $this->assertFalse($this->Token->check($token, $tokenOptions));

        //Creates a token for a pending user
        $tokenOptions = ['type' => 'signup', 'user_id' => 2];
        $token = $this->Token->create('gamma@test.com', $tokenOptions);

        //GET request. This request is valid, because the user is pending
        $this->get($url + ['id' => '2'] + compact('token'));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->extract('active')->first());
        $this->assertFalse($this->Token->check($token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
        $this->disableErrorHandlerMiddleware();
        $this->get($url + ['id' => '1'] + ['token' => 'invalidToken']);
    }

    /**
     * Test for `activationResend()` method
     * @test
     */
    public function testActivationResend(): void
    {
        $url = ['_name' => 'activationResend'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'activation_resend.php');
        $this->assertLayout('login.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

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
     * Test for `login()` method
     * @test
     */
    public function testLogin(): void
    {
        $url = ['_name' => 'login'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('login.php');
        $this->assertLayout('login.php');

        //POST request with invalid data
        $this->post($url, ['username' => 'wrong', 'password' => 'wrong']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertCookieNotSet('login');
        $this->assertSessionEmpty('Auth');
        $this->assertLogContains('Failed login: username `wrong`, password `wrong`', 'users');

        //POST request. Now data are valid
        $password = 'newPassword1!';
        $user = $this->Table->get(1);
        $this->Table->save($user->set('password', $password));
        $this->post($url, ['username' => $user->get('username'), 'remember_me' => true] + compact('password'));
        $this->assertRedirect($this->Controller->Auth->redirectUrl());
        $this->assertSession($user->get('id'), 'Auth.User.id');
        $this->assertCookieEncrypted(['username' => $user->get('username')] + compact('password'), 'login');
        $expires = Time::createFromTimestamp($this->_response->getCookie('login')['expires']);
        $this->assertTrue($expires->isWithinNext('1 year'));

        //POST request. The user is banned
        $this->Table->save($user->set('banned', true));
        $this->post($url, ['username' => $user->get('username'), 'remember_me' => true] + compact('password'));
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertCookieNotSet('login');
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Your account has been banned by an admin');

        //POST request. The user is pending
        $this->Table->save($user->set(['active' => false, 'banned' => false]));
        $this->post($url, ['username' => $user->get('username'), 'remember_me' => true] + compact('password'));
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertCookieNotSet('login');
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Your account has not been activated yet');
    }

    /**
     * Test for `logout()` method
     * @test
     */
    public function testLogout(): void
    {
        $this->cookie('login', 'value');
        $this->get(['_name' => 'logout']);
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertCookieNotSet('login');
        $this->assertFlashMessage('You are successfully logged out');
    }

    /**
     * Test for `passwordForgot()` method
     * @test
     */
    public function testPasswordForgot(): void
    {
        $url = ['_name' => 'passwordForgot'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'password_forgot.php');
        $this->assertLayout('login.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

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
     * Test for `passwordReset()` method
     * @test
     */
    public function testPasswordReset(): void
    {
        //Creates the token for an active user
        $tokenOptions = ['type' => 'password_forgot', 'user_id' => 1];
        $token = $this->Token->create('alfa@test.com', $tokenOptions);
        $url = ['_name' => 'passwordReset', 'id' => '1'] + compact('token');

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'password_reset.php');
        $this->assertLayout('login.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. Data are invalid
        $this->post($url, ['password' => '', 'password_repeat' => '']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The password has not been edited');

        //The password has not been changed and the token still exists
        $this->assertTrue($this->Token->check($token, $tokenOptions));
        $this->assertEmpty($this->Table->findById(1)->extract('password')->first());

        //POST request again. Now data are valid
        $this->post($url, ['password' => 'newPassword1!', 'password_repeat' => 'newPassword1!']);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('The password has been edited');

        //The password has changed and the token no longer exists
        $this->assertNotEmpty($this->Table->findById(1)->extract('password')->first());
        $this->assertFalse($this->Token->check($token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
        $this->disableErrorHandlerMiddleware();
        $this->get($url + ['id' => '1'] + ['token' => 'invalidToken']);
    }

    /**
     * Test for `signup()` method
     * @test
     */
    public function testSignup(): void
    {
        $data = [
            'username' => 'example',
            'email' => 'example@example.it',
            'email_repeat' => 'example@example.it',
            'password' => 'password1!',
            'password_repeat' => 'password1!',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
        ];
        $url = ['_name' => 'signup'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'signup.php');
        $this->assertLayout('login.php');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $this->post($url, ['password' => 'anotherPassword'] + $data);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The account has not been created');

        //POST request. Data are valid, the account needs to be activated by an admin
        Configure::write('MeCms.users.activation', 2);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created, but it needs to be activated by an admin');
        $user = $this->Table->findByUsername($data['username'])->first()->extract(['group_id', 'active']);
        $this->assertEquals(['group_id' => getConfigOrFail('users.default_group'), 'active' => false], $user);

        //Deletes the user
        $this->Table->deleteAll(['username' => $data['username']]);

        //POST request. Data are valid, an email is sent to the user
        Configure::write('MeCms.users.activation', 1);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('We send you an email to activate your account');
        $user = $this->Table->findByUsername($data['username'])->first()->extract(['group_id', 'active']);
        $this->assertEquals(['group_id' => getConfigOrFail('users.default_group'), 'active' => false], $user);

        //Deletes the user
        $this->Table->deleteAll(['username' => $data['username']]);

        //POST request. Data are valid
        Configure::write('MeCms.users.activation', 0);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created. Now you can login');
        $user = $this->Table->findByUsername($data['username'])->first()->extract(['group_id', 'active']);
        $this->assertEquals(['group_id' => getConfigOrFail('users.default_group'), 'active' => true], $user);

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
