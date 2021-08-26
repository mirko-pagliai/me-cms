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

namespace MeCms\Test\TestCase\View\Helper;

use MeTools\TestSuite\HelperTestCase;

/**
 * AuthHelperTest class
 * @property \MeCms\View\Helper\AuthHelper $Helper
 */
class AuthHelperTest extends HelperTestCase
{
    /**
     * Internal method to write auth data on session
     * @param array $data Data you want to write
     * @return void
     */
    protected function writeAuthOnSession(array $data = []): void
    {
        $this->Helper->getView()->getRequest()->getSession()->write('Auth.User', $data);
        $this->Helper->initialize([]);
    }

    /**
     * Tests for `hasId()` method
     * @test
     */
    public function testHasId(): void
    {
        $this->assertFalse($this->Helper->hasId(1));

        $this->writeAuthOnSession(['id' => 1]);
        $this->assertTrue($this->Helper->hasId(1));
        $this->assertTrue($this->Helper->hasId([1, 2]));
        $this->assertFalse($this->Helper->hasId(2));
        $this->assertFalse($this->Helper->hasId([2, 3]));
    }

    /**
     * Tests for `isFounder()` method
     * @test
     */
    public function testIsFounder(): void
    {
        $this->assertFalse($this->Helper->isFounder());

        $this->writeAuthOnSession(['id' => 1]);
        $this->assertTrue($this->Helper->isFounder());

        $this->writeAuthOnSession(['id' => 2]);
        $this->assertFalse($this->Helper->isFounder());
    }

    /**
     * Tests for `isGroup()` method
     * @test
     */
    public function testIsGroup(): void
    {
        $this->assertFalse($this->Helper->isGroup('admin'));

        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        $this->assertTrue($this->Helper->isGroup('admin'));
        $this->assertTrue($this->Helper->isGroup(['admin', 'manager']));
        $this->assertFalse($this->Helper->isGroup('manager'));
        $this->assertFalse($this->Helper->isGroup(['manager', 'otherGroup']));
    }

    /**
     * Tests for `isLogged()` method
     * @test
     */
    public function testIsLogged(): void
    {
        $this->assertFalse($this->Helper->isLogged());

        $this->writeAuthOnSession(['id' => 1]);
        $this->assertTrue($this->Helper->isLogged());
    }

    /**
     * Tests for `user()` method
     * @test
     */
    public function testUser(): void
    {
        $this->assertEmpty($this->Helper->user());
        $this->assertEmpty($this->Helper->user('id'));

        $this->writeAuthOnSession(['id' => 1, 'group' => ['name' => 'admin']]);
        $this->assertEquals(['id' => 1, 'group' => ['name' => 'admin']], $this->Helper->user());
        $this->assertEquals(1, $this->Helper->user('id'));
        $this->assertEmpty($this->Helper->user('noExistingKey'));
    }
}
