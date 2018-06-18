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
use MeTools\TestSuite\TestCase;

/**
 * ContactUsFormTest class
 */
class ContactUsFormTest extends TestCase
{
    use MailerAwareTrait;

    /**
     * @var \MeCms\Form\ContactUsForm
     */
    public $ContactUsForm;

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

        $this->ContactUsForm = new ContactUsForm;

        $this->example = [
            'email' => 'test@test.com',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'message' => 'Example of message',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertTrue($this->ContactUsForm->validate($this->example));
        $this->assertEmpty($this->ContactUsForm->errors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertFalse($this->ContactUsForm->validate($copy));
            $errors = $this->ContactUsForm->errors();
            $this->assertEquals([$key => ['_required' => 'This field is required']], $errors);
        }
    }

    /**
     * Test validation for `message` property
     * @test
     */
    public function testValidationForMessage()
    {
        foreach ([str_repeat('a', 9), str_repeat('a', 1001)] as $value) {
            $this->example['message'] = $value;

            $this->assertFalse($this->ContactUsForm->validate($this->example));
            $errors = $this->ContactUsForm->errors();
            $this->assertEquals(['message' => ['lengthBetween' => 'Must be between 10 and 1000 chars']], $errors);
        }

        foreach ([str_repeat('a', 10), str_repeat('a', 1000)] as $value) {
            $this->example['message'] = $value;

            $this->assertTrue($this->ContactUsForm->validate($this->example));
            $this->assertEmpty($this->ContactUsForm->errors());
        }
    }

    /**
     * Tests for `_execute()` method
     * @test
     */
    public function testExecute()
    {
        $this->assertArrayKeysEqual(['headers', 'message'], $this->ContactUsForm->execute($this->example));
    }
}
