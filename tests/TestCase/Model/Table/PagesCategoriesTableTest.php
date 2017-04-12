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

        $this->PagesCategories = TableRegistry::get('MeCms.PagesCategories');

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
        ], $entity->errors());

        $entity = $this->PagesCategories->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->PagesCategories->save($entity));
        $this->assertEquals(['parent_id' => ['_existsIn' => 'You have to select a valid option']], $entity->errors());
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
        $this->assertEquals('MeCms.PagesCategories', $this->PagesCategories->Parents->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PagesCategories->Childs);
        $this->assertEquals('parent_id', $this->PagesCategories->Childs->getForeignKey());
        $this->assertEquals('MeCms.PagesCategories', $this->PagesCategories->Childs->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PagesCategories->Pages);
        $this->assertEquals('category_id', $this->PagesCategories->Pages->getForeignKey());
        $this->assertEquals('MeCms.Pages', $this->PagesCategories->Pages->className());

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
        $category = $this->PagesCategories->findById(4)->contain(['Pages'])->first();

        $this->assertNotEmpty($category->pages);

        foreach ($category->pages as $page) {
            $this->assertInstanceOf('MeCms\Model\Entity\Page', $page);
            $this->assertEquals(4, $page->category_id);
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
        $this->assertStringEndsWith('FROM pages_categories PagesCategories WHERE PagesCategories.page_count > :c0', $query->sql());

        $this->assertEquals(0, $query->valueBinder()->bindings()[':c0']['value']);
    }
}
