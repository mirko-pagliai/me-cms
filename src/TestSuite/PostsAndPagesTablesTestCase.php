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
     * @var \MeCms\ORM\PostsAndPagesTables
     */
    protected $Table;

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
     * Test for `buildRules()` method
     * @return void
     * @test
     */
    public function testBuildRules(): void
    {
        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());

        $entity = $this->Table->newEntity([
            'category_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ] + self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['category_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `_initializeSchema()` method
     * @return void
     * @test
     */
    public function testInitializeSchema(): void
    {
        $this->assertEquals('jsonEntity', $this->Table->getSchema()->getColumnType('preview'));
    }

    /**
     * Test for event methods
     * @return void
     * @test
     */
    public function testEventMethods(): void
    {
        [$event, $entity] = [new Event('myEvent'), $this->Table->newEmptyEntity(), new ArrayObject()];

        /** @var \MeCms\ORM\PostsAndPagesTables&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('MeCms. ' . $this->Table->getAlias(), ['clearCache', 'getPreviewSize', 'setNextToBePublished']);
        $Table->expects($this->exactly(2))->method('clearCache');
        $Table->expects($this->exactly(2))->method('setNextToBePublished');
        $Table->afterDelete($event, $entity);
        $Table->afterSave($event, $entity);

        $Table->method('getPreviewSize')->will($this->returnValue([400, 300]));

        //Tries with a text without images or videos
        $entity = $Table->newEntity(self::$example);
        $Table->beforeSave($event, $entity);
        $this->assertTrue($entity->get('preview')->isEmpty());

        //Tries with a text with an image
        $entity = $Table->newEntity(['text' => '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg\' />'] + self::$example);
        $Table->beforeSave($event, $entity);
        $this->assertCount(1, $entity->get('preview'));
        $this->assertContainsOnlyInstancesOf(Entity::class, $entity->get('preview'));
        $this->assertMatchesRegularExpression('/^http:\/\/localhost\/thumb\/[A-z\d]+/', $entity->get('preview')->first()->get('url'));
        $this->assertEquals(400, $entity->get('preview')->first()->get('width'));
        $this->assertEquals(300, $entity->get('preview')->first()->get('height'));
    }

    /**
     * Test for `initialize()` method
     * @return void
     * @test
     */
    public function testInitialize(): void
    {
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertEquals('Categories', $this->Table->Categories->getAlias());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);
    }

    /**
     * Test for `find()` method
     * @return void
     * @test
     */
    public function testFind(): void
    {
        //Writes `next_to_be_published` and some data on cache
        $anHourAgo = (string)(time() - HOUR);
        Cache::write('next_to_be_published', $anHourAgo, $this->Table->getCacheName());
        Cache::write('someData', 'someValue', $this->Table->getCacheName());

        //The cache will now be cleared
        $this->Table->find();
        $this->assertNotEquals($anHourAgo, Cache::read('next_to_be_published', $this->Table->getCacheName()));
        $this->assertEmpty(Cache::read('someData', $this->Table->getCacheName()));
    }
}
