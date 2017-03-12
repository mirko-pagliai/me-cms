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

        Email::configTransport(['debug' => ['className' => 'Debug']]);

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

        Email::dropTransport('debug');

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

        $this->assertEquals(['test@test.com' => 'James Blue'], $email->sender());
        $this->assertEquals(['test@test.com' => 'James Blue'], $email->replyTo());
        $this->assertEquals(['email@example.com' => 'email@example.com'], $email->to());
        $this->assertEquals('Email from MeCms', $email->subject());
        $this->assertEquals([
            'template' => 'MeCms.Systems/contact_us',
            'layout' => 'default',
        ], $email->template());

        $this->assertEquals([
            'email' => 'test@test.com',
            'message' => 'Example of message',
            'firstName' => 'James',
            'lastName' => 'Blue',
        ], $email->viewVars);
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
        $result = $this->ContactUsMailer->transport('debug')
            ->layout(false)
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
