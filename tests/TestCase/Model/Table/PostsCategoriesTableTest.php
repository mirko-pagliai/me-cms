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
use MeCms\Model\Entity\PostsCategory;
use MeCms\Model\Validation\PostsCategoryValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * PostsCategoriesTableTest class
 * @property \MeCms\Model\Table\PostsCategoriesTable $Table
 */
class PostsCategoriesTableTest extends TableTestCase
{
    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
    ];

    /**
     * @test
     * @uses \MeCms\Model\Table\PostsCategoriesTable::buildRules()
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
     * @uses \MeCms\Model\Table\PostsCategoriesTable::initialize()
     */
    public function testInitialize(): void
    {
        $this->assertEquals('posts_categories', $this->Table->getTable());
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Parents);
        $this->assertEquals('parent_id', $this->Table->Parents->getForeignKey());

        $this->assertHasMany($this->Table->Childs);
        $this->assertEquals('parent_id', $this->Table->Childs->getForeignKey());

        $this->assertHasMany($this->Table->Posts);
        $this->assertEquals('category_id', $this->Table->Posts->getForeignKey());

        $this->assertHasBehavior(['Timestamp', 'Tree']);

        $this->assertInstanceOf(PostsCategoryValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for associations
     * @test
     */
    public function testAssociations(): void
    {
        /** @var \MeCms\Model\Entity\PostsCategory $Category */
        $Category = $this->Table->findById(4)->contain('Parents')->first();
        $this->assertNotEmpty($Category->get('parent'));
        $this->assertInstanceOf(PostsCategory::class, $Category->get('parent'));
        $this->assertEquals(3, $Category->get('parent')->get('id'));

        /** @var \MeCms\Model\Entity\PostsCategory $Category */
        $Category = $this->Table->findById($Category->get('parent')->get('id'))->contain('Parents')->first();
        $this->assertInstanceOf(PostsCategory::class, $Category->get('parent'));
        $this->assertEquals(1, $Category->get('parent')->get('id'));

        $childs = $this->Table->find()->contain('Childs')->all()->extract('childs')->first();
        $this->assertContainsOnlyInstancesOf(PostsCategory::class, $childs);

        foreach ($childs as $children) {
            $this->assertEquals(1, $children->get('parent_id'));
            /** @var array<\MeCms\Model\Entity\PostsCategory> $childs */
            $childs = $this->Table->findById($children->get('id'))->contain('Childs')->all()->extract('childs')->first();
            $this->assertContainsOnlyInstancesOf(PostsCategory::class, $childs);
            $this->assertEquals(3, array_value_first($childs)->get('parent_id'));
        }
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\PostsCategoriesTable::findActive()
     */
    public function testFindActive(): void
    {
        $query = $this->Table->find('active');
        $sql = $query->sql();
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf(FrozenTime::class, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertSqlEndsWith('FROM posts_categories PostsCategories INNER JOIN posts Posts ON (Posts.active = :c0 AND Posts.created <= :c1 AND PostsCategories.id = Posts.category_id)', $sql);
    }
}
