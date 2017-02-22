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
        ], $entity->errors());

        $entity = $this->PostsCategories->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->PostsCategories->save($entity));
        $this->assertEquals(['parent_id' => ['_existsIn' => 'You have to select a valid option']], $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('posts_categories', $this->PostsCategories->table());
        $this->assertEquals('title', $this->PostsCategories->displayField());
        $this->assertEquals('id', $this->PostsCategories->primaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->PostsCategories->Parents);
        $this->assertEquals('parent_id', $this->PostsCategories->Parents->foreignKey());
        $this->assertEquals('MeCms.PostsCategories', $this->PostsCategories->Parents->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PostsCategories->Childs);
        $this->assertEquals('parent_id', $this->PostsCategories->Childs->foreignKey());
        $this->assertEquals('MeCms.PostsCategories', $this->PostsCategories->Childs->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PostsCategories->Posts);
        $this->assertEquals('category_id', $this->PostsCategories->Posts->foreignKey());
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
        $category = $this->PostsCategories->findById(4)->contain(['Posts'])->first();

        $this->assertNotEmpty($category->posts);

        foreach ($category->posts as $post) {
            $this->assertInstanceOf('MeCms\Model\Entity\Post', $post);
            $this->assertEquals(4, $post->category_id);
        }
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->PostsCategories->hasFinder('active'));

        $query = $this->PostsCategories->find('active');
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals('SELECT PostsCategories.id AS `PostsCategories__id`, PostsCategories.parent_id AS `PostsCategories__parent_id`, PostsCategories.lft AS `PostsCategories__lft`, PostsCategories.rght AS `PostsCategories__rght`, PostsCategories.title AS `PostsCategories__title`, PostsCategories.slug AS `PostsCategories__slug`, PostsCategories.description AS `PostsCategories__description`, PostsCategories.post_count AS `PostsCategories__post_count`, PostsCategories.created AS `PostsCategories__created`, PostsCategories.modified AS `PostsCategories__modified` FROM posts_categories PostsCategories WHERE PostsCategories.post_count > :c0', $query->sql());

        $this->assertEquals(0, $query->valueBinder()->bindings()[':c0']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $category) {
            $this->assertNotEquals(0, $category->post_count);
        }
    }
}
