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
 */

namespace MeCms\Test\TestCase\Model\Table;

use Cake\I18n\FrozenTime;
use MeCms\Model\Entity\PagesCategory;
use MeCms\Model\Validation\PagesCategoryValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * PagesCategoriesTableTest class
 * @property \MeCms\Model\Table\PagesCategoriesTable $Table
 */
class PagesCategoriesTableTest extends TableTestCase
{
    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * @test
     * @uses \MeCms\Model\Table\PagesCategoriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $example = ['title' => 'My title', 'slug' => 'my-slug'];

        $Entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($this->Table->save($Entity));

        //Saves again the same entity
        $Entity = $this->Table->newEntity($example);
        $this->assertFalse($this->Table->save($Entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $Entity->getErrors());

        $Entity = $this->Table->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->Table->save($Entity));
        $this->assertEquals(['parent_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $Entity->getErrors());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\PagesCategoriesTable::initialize()
     */
    public function testInitialize(): void
    {
        $this->assertEquals('Categories', $this->Table->getAlias());
        $this->assertEquals('pages_categories', $this->Table->getTable());
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Parents);
        $this->assertEquals('parent_id', $this->Table->Parents->getForeignKey());

        $this->assertHasMany($this->Table->Childs);
        $this->assertEquals('parent_id', $this->Table->Childs->getForeignKey());

        $this->assertHasMany($this->Table->Pages);
        $this->assertEquals('category_id', $this->Table->Pages->getForeignKey());

        $this->assertHasBehavior(['Timestamp', 'Tree']);

        $this->assertInstanceOf(PagesCategoryValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for associations
     * @test
     */
    public function testAssociations(): void
    {
        $childs = $this->Table->findById(1)->contain('Childs')->all()->extract('childs')->first();
        $this->assertContainsOnlyInstancesOf(PagesCategory::class, $childs);
        foreach ($childs as $children) {
            $this->assertEquals(1, $children->get('parent_id'));
            /** @var array<\MeCms\Model\Entity\PagesCategory> $childs */
            $childs = $this->Table->findById($children->get('id'))->contain('Childs')->all()->extract('childs')->first();
            $this->assertContainsOnlyInstancesOf(PagesCategory::class, $childs);
            $this->assertEquals(3, array_value_first($childs)->get('parent_id'));
        }
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\PagesCategoriesTable::findActive()
     */
    public function testFindActive(): void
    {
        $query = $this->Table->find('active');
        $sql = $query->sql();
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(FrozenTime::class, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertStringEndsWith('FROM pages_categories Categories INNER JOIN pages Pages ON (Pages.active = :c0 AND Pages.created <= :c1 AND Categories.id = Pages.category_id)', $sql);
    }
}
