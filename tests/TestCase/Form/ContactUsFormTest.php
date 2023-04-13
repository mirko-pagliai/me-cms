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

namespace MeCms\Test\TestCase\Form;

use Cake\Mailer\MailerAwareTrait;
use MeCms\Form\ContactUsForm;
use MeCms\TestSuite\TestCase;
use StopSpam\SpamDetector;

/**
 * ContactUsFormTest class
 */
class ContactUsFormTest extends TestCase
{
    use MailerAwareTrait;

    /**
     * @var \MeCms\Form\ContactUsForm
     */
    public ContactUsForm $Form;

    /**
     * @var string[]
     */
    protected array $example = [
        'email' => 'mymail@example.com',
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'message' => 'Example of message',
    ];

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($this->Form)) {
            $this->Form = new ContactUsForm();

            $this->Form->SpamDetector = $this->getMockBuilder(SpamDetector::class)
                ->onlyMethods(['verify'])
                ->getMock();

            $this->Form->SpamDetector->method('verify')->willReturn(true);
        }
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData(): void
    {
        $this->assertTrue($this->Form->validate($this->example));
        $this->assertEmpty($this->Form->getErrors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $expected = [$key => ['_required' => 'This field is required']];
            $this->assertFalse($this->Form->validate($copy));
            $this->assertEquals($expected, $this->Form->getErrors());
        }
    }

    /**
     * Test validation for `email` property
     * @test
     */
    public function testValidationForEmail(): void
    {
        $SpamDetector = $this->getMockBuilder(SpamDetector::class)
            ->onlyMethods(['verify'])
            ->getMock();

        $SpamDetector->method('verify')->willReturn(false);

        $this->Form->SpamDetector = $SpamDetector;

        $this->assertFalse($this->Form->validate(['email' => 'spammer@example.com'] + $this->example));
        $this->assertEquals(['email' => ['notSpammer' => 'This email address has been reported as a spammer']], $this->Form->getErrors());
    }

    /**
     * Test validation for `message` property
     * @test
     */
    public function testValidationForMessage(): void
    {
        $expected = ['message' => ['lengthBetween' => 'Must be between 10 and 1000 chars']];
        foreach ([str_repeat('a', 9), str_repeat('a', 1001)] as $message) {
            $this->assertFalse($this->Form->validate(compact('message') + $this->example));
            $this->assertEquals($expected, $this->Form->getErrors());
        }

        foreach ([str_repeat('a', 10), str_repeat('a', 1000)] as $message) {
            $this->assertTrue($this->Form->validate(compact('message') + $this->example));
            $this->assertEmpty($this->Form->getErrors());
        }
    }

    /**
     * Tests for `_execute()` method
     * @uses \MeCms\Form\ContactUsForm::execute()
     * @test
     */
    public function testExecute(): void
    {
        $this->assertTrue($this->Form->execute($this->example));
    }
}
