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
use MeTools\TestSuite\TestCase;

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

        $this->PostsCategories = TableRegistry::get(ME_CMS . '.PostsCategories');

        Cache::clear(false, $this->PostsCategories->cache);
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
        $example = ['title' => 'My title', 'slug' => 'my-slug'];

        $entity = $this->PostsCategories->newEntity($example);
        $this->assertNotEmpty($this->PostsCategories->save($entity));

        //Saves again the same entity
        $entity = $this->PostsCategories->newEntity($example);
        $this->assertFalse($this->PostsCategories->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());

        $entity = $this->PostsCategories->newEntity([
            'parent_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
        ]);
        $this->assertFalse($this->PostsCategories->save($entity));
        $this->assertEquals([
            'parent_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION],
        ], $entity->getErrors());
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
        $this->assertEquals(ME_CMS . '.PostsCategories', $this->PostsCategories->Parents->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PostsCategories->Childs);
        $this->assertEquals('parent_id', $this->PostsCategories->Childs->getForeignKey());
        $this->assertEquals(ME_CMS . '.PostsCategories', $this->PostsCategories->Childs->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->PostsCategories->Posts);
        $this->assertEquals('category_id', $this->PostsCategories->Posts->getForeignKey());
        $this->assertEquals(ME_CMS . '.Posts', $this->PostsCategories->Posts->className());

        $this->assertTrue($this->PostsCategories->hasBehavior('Timestamp'));
        $this->assertTrue($this->PostsCategories->hasBehavior('Tree'));

        $this->assertInstanceOf('MeCms\Model\Validation\PostsCategoryValidator', [$this->PostsCategories->getValidator()]);
    }
    /**
     * Test for the `belongsTo` association with `PostsCategories` parents
     * @test
     */
    public function testBelongsToParents()
    {
        $category = $this->PostsCategories->findById(4)->contain('Parents')->first();

        $this->assertNotEmpty($category->parent);
        $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $category->parent);
        $this->assertEquals(3, $category->parent->id);

        $category = $this->PostsCategories->findById($category->parent->id)->contain('Parents')->first();

        $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $category->parent);
        $this->assertEquals(1, $category->parent->id);
    }

    /**
     * Test for the `hasMany` association with `PostsCategories` childs
     * @test
     */
    public function testHasManyChilds()
    {
        $category = $this->PostsCategories->findById(1)->contain('Childs')->first();

        $this->assertNotEmpty($category->childs);

        foreach ($category->childs as $children) {
            $this->assertInstanceOf('MeCms\Model\Entity\PostsCategory', $children);
            $this->assertEquals(1, $children->parent_id);

            $category = $this->PostsCategories->findById($children->id)->contain('Childs')->first();

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
        $category = $this->PostsCategories->find()->contain('Posts')->first();

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
        $this->assertStringEndsWith('FROM posts_categories Categories INNER JOIN posts Posts ON (Posts.active = :c0 AND Posts.created <= :c1 AND Categories.id = (Posts.category_id))', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertInstanceOf('Cake\I18n\Time', $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->_matchingData['Posts']->active &&
                !$entity->_matchingData['Posts']->created->isFuture());
        }
    }
}
