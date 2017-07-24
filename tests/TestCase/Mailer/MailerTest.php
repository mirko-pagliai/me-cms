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
use MeCms\Mailer\Mailer;
use Reflection\ReflectionTrait;

/**
 * MailerTest class
 */
class MailerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Mailer\Mailer
     */
    public $Mailer;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Mailer = new Mailer;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Mailer);
    }

    /**
     * Tests for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        //Gets `Email` instance
        $email = $this->invokeMethod($this->Mailer, 'getEmailInstance');

        $this->assertEquals([METOOLS . '.Html'], $email->getHelpers());
        $this->assertEquals(['email@example.com' => ME_CMS], $email->getSender());
        $this->assertEquals(['email@example.com' => ME_CMS], $email->getFrom());
        $this->assertEquals('html', $email->getEmailFormat());
        $this->assertEquals([], $email->getViewVars());
    }
}
