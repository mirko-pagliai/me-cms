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

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * NextToBePublishedTraitTest class
 */
class NextToBePublishedTraitTest extends TestCase
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
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.users',
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
    }

    /**
     * Test for `getNextToBePublished()` and `setNextToBePublished()` methods
     * @test
     */
    public function testGetNextToBePublishedAndSetNextToBePublished()
    {
        //Creates a record with a future publication time (1 hours)
        $future = new Time('+1 hours');

        $entity = $this->Posts->newEntity([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Future record',
            'slug' => 'future-record',
            'text' => 'Example text',
            'created' => $future,
        ]);

        $this->assertNotEmpty($this->Posts->save($entity));
        $this->assertEquals($future->toUnixString(), $this->Posts->setNextToBePublished());
        $this->assertEquals($future->toUnixString(), $this->Posts->getNextToBePublished());

        //Creates another record with a future publication time (30 minuts)
        //This record takes precedence over the previous
        $future = new Time('+30 minutes');

        $entity = $this->Posts->newEntity([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Another future record',
            'slug' => 'another-future-record',
            'text' => 'Example text',
            'created' => $future,
        ]);

        $this->assertNotEmpty($this->Posts->save($entity));
        $this->assertEquals($future->toUnixString(), $this->Posts->setNextToBePublished());
        $this->assertEquals($future->toUnixString(), $this->Posts->getNextToBePublished());
    }
}
