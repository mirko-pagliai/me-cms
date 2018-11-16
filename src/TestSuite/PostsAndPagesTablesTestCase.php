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
 * @since       2.23.0
 */
namespace MeCms\TestSuite;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\ORM\Entity;
use MeCms\TestSuite\TableTestCase;

/**
 * Abstract class for `PagesTableTest` and `PostsTableTest` classes
 */
abstract class PostsAndPagesTablesTestCase extends TableTestCase
{
    /**
     * @var array
     */
    protected static $example = [
        'category_id' => 1,
        'title' => 'My title',
        'slug' => 'my-slug',
        'text' => 'My text',
    ];

    /**
     * Test for `cache` property
     * @return void
     * @test
     */
    abstract public function testCacheProperty();

    /**
     * Test for `buildRules()` method
     * @return void
     * @test
     */
    abstract public function testBuildRules();

    /**
     * Test for `_initializeSchema()` method
     * @return void
     * @test
     */
    public function testInitializeSchema()
    {
        $this->assertEquals('jsonEntity', $this->Table->getSchema()->getColumnType('preview'));
    }

    /**
     * Test for `afterDelete()` method
     * @return void
     * @test
     */
    public function testAfterDelete()
    {
        $this->loadFixtures();

        $this->Table = $this->getMockForTable(get_parent_class($this->Table), ['setNextToBePublished']);
        $this->Table->expects($this->once())->method('setNextToBePublished');
        $this->Table->afterDelete(new Event(null), new Entity, new ArrayObject);
    }

    /**
     * Test for `afterSave()` method
     * @return void
     * @test
     */
    public function testAfterSave()
    {
        $this->loadFixtures();

        $this->Table = $this->getMockForTable(get_parent_class($this->Table), ['setNextToBePublished']);
        $this->Table->expects($this->once())->method('setNextToBePublished');
        $this->Table->afterSave(new Event(null), new Entity, new ArrayObject);
    }

    /**
     * Test for `beforeSave()` method
     * @return void
     * @test
     */
    public function testBeforeSave()
    {
        $this->loadFixtures();

        $this->Table = $this->getMockForTable(get_parent_class($this->Table), ['getPreviewSize']);
        $this->Table->method('getPreviewSize')->will($this->returnValue([400, 300]));

        //Tries with a text without images or videos
        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));
        $this->assertEmpty($entity->preview);

        $this->Table->delete($entity);

        //Tries with a text with an image
        $example = self::$example;
        $example['text'] = '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg' . '\' />';
        $entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($this->Table->save($entity));
        $this->assertCount(1, $entity->preview);
        $this->assertInstanceOf(Entity::class, $entity->preview[0]);
        $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+/', $entity->preview[0]->url);
        $this->assertEquals(400, $entity->preview[0]->width);
        $this->assertEquals(300, $entity->preview[0]->height);
    }

    /**
     * Test for `initialize()` method
     * @return void
     * @test
     */
    abstract public function testInitialize();

    /**
     * Test for `find()` method
     * @return void
     * @test
     */
    public function testFind()
    {
        //Writes `next_to_be_published` and some data on cache
        $anHourAgo = time() - HOUR;
        Cache::write('next_to_be_published', $anHourAgo, $this->Table->cache);
        Cache::write('someData', 'someValue', $this->Table->cache);

        //The cache will now be cleared
        $this->Table->find();
        $this->assertNotEquals($anHourAgo, Cache::read('next_to_be_published', $this->Table->cache));
        $this->assertEmpty(Cache::read('someData', $this->Table->cache));
    }
}
