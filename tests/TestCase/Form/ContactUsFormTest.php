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
namespace MeCms\Test\TestCase\Form;

use Cake\Mailer\MailerAwareTrait;
use MeCms\Form\ContactUsForm;
use MeCms\TestSuite\TestCase;

/**
 * ContactUsFormTest class
 */
class ContactUsFormTest extends TestCase
{
    use MailerAwareTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    public $Form;

    /**
     * @var array
     */
    protected $example = [
        'email' => 'test@test.com',
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'message' => 'Example of message',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Form = $this->getMockBuilder(ContactUsForm::class)
            ->setMethods(null)
            ->getMock();
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertTrue($this->Form->validate($this->example));
        $this->assertEmpty($this->Form->errors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $expected = [$key => ['_required' => 'This field is required']];
            $this->assertFalse($this->Form->validate($copy));
            $this->assertEquals($expected, $this->Form->errors());
        }
    }

    /**
     * Test validation for `message` property
     * @test
     */
    public function testValidationForMessage()
    {
        $expected = ['message' => ['lengthBetween' => 'Must be between 10 and 1000 chars']];
        foreach ([str_repeat('a', 9), str_repeat('a', 1001)] as $value) {
            $this->example['message'] = $value;

            $this->assertFalse($this->Form->validate($this->example));
            $this->assertEquals($expected, $this->Form->errors());
        }

        foreach ([str_repeat('a', 10), str_repeat('a', 1000)] as $value) {
            $this->example['message'] = $value;

            $this->assertTrue($this->Form->validate($this->example));
            $this->assertEmpty($this->Form->errors());
        }
    }

    /**
     * Tests for `_execute()` method
     * @test
     */
    public function testExecute()
    {
        $this->assertArrayKeysEqual(['headers', 'message'], $this->Form->execute($this->example));
    }
}
