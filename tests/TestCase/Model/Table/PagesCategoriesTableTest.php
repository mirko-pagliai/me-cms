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
use Cake\TestSuite\TestCase;

/**
 * PagesCategoriesTableTest class
 */
class PagesCategoriesTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PagesCategoriesTable
     */
    protected $PagesCategories;

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

        $this->PagesCategories = TableRegistry::get(ME_CMS . '.PagesCategories');

        Cache::clear(false, $this->PagesCategories->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PagesCategories);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('pages', $this->PagesCategories->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = [
            'title' => 'My title',
            'slug' => 'my-slug',
        ];

        $entity = $this->PagesCategories->newEntity($example);
        $this->assertNotEmpty($this->PagesCategories->save($entity));

        //Saves again the same entity
        $entity = $this->PagesCategories->newEntity($example);
        $this->assertFalse($this->PagesCategories->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => 'This value is already used'],
            'title' => ['_isUnique' => 'This value is already used'],
        ], $entity->getErrors());

        $entity = $this->PagesCategories->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->PagesCategories->save($entity));
        $this->assertEquals(['parent_id' => ['_existsIn' => 'You have to select a valid option']], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('pages_categories', $this->PagesCategories->getTable());
        $this->assertEquals('title', $this->PagesCategories->getDisplayField());
        $this->assertEquals('id', $this->PagesCategories->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->PagesCategories->Parents);
        $this->assertEquals('parent_id', $this->PagesCategories->Parents->getForeignKey());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->PagesCategories->Parents->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PagesCategories->Childs);
        $this->assertEquals('parent_id', $this->PagesCategories->Childs->getForeignKey());
        $this->assertEquals(ME_CMS . '.PagesCategories', $this->PagesCategories->Childs->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PagesCategories->Pages);
        $this->assertEquals('category_id', $this->PagesCategories->Pages->getForeignKey());
        $this->assertEquals(ME_CMS . '.Pages', $this->PagesCategories->Pages->className());

        $this->assertTrue($this->PagesCategories->hasBehavior('Timestamp'));
        $this->assertTrue($this->PagesCategories->hasBehavior('Tree'));

        $this->assertInstanceOf('MeCms\Model\Validation\PagesCategoryValidator', $this->PagesCategories->validator());
    }

    /**
     * Test for the `belongsTo` association with `PagesCategories` parents
     * @test
     */
    public function testBelongsToParents()
    {
        $category = $this->PagesCategories->findById(4)->contain(['Parents'])->first();

        $this->assertNotEmpty($category->parent);

        $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $category->parent);
        $this->assertEquals(3, $category->parent->id);

        $category = $this->PagesCategories->findById($category->parent->id)->contain(['Parents'])->first();

        $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $category->parent);
        $this->assertEquals(1, $category->parent->id);
    }

    /**
     * Test for the `hasMany` association with `PagesCategories` childs
     * @test
     */
    public function testHasManyChilds()
    {
        $category = $this->PagesCategories->findById(1)->contain(['Childs'])->first();

        $this->assertNotEmpty($category->childs);

        foreach ($category->childs as $children) {
            $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $children);
            $this->assertEquals(1, $children->parent_id);

            $category = $this->PagesCategories->findById($children->id)->contain(['Childs'])->first();

            $this->assertNotEmpty($category->childs);

            foreach ($category->childs as $children) {
                $this->assertInstanceOf('MeCms\Model\Entity\PagesCategory', $children);
                $this->assertEquals(3, $children->parent_id);
            }
        }
    }

    /**
     * Test for the `hasMany` association with `Pages`
     * @test
     */
    public function testHasManyPages()
    {
        $category = $this->PagesCategories->find()->contain(['Pages'])->first();

        $this->assertNotEmpty($category->pages);

        foreach ($category->pages as $page) {
            $this->assertInstanceOf('MeCms\Model\Entity\Page', $page);
            $this->assertEquals($category->id, $page->category_id);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->PagesCategories->find('active');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM pages_categories Categories INNER JOIN pages Pages ON (Pages.active = :c0 AND Pages.created <= :c1 AND Categories.id = (Pages.category_id))', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf('Cake\I18n\Time', $query->valueBinder()->bindings()[':c1']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->_matchingData['Pages']->active);
            $this->assertTrue(!$entity->_matchingData['Pages']->created->isFuture());
        }
    }
}
