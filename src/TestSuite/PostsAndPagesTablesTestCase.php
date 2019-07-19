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
    public function testBuildRules()
    {
        $this->loadFixtures();
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
    public function testInitializeSchema()
    {
        $this->assertEquals('jsonEntity', $this->Table->getSchema()->getColumnType('preview'));
    }

    /**
     * Test for `afterDelete()` and `afterSave()` methods
     * @return void
     * @test
     */
    public function testAfterDeleteAndAfterSave()
    {
        [$event, $entity, $options] = [new Event(null), $this->Table->newEntity([]), new ArrayObject()];

        foreach (['afterDelete', 'afterSave'] as $methodToCall) {
            $Table = $this->getMockForModel('MeCms. ' . $this->Table->getAlias(), ['clearCache', 'setNextToBePublished']);
            $Table->expects($this->once())->method('clearCache');
            $Table->expects($this->once())->method('setNextToBePublished');
            $Table->$methodToCall($event, $entity, $options);
        }
    }

    /**
     * Test for `beforeSave()` method
     * @return void
     * @test
     */
    public function testBeforeSave()
    {
        $this->loadFixtures();

        $Table = $this->getMockForModel('MeCms.' . $this->Table->getAlias(), ['getPreviewSize']);
        $Table->method('getPreviewSize')->will($this->returnValue([400, 300]));

        //Tries with a text without images or videos
        $entity = $Table->newEntity(self::$example);
        $this->assertNotEmpty($Table->save($entity));
        $this->assertEmpty($entity->get('preview'));
        $Table->delete($entity);

        //Tries with a text with an image
        $entity = $Table->newEntity(['text' => '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg' . '\' />'] + self::$example);
        $this->assertNotEmpty($Table->save($entity));
        $this->assertCount(1, $entity->get('preview'));
        $this->assertInstanceOf(Entity::class, $entity->get('preview')[0]);
        $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z\d]+/', $entity->get('preview')[0]->get('url'));
        $this->assertEquals([400, 300], [$entity->get('preview')[0]->get('width'), $entity->get('preview')[0]->get('height')]);
    }

    /**
     * Test for `initialize()` method
     * @return void
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertEquals('Categories', $this->Table->Categories->getAlias());
        $this->assertEquals(sprintf('MeCms.%sCategories', $this->Table->getAlias()), $this->Table->Categories->getClassName());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);
    }

    /**
     * Test for `find()` method
     * @return void
     * @test
     */
    public function testFind()
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
