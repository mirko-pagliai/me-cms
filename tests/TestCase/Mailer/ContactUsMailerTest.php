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
    public function setUp()
    {
        parent::setUp();

        $this->Mailer = new ContactUsMailer;
        $this->Mailer->viewBuilder()->setLayout(false);
    }

    /**
     * Tests for `contactUsMail()` method
     * @test
     */
    public function testContactUsMail()
    {
        $this->Mailer->contactUsMail($this->example);
        $result = $this->Mailer->getEmailInstance();
        $this->assertEquals(['mymail@example.com' => 'James Blue'], $result->getSender());
        $this->assertEquals(['mymail@example.com' => 'James Blue'], $result->getReplyTo());
        $this->assertEquals(['email@example.com' => 'email@example.com'], $result->getTo());
        $this->assertEquals('Email from MeCms', $result->getSubject());
        $this->assertEquals('MeCms.Systems/contact_us', $result->viewBuilder()->getTemplate());
        $this->assertEquals([
            'email' => 'mymail@example.com',
            'message' => 'Example of message',
            'firstName' => 'James',
            'lastName' => 'Blue',
        ], $result->getViewVars());

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
    public function testContactUsMailWithSend()
    {
        $result = $this->Mailer->setTransport('debug')
            ->send('contactUsMail', [$this->example]);

        $headers = $message = null;
        extract($result);

        //Checks headers
        $this->assertContains('From: MeCms <email@example.com>', $headers);
        $this->assertContains('Reply-To: James Blue <mymail@example.com>', $headers);
        $this->assertContains('Sender: James Blue <mymail@example.com>', $headers);
        $this->assertContains('To: email@example.com', $headers);
        $this->assertContains('Subject: Email from MeCms', $headers);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $headers);

        //Checks message
        $this->assertContains('Email from James Blue (mymail@example.com)', $message);
        $this->assertContains('Example of message', $message);
    }
}
