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
namespace MeCms\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * TagsTableTest class
 */
class TagsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Tags;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
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

        $this->Tags = TableRegistry::get('MeCms.Tags');

        Cache::clear(false, $this->Tags->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Tags);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('posts', $this->Tags->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = [
            'tag' => 'my tag',
        ];

        $entity = $this->Tags->newEntity($example);
        $this->assertNotEmpty($this->Tags->save($entity));

        //Saves again the same entity
        $entity = $this->Tags->newEntity($example);
        $this->assertFalse($this->Tags->save($entity));
        $this->assertEquals(['tag' => ['_isUnique' => 'This value is already used']], $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('tags', $this->Tags->table());
        $this->assertEquals('tag', $this->Tags->displayField());
        $this->assertEquals('id', $this->Tags->primaryKey());

        $this->assertEquals('Cake\ORM\Association\BelongsToMany', get_class($this->Tags->Posts));
        $this->assertEquals('tag_id', $this->Tags->Posts->foreignKey());
        $this->assertEquals('post_id', $this->Tags->Posts->targetForeignKey());
        $this->assertEquals('MeCms.Posts', $this->Tags->Posts->className());

        //Missing checks for `joinTable` and `through` options
        $this->markTestIncomplete('This test has not been implemented yet');

        $this->assertTrue($this->Tags->hasBehavior('Timestamp'));
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->Tags->hasFinder('active'));

        $query = $this->Tags->find('active');
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Tags.id AS `Tags__id`, Tags.tag AS `Tags__tag`, Tags.post_count AS `Tags__post_count`, Tags.created AS `Tags__created`, Tags.modified AS `Tags__modified` FROM tags Tags WHERE Tags.post_count > :c0', $query->sql());

        $this->assertEquals(0, $query->valueBinder()->bindings()[':c0']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $tag) {
            $this->assertNotEquals(0, $tag->post_count);
        }
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['name' => 'test'];

        $query = $this->Tags->queryFromFilter($this->Tags->find(), $data);
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Tags.id AS `Tags__id`, Tags.tag AS `Tags__tag`, Tags.post_count AS `Tags__post_count`, Tags.created AS `Tags__created`, Tags.modified AS `Tags__modified` FROM tags Tags WHERE Tags.tag like :c0', $query->sql());

        $this->assertEquals('%test%', $query->valueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `tagsAsArray()` method
     * @test
     */
    public function testTagsAsArray()
    {
        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for `tagsAsString()` method
     * @test
     */
    public function testTagsAsString()
    {
        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\TagValidator',
            get_class($this->Tags->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
