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
 * PagesTableTest class
 */
class PagesTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
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

        $this->Pages = TableRegistry::get('MeCms.Pages');

        Cache::clear(false, $this->Pages->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Pages);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('pages', $this->Pages->cache);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $this->Pages = $this->getMockBuilder(get_class($this->Pages))
            ->setMethods(['setNextToBePublished'])
            ->setConstructorArgs([[
                'table' => $this->Pages->table(),
                'connection' => $this->Pages->connection(),
            ]])
            ->getMock();

        $this->Pages->expects($this->once())
            ->method('setNextToBePublished');

        $this->Pages->afterDelete(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        $this->Pages = $this->getMockBuilder(get_class($this->Pages))
            ->setMethods(['setNextToBePublished'])
            ->setConstructorArgs([[
                'table' => $this->Pages->table(),
                'connection' => $this->Pages->connection(),
            ]])
            ->getMock();

        $this->Pages->expects($this->once())
            ->method('setNextToBePublished');

        $this->Pages->afterSave(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = [
            'category_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];

        $entity = $this->Pages->newEntity($example);
        $this->assertNotEmpty($this->Pages->save($entity));

        //Saves again the same entity
        $entity = $this->Pages->newEntity($example);
        $this->assertFalse($this->Pages->save($entity));
        $this->assertEquals([
            'slug' => [
                '_isUnique' => 'This value is already used',
            ],
            'title' => [
                '_isUnique' => 'This value is already used',
            ],
        ], $entity->errors());

        $entity = $this->Pages->newEntity([
            'category_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ]);
        $this->assertFalse($this->Pages->save($entity));
        $this->assertEquals(['category_id' => ['_existsIn' => 'You have to select a valid option']], $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('pages', $this->Pages->table());
        $this->assertEquals('title', $this->Pages->displayField());
        $this->assertEquals('id', $this->Pages->primaryKey());

        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->Pages->Categories));
        $this->assertEquals('category_id', $this->Pages->Categories->foreignKey());
        $this->assertEquals('INNER', $this->Pages->Categories->joinType());
        $this->assertEquals('MeCms.PagesCategories', $this->Pages->Categories->className());

        $this->assertTrue($this->Pages->hasBehavior('Timestamp'));
        $this->assertTrue($this->Pages->hasBehavior('CounterCache'));
    }

    /**
     * Test for the `belongsTo` association with `PagesCategories`
     * @test
     */
    public function testBelongsToPagesCategories()
    {
        $page = $this->Pages->findById(1)->contain(['Categories'])->first();

        $this->assertNotEmpty($page->category);

        $this->assertEquals('MeCms\Model\Entity\PagesCategory', get_class($page->category));
        $this->assertEquals(4, $page->category->id);
    }

    /**
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        $query = $this->Pages->find();
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        //Writes `next_to_be_published` and some data on cache
        Cache::write('next_to_be_published', time() - 3600, $this->Pages->cache);
        Cache::write('someData', 'someValue', $this->Pages->cache);

        $this->assertNotEmpty(Cache::read('next_to_be_published', $this->Pages->cache));
        $this->assertNotEmpty(Cache::read('someData', $this->Pages->cache));

        //The cache will now be cleared
        $query = $this->Pages->find();
        $this->assertEquals('Cake\ORM\Query', get_class($query));

        $this->assertEmpty(Cache::read('next_to_be_published', $this->Pages->cache));
        $this->assertEmpty(Cache::read('someData', $this->Pages->cache));
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\PageValidator',
            get_class($this->Pages->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
