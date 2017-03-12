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

        $this->assertEquals(['MeTools.Html'], $email->helpers());
        $this->assertEquals(['email@example.com' => 'MeCms'], $email->sender());
        $this->assertEquals(['email@example.com' => 'MeCms'], $email->from());
        $this->assertEquals('html', $email->emailFormat());

        $this->assertEquals([], $email->viewVars);
    }
}
