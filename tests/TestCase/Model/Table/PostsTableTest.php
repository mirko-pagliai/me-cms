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
use Cake\I18n\Time;
use Cake\ORM\Query;
use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\Tag;
use MeCms\Model\Table\PostsCategoriesTable;
use MeCms\Model\Validation\PostValidator;
use MeCms\TestSuite\PostsAndPagesTablesTestCase;
use ReflectionFunction;

/**
 * PostsTableTest class
 */
class PostsTableTest extends PostsAndPagesTablesTestCase
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
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
        'plugin.me_cms.PostsTags',
        'plugin.me_cms.Tags',
        'plugin.me_cms.Users',
    ];

    /**
     * Called once before test methods in a case are started
     */
    public static function setupBeforeClass()
    {
        self::$example += [
            'user_id' => 1,
            'tags_as_string' => 'first tag, second tag',
        ];
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('posts', $this->Table->cache);
    }

    /**
     * Test for `beforeMarshal()` method
     * @test
     */
    public function testBeforeMarshal()
    {
        $this->loadFixtures();

        $tags = $this->Table->newEntity(self::$example)->tags;
        $this->assertContainsInstanceOf(Tag::class, $tags);
        $this->assertEquals('first tag', $tags[0]->tag);
        $this->assertEquals('second tag', $tags[1]->tag);

        //In this case, the `dog` tag already exists
        self::$example['tags_as_string'] = 'first tag, dog';
        $tags = $this->Table->newEntity(self::$example)->tags;
        $this->assertContainsInstanceOf(Tag::class, $tags);
        $this->assertEmpty($tags[0]->id);
        $this->assertEquals('first tag', $tags[0]->tag);
        $this->assertEquals(2, $tags[1]->id);
        $this->assertEquals('dog', $tags[1]->tag);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $this->loadFixtures();

        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals([
            'slug' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'title' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());

        $entity = $this->Table->newEntity([
            'category_id' => 999,
            'user_id' => 999,
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'text' => 'My text',
        ]);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals([
            'category_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION],
            'user_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION],
        ], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('posts', $this->Table->getTable());
        $this->assertEquals('title', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Categories);
        $this->assertEquals('category_id', $this->Table->Categories->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Categories->getJoinType());
        $this->assertEquals(ME_CMS . '.PostsCategories', $this->Table->Categories->className());
        $this->assertInstanceOf(PostsCategoriesTable::class, $this->Table->Categories->getTarget());
        $this->assertEquals(ME_CMS . '.PostsCategories', $this->Table->Categories->getTarget()->getRegistryAlias());
        $this->assertEquals('Categories', $this->Table->Categories->getAlias());

        $this->assertBelongsTo($this->Table->Users);
        $this->assertEquals('user_id', $this->Table->Users->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Users->getJoinType());
        $this->assertEquals(ME_CMS . '.Users', $this->Table->Users->className());

        $this->assertBelongsToMany($this->Table->Tags);
        $this->assertEquals('post_id', $this->Table->Tags->getForeignKey());
        $this->assertEquals('tag_id', $this->Table->Tags->getTargetForeignKey());
        $this->assertEquals('posts_tags', $this->Table->Tags->junction()->getTable());
        $this->assertEquals(ME_CMS . '.Tags', $this->Table->Tags->className());
        $this->assertEquals(ME_CMS . '.PostsTags', $this->Table->Tags->getThrough());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);

        $this->assertInstanceOf(PostValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for the `belongsToMany` association with `Tags`
     * @test
     */
    public function testBelongsToManyTags()
    {
        $this->loadFixtures();

        $tags = $this->Table->findById(2)->contain('Tags')->extract('tags')->first();
        $this->assertContainsInstanceOf(Tag::class, $tags);

        foreach ($tags as $tag) {
            $this->assertInstanceOf('MeCms\Model\Entity\PostsTag', $tag->_joinData);
            $this->assertEquals(2, $tag->_joinData->post_id);
        }
    }

    /**
     * Test for `findForIndex()` method
     * @test
     */
    public function testFindForIndex()
    {
        $this->loadFixtures();

        $query = $this->Table->find('forIndex');
        $sql = $query->sql();

        $this->assertEquals(['title', 'slug'], $query->getContain()['Categories']['fields']);
        $this->assertTrue((new ReflectionFunction($query->getContain()['Tags']['queryBuilder']))->isClosure());
        $this->assertEquals(['id', 'first_name', 'last_name'], $query->getContain()['Users']['fields']);

        $this->assertStringStartsWith('SELECT Posts.id AS `Posts__id`, Posts.title AS `Posts__title`, Posts.preview AS `Posts__preview`, Posts.subtitle AS `Posts__subtitle`, Posts.slug AS `Posts__slug`, Posts.text AS `Posts__text`, Posts.created AS `Posts__created`', $sql);
        $this->assertStringEndsWith('ORDER BY Posts.created DESC', $sql);
    }

    /**
     * Test for `getRelated()` method
     * @test
     */
    public function testGetRelated()
    {
        $this->loadFixtures();

        //Gets a post from which to search the related posts.
        //Note that the tags of this post are sorted in ascending order
        $post = $this->Table->findById(1)->contain('Tags', function (Query $q) {
            return $q->order(['post_count' => 'ASC']);
        })->first();
        $this->assertNotEmpty($post->tags);

        $relatedPosts = $this->Table->getRelated($post, 2, false);

        $this->assertCount(2, $relatedPosts);
        $this->assertEquals($relatedPosts, Cache::read('related_2_posts_for_1', $this->Table->cache));

        foreach ($relatedPosts as $related) {
            $this->assertTrue($related->has(['id', 'title', 'slug', 'text']));
            $this->assertInstanceOf(Post::class, $related);
        }

        //Gets related posts with image
        $related = $this->Table->getRelated($post, 2, true);

        $this->assertCount(1, $related);
        $this->assertEquals($related, Cache::read('related_2_posts_for_1_with_images', $this->Table->cache));

        $this->assertInstanceOf(Post::class, $related[0]);
        $this->assertEquals(2, $related[0]->id);
        $this->assertNotEmpty($related[0]->title);
        $this->assertNotEmpty($related[0]->slug);
        $this->assertContains('<img src="image.jpg" />Text of the second post', $related[0]->text);
        $this->assertCount(1, $related[0]->preview);
        $this->assertEquals('image.jpg', $related[0]->preview[0]->url);
        $this->assertEquals(400, $related[0]->preview[0]->width);
        $this->assertEquals(400, $related[0]->preview[0]->height);

        //This post has no tags
        $post = $this->Table->findById(4)->contain('Tags')->first();
        $this->assertEquals([], $post->tags);
        $this->assertEquals([], $this->Table->getRelated($post));
        $this->assertEquals([], Cache::read('related_5_posts_for_4_with_images', $this->Table->cache));

        //This post has one tag, but this is not related to any other post
        $post = $this->Table->findById(5)->contain('Tags')->first();
        $this->assertCount(1, $post->tags);
        $this->assertEquals([], $this->Table->getRelated($post));
        $this->assertEquals([], Cache::read('related_5_posts_for_5_with_images', $this->Table->cache));
    }

    /**
     * Test for `getRelated()` method, with an entity with no `tags` property
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage ID or tags of the post are missing
     */
    public function testGetRelatedNoTagsProperty()
    {
        $this->loadFixtures();

        $this->Table->getRelated($this->Table->get(1));
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $this->loadFixtures();

        $query = $this->Table->queryFromFilter($this->Table->find(), ['tag' => 'test']);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.tag = :c0 AND Tags.id = (PostsTags.tag_id))', $query->sql());
        $this->assertEquals('test', $query->getValueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `queryForRelated()` method
     * @test
     */
    public function testQueryForRelated()
    {
        $this->loadFixtures();

        $query = $this->Table->queryForRelated(4, true);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.id = :c0 AND Tags.id = (PostsTags.tag_id)) WHERE (Posts.active = :c1 AND Posts.created <= :c2 AND Posts.preview not in (:c3,:c4))', $query->sql());

        $this->assertEquals(4, $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(true, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertInstanceof(Time::class, $query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertEquals(null, $query->getValueBinder()->bindings()[':c3']['value']);
        $this->assertEquals([], $query->getValueBinder()->bindings()[':c4']['value']);

        $query = $this->Table->queryForRelated(4, false);
        $this->assertStringEndsWith('FROM posts Posts INNER JOIN posts_tags PostsTags ON Posts.id = (PostsTags.post_id) INNER JOIN tags Tags ON (Tags.id = :c0 AND Tags.id = (PostsTags.tag_id)) WHERE (Posts.active = :c1 AND Posts.created <= :c2)', $query->sql());
        $this->assertEquals(4, $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(true, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertInstanceof(Time::class, $query->getValueBinder()->bindings()[':c2']['value']);
    }
}
