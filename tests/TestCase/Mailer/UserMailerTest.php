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
namespace MeCms\Test\TestCase\Mailer;

use MeCms\Mailer\UserMailer;
use MeTools\TestSuite\TestCase;

/**
 * UserMailerTest class
 */
class UserMailerTest extends TestCase
{
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

        $this->example = (object)[
            'email' => 'test@test.com',
            'full_name' => 'James Blue',
        ];

        $this->UserMailer = new UserMailer;
    }

    /**
     * Tests for `activation()` method
     * @test
     */
    public function testActivation()
    {
        $this->UserMailer->activation($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->UserMailer, '_email');

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->getTo());
        $this->assertEquals('Activate your account', $email->getSubject());
        $this->assertEquals(ME_CMS . '.Users/activation', $email->getTemplate());
        $this->assertEquals(['fullName' => 'James Blue'], $email->getViewVars());
    }

    /**
     * Tests for `activation()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` property from data
     * @test
     */
    public function testActivationMissingData()
    {
        unset($this->example->email);

        $this->UserMailer->activation($this->example);
    }

    /**
     * Tests for `activation()` method, calling `send()` method
     * @test
     */
    public function testActivationWithSend()
    {
        $result = $this->UserMailer->setTransport('debug')
            ->setLayout(false)
            ->setViewVars(['url' => 'http://example/link'])
            ->send('activation', [$this->example]);

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

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->getTo());
        $this->assertEquals('Your password has been changed', $email->getSubject());
        $this->assertEquals(ME_CMS . '.Users/change_password', $email->getTemplate());
        $this->assertEquals(['fullName' => 'James Blue'], $email->getViewVars());
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
        $result = $this->UserMailer->setTransport('debug')
            ->setLayout(false)
            ->setViewVars(['url' => 'http://example/link'])
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
     * Tests for `passwordForgot()` method
     * @test
     */
    public function testPasswordForgot()
    {
        $this->UserMailer->passwordForgot($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->UserMailer, '_email');

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->getTo());
        $this->assertEquals('Reset your password', $email->getSubject());
        $this->assertEquals(ME_CMS . '.Users/password_forgot', $email->getTemplate());
        $this->assertEquals(['fullName' => 'James Blue'], $email->getViewVars());
    }

    /**
     * Tests for `passwordForgot()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` property from data
     * @test
     */
    public function testPasswordForgotMissingData()
    {
        unset($this->example->email);

        $this->UserMailer->passwordForgot($this->example);
    }

    /**
     * Tests for `passwordForgot()` method, calling `send()` method
     * @test
     */
    public function testPasswordForgotWithSend()
    {
        $result = $this->UserMailer->setTransport('debug')
            ->setLayout(false)
            ->setViewVars(['url' => 'http://example/link'])
            ->send('passwordForgot', [$this->example]);

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
