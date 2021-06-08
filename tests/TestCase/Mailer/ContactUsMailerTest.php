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

use MeCms\Mailer\ContactUsMailer;
use MeCms\TestSuite\TestCase;
use Tools\Exception\KeyNotExistsException;

/**
 * ContactUsMailerTest class
 */
class ContactUsMailerTest extends TestCase
{
    /**
     * @var \MeCms\Mailer\ContactUsMailer
     */
    public $Mailer;

    /**
     * @var array
     */
    protected $example = [
        'first_name' => 'James',
        'email' => 'mymail@example.com',
        'last_name' => 'Blue',
        'message' => 'Example of message',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Mailer = new ContactUsMailer();
        $this->Mailer->viewBuilder()->setLayout(null);
    }

    /**
     * Tests for `contactUsMail()` method
     * @test
     */
    public function testContactUsMail(): void
    {
        $this->Mailer->contactUsMail($this->example);
        $this->assertEquals(['mymail@example.com' => 'James Blue'], $this->Mailer->getSender());
        $this->assertEquals(['mymail@example.com' => 'James Blue'], $this->Mailer->getReplyTo());
        $this->assertEquals(['email@example.com' => 'email@example.com'], $this->Mailer->getTo());
        $this->assertEquals('Email from MeCms', $this->Mailer->getSubject());
        $this->assertEquals('MeCms.Systems/contact_us', $this->Mailer->viewBuilder()->getTemplate());
        $this->assertEquals([
            'email' => 'mymail@example.com',
            'message' => 'Example of message',
            'firstName' => 'James',
            'lastName' => 'Blue',
        ], $this->Mailer->viewBuilder()->getVars());

        //With some missing data
        $this->expectException(KeyNotExistsException::class);
        $this->expectExceptionMessage('Key `email` does not exist');
        $copy = $this->example;
        unset($copy['email']);
        $this->Mailer->contactUsMail($copy);
    }

    /**
     * Tests for `contactUsMail()` method, calling `send()` method
     * @test
     */
    public function testContactUsMailWithSend(): void
    {
        $result = $this->Mailer->setTransport('debug')
            ->send('contactUsMail', [$this->example]);
        $this->assertStringContainsString('From: MeCms <email@example.com>', $result['headers']);
        $this->assertStringContainsString('Reply-To: James Blue <mymail@example.com>', $result['headers']);
        $this->assertStringContainsString('Sender: James Blue <mymail@example.com>', $result['headers']);
        $this->assertStringContainsString('To: email@example.com', $result['headers']);
        $this->assertStringContainsString('Subject: Email from MeCms', $result['headers']);
        $this->assertStringContainsString('Content-Type: text/html; charset=UTF-8', $result['headers']);
        $this->assertStringContainsString('Email from James Blue (mymail@example.com)', $result['message']);
        $this->assertStringContainsString('Example of message', $result['message']);
    }
}
