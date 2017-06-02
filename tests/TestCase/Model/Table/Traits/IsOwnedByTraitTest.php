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
namespace MeCms\Test\TestCase\Model\Table\Traits;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * IsOwnedByTraitTest class
 */
class IsOwnedByTraitTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Posts);
    }

    /**
     * Test for `isOwnedBy()` method
     * @test
     */
    public function testIsOwnedBy()
    {
        $this->assertTrue($this->Posts->isOwnedBy(2, 4));
        $this->assertFalse($this->Posts->isOwnedBy(2, 1));
        $this->assertTrue($this->Posts->isOwnedBy(1, 1));
        $this->assertFalse($this->Posts->isOwnedBy(1, 2));
    }
}
