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
 * @since       2.22.9
 */
namespace MeCms\TestSuite;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\ORM\Entity;
use MeTools\TestSuite\TestCase;

/**
 * PostsAndPagesTablesTestCase abstract class for `PagesTableTest` and
 *  `PostsTableTest` classes
 */
abstract class PostsAndPagesTablesTestCase extends TestCase
{
    /**
     * A table instance
     * @var type
     */
    protected $Table;

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
        $this->Table = $this->getMockForModel($this->Table->getRegistryAlias(), ['setNextToBePublished']);
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
        $this->Table = $this->getMockForModel($this->Table->getRegistryAlias(), ['setNextToBePublished']);
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
        $this->Table = $this->getMockForModel($this->Table->getRegistryAlias(), ['getPreviewSize']);
        $this->Table->method('getPreviewSize')->will($this->returnValue([400, 300]));

        //Tries with a text without images or videos
        $entity = $this->Table->newEntity($this->example);
        $this->assertNotEmpty($this->Table->save($entity));
        $this->assertEmpty($entity->preview);

        $this->Table->delete($entity);

        //Tries with a text with an image
        $this->example['text'] = '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg' . '\' />';
        $entity = $this->Table->newEntity($this->example);
        $this->assertNotEmpty($this->Table->save($entity));
        $this->assertCount(1, $entity->preview);
        $this->assertInstanceOf('Cake\ORM\Entity', $entity->preview[0]);
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
        $anHourAgo = time() - 3600;

        //Writes `next_to_be_published` and some data on cache
        Cache::write('next_to_be_published', $anHourAgo, $this->Table->cache);
        Cache::write('someData', 'someValue', $this->Table->cache);

        //The cache will now be cleared
        $this->Table->find();

        $this->assertNotEquals($anHourAgo, Cache::read('next_to_be_published', $this->Table->cache));
        $this->assertEmpty(Cache::read('someData', $this->Table->cache));
    }
}
