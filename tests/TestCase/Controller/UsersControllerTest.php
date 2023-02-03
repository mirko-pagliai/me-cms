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

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
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

        $this->Token ??= $this->createPartialMock(TokenComponent::class, []);
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
        $this->assertTrue($this->Table->findById(2)->all()->extract('active')->first());
        $this->assertFalse($this->Token->check($token, $tokenOptions));

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
        $this->assertLayout('login.php');
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
     * @uses \MeCms\Controller\UsersController::login()
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
        $this->enableRetainFlashMessages();
        $this->post($url, ['username' => 'wrong', 'password' => 'wrong']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Invalid username or password');
        $this->assertLogContains('Failed login: username `wrong`, password `wrong`', 'users');

        //POST request. Now data are valid
        $password = 'newPassword1!';
        $user = $this->Table->get(1);
        $this->Table->save($user->set('password', $password));
        $this->cleanup();
        $this->post($url, ['username' => $user->get('username')] + compact('password'));
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertSession($user->get('id'), 'Auth.id');

        //POST request. The user is banned
        $this->Table->save($user->set('banned', true));
        $this->enableRetainFlashMessages();
        $this->post($url, ['username' => $user->get('username')] + compact('password'));
        $this->assertResponseOkAndNotEmpty();
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Invalid username or password');

        //POST request. The user is pending
        $this->Table->save($user->set(['active' => false, 'banned' => false]));
        $this->enableRetainFlashMessages();
        $this->post($url, ['username' => $user->get('username')] + compact('password'));
        $this->assertResponseOkAndNotEmpty();
        $this->assertSessionEmpty('Auth');
        $this->assertFlashMessage('Invalid username or password');
    }

    /**
     * @uses \MeCms\Controller\UsersController::logout()
     * @test
     */
    public function testLogout(): void
    {
        $this->setAuthData();
        $this->session(['otherSessionValue' => 'value']);

        //The user is currently logged in
        $this->get('/');
        $this->assertSession(1, 'Auth.id');

        $this->get(['_name' => 'logout']);
        $this->assertResponseCode(302);
        $this->assertSessionEmpty('Auth');
        $this->assertSession('value', 'otherSessionValue');
    }

    /**
     * @uses \MeCms\Controller\UsersController::passwordForgot()
     * @test
     */
    public function testPasswordForgot(): void
    {
        $url = ['_name' => 'passwordForgot'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Users' . DS . 'password_forgot.php');
        $this->assertLayout('login.php');
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
     * @uses \MeCms\Controller\UsersController::passwordReset()
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
        $this->assertInstanceOf(User::class, $this->viewVariable('user'));

        //POST request. Data are invalid
        $this->post($url, ['password' => '', 'password_repeat' => '']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The password has not been edited');

        //The password has not been changed and the token still exists
        $this->assertTrue($this->Token->check($token, $tokenOptions));
        $this->assertEmpty($this->Table->findById(1)->all()->extract('password')->first());

        //POST request again. Now data are valid
        $this->post($url, ['password' => 'newPassword1!', 'password_repeat' => 'newPassword1!']);
        $this->assertRedirect(['_name' => 'login']);
        $this->assertFlashMessage('The password has been edited');

        //The password has changed and the token no longer exists
        $this->assertNotEmpty($this->Table->findById(1)->all()->extract('password')->first());
        $this->assertFalse($this->Token->check($token, $tokenOptions));

        //With an invalid token
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Invalid token');
        $this->disableErrorHandlerMiddleware();
        $this->get($url + ['token' => 'invalidToken']);
    }

    /**
     * @uses \MeCms\Controller\UsersController::signup()
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
