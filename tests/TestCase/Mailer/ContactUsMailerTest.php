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

use Cake\TestSuite\TestCase;
use MeCms\Mailer\ContactUsMailer;
use Reflection\ReflectionTrait;

/**
 * ContactUsMailerTest class
 */
class ContactUsMailerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Mailer\ContactUsMailer
     */
    public $ContactUsMailer;

    /**
     * @var array
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

        $this->ContactUsMailer = new ContactUsMailer;

        $this->example = [
            'email' => 'test@test.com',
            'first_name' => 'James',
            'last_name' => 'Blue',
            'message' => 'Example of message',
        ];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->ContactUsMailer);
    }

    /**
     * Tests for `contactUsMail()` method
     * @test
     */
    public function testContactUsMail()
    {
        $this->ContactUsMailer->contactUsMail($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->ContactUsMailer, '_email');

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->getSender());
        $this->assertEquals(['test@test.com' => 'James Blue'], $email->getReplyTo());
        $this->assertEquals(['email@example.com' => 'email@example.com'], $email->getTo());
        $this->assertEquals('Email from MeCms', $email->getSubject());
        $this->assertEquals(ME_CMS . '.Systems/contact_us', $email->getTemplate());
        $this->assertEquals([
            'email' => 'test@test.com',
            'message' => 'Example of message',
            'firstName' => 'James',
            'lastName' => 'Blue',
        ], $email->getViewVars());
    }

    /**
     * Tests for `contactUsMail()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` key from data
     * @test
     */
    public function testContactUsMailMissingData()
    {
        unset($this->example['email']);

        $this->ContactUsMailer->contactUsMail($this->example);
    }

    /**
     * Tests for `contactUsMail()` method, calling `send()` method
     * @test
     */
    public function testContactUsMailWithSend()
    {
        $result = $this->ContactUsMailer->setTransport('debug')
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

        //Checks the message
        $this->assertContains('Email from James Blue (test@test.com)', $message);
        $this->assertContains('Example of message', $message);
    }
}
