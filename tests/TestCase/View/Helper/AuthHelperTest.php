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
namespace MeCms\Test\TestCase\View\Helper;

use MeCms\TestSuite\HelperTestCase;

/**
 * AuthHelperTest class
 */
class AuthHelperTest extends HelperTestCase
{
    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEmpty($this->getProperty($this->Helper, 'user'));

        $this->Helper->initialize(['id' => 1]);
        $this->assertEquals(['id' => 1], $this->getProperty($this->Helper, 'user'));
    }

    /**
     * Tests for `hasId()` method
     * @test
     */
    public function testHasId()
    {
        $this->assertFalse($this->Helper->hasId(1));

        $this->Helper->initialize(['id' => 1]);
        $this->assertTrue($this->Helper->hasId(1));
        $this->assertFalse($this->Helper->hasId(2));
        $this->assertTrue($this->Helper->hasId([1, 2]));
        $this->assertFalse($this->Helper->hasId([2, 3]));
    }

    /**
     * Tests for `isFounder()` method
     * @test
     */
    public function testIsFounder()
    {
        $this->assertFalse($this->Helper->isFounder());

        $this->Helper->initialize(['id' => 1]);
        $this->assertTrue($this->Helper->isFounder());

        $this->Helper->initialize(['id' => 2]);
        $this->assertFalse($this->Helper->isFounder());
    }

    /**
     * Tests for `isGroup()` method
     * @test
     */
    public function testIsGroup()
    {
        $this->assertFalse($this->Helper->isGroup('admin'));

        $this->Helper->initialize(['group' => ['name' => 'admin']]);
        $this->assertTrue($this->Helper->isGroup('admin'));
        $this->assertTrue($this->Helper->isGroup(['admin', 'manager']));
        $this->assertFalse($this->Helper->isGroup('manager'));
        $this->assertFalse($this->Helper->isGroup(['manager', 'otherGroup']));
    }

    /**
     * Tests for `isLogged()` method
     * @test
     */
    public function testIsLogged()
    {
        $this->assertFalse($this->Helper->isLogged());

        $this->Helper->initialize(['id' => 1]);
        $this->assertTrue($this->Helper->isLogged());
    }

    /**
     * Tests for `user()` method
     * @test
     */
    public function testUser()
    {
        $this->assertNull($this->Helper->user());
        $this->assertNull($this->Helper->user('id'));

        $this->Helper->initialize(['id' => 1, 'group' => ['name' => 'admin']]);
        $this->assertEquals(['id' => 1, 'group' => ['name' => 'admin']], $this->Helper->user());
        $this->assertEquals(1, $this->Helper->user('id'));
        $this->assertNull($this->Helper->user('noExistingKey'));
    }
}
