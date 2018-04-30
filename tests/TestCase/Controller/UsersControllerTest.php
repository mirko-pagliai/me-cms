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

use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use MeCms\Controller\Component\LoginRecorderComponent;
use MeCms\Controller\UsersController;
use MeCms\Mailer\UserMailer;
use MeCms\TestSuite\IntegrationTestCase;
use Tokens\Controller\Component\TokenComponent;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\UsersController
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
        'plugin.me_cms.tokens',
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * @var string
     */
    protected $keyForCookies = 'somerandomhaskeysomerandomhaskey';

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        //Sets key for cookies
        $this->_controller->Cookie->config('key', $this->keyForCookies);

        //Mocks the `LoginRecorder` component
        $this->_controller->LoginRecorder = $this->getMockBuilder(LoginRecorderComponent::class)
            ->setConstructorArgs([new ComponentRegistry])
            ->getMock();

        $this->_controller->LoginRecorder->method('config')
            ->will($this->returnSelf());
    }

    /**
     * Internal method to set a mock of `UsersController`
     * @param array $methodsToSet Methods to set
     */
    protected function setUsersControllerMock($methodsToSet = ['getUserMailer', 'redirect'])
    {
        //Mocks the `UsersController` class
        $this->Controller = $this->getMockBuilder(UsersController::class)
            ->setMethods($methodsToSet)
            ->getMock();

        //Stubs the `getUserMailer()` method
        if (in_array('getUserMailer', (array)$methodsToSet)) {
            $this->Controller->method('getUserMailer')
                ->will($this->returnCallback(function () {
                    $userMailerMock = $this->getMockBuilder(UserMailer::class)->getMock();

                    $userMailerMock->method('set')->will($this->returnSelf());
                    $userMailerMock->method('send')->will($this->returnValue(true));

                    return $userMailerMock;
                }));
        }

        //Stubs the `redirect()` method
        if (in_array('redirect', (array)$methodsToSet)) {
            $this->Controller->method('redirect')->will($this->returnArgument(0));
        }

        //Sets key for cookies
        $this->Controller->Cookie->config('key', $this->keyForCookies);

        //Mocks the `LoginRecorder` component
        $this->Controller->LoginRecorder = $this->getMockBuilder(LoginRecorderComponent::class)
            ->setConstructorArgs([new ComponentRegistry])
            ->getMock();

        $this->Controller->LoginRecorder->method('config')
            ->will($this->returnSelf());
    }

    /**
     * Internal method to set a mock of `Token`
     */
    protected function setTokenMock()
    {
        //Mocks the `Token` component
        $this->Controller->Token = $this->getMockBuilder(TokenComponent::class)
            ->setConstructorArgs([new ComponentRegistry])
            ->getMock();

        $this->Controller->Token->expects($this->any())
            ->method('create')
            ->will($this->returnValue('aTokenString'));
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

        $this->setUsersControllerMock();

        $this->Users = TableRegistry::get(ME_CMS . '.Users');

        Cache::clear(false, $this->Users->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes all tokens
        TableRegistry::get('Tokens.Tokens')->deleteAll([]);
    }

    /**
     * Test for `loginWithCookie()` method
     * @test
     */
    public function testLoginWithCookie()
    {
        $loginWithCookieMethod = function () {
            return $this->invokeMethod($this->Controller, 'loginWithCookie');
        };

        //No user data on cookies
        $this->assertNull($loginWithCookieMethod());
        $this->assertNull($this->Controller->Auth->user());

        //Writes wrong data on cookie
        $this->Controller->Cookie->write('login', ['username' => 'a', 'password' => 'b']);

        $this->assertEquals($this->Controller->Auth->logout(), $loginWithCookieMethod());
        $this->assertNull($this->Controller->Auth->user());

        //Saves a new user
        $password = 'mypassword1!';
        $user = $this->Users->newEntity([
            'group_id' => 1,
            'email' => 'example@test.com',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'username' => 'myusername',
            'password' => $password,
            'password_repeat' => $password,
            'active' => true,
            'banned' => false,
        ]);
        $this->assertNotEmpty($this->Users->save($user));

        //Writes right data on cookie
        $this->Controller->Cookie->write('login', ['username' => $user->username, 'password' => $password]);

        $this->assertEquals($this->Controller->Auth->redirectUrl(), $loginWithCookieMethod());
        $this->assertNotEmpty($this->Controller->Auth->user());

        //Saves the user as pending and writes again data on cookie
        $user->active = false;
        $this->assertNotEmpty($this->Users->save($user));
        $this->Controller->Cookie->write('login', ['username' => $user->username, 'password' => $password]);

        $this->assertEquals($this->Controller->Auth->logout(), $loginWithCookieMethod());
        $this->assertNull($this->Controller->Auth->user());

        //Saves the user as banned and writes again data on cookie
        $user->active = $user->banned = true;
        $this->assertNotEmpty($this->Users->save($user));
        $this->Controller->Cookie->write('login', ['username' => $user->username, 'password' => $password]);

        $this->assertEquals($this->Controller->Auth->logout(), $loginWithCookieMethod());
        $this->assertNull($this->Controller->Auth->user());
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
        $this->Controller->request->session()->write('KCFINDER', 'value');

        $this->assertTrue($this->Controller->Cookie->check('login'));
        $this->assertTrue($this->Controller->Cookie->check('sidebar-lastmenu'));
        $this->assertTrue($this->Controller->request->session()->check('KCFINDER'));

        $result = $this->invokeMethod($this->Controller, 'buildLogout');

        $this->assertEquals($this->Controller->Auth->logout(), $result);

        $this->assertFalse($this->Controller->Cookie->check('login'));
        $this->assertFalse($this->Controller->Cookie->check('sidebar-lastmenu'));
        $this->assertFalse($this->Controller->request->session()->check('KCFINDER'));
    }

    /**
     * Test for `sendActivationMail()` method
     * @test
     */
    public function testSendActivationMail()
    {
        $user = $this->Users->find()->first();

        $result = $this->invokeMethod($this->Controller, 'sendActivationMail', [$user]);
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $componentsInstance = $this->Controller->components();

        $components = collection($componentsInstance->loaded())
            ->map(function ($value) use ($componentsInstance) {
                return get_class($componentsInstance->{$value});
            })->toList();

        $this->assertEquals([
            'Cake\Controller\Component\CookieComponent',
            ME_CMS . '\Controller\Component\AuthComponent',
            ME_TOOLS . '\Controller\Component\FlashComponent',
            'Cake\Controller\Component\RequestHandlerComponent',
            ME_TOOLS . '\Controller\Component\UploaderComponent',
            'Recaptcha\Controller\Component\RecaptchaComponent',
            'Tokens\Controller\Component\TokenComponent',
            ME_CMS . '\Controller\Component\LoginRecorderComponent',
        ], $components);

        $this->assertEquals('aes', $this->Controller->Cookie->configKey('login')['encryption']);
        $this->assertEquals('+365 days', $this->Controller->Cookie->configKey('login')['expires']);
    }

    /**
     * Test for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->setUsersControllerMock(null);
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
        $user = $this->Users->find('active')->first();
        $tokenOptions = ['type' => 'signup', 'user_id' => $user->id];
        $token = $this->Controller->Token->create($user->email, $tokenOptions);

        //GET request. This request is invalid, because the user is already active
        $this->get(array_merge($url, ['id' => $user->id], compact('token')));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);

        //The token no longer exists
        $this->assertFalse($this->Controller->Token->check($token, $tokenOptions));

        //Gets a pending user and creates a token
        $user = $this->Users->find('pending')->first();
        $tokenOptions = ['type' => 'signup', 'user_id' => $user->id];
        $token = $this->Controller->Token->create($user->email, $tokenOptions);

        //GET request. This request is valid, because the user is pending
        $this->get(array_merge($url, ['id' => $user->id], compact('token')));
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //Now the user is active and the token no longer exists
        $this->assertTrue($this->Users->findById($user->id)->extract('active')->first());
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
        $this->assertTemplate(ROOT . 'src/Template/Users/activation_resend.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        $wrongEmail = 'wrongEmail@example.com';

        //POST request. For now, data are invalid
        $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No valid account was found');
        $this->assertLogContains('Resend activation request with invalid email `' . $wrongEmail . '`', 'users');

        //Gets an active user
        $email = $this->Users->find('pending')->extract('email')->first();

        //POST request. Now, data are valid
        $this->post($url, ['email' => $email, 'email_repeat' => $email]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('We send you an email to activate your account');

        $this->markTestIncomplete('this must be completed');

        //With reCAPTCHA
        Configure::write(ME_CMS . '.security.recaptcha', true);
        $this->post($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('You must fill in the reCAPTCHA control correctly');

        //Disabled
        Configure::write(ME_CMS . '.users.signup', false);
        Configure::write(ME_CMS . '.users.activation', 1);
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
        $this->assertTemplate(ROOT . 'src/Template/Users/login.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');

        //POST request with invalid data
        $this->post($url, ['username' => 'wrong', 'password' => 'wrong']);
        $this->assertResponseOkAndNotEmpty();

        $this->assertCookieNotSet('login');
        $this->assertSession(null, 'Auth');
        $this->assertLogContains('Failed login with username `wrong` and password `wrong`', 'users');

        //Gets the first user, sets a valid password and saves
        $password = 'newPassword1!';
        $user = $this->Users->get(1);
        $user->password = $password;
        $this->Users->save($user);

        //POST request. Now data are valid
        $this->post($url, [
            'username' => $user->username,
            'password' => $password,
            'remember_me' => true,
        ]);
        $this->assertRedirect($this->Controller->Auth->redirectUrl());

        $this->assertSession($user->id, 'Auth.User.id');
        $this->assertCookieEncrypted([
            'username' => $user->username,
            'password' => $password,
        ], 'login', 'aes', $this->keyForCookies);
        $cookieExpire = Time::createFromTimestamp($this->_response->cookie('login')['expire']);
        $this->assertTrue($cookieExpire->isWithinNext('1 year'));

        //Sets the user as banned
        $user->banned = true;
        $this->Users->save($user);

        //POST request. The user is banned
        $this->post($url, [
            'username' => $user->username,
            'password' => $password,
            'remember_me' => true,
        ]);
        $this->assertRedirect($this->Controller->Auth->logout());

        $this->assertCookieNotSet('login');
        $this->assertSession(null, 'Auth');
        $this->assertFlashMessage('Your account has been banned by an admin');

        //Sets the user as pending
        $user->active = $user->banned = false;
        $this->Users->save($user);

        //POST request. The user is pending
        $this->post($url, [
            'username' => $user->username,
            'password' => $password,
            'remember_me' => true,
        ]);
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
        $this->assertTemplate(ROOT . 'src/Template/Users/password_forgot.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        $wrongEmail = 'wrongMail@example.it';

        //POST request. For now, data are invalid
        $this->post($url, ['email' => $wrongEmail, 'email_repeat' => $wrongEmail]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No account found');
        $this->assertLogContains('Forgot password request with invalid email `' . $wrongEmail . '`', 'users');
        $this->deleteLog('users');

        //Gets a pending user
        $email = $this->Users->find('pending')->extract('email')->first();

        //POST request. This request is invalid, because the user is pending
        $this->post($url, ['email' => $email, 'email_repeat' => $email]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('No account found');
        $this->assertLogContains('Forgot password request with invalid email `' . $email . '`', 'users');

        //Gets an active user
        $email = $this->Users->find('active')->extract('email')->first();

        //POST request. This request is valid
        $this->post($url, ['email' => $email, 'email_repeat' => $email]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('We have sent you an email to reset your password');

        $this->markTestIncomplete('this must be completed');

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
        $user = $this->Users->find('active')->first();
        $tokenOptions = ['type' => 'password_forgot', 'user_id' => $user->id];
        $token = $this->Controller->Token->create($user->email, $tokenOptions);

        $url = array_merge(['_name' => 'passwordReset', 'id' => $user->id], compact('token'));

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Users/password_reset.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        //POST request. Data are invalid
        $this->post($url, ['password' => '', 'password_repeat' => '']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The password has not been edited');

        //The password has not been changed and the token still exists
        $newPassword = $this->Users->findById($user->id)->extract('password')->first();
        $this->assertTrue($this->Controller->Token->check($token, $tokenOptions));
        $this->assertEquals($newPassword, $user->password);

        //POST request again. Now data are valid
        $password = 'newPassword1!';
        $this->post($url, ['password' => $password, 'password_repeat' => $password]);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('The password has been edited');

        //The password has changed and the token no longer exists
        $newPassword = $this->Users->findById($user->id)->extract('password')->first();
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
        $this->assertTemplate(ROOT . 'src/Template/Users/signup.ctp');
        $this->assertLayout(ROOT . 'src/Template/Layout/login.ctp');

        $userFromView = $this->viewVariable('user');
        $this->assertNotEmpty($userFromView);
        $this->assertInstanceof('MeCms\Model\Entity\User', $userFromView);

        //POST request. For now, data are invalid
        $this->post($url, array_merge($data, ['password' => 'anotherPassword']));
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The account has not been created');

        $this->markTestIncomplete('this must be completed');

        Configure::write(ME_CMS . '.users.activation', 2);

        //POST request. Data are valid, the account needs to be activated by an admin
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created, but it needs to be activated by an admin');

        $user = $this->Users->findByUsername($data['username'])->first();
        $this->assertEquals(getConfigOrFail('users.default_group'), $user->group_id);
        $this->assertFalse($user->active);

        //Deletes the user
        $this->Users->deleteAll(['username' => $data['username']]);

        Configure::write(ME_CMS . '.users.activation', 1);

        //POST request. Data are valid, an email is sent to the user
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('We send you an email to activate your account');

        $user = $this->Users->findByUsername($data['username'])->first();
        $this->assertEquals(getConfigOrFail('users.default_group'), $user->group_id);
        $this->assertFalse($user->active);

        //Deletes the user
        $this->Users->deleteAll(['username' => $data['username']]);

        Configure::write(ME_CMS . '.users.activation', 0);

        //POST request. Data are valid
        $this->post($url, $data);
        $this->assertRedirect(['_name' => 'homepage']);
        $this->assertFlashMessage('Account created. Now you can login');

        $user = $this->Users->findByUsername($data['username'])->first();
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
