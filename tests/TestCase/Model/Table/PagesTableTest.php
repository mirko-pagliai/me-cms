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
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Reflection\ReflectionTrait;

/**
 * PagesTableTest class
 */
class PagesTableTest extends TestCase
{
    use ReflectionTrait;

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
            ->setConstructorArgs([['table' => $this->Pages->table(), 'connection' => $this->Pages->connection()]])
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
            ->setConstructorArgs([['table' => $this->Pages->table(), 'connection' => $this->Pages->connection()]])
            ->getMock();

        $this->Pages->expects($this->once())
            ->method('setNextToBePublished');

        $this->Pages->afterSave(new \Cake\Event\Event(null), new \Cake\ORM\Entity, new \ArrayObject);
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
        $this->Pages = $this->getMockBuilder(get_class($this->Pages))
            ->setMethods(['getNextToBePublished', 'setNextToBePublished'])
            ->setConstructorArgs([['table' => $this->Pages->table(), 'connection' => $this->Pages->connection()]])
            ->getMock();

        $this->Pages->expects($this->at(0))
            ->method('getNextToBePublished')
            ->will($this->returnValue(time()+3600));

        $this->Pages->expects($this->at(1))
            ->method('getNextToBePublished')
            ->will($this->returnValue(time()-3600));

        $this->Pages->expects($this->at(2))
            ->method('setNextToBePublished');

        $this->Pages->find();
        $this->Pages->find();

        $this->markTestIncomplete('This test has not been implemented yet');
    }

    /**
     * Test for `setNextToBePublished()` method
     * @test
     */
    public function testSetNextToBePublished()
    {
        $this->assertFalse($this->Pages->setNextToBePublished());

        //Creates a page with a future publication time (1 hours)
        $created = new Time('+1 hours');

        $entity = $this->Pages->newEntity([
            'category_id' => 1,
            'title' => 'Test page',
            'slug' => 'test-page',
            'text' => 'Example test',
            'created' => $created,
        ]);

        $this->assertNotEmpty($this->Pages->save($entity));

        $this->assertEquals($created->toUnixString(), $this->Pages->setNextToBePublished());
        $this->assertEquals($created->toUnixString(), Cache::read('next_to_be_published', $this->Pages->cache));

        //Creates another page with a future publication time (30 minuts)
        //This page takes precedence over the previous
        $created = new Time('+30 minutes');

        $entity = $this->Pages->newEntity([
            'category_id' => 1,
            'title' => 'Another test page',
            'slug' => 'another-test-page',
            'text' => 'Example test',
            'created' => $created,
        ]);

        $this->assertNotEmpty($this->Pages->save($entity));

        $this->assertEquals($created->toUnixString(), $this->Pages->setNextToBePublished());
        $this->assertEquals($created->toUnixString(), Cache::read('next_to_be_published', $this->Pages->cache));
    }
}
