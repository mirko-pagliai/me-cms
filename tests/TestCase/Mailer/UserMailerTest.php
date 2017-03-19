<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Mailer;

use Cake\Mailer\Email;
use Cake\TestSuite\TestCase;
use MeCms\Mailer\UserMailer;
use Reflection\ReflectionTrait;

/**
 * UserMailerTest class
 */
class UserMailerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Mailer\UserMailer
     */
    public $UserMailer;

    /**
     * @var object
     */
    protected $example;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Email::setConfigTransport('debug', ['className' => 'Debug']);

        $this->example = (object)[
            'email' => 'test@test.com',
            'full_name' => 'James Blue',
        ];

        $this->UserMailer = new UserMailer;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Email::dropTransport('debug');

        unset($this->UserMailer);
    }

    /**
     * Tests for `activateAccount()` method
     * @test
     */
    public function testActivateAccount()
    {
        $this->UserMailer->activateAccount($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->UserMailer, '_email');

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->to());
        $this->assertEquals('Activate your account', $email->subject());
        $this->assertEquals([
            'template' => 'MeCms.Users/activate_account',
            'layout' => 'default',
        ], $email->template());

        $this->assertEquals([
            'fullName' => 'James Blue',
        ], $email->viewVars);
    }

    /**
     * Tests for `activateAccount()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` property from data
     * @test
     */
    public function testActivateAccountMissingData()
    {
        unset($this->example->email);

        $this->UserMailer->activateAccount($this->example);
    }

    /**
     * Tests for `activateAccount()` method, calling `send()` method
     * @test
     */
    public function testActivateAccountWithSend()
    {
        $result = $this->UserMailer->transport('debug')
            ->setLayout(false)
            ->set('url', 'http://example/link')
            ->send('activateAccount', [$this->example]);

        $headers = $message = null;
        extract($result);

        //Checks headers
        $this->assertContains('From: MeCms <email@example.com>', $headers);
        $this->assertContains('To: James Blue <test@test.com>', $headers);
        $this->assertContains('Subject: Activate your account', $headers);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $headers);

        //Checks the message
        $this->assertContains('Hello James Blue,', $message);
        $this->assertContains('you have signed on the site MeCms.', $message);
        $this->assertContains('To activate your account, click <a href="http://example/link" title="here">here</a>.', $message);
        $this->assertContains('If you have not made this request, please contact an administrator.', $message);
    }

    /**
     * Tests for `changePassword()` method
     * @test
     */
    public function testChangePassword()
    {
        $this->UserMailer->changePassword($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->UserMailer, '_email');

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->to());
        $this->assertEquals('Your password has been changed', $email->subject());
        $this->assertEquals([
            'template' => 'MeCms.Users/change_password',
            'layout' => 'default',
        ], $email->template());

        $this->assertEquals([
            'fullName' => 'James Blue',
        ], $email->viewVars);
    }

    /**
     * Tests for `changePassword()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` property from data
     * @test
     */
    public function testChangePasswordMissingData()
    {
        unset($this->example->email);

        $this->UserMailer->changePassword($this->example);
    }

    /**
     * Tests for `changePassword()` method, calling `send()` method
     * @test
     */
    public function testChangePasswordWithSend()
    {
        $result = $this->UserMailer->transport('debug')
            ->setLayout(false)
            ->set('url', 'http://example/link')
            ->send('changePassword', [$this->example]);

        $headers = $message = null;
        extract($result);

        //Checks headers
        $this->assertContains('From: MeCms <email@example.com>', $headers);
        $this->assertContains('To: James Blue <test@test.com>', $headers);
        $this->assertContains('Subject: Your password has been changed', $headers);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $headers);

        //Checks the message
        $this->assertContains('Hello James Blue,', $message);
        $this->assertContains('you have recently changed your password on our site MeCms.', $message);
        $this->assertContains('If you have not made this request, please contact an administrator.', $message);
    }

    /**
     * Tests for `forgotPassword()` method
     * @test
     */
    public function testForgotPassword()
    {
        $this->UserMailer->forgotPassword($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->UserMailer, '_email');

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->to());
        $this->assertEquals('Reset your password', $email->subject());
        $this->assertEquals([
            'template' => 'MeCms.Users/forgot_password',
            'layout' => 'default',
        ], $email->template());

        $this->assertEquals([
            'fullName' => 'James Blue',
        ], $email->viewVars);
    }

    /**
     * Tests for `forgotPassword()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` property from data
     * @test
     */
    public function testForgotPasswordMissingData()
    {
        unset($this->example->email);

        $this->UserMailer->forgotPassword($this->example);
    }

    /**
     * Tests for `forgotPassword()` method, calling `send()` method
     * @test
     */
    public function testForgotPasswordWithSend()
    {
        $result = $this->UserMailer->transport('debug')
            ->setLayout(false)
            ->set('url', 'http://example/link')
            ->send('forgotPassword', [$this->example]);

        $headers = $message = null;
        extract($result);

        //Checks headers
        $this->assertContains('From: MeCms <email@example.com>', $headers);
        $this->assertContains('To: James Blue <test@test.com>', $headers);
        $this->assertContains('Subject: Reset your password', $headers);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $headers);

        //Checks the message
        $this->assertContains('Hello James Blue,', $message);
        $this->assertContains('you have requested to change your password on the site MeCms.', $message);
        $this->assertContains('To reset your password, click <a href="http://example/link" title="here">here</a>.', $message);
        $this->assertContains('If you have not made this request, please contact an administrator.', $message);
    }
}
