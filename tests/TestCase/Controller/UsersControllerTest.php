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
namespace MeCms\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\Controller\UsersController;
use MeCms\Mailer\UserMailer;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\ControllerTestCase;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Tokens',
        'plugin.me_cms.Users',
        'plugin.me_cms.UsersGroups',
    ];

    /**
     * Internal method to get a mock instance of `LoginRecorderComponent`
     * @return LoginRecorderComponent
     */
    protected function getLoginRecorderMock()
    {
        $class = LoginRecorderComponent::class;
        $LoginRecorderComponent = $this->getMockForComponent($class, array_diff(get_class_methods($class), ['__debugInfo']));
        $LoginRecorderComponent->method('config')->will($this->returnSelf());

        return $LoginRecorderComponent;
    }

    /**
     * Mocks a controller
     * @param string $className Controller class name
     * @param array|null $methods The list of methods to mock
     * @param string $alias Controller alias
     * @return object
     * @uses getClassAlias()
     */
    protected function getMockForController($className = null, $methods = null, $alias = 'Users')
    {
        $className = $className ?: UsersController::class;
        $controller = parent::getMockForController($className, $methods, $alias);

        //Stubs the `getUserMailer()` method
        if (in_array('getUserMailer', (array)$methods)) {
            $userMailerMock = $this->getMockBuilder(UserMailer::class)->getMock();
            $userMailerMock->method('set')->will($this->returnSelf());
            $userMailerMock->method('send')->will($this->returnValue(true));

            $controller->method('getUserMailer')->will($this->returnValue($userMailerMock));
        }

        //Stubs the `redirect()` method
        if (in_array('redirect', (array)$methods)) {
            $controller->method('redirect')->will($this->returnArgument(0));
        }

        //Sets key for cookies
        $controller->Cookie->setConfig('key', 'somerandomhaskeysomerandomhaskey');

        //Mocks the `LoginRecorder` component
        $controller->LoginRecorder = $this->getLoginRecorderMock();

        return $controller;
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

        //Mocks the `LoginRecorder` component
        $this->_controller->LoginRecorder = $this->getLoginRecorderMock();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        //Deletes all tokens
        $this->Table->Tokens->deleteAll(['id IS NOT' => null]);

        parent::tearDown();
    }

    /**
     * Test for `loginWithCookie()` method
     * @test
     */
    public function testLoginWithCookie()
    {
        //No user data on cookies
        $controller = $this->getMockForController();
        $this->assertNull($this->invokeMethod($controller, 'loginWithCookie'));
        $this->assertNull($controller->Auth->user());
        $this->assertNull($controller->Cookie->read('login'));

        //Writes wrong data on cookies
        $controller->Cookie->write('login', ['username' => 'a', 'password' => 'b']);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertNull($controller->Auth->user());
        $this->assertNull($controller->Cookie->read('login'));

        //Gets an user and sets a password, then writes right data on cookies
        $user = $this->Table->findByActiveAndBanned(true, false)->first();
        $password = 'mypassword1!';
        $user->password = $user->password_repeat = $password;
        $this->Table->save($user);
        $data = ['username' => $user->username, 'password' => $password];
        $controller = $this->getMockForController();
        $controller->Cookie->write('login', $data);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->redirectUrl());
        $this->assertNotEmpty($controller->Auth->user());
        $this->assertEquals($data, $controller->Cookie->read('login'));

        //Sets the user as "pending" user, then writes again data on cookies
        $user->active = false;
        $this->Table->save($user);
        $controller = $this->getMockForController();
        $controller->Cookie->write('login', ['username' => $user->username, 'password' => $password]);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertNull($controller->Auth->user());
        $this->assertNull($controller->Cookie->read('login'));

        //Sets the user as "banned" user,then writes again data on cookies
        $user->active = $user->banned = true;
        $this->Table->save($user);
        $controller = $this->getMockForController();
        $controller->Cookie->write('login', ['username' => $user->username, 'password' => $password]);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertNull($controller->Auth->user());
        $this->assertNull($controller->Cookie->read('login'));
    }

    /**
     * Test for `buildLogout()` method
     * @test
     */
    public function testBuildLogout()
    {
        //Sets cookies and session values
        $this->Controller->Cookie->write('login', 'testLogin');
        $this->Controller->Cookie->write('sidebar-lastmenu', 'value');
        $this->Controller->request->getSession()->write('KCFINDER', 'value');
        $this->_response = $this->invokeMethod($this->Controller, 'buildLogout');
        $this->assertRedirect($this->Controller->Auth->logout());

        $this->assertFalse($this->Controller->Cookie->check('login'));
        $this->assertFalse($this->Controller->Cookie->check('sidebar-lastmenu'));
        $this->assertFalse($this->Controller->request->getSession()->check('KCFINDER'));
    }

    /**
     * Test for `sendActivationMail()` method
     * @test
     */
    public function testSendActivationMail()
    {
        $result = $this->invokeMethod($this->Controller, 'sendActivationMail', [$this->Table->find()->first()]);
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $expectedComponents = [
            'Cookie',
            'Auth',
            'Flash',
            'RequestHandler',
            'Uploader',
            'Recaptcha',
            'Token',
            'LoginRecorder',
        ];
        foreach ($expectedComponents as $component) {
            $this->assertHasComponent($component);
        }

        $this->assertEquals('aes', $this->Controller->Cookie->configKey('login')['encryption']);
        $this->assertEquals('+365 days', $this->Controller->Cookie->configKey('login')['expires']);
    }

    /**
     * Test for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->setUserId(1);
        $this->Controller->request = $this->Controller->request->withParam('action', 'my-action');
        $this->_response = $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertRedirect(['_name' => 'dashboard']);
    }

    /**
     * Test for `activation()` method
     * @test
     */
    public function testActivation()
    {
        $url = ['_name' => 'activation'];

        //Gets an active user and creates a token
        $user = $this->Table->find('active')->first();
        $tokenOptions = ['type' => 'signup', 'user_id' => $user->id];
        $token = $this->Controller->Token->create($user->email, $tokenOptions);

        //GET request. This request is invalid, because the user is already active
        $this->get($url + ['id' => $user->id] + compact('token'));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);

        //The token no longer exists
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));

        //Gets a pending user and creates a token
        $user = $this->Table->find('pending')->first();
        $tokenOptions = ['type' => 'signup', 'user_id' => $user->id];
        $token = $this->Controller->Token->create($user->email, $tokenOptions);

        //GET request. This request is valid, because the user is pending
        $this->get($url + ['id' => $user->id] + compact('token'));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //Now the user is active and the token no longer exists
        $this->assertTrue($this->Table->findById($user->id)->extract('active')->first());
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));
    }

    /**
     * Test for `activation()` method, with an invalid token
     * @expectedException Cake\Datasource\Exception\RecordNotFoundException
     * @expectedExceptionMessage Invalid token
     * @test
     */
    public function testActivationInvalidToken()
    {
        $this->Controller->activation(1, 'invalidToken');
    }

    /**
     * Test for `activationResend()` method
     * @test
     */
    public function testActivationResend()
    {
        $url = ['_name' => 'activationResend'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users/activation_resend.ctp');
        $this->assertLayout('login.ctp');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $wrongEmail = 'wrongEmail@example.com';
        $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No valid account was found');
        $this->assertLogContains('Resend activation request with invalid email `' . $wrongEmail . '`', 'users');

        //POST request. Now, data are valid
        $email = $this->Table->find('pending')->extract('email')->first();
        $this->post($url, ['email' => $email, 'email_repeat' => $email]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('We send you an email to activate your account');

        //With reCAPTCHA
        Configure::write(ME_CMS . '.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write(ME_CMS . '.users', ['signup' => false, 'activation' => 1]);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }

    /**
     * Test for `login()` method
     * @test
     */
    public function testLogin()
    {
        $url = ['_name' => 'login'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('login.ctp');
        $this->assertLayout('login.ctp');

        //POST request with invalid data
        $this->post($url, ['username' => 'wrong', 'password' => 'wrong']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertCookieNotSet('login');
        $this->assertSession(null, 'Auth');
        $this->assertLogContains('Failed login with username `wrong` and password `wrong`', 'users');

        //POST request. Now data are valid
        $password = 'newPassword1!';
        $user = $this->Table->get(1);
        $user->password = $password;
        $this->Table->save($user);
        $this->post($url, ['username' => $user->username, 'password' => $password, 'remember_me' => true]);
        $this->assertRedirect($this->Controller->Auth->redirectUrl());
        $this->assertSession($user->id, 'Auth.User.id');
        $this->assertCookieEncrypted([
            'username' => $user->username,
            'password' => $password,
        ], 'login', 'aes', $this->_controller->Cookie->getConfig('key'));
        $cookieExpire = Time::createFromTimestamp($this->_response->getCookie('login')['expire']);
        $this->assertTrue($cookieExpire->isWithinNext('1 year'));

        //POST request. The user is banned
        $user->banned = true;
        $this->Table->save($user);
        $this->post($url, ['username' => $user->username, 'password' => $password, 'remember_me' => true]);
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertCookieNotSet('login');
        $this->assertSession(null, 'Auth');
        $this->assertFlashMessage('Your account has been banned by an admin');

        //POST request. The user is pending
        $user->active = $user->banned = false;
        $this->Table->save($user);
        $this->post($url, ['username' => $user->username, 'password' => $password, 'remember_me' => true]);
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertCookieNotSet('login');
        $this->assertSession(null, 'Auth');
        $this->assertFlashMessage('Your account has not been activated yet');
    }

    /**
     * Test for `logout()` method
     * @test
     */
    public function testLogout()
    {
        $this->get(['_name' => 'logout']);
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertFlashMessage('You are successfully logged out');
    }

    /**
     * Test for `passwordForgot()` method
     * @test
     */
    public function testPasswordForgot()
    {
        $url = ['_name' => 'passwordForgot'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users/password_forgot.ctp');
        $this->assertLayout('login.ctp');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $wrongEmail = 'wrongMail@example.it';
        $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No account found');
        $this->assertLogContains('Forgot password request with invalid email `' . $wrongEmail . '`', 'users');
        $this->deleteLog('users');

        //POST request. This request is invalid, because the user is pending
        $email = $this->Table->find('pending')->extract('email')->first();
        $this->post($url, ['email' => $email, 'email_repeat' => $email]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No account found');
        $this->assertLogContains('Forgot password request with invalid email `' . $email . '`', 'users');

        //POST request. This request is valid
        $email = $this->Table->find('active')->extract('email')->first();
        $this->post($url, ['email' => $email, 'email_repeat' => $email]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('We have sent you an email to reset your password');

        //With reCAPTCHA
        Configure::write(ME_CMS . '.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write(ME_CMS . '.users.reset_password', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }

    /**
     * Test for `passwordReset()` method
     * @test
     */
    public function testPasswordReset()
    {
        //Gets an active user and creates the token
        $user = $this->Table->find('active')->first();
        $tokenOptions = ['type' => 'password_forgot', 'user_id' => $user->id];
        $token = $this->Controller->Token->create($user->email, $tokenOptions);

        $url = ['_name' => 'passwordReset', 'id' => $user->id] + compact('token');

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users/password_reset.ctp');
        $this->assertLayout('login.ctp');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. Data are invalid
        $this->post($url, ['password' => '', 'password_repeat' => '']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The password has not been edited');

        //The password has not been changed and the token still exists
        $newPassword = $this->Table->findById($user->id)->extract('password')->first();
        $this->assertTrue($this->Controller->Token->check($token, $tokenOptions));
        $this->assertEquals($newPassword, $user->password);

        //POST request again. Now data are valid
        $password = 'newPassword1!';
        $this->post($url, ['password' => $password, 'password_repeat' => $password]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('The password has been edited');

        //The password has changed and the token no longer exists
        $newPassword = $this->Table->findById($user->id)->extract('password')->first();
        $this->assertNotEmpty($newPassword);
        $this->assertNotEquals($newPassword, $user->password);
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));
    }

    /**
     * Test for `passwordReset()` method, with an invalid token
     * @expectedException Cake\Datasource\Exception\RecordNotFoundException
     * @expectedExceptionMessage Invalid token
     * @test
     */
    public function testPasswordResetInvalidToken()
    {
        $this->Controller->passwordReset(1, 'invalidToken');
    }

    /**
     * Test for `signup()` method
     * @test
     */
    public function testSignup()
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
        $this->assertTemplate('Users/signup.ctp');
        $this->assertLayout('login.ctp');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $this->post($url, ['password' => 'anotherPassword'] + $data);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The account has not been created');

        //POST request. Data are valid, the account needs to be activated by an admin
        Configure::write(ME_CMS . '.users.activation', 2);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created, but it needs to be activated by an admin');
        $user = $this->Table->findByUsername($data['username'])->first();
        $this->assertEquals(getConfigOrFail('users.default_group'), $user->group_id);
        $this->assertFalse($user->active);

        //Deletes the user
        $this->Table->deleteAll(['username' => $data['username']]);

        //POST request. Data are valid, an email is sent to the user
        Configure::write(ME_CMS . '.users.activation', 1);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('We send you an email to activate your account');

        $user = $this->Table->findByUsername($data['username'])->first();
        $this->assertEquals(getConfigOrFail('users.default_group'), $user->group_id);
        $this->assertFalse($user->active);

        //Deletes the user
        $this->Table->deleteAll(['username' => $data['username']]);

        //POST request. Data are valid
        Configure::write(ME_CMS . '.users.activation', 0);
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created. Now you can login');
        $user = $this->Table->findByUsername($data['username'])->first();
        $this->assertEquals(getConfigOrFail('users.default_group'), $user->group_id);
        $this->assertTrue($user->active);

        //With reCAPTCHA
        Configure::write(ME_CMS . '.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write(ME_CMS . '.users.signup', false);
        $this->get($url);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Disabled');
    }
}
