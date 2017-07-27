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

use Cake\View\View;
use MeCms\View\Helper\AuthHelper;
use MeTools\TestSuite\TestCase;

/**
 * AuthHelperTest class
 */
class AuthHelperTest extends TestCase
{
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

        $this->Auth->initialize(['id' => 1, 'group' => ['name' => 'admin']]);

        $this->assertEquals(['id' => 1, 'group' => ['name' => 'admin']], $this->Auth->user());
        $this->assertEquals(1, $this->Auth->user('id'));
        $this->assertNull($this->Auth->user('noExistingKey'));
    }
}
