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
use Cake\ORM\Entity;

/**
 * Abstract class for `PagesTableTest` and `PostsTableTest` classes
 * @property \MeCms\Model\Table\PagesTable|\MeCms\Model\Table\PostsTable $Table
 * @see \MeCms\Test\TestCase\Model\Table\PagesTableTest
 * @see \MeCms\Test\TestCase\Model\Table\PostsTableTest
 */
abstract class PostsAndPagesTablesTestCase extends TableTestCase
{
    /**
     * @var array{category_id: int, title: string, slug: string, text: string}
     */
    protected static array $example = [
        'category_id' => 1,
        'title' => 'My title',
        'slug' => 'my-slug',
        'text' => 'My text',
    ];

    /**
     * @return void
     * @test
     * @uses \MeCms\Model\Table\PagesTable::buildRules()
     * @uses \MeCms\Model\Table\PostsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $Entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($Entity));

        //Saves again the same entity
        $Entity = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($Entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $Entity->getErrors());

        $Entity = $this->Table->newEntity([
            'category_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ] + self::$example);
        $this->assertFalse($this->Table->save($Entity));
        $this->assertEquals(['category_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $Entity->getErrors());
    }

    /**
     * @return void
     * @test
     * @uses \MeCms\Model\Table\PagesTable::_initializeSchema()
     * @uses \MeCms\Model\Table\PostsTable::_initializeSchema()
     */
    public function testInitializeSchema(): void
    {
        $this->assertEquals('jsonEntity', $this->Table->getSchema()->getColumnType('preview'));
    }

    /**
     * @return void
     * @test
     * @uses \MeCms\Model\Table\PagesTable::afterDelete()
     * @uses \MeCms\Model\Table\PagesTable::afterSave()
     * @uses \MeCms\Model\Table\PostsTable::afterDelete()
     * @uses \MeCms\Model\Table\PostsTable::afterSave()
     */
    public function testAfterDeleteAndAfterSave(): void
    {
        $Entity = $this->Table->newEmptyEntity();

        /** @var (\MeCms\Model\Table\PagesTable|\MeCms\Model\Table\PostsTable)&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('MeCms. ' . $this->Table->getAlias(), ['clearCache', 'getPreviewSize', 'setNextToBePublished']);
        $Table->expects($this->exactly(2))->method('clearCache');
        $Table->expects($this->exactly(2))->method('setNextToBePublished');
        $Table->dispatchEvent('Model.afterDelete', [$Entity, new ArrayObject()]);
        $Table->dispatchEvent('Model.afterSave', [$Entity, new ArrayObject()]);
    }

    /**
     * @return void
     * @test
     * @uses \MeCms\Model\Table\PagesTable::beforeSave()
     * @uses \MeCms\Model\Table\PostsTable::beforeSave()
     */
    public function testBeforeSave(): void
    {
        /** @var (\MeCms\Model\Table\PagesTable|\MeCms\Model\Table\PostsTable)&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('MeCms. ' . $this->Table->getAlias(), ['clearCache', 'getPreviewSize', 'setNextToBePublished']);
        $Table->method('getPreviewSize')->willReturn([400, 300]);

        //Tries with a text without images or videos
        $Entity = $Table->newEntity(self::$example);
        $Table->dispatchEvent('Model.beforeSave', [$Entity, new ArrayObject()]);
        $this->assertTrue($Entity->get('preview')->isEmpty());

        //Tries with a text with an image
        $Entity = $Table->newEntity(['text' => '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg\' />'] + self::$example);
        $Table->dispatchEvent('Model.beforeSave', [$Entity, new ArrayObject()]);
        $this->assertCount(1, $Entity->get('preview'));
        $first = $Entity->get('preview')->first();
        $this->assertMatchesRegularExpression('/^http:\/\/localhost\/thumb\/[A-z\d]+/', $first['url']);
        $this->assertSame(400, $first['width']);
        $this->assertSame(300, $first['height']);
    }

    /**
     * @return void
     * @test
     * @uses \MeCms\Model\Table\PagesTable::initialize()
     * @uses \MeCms\Model\Table\PostsTable::initialize()
     */
    public function testInitialize(): void
    {
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertEquals('Categories', $this->Table->Categories->getAlias());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);
    }

    /**
     * @return void
     * @test
     * @uses \MeCms\Model\Table\PagesTable::find()
     * @uses \MeCms\Model\Table\PostsTable::find()
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
