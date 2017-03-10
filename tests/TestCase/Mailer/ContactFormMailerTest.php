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
use MeCms\Mailer\ContactFormMailer;
use Reflection\ReflectionTrait;

/**
 * ContactFormMailerTest class
 */
class ContactFormMailerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Mailer\ContactFormMailer
     */
    public $ContactFormMailer;

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

        $this->ContactFormMailer = new ContactFormMailer;

        Email::configTransport(['debug' => ['className' => 'Debug']]);

        $this->example = [
            'email' => 'test@test.com',
            'first_name' => 'First name',
            'last_name' => 'Last name',
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

        Email::dropTransport('debug');

        unset($this->ContactFormMailer);
    }

    /**
     * Tests for `contactFormMail()` method
     * @test
     */
    public function testContactFormMail()
    {
        $this->ContactFormMailer->contactFormMail($this->example);

        //Gets `Email` instance
        $email = $this->getProperty($this->ContactFormMailer, '_email');

        $this->assertEquals(['test@test.com' => 'First name Last name'], $email->from());
        $this->assertEquals(['test@test.com' => 'First name Last name'], $email->replyTo());
        $this->assertEquals(['email@example.com' => 'email@example.com'], $email->to());
        $this->assertEquals('Email from MeCms', $email->subject());
        $this->assertEquals([
            'template' => 'MeCms.Systems/contact_form',
            'layout' => 'default',
        ], $email->template());

        $this->assertEquals([
            'email' => 'test@test.com',
            'message' => 'Example of message',
            'ipAddress' => false,
            'firstName' => 'First name',
            'lastName' => 'Last name',
        ], $email->viewVars);
    }

    /**
     * Tests for `contactFormMail()` method, with some missing data
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Missing `email` key from data
     * @test
     */
    public function testContactFormMailMissingData()
    {
        unset($this->example['email']);

        $this->ContactFormMailer->contactFormMail($this->example);
    }

    /**
     * Tests for `contactFormMail()` method, calling `send()` method
     * @test
     */
    public function testContactFormMailWithSend()
    {
        $result = $this->ContactFormMailer->transport('debug')
            ->layout(false)
            ->send('contactFormMail', [$this->example]);

        $headers = $message = null;
        extract($result);

        //Checks headers
        $this->assertContains('From: First name Last name <test@test.com>', $headers);
        $this->assertContains('Reply-To: First name Last name <test@test.com>', $headers);
        $this->assertContains('Sender: MeCms <email@example.com>', $headers);
        $this->assertContains('To: email@example.com', $headers);
        $this->assertContains('Subject: Email from MeCms', $headers);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $headers);

        //Checks the message
        $this->assertContains('Email from First name Last name (test@test.com)', $message);
        $this->assertContains('Example of message', $message);
    }
}
