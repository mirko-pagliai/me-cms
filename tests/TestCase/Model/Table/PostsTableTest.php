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

use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\PostsTag;
use MeCms\Model\Entity\Tag;
use MeCms\Model\Validation\PostValidator;
use MeCms\TestSuite\PostsAndPagesTablesTestCase;
use ReflectionFunction;
use Tools\Exception\KeyNotExistsException;

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
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
        'plugin.MeCms.Users',
    ];

    /**
     * Called once before test methods in a case are started
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        self::$example += ['user_id' => 1, 'tags_as_string' => 'first tag, second tag'];
    }

    /**
     * Test for `beforeMarshal()` method
     * @test
     */
    public function testBeforeMarshal()
    {
        $this->loadFixtures();
        $tags = $this->Table->newEntity(self::$example)->tags;
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        $this->assertEquals(['first tag', 'second tag'], Hash::extract($tags, '{n}.tag'));

        //In this case, the `dog` tag already exists
        self::$example['tags_as_string'] = 'first tag, dog';
        $tags = $this->Table->newEntity(self::$example)->tags;
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        $this->assertEquals([2], Hash::extract($tags, '{n}.id'));
        $this->assertEquals(['first tag', 'dog'], Hash::extract($tags, '{n}.tag'));
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        parent::testBuildRules();

        $entity = $this->Table->newEntity([
            'title' => 'My title 2',
            'slug' => 'my-slug-2',
            'user_id' => 999,
        ] + self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['user_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        parent::testInitialize();

        $this->assertEquals('posts', $this->Table->getTable());

        $this->assertBelongsTo($this->Table->Users);
        $this->assertEquals('user_id', $this->Table->Users->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Users->getJoinType());
        $this->assertEquals('MeCms.Users', $this->Table->Users->getClassName());

        $this->assertBelongsToMany($this->Table->Tags);
        $this->assertEquals('post_id', $this->Table->Tags->getForeignKey());
        $this->assertEquals('tag_id', $this->Table->Tags->getTargetForeignKey());
        $this->assertEquals('posts_tags', $this->Table->Tags->junction()->getTable());
        $this->assertEquals('MeCms.Tags', $this->Table->Tags->getClassName());
        $this->assertEquals('MeCms.PostsTags', $this->Table->Tags->getThrough());

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
        $this->assertContainsOnlyInstancesOf(Tag::class, $tags);
        foreach ($tags as $tag) {
            $this->assertInstanceOf(PostsTag::class, $tag->_joinData);
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
        $this->assertStringStartsWith('SELECT Posts.id AS `Posts__id`, Posts.title AS `Posts__title`, Posts.preview AS `Posts__preview`, Posts.subtitle AS `Posts__subtitle`, Posts.slug AS `Posts__slug`, Posts.text AS `Posts__text`, Posts.enable_comments AS `Posts__enable_comments`, Posts.created AS `Posts__created`, Categories.title AS `Categories__title`, Categories.slug AS `Categories__slug`, Users.id AS `Users__id`, Users.first_name AS `Users__first_name`, Users.last_name AS `Users__last_name`', $sql);
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
        $this->assertEquals($relatedPosts, Cache::read('related_2_posts_for_1', $this->Table->getCacheName()));

        $this->assertContainsOnlyInstancesOf(Post::class, $relatedPosts);
        foreach ($relatedPosts as $related) {
            $this->assertTrue($related->has(['id', 'title', 'slug', 'text']));
        }

        //Gets related posts with image
        $related = $this->Table->getRelated($post, 2, true);
        $this->assertCount(1, $related);
        $this->assertEquals($related, Cache::read('related_2_posts_for_1_with_images', $this->Table->getCacheName()));
        $firstRelated = array_value_first($related);
        $this->assertInstanceOf(Post::class, $firstRelated);
        $this->assertEquals(2, $firstRelated->id);
        $this->assertNotEmpty($firstRelated->title);
        $this->assertNotEmpty($firstRelated->slug);
        $this->assertContains('<img src="image.jpg" />Text of the second post', $firstRelated->text);
        $this->assertCount(1, $firstRelated->preview);
        $this->assertEquals('image.jpg', array_value_first($firstRelated->preview)->url);
        $this->assertEquals(400, array_value_first($firstRelated->preview)->width);
        $this->assertEquals(400, array_value_first($firstRelated->preview)->height);

        //This post has no tags
        $post = $this->Table->findById(4)->contain('Tags')->first();
        $this->assertEquals([], $post->tags);
        $this->assertEquals([], $this->Table->getRelated($post));
        $this->assertEquals([], Cache::read('related_5_posts_for_4_with_images', $this->Table->getCacheName()));

        //This post has one tag, but this is not related to any other post
        $post = $this->Table->findById(5)->contain('Tags')->first();
        $this->assertCount(1, $post->tags);
        $this->assertEquals([], $this->Table->getRelated($post));
        $this->assertEquals([], Cache::read('related_5_posts_for_5_with_images', $this->Table->getCacheName()));

        //With an entity with no `tags` property
        $this->expectException(KeyNotExistsException::class);
        $this->expectExceptionMessage('ID or tags of the post are missing');
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
