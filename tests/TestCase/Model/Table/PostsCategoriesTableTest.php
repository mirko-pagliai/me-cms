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
 * PostsCategoriesTableTest class
 */
class PostsCategoriesTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsCategoriesTable
     */
    protected $PostsCategories;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
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

        $this->PostsCategories = TableRegistry::get('MeCms.PostsCategories');

        Cache::clear(false, $this->PostsCategories->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PostsCategories);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('posts', $this->PostsCategories->cache);
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

        $entity = $this->PostsCategories->newEntity($example);
        $this->assertNotEmpty($this->PostsCategories->save($entity));

        //Saves again the same entity
        $entity = $this->PostsCategories->newEntity($example);
        $this->assertFalse($this->PostsCategories->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => 'This value is already used'],
            'title' => ['_isUnique' => 'This value is already used'],
        ], $entity->getErrors());

        $entity = $this->PostsCategories->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->PostsCategories->save($entity));
        $this->assertEquals(['parent_id' => ['_existsIn' => 'You have to select a valid option']], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('posts_categories', $this->PostsCategories->getTable());
        $this->assertEquals('title', $this->PostsCategories->getDisplayField());
        $this->assertEquals('id', $this->PostsCategories->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->PostsCategories->Parents);
        $this->assertEquals('parent_id', $this->PostsCategories->Parents->getForeignKey());
        $this->assertEquals('MeCms.PostsCategories', $this->PostsCategories->Parents->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PostsCategories->Childs);
        $this->assertEquals('parent_id', $this->PostsCategories->Childs->getForeignKey());
        $this->assertEquals('MeCms.PostsCategories', $this->PostsCategories->Childs->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PostsCategories->Posts);
        $this->assertEquals('category_id', $this->PostsCategories->Posts->getForeignKey());
        $this->assertEquals('MeCms.Posts', $this->PostsCategories->Posts->className());

        $this->assertTrue($this->PostsCategories->hasBehavior('Timestamp'));
        $this->assertTrue($this->PostsCategories->hasBehavior('Tree'));

        $this->assertInstanceOf('MeCms\Model\Validation\PostsCategoryValidator', $this->PostsCategories->validator());
    }
    /**
     * Test for the `belongsTo` association with `PostsCategories` parents
     * @test
     */
    public function testBelongsToParents()
    {
        $category = $this->PostsCategories->findById(4)->contain(['Parents'])->first();

        $this->assertNotEmpty($category->parent);

        $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $category->parent);
        $this->assertEquals(3, $category->parent->id);

        $category = $this->PostsCategories->findById($category->parent->id)->contain(['Parents'])->first();

        $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $category->parent);
        $this->assertEquals(1, $category->parent->id);
    }

    /**
     * Test for the `hasMany` association with `PostsCategories` childs
     * @test
     */
    public function testHasManyChilds()
    {
        $category = $this->PostsCategories->findById(1)->contain(['Childs'])->first();

        $this->assertNotEmpty($category->childs);

        foreach ($category->childs as $children) {
            $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $children);
            $this->assertEquals(1, $children->parent_id);

            $category = $this->PostsCategories->findById($children->id)->contain(['Childs'])->first();

            $this->assertNotEmpty($category->childs);

            foreach ($category->childs as $children) {
                $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $children);
                $this->assertEquals(3, $children->parent_id);
            }
        }
    }

    /**
     * Test for the `hasMany` association with `Posts`
     * @test
     */
    public function testHasManyPosts()
    {
        $category = $this->PostsCategories->find()->contain(['Posts'])->first();

        $this->assertNotEmpty($category->posts);

        foreach ($category->posts as $post) {
            $this->assertInstanceOf('MeCms\Model\Entity\Post', $post);
            $this->assertEquals($category->id, $post->category_id);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->PostsCategories->find('active');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertStringEndsWith('FROM posts_categories Categories INNER JOIN posts Posts ON (Posts.active = :c0 AND Posts.created <= :c1 AND Categories.id = (Posts.category_id))', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf('Cake\I18n\Time', $query->valueBinder()->bindings()[':c1']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->_matchingData['Posts']->active);
            $this->assertTrue(!$entity->_matchingData['Posts']->created->isFuture());
        }
    }
}
