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
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('pages_categories', $this->PagesCategories->table());
        $this->assertEquals('title', $this->PagesCategories->displayField());
        $this->assertEquals('id', $this->PagesCategories->primaryKey());

        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->PagesCategories->Parents));
        $this->assertEquals('parent_id', $this->PagesCategories->Parents->foreignKey());
        $this->assertEquals('MeCms.PagesCategories', $this->PagesCategories->Parents->className());

        $this->assertEquals('Cake\ORM\Association\HasMany', get_class($this->PagesCategories->Childs));
        $this->assertEquals('parent_id', $this->PagesCategories->Childs->foreignKey());
        $this->assertEquals('MeCms.PagesCategories', $this->PagesCategories->Childs->className());

        $this->assertEquals('Cake\ORM\Association\HasMany', get_class($this->PagesCategories->Pages));
        $this->assertEquals('category_id', $this->PagesCategories->Pages->foreignKey());
        $this->assertEquals('MeCms.Pages', $this->PagesCategories->Pages->className());

        $this->assertTrue($this->PagesCategories->hasBehavior('Timestamp'));
        $this->assertTrue($this->PagesCategories->hasBehavior('Tree'));
    }

    /**
     * Test for the `belongsTo` association with `PagesCategory` parents
     * @test
     */
    public function testBelongsToParents()
    {
        $category = $this->PagesCategories->findById(4)->contain(['Parents'])->first();

        $this->assertNotEmpty($category->parent);

        $this->assertEquals('MeCms\Model\Entity\PagesCategory', get_class($category->parent));
        $this->assertEquals(3, $category->parent->id);

        $category = $this->PagesCategories->findById($category->parent->id)->contain(['Parents'])->first();

        $this->assertEquals('MeCms\Model\Entity\PagesCategory', get_class($category->parent));
        $this->assertEquals(1, $category->parent->id);
    }

    /**
     * Test for the `hasMany` association with `PagesCategory` childs
     * @test
     */
    public function testHasManyChilds()
    {
        $category = $this->PagesCategories->findById(1)->contain(['Childs'])->first();

        $this->assertNotEmpty($category->childs);

        foreach ($category->childs as $children) {
            $this->assertEquals('MeCms\Model\Entity\PagesCategory', get_class($children));
            $this->assertEquals(1, $children->parent_id);

            $category = $this->PagesCategories->findById($children->id)->contain(['Childs'])->first();

            $this->assertNotEmpty($category->childs);

            foreach ($category->childs as $children) {
                $this->assertEquals('MeCms\Model\Entity\PagesCategory', get_class($children));
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
            $this->assertEquals('MeCms\Model\Entity\Page', get_class($page));
            $this->assertEquals(4, $page->category_id);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->PagesCategories->hasFinder('active'));

        $query = $this->PagesCategories->find('active');

        $this->assertEquals(3, $query->count());

        foreach ($query->toArray() as $category) {
            $this->assertNotEquals(0, $category->page_count);
        }
    }

    /**
     * Test for `getList()` method
     * @test
     */
    public function testGetList()
    {
        $categories = $this->PagesCategories->getList();
        $this->assertEquals([
            2 => 'Another category',
            1 => 'First category',
            3 => 'Sub category',
            4 => 'Sub sub category',
        ], $categories);
    }

    /**
     * Test for `getTreeList()` method
     * @test
     */
    public function testGetTreeList()
    {
        $categories = $this->PagesCategories->getTreeList();
        $this->assertEquals([
            1 => 'First category',
            3 => '—Sub category',
            4 => '——Sub sub category',
            2 => 'Another category'
        ], $categories);
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\PagesCategoryValidator',
            get_class($this->PagesCategories->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
