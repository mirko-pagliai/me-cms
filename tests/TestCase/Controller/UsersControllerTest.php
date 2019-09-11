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
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\TestSuite\EmailAssertTrait;
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
    use EmailAssertTrait;

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
     * Internal method to get a mock instance of `LoginRecorderComponent`
     * @return LoginRecorderComponent
     */
    protected function getLoginRecorderMock()
    {
        $LoginRecorder = $this->getMockForComponent(LoginRecorderComponent::class);
        $LoginRecorder->method('setConfig')->will($this->returnSelf());

        return $LoginRecorder;
    }

    /**
     * Mocks a controller
     * @param string|null $className Controller class name
     * @param array|null $methods The list of methods to mock
     * @param string $alias Controller alias
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockForController($className = null, $methods = null, $alias = 'Users')
    {
        $controller = parent::getMockForController($className ?: UsersController::class, $methods, $alias);

        if (in_array('getUserMailer', (array)$methods)) {
            $userMailer = $this->getMockForMailer(UserMailer::class);
            $controller->method('getUserMailer')->will($this->returnValue($userMailer));
        }

        if (in_array('redirect', (array)$methods)) {
            $controller->method('redirect')->will($this->returnArgument(0));
        }

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

        $this->_controller->LoginRecorder = $this->getLoginRecorderMock();
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
        $this->assertEmpty($controller->getRequest()->getCookieCollection());

        //Writes wrong data on cookies
        $controller->request = $controller->getRequest()->withCookieParams(['login' => ['username' => 'a', 'password' => 'b']]);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertNull($controller->Auth->user());
        $this->assertEmpty($controller->getRequest()->getCookieCollection());

        //Gets an user and sets a password, then writes right data on cookies
        $password = 'mypassword1!';
        $user = $this->Table->findByActiveAndBanned(true, false)->first();
        $this->Table->save($user->set(compact('password') + ['password_repeat' => $password]));
        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withCookieParams(['login' => ['username' => $user->username] + compact('password')]);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->redirectUrl());
        $this->assertNotEmpty($controller->Auth->user());
        $this->assertNotEmpty($controller->getRequest()->getCookieCollection());

        //Sets the user as "pending" user, then writes again data on cookies
        $this->Table->save($user->set('active', false));
        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withCookieParams(['login' => ['username' => $user->username] + compact('password')]);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertNull($controller->Auth->user());
        $this->assertEmpty($controller->getRequest()->getCookieCollection());

        //Sets the user as "banned" user,then writes again data on cookies
        $this->Table->save($user->set(['active' => true, 'banned' => true]));
        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withCookieParams(['login' => ['username' => $user->username] + compact('password')]);
        $this->_response = $this->invokeMethod($controller, 'loginWithCookie');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertNull($controller->Auth->user());
        $this->assertEmpty($controller->getRequest()->getCookieCollection());
    }

    /**
     * Test for `buildLogout()` method
     * @test
     */
    public function testBuildLogout()
    {
        //Sets cookies and session values
        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withCookieParams(['login' => 'testLogin', 'sidebar-lastmenu' => 'testSidebar']);
        $controller->getRequest()->getSession()->write('KCFINDER', 'value');
        $this->_response = $this->invokeMethod($controller, 'buildLogout');
        $this->assertRedirect($controller->Auth->logout());
        $this->assertEmpty($controller->getRequest()->getCookieCollection());
        $this->assertEmpty($controller->getRequest()->getSession()->read());
    }

    /**
     * Test for `sendActivationMail()` method
     * @test
     */
    public function testSendActivationMail()
    {
        $this->assertTrue($this->invokeMethod($this->Controller, 'sendActivationMail', [$this->Table->find()->first()]));
    }

    /**
     * Test for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->setUserId(1);
        $this->Controller->request = $this->Controller->getRequest()->withParam('action', 'my-action');
        $this->_response = $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertRedirect(['_name' => 'dashboard']);
    }

    /**
     * Test for `activation()` method
     * @test
     */
    public function testActivation()
    {
        //Creates a token for an active user
        $tokenOptions = ['type' => 'signup', 'user_id' => 1];
        $token = $this->Controller->Token->create('alfa@test.com', $tokenOptions);
        $url = ['_name' => 'activation'];

        //GET request. This request is invalid, because the user is already active
        $this->get($url + ['id' => 1] + compact('token'));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));

        //Creates a token for a pending user
        $tokenOptions = ['type' => 'signup', 'user_id' => 2];
        $token = $this->Controller->Token->create('gamma@test.com', $tokenOptions);

        //GET request. This request is valid, because the user is pending
        $this->get($url + ['id' => 2] + compact('token'));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(2)->extract('active')->first());
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
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
        $this->assertTemplate('Users' . DS . 'activation_resend.ctp');
        $this->assertLayout('login.ctp');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. For now, data are invalid
        $wrongEmail = 'wrongEmail@example.com';
        $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No valid account was found');
        $this->assertLogContains('Resend activation request with invalid email `' . $wrongEmail . '`', 'users');

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
        $this->Table->save($user->set('password', $password));
        $this->post($url, ['username' => $user->username, 'remember_me' => true] + compact('password'));
        $this->assertRedirect($this->Controller->Auth->redirectUrl());
        $this->assertSession($user->id, 'Auth.User.id');
        $this->assertCookieEncrypted([
            'username' => $user->username,
        ] + compact('password'), 'login', 'aes', Configure::read('Security.cookieKey', md5(Configure::read('Security.salt', ''))));
        $cookieExpire = Time::createFromTimestamp($this->_response->getCookie('login')['expire']);
        $this->assertTrue($cookieExpire->isWithinNext('1 year'));

        //POST request. The user is banned
        $this->Table->save($user->set('banned', true));
        $this->post($url, ['username' => $user->username, 'remember_me' => true] + compact('password'));
        $this->assertRedirect($this->Controller->Auth->logout());
        $this->assertCookieNotSet('login');
        $this->assertSession(null, 'Auth');
        $this->assertFlashMessage('Your account has been banned by an admin');

        //POST request. The user is pending
        $this->Table->save($user->set(['active' => false, 'banned' => false]));
        $this->post($url, ['username' => $user->username, 'remember_me' => true] + compact('password'));
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
        $this->assertTemplate('Users' . DS . 'password_forgot.ctp');
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
        $this->post($url, ['email' => 'gamma@test.com', 'email_repeat' => 'gamma@test.com']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No account found');
        $this->assertLogContains('Forgot password request with invalid email `gamma@test.com`', 'users');

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
    public function testPasswordReset()
    {
        //Creates the token for an active user
        $tokenOptions = ['type' => 'password_forgot', 'user_id' => 1];
        $token = $this->Controller->Token->create('alfa@test.com', $tokenOptions);
        $url = ['_name' => 'passwordReset', 'id' => 1] + compact('token');

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'password_reset.ctp');
        $this->assertLayout('login.ctp');
        $this->assertInstanceof(User::class, $this->viewVariable('user'));

        //POST request. Data are invalid
        $this->post($url, ['password' => '', 'password_repeat' => '']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The password has not been edited');

        //The password has not been changed and the token still exists
        $this->assertTrue($this->Controller->Token->check($token, $tokenOptions));
        $this->assertEmpty($this->Table->findById(1)->extract('password')->first());

        //POST request again. Now data are valid
        $this->post($url, ['password' => 'newPassword1!', 'password_repeat' => 'newPassword1!']);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('The password has been edited');

        //The password has changed and the token no longer exists
        $this->assertNotEmpty($this->Table->findById(1)->extract('password')->first());
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
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
        $this->assertTemplate('Users' . DS . 'signup.ctp');
        $this->assertLayout('login.ctp');
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
