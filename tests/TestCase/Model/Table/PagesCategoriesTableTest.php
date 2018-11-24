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

use Cake\I18n\Time;
use MeCms\Model\Entity\PagesCategory;
use MeCms\Model\Validation\PagesCategoryValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * PagesCategoriesTableTest class
 */
class PagesCategoriesTableTest extends TableTestCase
{
    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
    ];

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $this->loadFixtures();
        $example = ['title' => 'My title', 'slug' => 'my-slug'];

        $entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity($example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());

        $entity = $this->Table->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['parent_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('Categories', $this->Table->getAlias());
        $this->assertEquals('pages_categories', $this->Table->getTable());
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Parents);
        $this->assertEquals('parent_id', $this->Table->Parents->getForeignKey());
        $this->assertEquals('MeCms.PagesCategories', $this->Table->Parents->className());

        $this->assertHasMany($this->Table->Childs);
        $this->assertEquals('parent_id', $this->Table->Childs->getForeignKey());
        $this->assertEquals('MeCms.PagesCategories', $this->Table->Childs->className());

        $this->assertHasMany($this->Table->Pages);
        $this->assertEquals('category_id', $this->Table->Pages->getForeignKey());
        $this->assertEquals('MeCms.Pages', $this->Table->Pages->className());

        $this->assertHasBehavior(['Timestamp', 'Tree']);

        $this->assertInstanceOf(PagesCategoryValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for the `hasMany` association with `PagesCategories` childs
     * @test
     */
    public function testHasManyChilds()
    {
        $this->loadFixtures();

        $childs = $this->Table->findById(1)->contain('Childs')->extract('childs')->first();
        $this->assertContainsInstanceOf(PagesCategory::class, $childs);

        foreach ($childs as $children) {
            $this->assertEquals(1, $children->parent_id);

            $childs = $this->Table->findById($children->id)->contain('Childs')->extract('childs')->first();
            $this->assertContainsInstanceOf(PagesCategory::class, $childs);

            foreach ($childs as $children) {
                $this->assertEquals(3, $children->parent_id);
            }
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->loadFixtures();

        $query = $this->Table->find('active');
        $this->assertStringEndsWith('FROM pages_categories Categories INNER JOIN pages Pages ON (Pages.active = :c0 AND Pages.created <= :c1 AND Categories.id = (Pages.category_id))', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(Time::class, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query as $entity) {
            $this->assertTrue($entity->_matchingData['Pages']->active &&
                !$entity->_matchingData['Pages']->created->isFuture());
        }
    }
}
