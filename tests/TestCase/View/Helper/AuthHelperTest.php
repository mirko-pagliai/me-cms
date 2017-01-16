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
namespace MeCms\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use MeCms\View\Helper\AuthHelper;
use Reflection\ReflectionTrait;

/**
 * AuthHelperTest class
 */
class AuthHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\View\Helper\AuthHelper
     */
    protected $Auth;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Auth = new AuthHelper(new View);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Auth);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEmpty($this->getProperty($this->Auth, 'user'));

        $this->Auth->initialize(['id' => 1]);

        $this->assertEquals(['id' => 1], $this->getProperty($this->Auth, 'user'));
    }

    /**
     * Tests for `hasId()` method
     * @test
     */
    public function testHasId()
    {
        $this->assertFalse($this->Auth->hasId(1));

        $this->Auth->initialize(['id' => 1]);

        $this->assertTrue($this->Auth->hasId(1));
        $this->assertFalse($this->Auth->hasId(2));
        $this->assertTrue($this->Auth->hasId([1, 2]));
        $this->assertFalse($this->Auth->hasId([2, 3]));
    }

    /**
     * Tests for `isFounder()` method
     * @test
     */
    public function testIsFounder()
    {
        $this->assertFalse($this->Auth->isFounder());

        $this->Auth->initialize(['id' => 1]);

        $this->assertTrue($this->Auth->isFounder());

        $this->Auth->initialize(['id' => 2]);

        $this->assertFalse($this->Auth->isFounder());
    }

    /**
     * Tests for `isGroup()` method
     * @test
     */
    public function testIsGroup()
    {
        $this->assertFalse($this->Auth->isGroup('admin'));

        $this->Auth->initialize(['group' => ['name' => 'admin']]);

        $this->assertTrue($this->Auth->isGroup('admin'));
        $this->assertFalse($this->Auth->isGroup('manager'));
        $this->assertTrue($this->Auth->isGroup(['admin', 'manager']));
        $this->assertFalse($this->Auth->isGroup(['manager', 'otherGroup']));
    }

    /**
     * Tests for `isLogged()` method
     * @test
     */
    public function testIsLogged()
    {
        $this->assertFalse($this->Auth->isLogged());

        $this->Auth->initialize(['id' => 1]);

        $this->assertTrue($this->Auth->isLogged());
    }

    /**
     * Tests for `user()` method
     * @test
     */
    public function testUser()
    {
        $this->assertNull($this->Auth->user());
        $this->assertNull($this->Auth->user('id'));

        $this->Auth->initialize([
            'id' => 1,
            'group' => ['name' => 'admin'],
        ]);

        $this->assertEquals([
            'id' => 1,
            'group' => ['name' => 'admin'],
        ], $this->Auth->user());

        $this->assertEquals(1, $this->Auth->user('id'));
        $this->assertNull($this->Auth->user('noExistingKey'));
    }
}
