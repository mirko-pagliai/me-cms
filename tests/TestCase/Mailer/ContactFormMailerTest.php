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
        $data = [
            'email' => 'test@test.com',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'message' => 'Example of message',
        ];

        $this->ContactFormMailer->contactFormMail($data);

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


        //Tries to send
        $email->transport('debug');
        $result = $this->ContactFormMailer->layout(false)->send('contactFormMail', [$data]);

        //Checks headers
        $this->assertContains('From: First name Last name <test@test.com>', $result['headers']);
        $this->assertContains('Reply-To: First name Last name <test@test.com>', $result['headers']);
        $this->assertContains('Sender: MeCms <email@example.com>', $result['headers']);
        $this->assertContains('To: email@example.com', $result['headers']);
        $this->assertContains('Subject: Email from MeCms', $result['headers']);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $result['headers']);

        //Checks the message
        $this->assertContains('Email from First name Last name (test@test.com)', $result['message']);
        $this->assertContains('Example of message', $result['message']);
    }
}
