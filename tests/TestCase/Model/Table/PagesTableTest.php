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
 */
namespace MeCms\Test\TestCase\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

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
     * @var array
     */
    protected $example;

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

        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');

        $this->example = [
            'category_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];

        Cache::clear(false, $this->Pages->cache);
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
     * Test for `_initializeSchema()` method
     * @test
     */
    public function testInitializeSchema()
    {
        $this->assertEquals('json', $this->Pages->getSchema()->columnType('preview'));
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $this->Pages = $this->getMockForModel($this->Pages->getRegistryAlias(), ['setNextToBePublished']);
        $this->Pages->expects($this->once())->method('setNextToBePublished');
        $this->Pages->afterDelete(new Event(null), new Entity, new ArrayObject);
    }

    /**
     * Test for `afterSave()` method
     * @test
     */
    public function testAfterSave()
    {
        $this->Pages = $this->getMockForModel($this->Pages->getRegistryAlias(), ['setNextToBePublished']);
        $this->Pages->expects($this->once())->method('setNextToBePublished');
        $this->Pages->afterSave(new Event(null), new Entity, new ArrayObject);
    }

    /**
     * Test for `beforeSave()` method
     * @test
     */
    public function testBeforeSave()
    {
        $this->Pages = $this->getMockForModel($this->Pages->getRegistryAlias(), ['getPreviewSize']);
        $this->Pages->method('getPreviewSize')->will($this->returnValue([400, 300]));

        //Tries with a text without images or videos
        $entity = $this->Pages->newEntity($this->example);
        $this->assertNotEmpty($this->Pages->save($entity));
        $this->assertNull($entity->preview);

        $this->Pages->delete($entity);

        //Tries with a text with an image
        $this->example['text'] = '<img src=\'' . WWW_ROOT . 'img' . DS . 'image.jpg' . '\' />';
        $entity = $this->Pages->newEntity($this->example);
        $this->assertNotEmpty($this->Pages->save($entity));
        $this->assertEquals(['preview', 'width', 'height'], array_keys($entity->preview));
        $this->assertRegExp('/^http:\/\/localhost\/thumb\/[A-z0-9]+/', $entity->preview['preview']);
        $this->assertEquals(400, $entity->preview['width']);
        $this->assertEquals(300, $entity->preview['height']);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $entity = $this->Pages->newEntity($this->example);
        $this->assertNotEmpty($this->Pages->save($entity));

        //Saves again the same entity
        $entity = $this->Pages->newEntity($this->example);
        $this->assertFalse($this->Pages->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => 'This value is already used'],
            'title' => ['_isUnique' => 'This value is already used'],
        ], $entity->getErrors());

        $entity = $this->Pages->newEntity([
            'category_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ]);
        $this->assertFalse($this->Pages->save($entity));
        $this->assertEquals([
            'category_id' => ['_existsIn' => 'You have to select a valid option'],
        ], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('pages', $this->Pages->getTable());
        $this->assertEquals('title', $this->Pages->getDisplayField());
        $this->assertEquals('id', $this->Pages->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Pages->Categories);
        $this->assertEquals('category_id', $this->Pages->Categories->getForeignKey());
        $this->assertEquals('INNER', $this->Pages->Categories->getJoinType());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->Pages->Categories->className());
        $this->assertInstanceOf('MeCms\Model\Table\PagesCategoriesTable', $this->Pages->Categories->getTarget());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->Pages->Categories->getTarget()->getRegistryAlias());
        $this->assertEquals('Categories', $this->Pages->Categories->getAlias());

        $this->assertTrue($this->Pages->hasBehavior('Timestamp'));
        $this->assertTrue($this->Pages->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\PageValidator', $this->Pages->validator());
    }

    /**
     * Test for the `belongsTo` association with `PagesCategories`
     * @test
     */
    public function testBelongsToPagesCategories()
    {
        $page = $this->Pages->findById(1)->contain(['Categories'])->first();

        $this->assertNotEmpty($page->category);
        $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $page->category);
        $this->assertEquals(4, $page->category->id);
    }

    /**
     * Test for `find()` method
     * @test
     */
    public function testFind()
    {
        //Writes `next_to_be_published` and some data on cache
        Cache::write('next_to_be_published', time() - 3600, $this->Pages->cache);
        Cache::write('someData', 'someValue', $this->Pages->cache);

        //The cache will now be cleared
        $this->Pages->find();

        $this->assertEmpty(Cache::read('next_to_be_published', $this->Pages->cache));
        $this->assertEmpty(Cache::read('someData', $this->Pages->cache));
    }
}
