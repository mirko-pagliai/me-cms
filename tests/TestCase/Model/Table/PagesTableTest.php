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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\PostsAndPagesTablesTestCase;

/**
 * PagesTableTest class
 */
class PagesTableTest extends PostsAndPagesTablesTestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Table;

    /**
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
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

        $this->Table = TableRegistry::get(ME_CMS . '.Pages');

        $this->example = [
            'category_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];

        Cache::clear(false, $this->Table->cache);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('pages', $this->Table->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $entity = $this->Table->newEntity($this->example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity($this->example);
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
        ]);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['category_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('pages', $this->Table->getTable());
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Table->Categories);
        $this->assertEquals('category_id', $this->Table->Categories->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Categories->getJoinType());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->Table->Categories->className());
        $this->assertInstanceOf('MeCms\Model\Table\PagesCategoriesTable', $this->Table->Categories->getTarget());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->Table->Categories->getTarget()->getRegistryAlias());
        $this->assertEquals('Categories', $this->Table->Categories->getAlias());

        $this->assertTrue($this->Table->hasBehavior('Timestamp'));
        $this->assertTrue($this->Table->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\PageValidator', $this->Table->getValidator());
    }

    /**
     * Test for the `belongsTo` association with `PagesCategories`
     * @test
     */
    public function testBelongsToPagesCategories()
    {
        $page = $this->Table->findById(1)->contain('Categories')->first();

        $this->assertNotEmpty($page->category);
        $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $page->category);
        $this->assertEquals(4, $page->category->id);
    }
}
