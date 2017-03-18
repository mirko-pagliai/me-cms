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
namespace MeCms\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use MeCms\Controller\Component\AuthComponent;

/**
 * AuthComponentTest class
 */
class AuthComponentTest extends TestCase
{
    /**
     * @var \MeCms\Controller\Component\AuthComponent
     */
    public $Auth;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Auth = new AuthComponent(new ComponentRegistry(new Controller));
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
     * Tests for `__construct()` and `initialize()` methods
     * @test
     */
    public function testConstructAndInitialize()
    {
        $keysToCheck = [
            'authenticate',
            'authError',
            'authorize',
            'flash',
            'loginAction',
            'loginRedirect',
            'logoutRedirect',
            'unauthorizedRedirect',
        ];

        $config = $this->Auth->getConfig();

        //Checks that all keys exist
        foreach ($keysToCheck as $key) {
            $this->assertTrue(array_key_exists($key, $config));
        }

        $this->assertFalse($this->Auth->getConfig('authError'));
    }

    /**
     * Tests for `hasId()` method
     * @test
     */
    public function testHasId()
    {
        $this->assertFalse($this->Auth->hasId(1));

        $this->Auth->setUser(['id' => 1]);
        $this->assertTrue($this->Auth->hasId(1));
        $this->assertTrue($this->Auth->hasId([1, 2]));
        $this->assertFalse($this->Auth->hasId(2));
        $this->assertFalse($this->Auth->hasId([2, 3]));
    }

    /**
     * Tests for `isFounder()` method
     * @test
     */
    public function testIsFounder()
    {
        $this->assertFalse($this->Auth->isFounder());

        $this->Auth->setUser(['id' => 1]);
        $this->assertTrue($this->Auth->isFounder());

        $this->Auth->setUser(['id' => 2]);
        $this->assertFalse($this->Auth->isFounder());
    }

    /**
     * Tests for `isLogged()` method
     * @test
     */
    public function testIsLogged()
    {
        $this->assertFalse($this->Auth->isLogged());

        $this->Auth->setUser(['id' => 1]);
        $this->assertTrue($this->Auth->isLogged());
    }

    /**
     * Tests for `isGroup()` method
     * @test
     */
    public function testIsGroup()
    {
        $this->assertFalse($this->Auth->isGroup('admin'));

        $this->Auth->setUser(['group' => ['name' => 'admin']]);
        $this->assertTrue($this->Auth->isGroup('admin'));
        $this->assertTrue($this->Auth->isGroup(['admin', 'manager']));
        $this->assertFalse($this->Auth->isGroup(['manager', 'noExistingGroup']));
    }
}
