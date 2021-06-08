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

namespace MeCms\Test\TestCase\Mailer;

use MeCms\Mailer\UserMailer;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\TestCase;
use Tools\Exception\KeyNotExistsException;

/**
 * UserMailerTest class
 */
class UserMailerTest extends TestCase
{
    /**
     * @var \MeCms\Mailer\UserMailer
     */
    public $Mailer;

    /**
     * @var \MeCms\Model\Entity\User
     */
    protected $example;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->example = new User([
            'email' => 'test@test.com',
            'first_name' => 'James',
            'last_name' => 'Blue',
        ]);

        $this->Mailer = new UserMailer();
        $this->Mailer->viewBuilder()->setLayout(null);
    }

    /**
     * Tests for `activation()` method
     * @test
     */
    public function testActivation(): void
    {
        $this->Mailer->activation($this->example);
        $this->assertEquals(['test@test.com' => 'James Blue'], $this->Mailer->getTo());
        $this->assertEquals('Activate your account', $this->Mailer->getSubject());
        $this->assertEquals('MeCms.Users/activation', $this->Mailer->viewBuilder()->getTemplate());
        $this->assertEquals(['fullName' => 'James Blue'], $this->Mailer->viewBuilder()->getVars());

        //With some missing data
        $this->expectException(KeyNotExistsException::class);
        $this->expectExceptionMessage('Key `email` does not exist');
        unset($this->example->email);
        $this->Mailer->activation($this->example);
    }

    /**
     * Tests for `activation()` method, calling `send()` method
     * @test
     */
    public function testActivationWithSend(): void
    {
        $result = $this->Mailer->setTransport('debug')
            ->setViewVars(['url' => 'http://example/link'])
            ->send('activation', [$this->example]);
        $this->assertStringContainsString('From: MeCms <email@example.com>', $result['headers']);
        $this->assertStringContainsString('To: James Blue <test@test.com>', $result['headers']);
        $this->assertStringContainsString('Subject: Activate your account', $result['headers']);
        $this->assertStringContainsString('Content-Type: text/html; charset=UTF-8', $result['headers']);
        $this->assertStringContainsString('Hello James Blue,', $result['message']);
        $this->assertStringContainsString('you have signed on the site MeCms.', $result['message']);
        $this->assertStringContainsString('To activate your account, click <a href="http://example/link" title="here">here</a>.', $result['message']);
        $this->assertStringContainsString('If you have not made this request, please contact an administrator.', $result['message']);
    }

    /**
     * Tests for `changePassword()` method
     * @test
     */
    public function testChangePassword(): void
    {
        $this->Mailer->changePassword($this->example);
        $this->assertEquals(['test@test.com' => 'James Blue'], $this->Mailer->getTo());
        $this->assertEquals('Your password has been changed', $this->Mailer->getSubject());
        $this->assertEquals('MeCms.Users/change_password', $this->Mailer->viewBuilder()->getTemplate());
        $this->assertEquals(['fullName' => 'James Blue'], $this->Mailer->viewBuilder()->getVars());

        //With some missing data
        $this->expectException(KeyNotExistsException::class);
        $this->expectExceptionMessage('Key `email` does not exist');
        unset($this->example->email);
        $this->Mailer->changePassword($this->example);
    }

    /**
     * Tests for `changePassword()` method, calling `send()` method
     * @test
     */
    public function testChangePasswordWithSend(): void
    {
        $result = $this->Mailer->setTransport('debug')
            ->setViewVars(['url' => 'http://example/link'])
            ->send('changePassword', [$this->example]);
        $this->assertStringContainsString('From: MeCms <email@example.com>', $result['headers']);
        $this->assertStringContainsString('To: James Blue <test@test.com>', $result['headers']);
        $this->assertStringContainsString('Subject: Your password has been changed', $result['headers']);
        $this->assertStringContainsString('Content-Type: text/html; charset=UTF-8', $result['headers']);
        $this->assertStringContainsString('Hello James Blue,', $result['message']);
        $this->assertStringContainsString('you have recently changed your password on our site MeCms.', $result['message']);
        $this->assertStringContainsString('If you have not made this request, please contact an administrator.', $result['message']);
    }

    /**
     * Tests for `passwordForgot()` method
     * @test
     */
    public function testPasswordForgot(): void
    {
        $this->Mailer->passwordForgot($this->example);
        $this->assertEquals(['test@test.com' => 'James Blue'], $this->Mailer->getTo());
        $this->assertEquals('Reset your password', $this->Mailer->getSubject());
        $this->assertEquals('MeCms.Users/password_forgot', $this->Mailer->viewBuilder()->getTemplate());
        $this->assertEquals(['fullName' => 'James Blue'], $this->Mailer->viewBuilder()->getVars());

        //With some missing data
        $this->expectException(KeyNotExistsException::class);
        $this->expectExceptionMessage('Key `email` does not exist');
        unset($this->example->email);
        $this->Mailer->passwordForgot($this->example);
    }

    /**
     * Tests for `passwordForgot()` method, calling `send()` method
     * @test
     */
    public function testPasswordForgotWithSend(): void
    {
        $result = $this->Mailer->setTransport('debug')
            ->setViewVars(['url' => 'http://example/link'])
            ->send('passwordForgot', [$this->example]);
        $this->assertStringContainsString('From: MeCms <email@example.com>', $result['headers']);
        $this->assertStringContainsString('To: James Blue <test@test.com>', $result['headers']);
        $this->assertStringContainsString('Subject: Reset your password', $result['headers']);
        $this->assertStringContainsString('Content-Type: text/html; charset=UTF-8', $result['headers']);
        $this->assertStringContainsString('Hello James Blue,', $result['message']);
        $this->assertStringContainsString('you have requested to change your password on the site MeCms.', $result['message']);
        $this->assertStringContainsString('To reset your password, click <a href="http://example/link" title="here">here</a>.', $result['message']);
        $this->assertStringContainsString('If you have not made this request, please contact an administrator.', $result['message']);
    }
}
