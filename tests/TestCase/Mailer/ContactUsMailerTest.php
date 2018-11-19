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
use MeTools\TestSuite\TestCase;

/**
 * ContactUsMailerTest class
 */
class ContactUsMailerTest extends TestCase
{
    /**
     * @var \MeCms\Mailer\ContactUsMailer
     */
    public $ContactUsMailer;

    /**
     * @var array
     */
    protected $example = [
        'first_name' => 'James',
        'email' => 'test@test.com',
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
    }

    /**
     * Tests for `contactUsMail()` method
     * @test
     */
    public function testContactUsMail()
    {
        $this->Mailer->contactUsMail($this->example);
        $this->assertEquals(['test@test.com' => 'James Blue'], $this->Mailer->getEmailInstance()->getSender());
        $this->assertEquals(['test@test.com' => 'James Blue'], $this->Mailer->getEmailInstance()->getReplyTo());
        $this->assertEquals(['email@example.com' => 'email@example.com'], $this->Mailer->getEmailInstance()->getTo());
        $this->assertEquals('Email from MeCms', $this->Mailer->getEmailInstance()->getSubject());
        $this->assertEquals(ME_CMS . '.Systems/contact_us', $this->Mailer->getEmailInstance()->getTemplate());
        $this->assertEquals([
            'email' => 'test@test.com',
            'message' => 'Example of message',
            'firstName' => 'James',
            'lastName' => 'Blue',
        ], $this->Mailer->getEmailInstance()->getViewVars());
    }

    /**
     * Tests for `contactUsMail()` method, with some missing data
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing `email` key from data
     * @test
     */
    public function testContactUsMailMissingData()
    {
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
            ->setLayout(false)
            ->send('contactUsMail', [$this->example]);

        $headers = $message = null;
        extract($result);

        //Checks headers
        $this->assertContains('From: MeCms <email@example.com>', $headers);
        $this->assertContains('Reply-To: James Blue <test@test.com>', $headers);
        $this->assertContains('Sender: James Blue <test@test.com>', $headers);
        $this->assertContains('To: email@example.com', $headers);
        $this->assertContains('Subject: Email from MeCms', $headers);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $headers);

        //Checks message
        $this->assertContains('Email from James Blue (test@test.com)', $message);
        $this->assertContains('Example of message', $message);
    }
}
